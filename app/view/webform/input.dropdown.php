<?php /*
<fusedoc>
	<io>
		<in>
			<boolean name="$editable" />
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$dataFieldName" example="firstName ===> data[firstName]; student.name ===> data[student][name]" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="placeholder" optional="yes" />
				<boolean name="required" />
				<boolean name="readonly" />
				<structure name="options">
					<structure name="~optGroup~" optional="yes">
						<string name="~optValue~" value="~optText~" />
					</structure>
					<string name="~optValue~" value="~optText~" optional="yes" />
				</structure>
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<string name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/ ?>
<div class="webform-input-dropdown input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// field
	if ( !empty($editable) ) :
		?><select
			id="<?php echo $fieldID; ?>"
			name="<?php echo $dataFieldName; ?>"
			class="custom-select <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required'; ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly'; ?>
		><?php
			// empty first item
			?><option value=""><?php if ( !empty($fieldConfig['placeholder']) ) echo $fieldConfig['placeholder']; ?></option><?php
			// user-defined items
			foreach ( $fieldConfig['options'] as $optValue => $optText ) :
				// optgroup
				if ( is_array($optText) ) :
					$optGroupLabel = $optValue;
					$optGroupItems = $optText;
					?><optgroup label="<?php echo $optGroupLabel; ?>"><?php
						// optgroup-option
						foreach ( $optGroupItems as $optValue => $optText ) :
							include F::appPath('view/webform/input.dropdown.item.php');
						endforeach;
					?></optgroup><?php
				// option
				else :
					include F::appPath('view/webform/input.dropdown.item.php');
				endif;
			endforeach;
		?></select><?php
	// readonly
	elseif ( $fieldValue !== '' ) :
		$flatten = array();
		foreach ( $fieldConfig['options'] as $optValue => $optText ) :
			if ( is_array($optText) ) $flatten = array_merge($flatten, $optText);
			else $flatten[$optValue] = $optText;
		endforeach;
		?><div class="form-control-plaintext text-primary"><strong><?php echo isset($flatten[$fieldValue]) ? $flatten[$fieldValue] : $fieldValue; ?></strong></div><?php
	// empty
	else :
		?><div class="form-control-plaintext text-muted">- - -</div><?php
	endif;
?></div>
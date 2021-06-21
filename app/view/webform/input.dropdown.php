<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" />
			<string name="$fieldName" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="icon" optional="yes" />
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
<div class="input-group"><?php
	// icon
	if ( !empty($fieldConfig['icon']) ) :
		?><div class="input-group-prepend">
			<span class="input-group-text">
				<i class="<?php echo $fieldConfig['icon']; ?>"></i>
			</span>
		</div><?php
	endif;
	// field
	?><select
		id="<?php echo $fieldID; ?>"
		name="data[<?php echo $fieldName; ?>]"
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
	?></select>
</div>
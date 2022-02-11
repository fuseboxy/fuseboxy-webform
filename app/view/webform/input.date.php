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
				<boolean name="required" optional="yes" />
				<boolean name="readonly" optional="yes" />
				<string name="class" optional="yes" />
				<string name="style" optional="yes" />
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
<div class="webform-input-date input-group"><?php
	// icon
	include F::appPath('view/webform/input.icon.php');
	// field
	if ( !empty($editable) ) :
		?><input 
			type="text"
			id="<?php echo $fieldID; ?>"
			name="<?php echo $dataFieldName; ?>"
			value="<?php echo htmlspecialchars($fieldValue); ?>"
			class="form-control datepicker br-0 <?php if ( !empty($fieldConfig['class']) ) echo $fieldConfig['class']; ?>"
			maxlength="10"
			<?php if ( !empty($fieldConfig['placeholder']) ) : ?>placeholder="<?php echo $fieldConfig['placeholder']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['style']) ) : ?>style="<?php echo $fieldConfig['style']; ?>"<?php endif; ?>
			<?php if ( !empty($fieldConfig['required']) ) echo 'required' ?>
			<?php if ( !empty($fieldConfig['readonly']) ) echo 'readonly' ?>
		/><?php
		// calendar icon
		?><div class="input-group-append"><span class="input-group-text bg-white px-2 bl-0"><i class="far fa-calendar-alt op-30"></i></span></div><?php
	// readonly
	elseif ( $fieldValue !== '' ) :
		?><div class="form-control-plaintext text-primary"><strong><?php echo $fieldValue; ?></strong></div><?php
	// empty
	else :
		?><div class="form-control-plaintext text-muted">- - -</div><?php
	endif;
?></div>
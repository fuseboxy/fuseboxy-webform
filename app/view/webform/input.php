<?php /*
<fusedoc>
	<io>
		<in>
			<string name="$fieldID" example="webform-field-first_name" />
			<string name="$fieldName" example="first_name" />
			<string name="$fieldValue" />
			<string name="$fieldWidth" example="col-2" />
			<structure name="$fieldConfig">
				<string name="format" />
				<string name="label" optional="yes" />
				<string name="help" optional="yes" comments="help text show under input field" />
				<boolean name="required" optional="yes" comments="show asterisk at label (or in-label of certain types)" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div class="webform-input form-group <?php echo $fieldWidth; ?>"><?php
	// label (when necessary)
	if ( !empty($fieldConfig['label']) ) :
		?><label for="<?php echo $fieldID; ?>"><?php
			echo $fieldConfig['label'];
			if ( !empty($fieldConfig['required']) and empty($fieldConfig['inline-label']) ) echo '<span class="text-danger ml-1">*</span>';
		?></label><?php
	endif;
	// field
	include F::appPath('view/webform/input.'.$fieldConfig['format'].'.php');
	// help
	if ( !empty($fieldConfig['help']) ) :
		?><small class="form-text text-muted"><?php echo $fieldConfig['help']; ?></small><?php
	endif;
?></div>
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
				<boolean name="required" optional="yes" comments="show asterisk at label" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/ ?>
<div class="form-group <?php echo $fieldWidth; ?>"><?php
	// label (when necessary)
	if ( !empty($fieldConfig['label']) ) :
		?><label for="<?php echo $fieldID; ?>"><?php
			echo $fieldConfig['label'];
			if ( !empty($fieldConfig['required']) ) echo ' <span class="text-danger">*</span>';
		?></label><?php
	endif;
	// field
	include 'input.'.$fieldConfig['format'].'.php';
	// help
	if ( !empty($fieldConfig['help']) ) :
		?><small class="form-text text-muted"><?php echo $fieldConfig['help']; ?></small><?php
	endif;
?></div>
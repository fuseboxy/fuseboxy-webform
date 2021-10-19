<?php /*
<fusedoc>
	<description>
		when [fieldName] is normal, e.g. {my_first_name}, form submit the field as {data[my_first_name]}
		when [fieldName] is nested, e.g. {my.nested.var}, form submit the fields as {data[my][nested][var]}
	</description>
	<io>
		<in>
			<string name="$fieldID" example="webform-field-first_name" />
			<string name="$fieldName" example="first_name" />
			<string name="$fieldValue" />
			<structure name="$fieldConfig">
				<string name="format" />
				<string name="label" optional="yes" />
				<string name="help" optional="yes" comments="help text show under input field" />
				<boolean name="required" optional="yes" comments="show asterisk at label (or in-label of certain types)" />
				<string name="customScript" optional="yes" oncondition="when [format=custom]" />
			</structure>
		</in>
		<out>
			<structure name="data" scope="form" optional="yes">
				<mixed name="~fieldName~" />
			</structure>
		</out>
	</io>
</fusedoc>
*/
// do not show wrapper when hidden field
if ( $fieldConfig['format'] == 'hidden' ) :
	include F::appPath('view/webform/input.hidden.php');

// otherwise, show field with wrapper
else :
	$hasLabel = !empty($fieldConfig['label']);
	$hasInlineLabel = !empty($fieldConfig['inline-label']);
	$hasRequiredMark = !empty($fieldConfig['required']);
	?><div class="webform-input form-group"><?php
		// label (when necessary)
		if ( $hasLabel or ( !$hasInlineLabel and $hasRequiredMark ) ) :
			?><label for="<?php echo $fieldID; ?>"><?php
				// label text
				if ( $hasLabel ) echo $fieldConfig['label'];
				// required mark
				if ( !$hasInlineLabel and $hasRequiredMark ) :
					?><span class="text-danger ml-1">*</span><?php
				endif;
			?></label><?php
		endif;
		// field
		if ( $fieldConfig['format'] == 'custom' ) include $fieldConfig['customScript'];
		else include F::appPath('view/webform/input.'.$fieldConfig['format'].'.php');
		// help
		if ( !empty($fieldConfig['help']) ) :
			?><small class="form-text text-muted"><?php echo $fieldConfig['help']; ?></small><?php
		endif;
	?></div><?php

endif;
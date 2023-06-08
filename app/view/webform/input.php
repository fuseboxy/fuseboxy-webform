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
				<string name="scriptPath" optional="yes" oncondition="when [format=custom]" />
				<string name="wrapperClass" optional="yes" />
				<string name="wrapperStyle" optional="yes" />
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
	?><div 
		class="webform-input form-group <?php if ( !empty($fieldConfig['wrapperClass']) ) echo $fieldConfig['wrapperClass']; ?>"
		<?php if ( !empty($fieldConfig['wrapperStyle']) ) : ?>style="<?php echo $fieldConfig['wrapperStyle']; ?>"<?php endif; ?>
	><?php
		// label
		include F::appPath('view/webform/input.label.php');
		// field
		if ( $fieldConfig['format'] == 'custom' ) include $fieldConfig['scriptPath'];
		else include F::appPath('view/webform/input.'.$fieldConfig['format'].'.php');
		// help
		include F::appPath('view/webform/input.help.php');
	?></div><?php

endif;
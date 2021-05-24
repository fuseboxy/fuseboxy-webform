<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform" comments="config">
				<!-- essential config -->
				<string name="beanType" />
				<string name="layoutPath" />
				<structure name="fieldConfig" optional="yes" comments="options of each input field in edit form; also define sequence of field in modal edit form">
					<string name="+" comments="when no key specified, value is column name" />
					<structure name="~column~" comments="when key was specified, key is column name and value is field options">
						<string name="label" optional="yes" comments="display name at table/form header">
						<string name="format" optional="yes" comments="text|hidden|output|textarea|checkbox|radio|file|image|one-to-many|many-to-many|wysiwyg|url" default="text" />
						<structure name="options" optional="yes" comments="show dropdown when specified">
							<string name="~optionValue~" value="~optionText~" optional="yes" />
							<structure name="~optGroup~" optional="yes">
								<structure name="~optionValue~" value="~optionText~" />
							</structure>
						</structure>
						<boolean name="required" optional="yes" />
						<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
						<string name="placeholder" optional="yes" default="column display name" />
						<string name="value" optional="yes" comments="force filling with this value even if field has value" />
						<string name="default" optional="yes" comments="filling with this value if field has no value" />
						<string name="class" optional="yes" />
						<string name="style" optional="yes" />
						<string name="pre-help" optional="yes" comments="help text show before input field" />
						<string name="help" optional="yes" comments="help text show after input field" />
						<!-- below are for [format=file|image] only -->
						<string name="filesize" optional="yes" comments="max file size in bytes" example="2MB|500KB" />
						<list name="filetype" optional="yes" delim="," example="pdf,doc,docx" />
						<!-- for [format=image] only -->
						<string name="resize" optional="yes" example="800x600|1024w|100h" />
					</structure>
				</structure>
				<!-- settings for log -->
				<boolean name="writeLog" optional="yes" comments="simply true to log all actions" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// disallow accessing this controller directly
F::error('Forbidden', F::is('webform.*'));

// allow component to access and update the config variable
WebForm::$config = &$webform;


// start!
switch ( $fusebox->action ) :


	// display full form
	case 'index':
		break;


	// save submitted data
	case 'save':
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
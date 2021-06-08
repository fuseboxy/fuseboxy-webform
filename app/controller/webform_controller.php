<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform" comments="config">
				<!-- essential config -->
				<string name="beanType" />
				<string name="layoutPath" />
				<!-- steps of form -->
				<structure name="steps">
					<structure name="*">
					</structure>


					<structure name="confirm" comments="reserved" />
					<structure name="thanks" comments="reserved" />
				</structure>
				<!-- settings of each field used in form -->
				<structure name="fieldConfig" optional="yes">


				</structure>
				<!-- settings for email notification -->
				<structure name="notification" optional="yes" comments="set to false to send no email">
					<string name="from" />
					<string name="to" />
					<string name="cc" />
					<string nmae="bcc" />
					<string name="subject" />
					<string name="body" />
				</structure>
				<!-- settings for log -->
				<boolean name="writeLog" optional="yes" comments="simply true to log all actions" />
			</structure>
			<structure name="Webform::$libPath">
				<string name="uploadFile" />
				<string name="uploadFileProgress" />
			</structure>
			<structure name="config" scope="$fusebox" comments="for file upload">
				<string name="uploadDir" optional="yes" comments="server path for saving file" />
				<string name="uploadUrl" optional="yes" comments="web path for image source" />
			</structure>
		</in>
		<out />
	</io>
</fusedoc>
*/
// disallow accessing this controller directly
F::error('Forbidden', F::is('webform.*'));
// allow component to access and update the config variable
Webform::$config = &$webform;
// check config
$valid = Webform::validateConfig();
F::error(Webform::error(), $valid === false);
// set default config
$init = Webform::initConfig();
F::error(Webform::error(), $init === false);


// start!
switch ( $fusebox->action ) :


	// display full form
	case 'index':
		ob_start();
var_dump(Webform::$config);
		$layout['content'] = ob_get_clean();
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// edit form
	case 'edit':
		break;


	// save submitted data
	case 'save':
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
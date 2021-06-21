<?php /*
<fusedoc>
	<io>
		<in>
			<structure name="$webform" comments="config">
				<!-- essential config -->
				<string name="beanType" />
				<string name="layoutPath" />
				<!-- edit submitted form -->
				<number name="beanID" optional="yes" default="0" comments="zero indicates new record" />
				<!-- steps of form -->
				<structure name="steps" optional="yes">
					<structure name="~stepName~">
						<list name="~fieldNameList~" value="~fieldWidthList~" optional="yes" delim="|" comments="use bootstrap grid layout for width" />
						<string name="~line~" optional="yes" example="---" comments="any number of dash(-) or equal(=)" />
						<string name="~heading~" optional="yes" example="## General" comments="number of pound-signs means H1,H2,H3..." />
					</structure>
				</structure>
				<!-- settings of each field used in form -->
				<structure name="fieldConfig">
					<structure name="~fieldName~">
						<string name="format" default="text" comments="text|textarea|checkbox|radio|date|file|image|signature|captcha|hidden|output" />
						<string name="label" optional="yes" />
						<string name="inline-label" optional="yes" />
						<string name="placeholder" optional="yes" />
						<string name="icon" optional="yes" />
						<string name="help" optional="yes" comments="help text show below input field" />
						<!-- options -->
						<structure name="options" optional="yes" comments="show dropdown when specified">
							<string name="~optionValue~" value="~optionText~" optional="yes" />
							<structure name="~optGroup~" optional="yes">
								<structure name="~optionValue~" value="~optionText~" />
							</structure>
						</structure>
						<!-- attribute -->
						<boolean name="required" optional="yes" />
						<boolean name="readonly" optional="yes" comments="output does not pass value; readonly does" />
						<string name="default" optional="yes" comments="filling with this value if field has no value" />
						<string name="value" optional="yes" comments="force filling with this value even if field has value" />
						<!-- styling -->
						<string name="class" optional="yes" />
						<string name="style" optional="yes" />
						<!-- for [format=file|image] only -->
						<string name="filesize" optional="yes" comments="max file size in bytes" example="2MB|500KB" />
						<list name="filetype" optional="yes" delim="," example="pdf,doc,docx" />
						<!-- for [format=image] only -->
						<string name="resize" optional="yes" example="800x600|1024w|100h" />
					</structure>
				</structure>
				<!-- settings for email notification -->
				<structure name="notification" optional="yes" default="false" comments="set to false to send no email">
					<string name="fromName" />
					<string name="from" />
					<list name="to" delim=";," />
					<list name="cc" delim=";," />
					<list name="bcc" delim=";," />
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


	// init form
	case 'index':
		// clear cache (if any)
		$cleared = Webform::clear();
		F::error(Webform::error(), $cleared === false);
		// go to first step
		$firstStep = Webform::firstStep();
		F::error(Webform::error(), $firstStep === false);
		// redirect
		F::redirect("{$fusebox->controller}.new&step={$firstStep}");
		break;


	// submit new form
	case 'new':
		F::error('Argument [step] is required', empty($arguments['step']));
		// display form
		$layout['content'] = Webform::render($arguments['step']);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// edit submitted form
	case 'edit':
		break;


	// simply return to specified step (without caching submitted data)
	case 'back':
		F::error('Argument [step] is required', empty($arguments['step']));
		F::redirect("{$fusebox->controller}&step={$arguments['step']}");
		break;


	// check submitted data of specific step
	case 'validate':
		F::error('Argument [step] is required', empty($arguments['step']));
//		F::error('No data submitted', empty($arguments['data']));
		// validate
if ( isset($arguments['data']) ) {
		$validated = Webform::validate($arguments['data']);
		if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Webform::error()));
		// retain data
		$cached = Webform::data($arguments['data']);
		F::error(Webform::error(), $cached === false);
}
		// return to last step (when necessary)
		F::redirect("{$fusebox->controller}&step={$arguments['step']}", isset($_SESSION['flash']));
		// go to next step
		$nextStep = Webform::nextStep($arguments['step']);
		F::error(Webform::error(), $nextStep === false);
		F::redirect("{$fusebox->controller}.new&step={$nextStep}");
		break;


	// save submitted data
	case 'save':
		// validate all data before save
		$validated = Webform::validateAll();
		if ( $validated === false ) return false;
		// commit to save
		$saved = Webform::save();
		F::error(Webform::error(), $saved === false);
		// send notification (when necessary)
		if ( !empty(Webform::$config['notification']) ) {
			$notified = Webform::notify();
			if ( $notified === false ) return false;
		}
		// clear cache
		$cleared = Webform::clear();
		F::error(Webform::error(), $cleared === false);
		// done!
		F::redirect("{$fusebox->controller}.completed");
		break;


	// thank you page
	case 'completed':
		// display
		ob_start();
		include dirname(__DIR__).'/view/webform/completed.php';
		$layout['content'] = ob_get_clean();
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// ajax file upload
	case 'upload':
/*
		$result = Scaffold::uploadFile($arguments);
		$result = ( $result !== false ) ? $result : array(
			'success' => false,
			'msg' => Scaffold::error(),
		);
		echo json_encode($result);
*/
		echo json_encode([
			'success' => false,
			'msg' => 'under construction'
		]);
		break;


	// ajax upload progress
	case 'upload-proress':
		require Scaffold::$libPath['uploadFileProgress'];
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
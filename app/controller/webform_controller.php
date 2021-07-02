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
						<string name="~line~" optional="yes" comments="any amount of dash(-) or equal(=) signs" example="---" />
						<string name="~heading~" optional="yes" comments="begins with pound(#) sign(s); number of pound-signs stands for H1,H2,H3..." example="## General" />
						<string name="~output~" optional="yes" comments="begins with tide(~) sign" example="~<strong>output content directly</strong><br />" />
					</structure>
					<boolean name="confirm" optional="yes" default="true" />
				</structure>
				<!-- settings of each field used in form -->
				<structure name="fieldConfig">
					<structure name="~fieldName~">
						<string name="format" default="text" comments="text|textarea|checkbox|radio|date|file|image|signature|hidden|output" />
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
						<string name="filesizeError" optional="yes" comments="error message shown when file size failed; use {FILE_SIZE} as mask" />
						<string name="filetypeError" optional="yes" comments="error message shown when file type failed; use {FILE_TYPE} as mask" />
						<string name="buttonText" optional="yes" comments="button text when no file chosen" />
						<string name="buttonAltText" optional="yes" comments="button text when has file chosen" />
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
				<boolean name="writeLog" optional="yes" default="false" comments="simply true to log all actions" />
				<boolean name="snapshot" optional="yes" default="false" comments="default save to {snapshot} table when true; or specify table name to save" />
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
// set default & fix config
$init = Webform::initConfig();
F::error(Webform::error(), $init === false);
// check config
$valid = Webform::validateConfig();
F::error(Webform::error(), $valid === false);


// start!
switch ( $fusebox->action ) :


	// init form
	case 'index':
		// clear cache (if any)
		$cleared = Webform::clear();
		F::error(Webform::error(), $cleared === false);
		// pre-load data (if any)
		$loaded = Webform::load();
		F::error(Webform::error(), $loaded === false);
		// go to form
		F::redirect("{$fusebox->controller}.new", empty($webform['beanID']));
		F::redirect("{$fusebox->controller}.view");
		break;


	// submit new form
	case 'new':
		F::error('Config [beanID] is invalid', !empty($webform['beanID']));
		// set form mode
		Webform::mode('new');
		// default step
		if ( empty($arguments['step']) ) $arguments['step'] = Webform::firstStep();
		F::error(Webform::error(), $arguments['step'] === false);
		// exit point
		$prevStep = Webform::prevStep($arguments['step']);
		if ( $prevStep ) $xfa['back'] = "{$fusebox->controller}.new&step={$prevStep}";
		// exit point : next
		$nextStep = Webform::nextStep($arguments['step']);
		if ( $nextStep ) $xfa['next'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		else $xfa['submit'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		// exit point : ajax upload
		$xfa['uploadHandler'] = "{$fusebox->controller}.upload";
		$xfa['uploadProgress'] = "{$fusebox->controller}.upload-progress";
		// display form
		$layout['content'] = Webform::render($arguments['step'], $xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// edit submitted form
	case 'edit':
		F::error('Config [beanID] is required', empty($webform['beanID']));
		// set form mode
		Webform::mode('edit');
		// default step
		$firstStep = Webform::firstStep();
		if ( empty($arguments['step']) ) $arguments['step'] = $firstStep;
		F::error(Webform::error(), $arguments['step'] === false);
		// exit point : back
		$prevStep = Webform::prevStep($arguments['step']);
		if ( $prevStep ) $xfa['back'] = "{$fusebox->controller}.edit&step={$prevStep}";
		elseif ( $arguments['step'] == $firstStep ) $xfa['back'] = "{$fusebox->controller}.view";
		// exit point : next
		$nextStep = Webform::nextStep($arguments['step']);
		if ( $nextStep ) $xfa['next'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		else $xfa['update'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		// exit point : ajax upload
		$xfa['uploadHandler'] = "{$fusebox->controller}.upload";
		$xfa['uploadProgress'] = "{$fusebox->controller}.upload-progress";
		// display form
		$layout['content'] = Webform::render($arguments['step'], $xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// view submitted form
	case 'view':
		F::error('Config [beanID] is invalid', F::is('*.edit') and  empty($webform['beanID']));
		// exit point
		$xfa['edit'] = "{$fusebox->controller}.edit";
		// display form
		$layout['content'] = Webform::renderAll($xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// check submitted data of specific step
	case 'validate':
		F::error('Argument [step] is required', empty($arguments['step']));
		// validate & retain data (when necessary)
		$validated = true;
		if ( isset($arguments['data']) ) {
			$validated = Webform::validate($arguments['step'], $arguments['data']);
			if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Webform::error()));
			// retain data
			$cached = Webform::data($arguments['data']);
			F::error(Webform::error(), $cached === false);
		}
		// return to last step (when necessary)
		F::redirect("{$fusebox->controller}.new&step={$arguments['step']}", !$validated and empty($webform['beanID']));
		F::redirect("{$fusebox->controller}.edit&step={$arguments['step']}", !$validated);
		// go to next step (or save)
		$lastStep = Webform::lastStep();
		F::error(Webform::error(), $lastStep === false);
		$nextStep = Webform::nextStep($arguments['step']);
		F::redirect("{$fusebox->controller}.save", $arguments['step'] == $lastStep);
		F::redirect("{$fusebox->controller}.new&step={$nextStep}", empty($webform['beanID']));
		F::redirect("{$fusebox->controller}.edit&step={$nextStep}");
		break;


	// save submitted data
	case 'save':
		// commit to save
		$saved = Webform::save();
		F::error(Webform::error(), $saved === false);
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
		// validation
		if     ( empty($arguments['uploaderID'])   ) $err = 'Argument [uploaderID] is required';
		elseif ( empty($arguments['fieldName'])    ) $err = 'Argument [fieldName] is required';
		elseif ( empty($arguments['originalName']) ) $err = 'Argument [originalName] is required';
		// commit to upload
		if ( empty($err) ) {
			$result = Webform::uploadFileToTemp($arguments['uploaderID'], $arguments['fieldName'], $arguments['originalName']);
			if ( $result === false ) $err = Webform::error();
		}
		// check if any error
		if ( !empty($err) ) $result = array('success' => false, 'msg' => Webform::error());
		// done!
		echo json_encode($result);
		break;


	// ajax upload progress
	case 'upload-progress':
		require Webform::$libPath['uploadProgress'];
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
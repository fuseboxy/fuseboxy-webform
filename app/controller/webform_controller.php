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
				<boolean name="allowEdit" optional="false" comments="user can view submitted form but cannot modify" />
				<boolean name="allowPrint" optional="false" comments="user can print submitted form" />
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
				<!-- email notification settings -->
				<boolean_or_structure name="notification" optional="yes" default="false" comments="set to false to send no email">
					<string name="fromName" />
					<string name="from" />
					<list name="to" delim=";," />
					<list name="cc" delim=";," />
					<list name="bcc" delim=";," />
					<string name="subject" />
					<string name="body" />
				</boolean_or_structure>
				<!-- other settings -->
				<boolean_or_string name="writeLog" optional="yes" default="false" comments="simply true to log with default action; or specify action name for log" />
				<boolean_or_string name="snapshot" optional="yes" default="false" comments="simply true to save to {snapshot} table; or specify table name to save" />
				<boolean name="opened" optional="yes" default="true" comments="whether the form is opened" />
				<boolean name="closed" optional="yes" default="false" comments="whether the form is closed" />
				<!-- advanced -->
				<structure name="otherData" optional="yes" comments="load other data to webform when start">
					<mixed name="~otherFieldName~" />
				</structure>
				<!-- custom text -->
				<structure name="customText">
					<string name="opened" />
					<string name="closed" />
					<string name="completed" />
				</structure>
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
	case 'start':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		// pre-load data (if any)
		$started = Webform::start();
		F::error(Webform::error(), $started === false);
		// go to form
		F::redirect("{$fusebox->controller}.new", empty($webform['beanID']));
		F::redirect("{$fusebox->controller}.view");
		break;


	// submit new form
	case 'new':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('Config [beanID] is invalid', !empty($webform['beanID']));
		// set form mode
		Webform::mode('new');
		// default step
		if ( empty($arguments['step']) ) $arguments['step'] = Webform::firstStep();
		F::error(Webform::error(), $arguments['step'] === false);
		// go to confirmation (when necessary)
		F::redirect("{$fusebox->controller}.confirm", $arguments['step'] == 'confirm');
		// exit point : back
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
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('Config [beanID] is required', empty($webform['beanID']));
		// set form mode
		Webform::mode('edit');
		// default step
		$firstStep = Webform::firstStep();
		if ( empty($arguments['step']) ) $arguments['step'] = $firstStep;
		F::error(Webform::error(), $arguments['step'] === false);
		// go to confirmation (when necessary)
		F::redirect("{$fusebox->controller}.confirm", $arguments['step'] == 'confirm');
		// exit point : back
		$prevStep = Webform::prevStep($arguments['step']);
		if ( $prevStep ) $xfa['back'] = "{$fusebox->controller}.edit&step={$prevStep}";
		elseif ( $arguments['step'] == $firstStep ) $xfa['back'] = "{$fusebox->controller}.start";
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


	// confirmation
	case 'confirm':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('Confirmation is not required', empty($webform['steps']['confirm']));
		// exit point : back
		$mode = empty($webform['beanID']) ? 'new' : 'edit';
		$prevStep = Webform::prevStep($fusebox->action);
		$xfa['back'] = "{$fusebox->controller}.{$mode}&step={$prevStep}";
		// exit point : save
		$btnKey = empty($webform['beanID']) ? 'submit' : 'update';
		$xfa[$btnKey] = "{$fusebox->controller}.validate&step={$fusebox->action}";
		// display form
		$layout['content'] = Webform::render('confirm', $xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// check submitted data of specific step
	case 'validate':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('Argument [step] is required', empty($arguments['step']));
		// obtain last step
		$lastStep = Webform::lastStep();
		F::error(Webform::error(), $lastStep === false);
		// validate & retain data (when necessary)
		$validated = true;
		if ( isset($arguments['data']) ) {
			$validated = Webform::validate($arguments['step'], $arguments['data']);
			if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Webform::error()));
			// retain data
			$cached = Webform::data($arguments['data']);
			F::error(Webform::error(), $cached === false);
		}
		// validate captcha (when last step)
		if ( $validated and $arguments['step'] == $lastStep and !empty(F::config('captcha')) ) {
			$validated = Captcha::validate();
			if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Captcha::error()));
		}
		// return to current step (when error)
		$action = empty($webform['beanID']) ? 'new' : 'edit';
		F::redirect("{$fusebox->controller}.{$action}&step={$arguments['step']}", $validated === false);
		// go to next step (when not last step)
		$nextStep = Webform::nextStep($arguments['step']);
		F::redirect("{$fusebox->controller}.{$action}&step={$nextStep}", $arguments['step'] != $lastStep);
		// go to save (when last step)
		F::redirect("{$fusebox->controller}.save");
		break;


	// save submitted data
	case 'save':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		// obtain last step
		$lastStep = Webform::lastStep();
		F::error(Webform::error(), $lastStep === false);
		// commit to save
		$saved = Webform::save();
		if ( $saved === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Webform::error()));
		$action = empty($webform['beanID']) ? 'new' : 'edit';
		F::redirect("{$fusebox->controller}.{$action}&step={$lastStep}", $saved === false);
		// clear cache
		$cleared = Webform::clear();
		F::error(Webform::error(), $cleared === false);
		// done!
		F::redirect("{$fusebox->controller}.completed");
		break;


	// thank you page
	case 'completed':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		// display
		ob_start();
		include F::appPath('view/webform/completed.php');
		$layout['content'] = ob_get_clean();
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// form closed
	case 'closed':
		F::error('Form not closed yet', empty($webform['closed']));
		// display
		ob_start();
		include F::appPath('view/webform/closed.php');
		$layout['content'] = ob_get_clean();
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// print submitted form
	case 'print':
		F::error('under construction');
		break;


	// view submitted form
	case 'view':
		F::error('Config [beanID] is invalid', empty($webform['beanID']));
		// get record
		$bean = ORM::get($webform['beanType'], $webform['beanID']);
		F::error(ORM::error(), $bean === false);
		// exit point : edit
		if ( !empty($webform['allowEdit']) and empty($webform['closed']) ) $xfa['edit'] = "{$fusebox->controller}.edit";
		// exit point : print
		if ( !empty($webform['allowPrint']) ) $xfa['print'] = "{$fusebox->controller}.print";
		// display message (when necessary)
		ob_start();
		if ( !empty($bean->updated_on) ) F::alert([ 'type' => 'info', 'message' => 'Last updated on '.date('Y-m-d H:i', strtotime($bean->updated_on)) ]);
		elseif ( !empty($bean->created_on) ) F::alert([ 'type' => 'info', 'message' => 'Submitted on '.date('Y-m-d H:i', strtotime($bean->created_on)) ]);
		$layout['content'] = ob_get_clean();
		// display form
		$formContent = Webform::renderAll( $xfa ?? [] );
		F::error(Webform::error(), $formContent === false);
		$layout['content'] .= $formContent;
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// ajax file upload
	case 'upload':
		if ( !empty($webform['closed']) ) die('Forbidden');
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
		if ( !empty($webform['closed']) ) die('Forbidden');
		include Webform::$libPath['uploadProgress'];
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
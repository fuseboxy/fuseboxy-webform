<?php /*
<fusedoc>
	<description>
		Different ways to define [bean] config
		1. string : 'foo:123'
		2. array  : array('type' => 'foo', 'id' => 123)
		3. object : ORM::get('foo', 123)
	</description>
	<io>
		<in>
			<structure name="$webform" comments="config">
				<!-- essential config -->
				<mixed name="bean" />
				<string name="layoutPath" />
				<!-- permission -->
				<boolean name="allowEdit" optional="false" comments="user can view submitted form but cannot modify" />
				<boolean name="allowPrint" optional="false" comments="user can print submitted form" />
				<!-- steps of form -->
				<structure name="steps" optional="yes">
					<structure name="~stepName~">
						<list name="~fieldNameList~" value="~fieldWidthList~" optional="yes" delim="|" comments="use bootstrap grid layout for width">
							<list name="~fieldNameSubList~" delim="," comments="multiple fields in same column" />
						</list>
						<string name="~line~" optional="yes" comments="any amount of dash(-) or equal(=) signs" example="---" />
						<string name="~heading~" optional="yes" comments="begins with pound(#) sign(s); number of pound-signs stands for H1,H2,H3..." example="## General" />
						<string name="~output~" optional="yes" comments="begins with tide(~) sign" example="~<strong>output content directly</strong><br />" />
					</structure>
					<boolean name="confirm" optional="yes" default="true" />
				</structure>
				<!-- settings of each field used in form -->
				<structure name="fieldConfig">
					<structure name="~fieldName~">
						<string name="format" default="text" comments="text|textarea|checkbox|radio|date|file|image|signature|hidden|output|table|custom" />
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
						<!-- for [format=image] only -->
						<string name="resize" optional="yes" example="800x600|1024w|100h" />
						<!-- for [format=table] only -->
						<string name="tableTitle" optional="yes" />
						<structure name="tableHeader" optional="yes">
							<string name="~columnHeader~" value="~columnWidth~" />
						</structure>
						<structure name="tableRow" optional="yes">
							<structure name="~rowFieldName~" />
						</structure>
						<file name="tableRow" optional="yes" example="/path/to/table/row.php" />
						<boolean name="appendRow" optional="yes" />
						<boolean name="removeRow" optional="yes" />
						<!-- for [format=custom] only -->
						<file name="customScript" optional="yes" example="/path/to/custom/input.php" />
					</structure>
				</structure>
				<!-- email notification settings -->
				<boolean_or_structure name="notification" optional="yes" default="false" comments="set to {false} to send no email">
					<string name="fromName" />
					<string name="from" />
					<list name="to" delim=";," />
					<list name="cc" delim=";," />
					<list name="bcc" delim=";," />
					<string name="subject" />
					<string name="body" />
				</boolean_or_structure>
				<!-- other settings -->
				<boolean_or_string name="writeLog" optional="yes" default="false" comments="simply true to log with default action; or specify action name to log" />
				<boolean_or_string name="snapshot" optional="yes" default="false" comments="simply true to save to {snapshot} table; or specify table name to save" />
				<boolean_or_string name="autosave" optional="yes" deafult="false" comments="simply true to save to {autosave} table; or specify table name to save" />
				<boolean name="opened" optional="yes" default="true" comments="whether the form is opened" />
				<boolean name="closed" optional="yes" default="false" comments="whether the form is closed" />
				<!-- customization -->
				<structure name="customMessage">
					<string name="opened" />
					<string name="closed" />
					<string name="completed" />
					<string name="neverSaved" comments="for autosave only" />
					<string name="lastSavedAt" comments="for autosave only" />
					<string name="lastSavedOn" comments="for autosave only" />
				</structure>
				<structure name="customButton">
					<structure name="next|back|edit|submit|update|print|autosave|chooseFile|chooseAnother">
						<string name="icon" />
						<string name="text" />
					</structure>
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


	// determine landing page
	case 'index':
	case 'start':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::redirect("{$fusebox->controller}.new", empty($webform['bean']['id']));
		F::redirect("{$fusebox->controller}.view");
		break;


	// submit new form
	// ===> should show form data in session
	case 'new':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('ID of [bean] must be empty', !empty($webform['bean']['id']));
		// set form mode
		Webform::mode('new');
		// set first step
		// ===> when no step specified
		// ===> considered as just enter (instead of looping this action with new step)
		// ===> reset form data accordingly
		if ( empty($arguments['step']) ) {
			$arguments['step'] = Webform::firstStep();
			F::error(Webform::error(), $arguments['step'] === false);
			$reset = Webform::resetData();
			F::error(Webform::error(), $reset === false);
		}
		// go to confirmation (when necessary)
		F::redirect("{$fusebox->controller}.confirm", $arguments['step'] == 'confirm');
		// exit point : back
		$prevStep = Webform::prevStep($arguments['step']);
		if ( $prevStep ) $xfa['back'] = "{$fusebox->controller}.new&step={$prevStep}";
		// exit point : next
		$nextStep = Webform::nextStep($arguments['step']);
		if ( $nextStep ) $xfa['next'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		else $xfa['submit'] = "{$fusebox->controller}.validate&step={$arguments['step']}";
		// exit point : autosave
		if ( !empty($webform['autosave']) ) $xfa['autosave'] = "{$fusebox->controller}.autosave";
		// display form
		$layout['content'] = Webform::renderStep($arguments['step'], $xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// edit submitted form
	// ===> should show form data in session
	case 'edit':
		F::redirect("{$fusebox->controller}.closed", !empty($webform['closed']));
		F::error('ID of [bean] is required', empty($webform['bean']['id']));
		// set form mode
		Webform::mode('edit');
		// set first step
		// ===> when no step specified
		// ===> considered as just enter (instead of looping this action with new step)
		// ===> reset form data accordingly
		if ( empty($arguments['step']) ) {
			$arguments['step'] = $firstStep = Webform::firstStep();
			F::error(Webform::error(), $arguments['step'] === false);
			$reset = Webform::resetData();
			F::error(Webform::error(), $reset === false);
		}
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
		// exit point : autosave
		if ( !empty($webform['autosave']) ) $xfa['autosave'] = "{$fusebox->controller}.autosave";
		// display form
		$layout['content'] = Webform::renderStep($arguments['step'], $xfa);
		F::error(Webform::error(), $layout['content'] === false);
		// layout
		if ( !empty($webform['layoutPath']) ) include $webform['layoutPath'];
		else echo $layout['content'];
		break;


	// view submitted form
	// ===> should show data of config-bean
	case 'view':
F::error('Refactor in progress (soley rely on config-bean for display)');
var_dump(Webform::$bean);
Webform::initData();
var_dump(Webform::$bean->export());
		F::error('ID of [bean] is required', empty($webform['bean']['id']));
// pre-load data (if any)
//$started = Webform::start();
//F::error(Webform::error(), $started === false);
		// exit point : edit
		if ( $webform['allowEdit'] and !$webform['closed'] ) $xfa['edit'] = "{$fusebox->controller}.edit";
		// exit point : print
		if ( $webform['allowPrint'] ) $xfa['print'] = "{$fusebox->controller}.print";
		// display form
		$layout['content'] = Webform::renderAll( $xfa ?? [] );
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
		$operation = empty($webform['bean']['id']) ? 'new' : 'edit';
		$prevStep = Webform::prevStep($fusebox->action);
		$xfa['back'] = "{$fusebox->controller}.{$operation}&step={$prevStep}";
		// exit point : save
		$btnKey = empty($webform['bean']['id']) ? 'submit' : 'update';
		$xfa[$btnKey] = "{$fusebox->controller}.validate&step={$fusebox->action}";
		// display form
		$layout['content'] = Webform::renderStep('confirm', $xfa);
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
			// check data just submitted
			$validated = Webform::validate($arguments['step'], $arguments['data']);
			if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Webform::error()));
			// retain data to session
			$cached = Webform::progressData($arguments['data']);
			F::error(Webform::error(), $cached === false);
		}
		// validate captcha (when last step)
		if ( $validated and $arguments['step'] == $lastStep and !empty(F::config('captcha')) ) {
			$validated = Captcha::validate();
			if ( $validated === false ) $_SESSION['flash'] = array('type' => 'danger', 'message' => nl2br(Captcha::error()));
		}
		// return to current step (when error)
		$action = empty($webform['bean']['id']) ? 'new' : 'edit';
		F::redirect("{$fusebox->controller}.{$action}&step={$arguments['step']}", $validated === false);
		// go to next step (thru an intermediate action)
		F::redirect("{$fusebox->controller}.nextStep&step={$arguments['step']}");
		break;


	// intermediate action before going to next step
	// ===> for the scenario which developer refer to data-in-progress to determine steps conditionally
	// ===> make sure submitted data cached into session before actual next-step determined
	case 'nextStep':
		F::error('Argument [step] is required', empty($arguments['step']));
		// determine action
		$action = empty($webform['bean']['id']) ? 'new' : 'edit';
		// obtain last step
		$lastStep = Webform::lastStep();
		F::error(Webform::error(), $lastStep === false);
		// obtain next step
		$nextStep = Webform::nextStep($arguments['step']);
		// go to next step (when not last step)
		F::redirect("{$fusebox->controller}.{$action}&step={$nextStep}", $arguments['step'] != $lastStep);
		// go to save (when last step)
		F::redirect("{$fusebox->controller}.save");
		break;


	// retain in-progress data
	case 'autosave':
		F::error('Forbidden', !F::ajaxRequest());
		// validate
		if ( empty($arguments['data']) ) $err = 'No data for autosave';
		// temply save to database
		if ( empty($err) ) $lastSaved = Webform::autosave($arguments['data']);
		if ( isset($lastSaved) and $lastSaved === false ) $err = Webform::error();
		// display error message (when necessary)
		// ===> do not use F::error to avoid triggering [500 Internal Server Error]
		if ( !empty($err) ) {
			F::alert(['type' => 'danger webform-autosave', 'message' => $err ]);
		// display form
		} else {
			$xfa['autosave'] = "{$fusebox->controller}.autosave";
			include F::appPath('view/webform/autosave.php');
		}
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
		$action = empty($webform['bean']['id']) ? 'new' : 'edit';
		F::redirect("{$fusebox->controller}.{$action}&step={$lastStep}", $saved === false);
		// clear retained form data
		$cleared = Webform::clearData();
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


	// ajax file upload (for [format=file|image] field)
	case 'upload':
		if ( !empty($webform['closed']) ) die('Forbidden');
		// validate
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


	// ajax upload progress (for [format=file|image] field)
	case 'uploadProgress':
		if ( !empty($webform['closed']) ) die('Forbidden');
		include Webform::$libPath['uploadProgress'];
		break;


	// append table row (for [format=table] field)
	case 'appendRow':
		F::error('Argument [fieldName] is required', empty($arguments['fieldName']));
		// set form mode
		Webform::mode( empty($webform['bean']['id']) ? 'new' : 'edit' );
		// load config
		$fieldConfig = Webform::fieldConfig($arguments['fieldName']);
		F::error(Webform::error(), $fieldConfig === false);
		F::error('Forbidden', isset($fieldConfig['format']) and $fieldConfig['format'] != 'table');
		// more essential variables
		$fieldName = $arguments['fieldName'];
		$fieldValue = array();
		// button
		$xfa['removeRow'] = F::command('controller').'.removeRow';
		// display
		include F::appPath('view/webform/input.table.row.php');
		break;


	// remove table row (for [format=table] field)
	case 'removeRow':
		break;


	// not found
	default:
		F::pageNotFound();


endswitch;
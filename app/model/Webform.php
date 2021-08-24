<?php
class Webform {


	// property : webform config
	public static $config;
	// property : webform working mode
	private static $mode = 'view';
	// property : library for corresponding methods
	public static $libPath = array(
		'uploadFile'     => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/Uploader.php',
		'uploadProgress' => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/uploadProgress.php',
	);



	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			clear cached data of webform
		</description>
		<io>
			<in>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function clear() {
		if ( isset($_SESSION['webform'][self::token()]) ) unset($_SESSION['webform'][self::token()]);
		return true;
	}




	/**
	<fusedoc>
		<description>
			getter & setter of form data
			===> regardless of step because field-names are unique
			===> use token to handle multiple webforms when user open more than one window
		</description>
		<io>
			<in>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" optional="yes" />
					</structure>
				</structure>
				<!-- parameter -->
				<structure name="$data" optional="yes" oncondition="setter" />
			</in>
			<out>
				<!-- setter -->
				<boolean name="~return~" optional="yes" value="true" />
				<!-- getter -->
				<structure name="~return~" optional="yes" comments="cached form data">
					<mixed name="~fieldName~" optional="yes" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function data($data=null) {
		// init container
		if ( !isset($_SESSION['webform'][self::token()]) ) $_SESSION['webform'][self::token()] = array();
		// when getter
		// ===> return cached data right away
		if ( $data === null ) return $_SESSION['webform'][self::token()];
		// when setter
		// ===> clean-up data
		// ===> update cache (field-by-field)
		$data = self::sanitize($data);
		if ( $data === false ) return false;
		foreach ( $data as $key => $val ) $_SESSION['webform'][self::token()][$key] = $val;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			access cached data of specific webform before init config
		</description>
		<io>
			<in>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~beanType~:~beanID~" />
				</structure>
				<!-- parameter -->
				<string name="beanType" />
				<number name="beanID" optional="yes" default="0" />
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function dataInProgress($beanType, $beanID=null) {
		$beanID = !empty($beanID) ? $beanID : 0;
		$token = $beanType.':'.$beanID;
		return isset($_SESSION['webform'][$token]) ? $_SESSION['webform'][$token] : array();
	}




	/**
	<fusedoc>
		<description>
			obtain field config of specific field
		</description>
		<io>
			<in>
				<string name="$fieldName" />
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldConfig($fieldName) {
		// validation
		if ( empty(self::$config['fieldConfig'][$fieldName]) ) {
			self::$error = "Field config for [{$fieldName}] not found";
			return false;
		}
		// done!
		return self::$config['fieldConfig'][$fieldName];
	}




	/**
	<fusedoc>
		<description>
			convert field name to [name] attribute for <input>
		</description>
		<io>
			<in>
				<string name="$fieldName" example="student_name|student.name" />
			</in>
			<out>
				<structure name="~return~" example="data[student_name]|data[student][name]" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldName2dataFieldName($fieldName) {
		return 'data['.str_replace('.', '][', $fieldName).']';
	}




	/**
	<fusedoc>
		<description>
			convert field name to [id] attribute for HTML element
		</description>
		<io>
			<in>
				<string name="$fieldName" example="student_name|student.name" />
			</in>
			<out>
				<structure name="~return~" example="webform-input-student_name|webform-input-student-name" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldName2fieldID($fieldName) {
		return 'webform-input-'.str_replace('.', '-', $fieldName);
	}




	/**
	<fusedoc>
		<description>
			convert human-readable file-size string to number of bytes
		</description>
		<io>
			<in>
				<string name="$input" example="2MB|110KB" />
			</in>
			<out>
				<number name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fileSizeInBytes($input) {
		$kb = 1024;
		$mb = $kb * 1024;
		$gb = $mb * 1024;
		$tb = $gb * 1024;
		// extra unit
		$input = strtoupper(str_replace(' ', '', $input));
		$lastOneDigit = substr($input, -1);
		$lastTwoDigit = substr($input, -2);
		// calculation
		if     ( $lastOneDigit == 'T' or $lastTwoDigit == 'TB' ) $result = floatval($input) * $tb;
		elseif ( $lastOneDigit == 'G' or $lastTwoDigit == 'GB' ) $result = floatval($input) * $gb;
		elseif ( $lastOneDigit == 'M' or $lastTwoDigit == 'MB' ) $result = floatval($input) * $mb;
		elseif ( $lastOneDigit == 'K' or $lastTwoDigit == 'KB' ) $result = floatval($input) * $kb;
		else $result = floatval($input);
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			obtain first step name
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
				</structure>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function firstStep() {
		$all = array_keys(self::$config['steps']);
		// validate
		if ( !isset($all[0]) ) {
			self::$error = 'First step not found';
			return false;
		}
		// done!
		return $all[0];
	}




	/**
	<fusedoc>
		<description>
			access nested array value by list delimited by period
			===> e.g. pass [student.name] to access [student][name] of array
		</description>
		<io>
			<in>
				<array name="$nestedArray" />
				<list name="$nestedKey" delim="." />
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function getNestedArrayValue($nestedArray, $nestedKey) {
		$nestedKey = explode('.', $nestedKey);
		$result = &$nestedArray;
		foreach ( $nestedKey as $key ) $result = &$result[$key] ?? null;
		return $result;
	}




	/**
	<fusedoc>
		<description>
			set default & fix config
		</description>
		<io>
			<in>
				<structure name="$config" scope="self" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<number name="beanID" default="0" />
					<boolean name="allowEdit" default="false" />
					<boolean name="allowPrint" default="false" />
					<!-- default steps (when unspecified) -->
					<structure name="steps">
						<structure name="default" />
						<boolean name="confirm" default="true" />
					</structure>
					<!-- default field config -->
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" default="text" />
							<string name="label" optional="yes" comments="derived from field name" />
							<string name="label-inline" optional="yes" comments="derived from field name" />
							<string name="placeholder" optional="yes" comments="derived from field name" />
							<!-- default for [format=file|image|signature] only -->
							<string name="filesize" default="10MB" />
							<list name="filetype" delim="," default="gif,jpg,jpeg,png,txt,doc,docx,pdf,ppt,pptx,xls,xlsx" />
							<string name="filesizeError" default="File cannot exceed {FILE_SIZE}" />
							<string name="filetypeError" default="Only file of {FILE_TYPE} is allowed" />
							<!-- attribute -->
							<string name="value" optional="yes" oncondition="when [beanID] specified" comments="force filling with this value even if field has value" />
						</structure>
					</structure>
					<!-- others settings -->
					<structure name="notification" optional="yes">
						<string name="to" default=":email" />
					</structure>
					<string name="snapshot" default="snapshot" comments="table to save snapshot; no snapshot to take when false" />
					<string name="closed" comments="message to show when form closed" />
					<!-- default custom message -->
					<structure name="customMessage">
						<string name="closed" />
						<string name="completed" />
					</structure>
					<!-- default custom button -->
					<structure name="customButton">
						<structure name="next|back|edit|submit|update|print|chooseFile|chooseAnotherFile">
							<string name="icon" />
							<string name="text" />
						</structure>
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig() {
		// beanID : default
		// ===> zero stands for submitting new form
		// ===> non-zero stands for editing submitted form
		if ( empty(self::$config['beanID']) ) self::$config['beanID'] = 0;
		// allowEdit : default
		if ( !isset(self::$config['allowEdit']) ) self::$config['allowEdit'] = false;
		// allowPrint : default
		if ( !isset(self::$config['allowPrint']) ) self::$config['allowPrint'] = false;
		// set default steps
		// ===> when none specified
		// ===> simply use all fields as specified in field-config
		if ( empty(self::$config['steps']) ) self::$config['steps'] = array('default' => array_keys(self::$config['fieldConfig']));
		// default having [confirm] step
		if ( !isset(self::$config['steps']['confirm']) ) self::$config['steps']['confirm'] = true;
		// fix [heading|line|output] of each step
		// ===> append space to make sure it is unique
		// ===> avoid being overridden after convert to key
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			if ( is_array($fieldLayout) ) {
				foreach ( $fieldLayout as $i => $stepRow ) {
					if ( self::parseStepRow($stepRow, true) != 'fields' ) {
						self::$config['steps'][$stepName][$i] = $stepRow.str_repeat(' ', $i);
					}
				}
			}
		}
		// fix field-layout of each step
		// ===> when only field-name-list specified
		// ===> use field-name-list as key & apply empty field-width-list
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			// turn string into array
			// ===> e.g. [ 'declare' => 'col_1|col_2' ]  >>>  [ 'declare' => array('col_1|col_2' => '') ]
			if ( is_string($fieldLayout) ) {
				self::$config['steps'][$stepName] = array($fieldLayout => '');
			// go through well-formatted field-layout
			// ===> make sure field-name-list is key & field-width-list is value
			// ===> e.g. [ 'my-step' => array('a|b|c', 'x|y|z' => '6|3|3') ]  >>>  [ 'my-step' => array('a|b|c' => '', 'x|y|z' => '6|3|3') ]
			} elseif ( is_array($fieldLayout) ) {
				self::$config['steps'][$stepName] = array();
				foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
					if ( is_numeric($fieldNameList) ) list($fieldNameList, $fieldWidthList) = array($fieldWidthList, '');
					self::$config['steps'][$stepName][$fieldNameList] = $fieldWidthList;
				}
			// remove empty step
			// ===> e.g. [ 'my-step' => array() ]  >>>  (remove)
			// ===> e.g. [ 'confirm' => false   ]  >>>  (remove)
			} elseif ( empty($fieldLayout) ) {
				unset(self::$config['steps'][$stepName]);
			// invalid format
			} elseif ( $stepName != 'confirm' ) {
				self::$error = "Field layout of step [{$stepName}] is invalid";
				return false;
			}
		}
		// field-config : fix format
		// ===> when only field-name specified, use field-name as key & apply empty config
		// ===> when false or null, remove field config
		// ===> when config is string, use as label
		$arr = self::$config['fieldConfig'];
		self::$config['fieldConfig'] = array();
		foreach ( $arr as $fieldName => $config ) {
			if ( is_numeric($fieldName) ) list($fieldName, $config) = array($config, []);
			if ( is_string($config) ) $config = array('label' => $config);
			if ( $config !== false and $config !== null ) self::$config['fieldConfig'][$fieldName] = is_array($config) ? $config : [];
		}
		// field config : remove [options] when false
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			if ( isset($cfg['options']) and $cfg['options'] === false ) {
				unset(self::$config['fieldConfig'][$fieldName]['options']);
			}
		}
		// field config : default
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// format : default
			if ( empty($cfg['format']) and isset($cfg['options']) ) {
				self::$config['fieldConfig'][$fieldName]['format'] = 'dropdown';
			} elseif ( empty($cfg['format']) or $cfg['format'] === true ) {
				self::$config['fieldConfig'][$fieldName]['format'] = 'text';
			}
			// label : derived from field name
			if ( !isset($cfg['label']) or $cfg['label'] === true ) {
				self::$config['fieldConfig'][$fieldName]['label'] = implode(' ', array_map(function($word){
					return in_array($word, ['id','url']) ? strtoupper($word) : ucfirst($word);
				}, explode('_', $fieldName)));
			}
			// label-inline & placeholder : derived from field name
			foreach ( ['label-inline','placeholder'] as $key ) {
				if ( isset($cfg[$key]) and $cfg[$key] === true ) {
					self::$config['fieldConfig'][$fieldName][$key] = implode(' ', array_map(function($word){
						return in_array($word, ['id','url']) ? strtoupper($word) : ucfirst($word);
					}, explode('_', $fieldName)));
				}
			}
			// file config
			if ( isset($cfg['format']) and in_array($cfg['format'], ['file','image','signature']) ) {
				// file size : default
				if ( empty($cfg['filesize']) ) self::$config['fieldConfig'][$fieldName]['filesize'] = '10MB';
				// file type : default
				if ( empty($cfg['filetype']) ) self::$config['fieldConfig'][$fieldName]['filetype'] = in_array($cfg['format'], ['image','signature']) ? 'gif,jpg,jpeg,png' : 'gif,jpg,jpeg,png,txt,doc,docx,pdf,ppt,pptx,xls,xlsx';
				// file size error : default
				if ( empty($cfg['filesizeError']) ) self::$config['fieldConfig'][$fieldName]['filesizeError'] = 'File cannot exceed {FILE_SIZE}';
				// file type error : default
				if ( empty($cfg['filetypeError']) ) self::$config['fieldConfig'][$fieldName]['filetypeError'] = 'Only file of {FILE_TYPE} is allowed';
			}
		}
		// notification : default & fix format
		if ( !isset(self::$config['notification']) ) self::$config['notification'] = false;
		if ( self::$config['notification'] === true ) self::$config['notification'] = array();
		// notification : default [to] setting
		if ( !empty(self::$config['notification']) and !isset(self::$config['notification']['to']) ) self::$config['notification']['to'] = ':email';
		// snapshot : default table name
		if ( isset(self::$config['snapshot']) and self::$config['snapshot'] === true ) self::$config['snapshot'] = 'snapshot';
		// opened & closed : default
		if ( !isset(self::$config['opened']) ) self::$config['opened'] = true;
		if ( !isset(self::$config['closed']) ) self::$config['closed'] = false;
		// custom message : default
		if ( empty(self::$config['customMessage']) ) self::$config['customMessage'] = array();
		if ( empty(self::$config['customMessage']['closed']) ) self::$config['customMessage']['closed'] = 'Form was closed.';
		if ( empty(self::$config['customMessage']['completed']) ) self::$config['customMessage']['completed'] = 'Your submission was received.';
		// custom button : default & fix format
		if ( empty(self::$config['customButton']) ) self::$config['customButton'] = array();
		foreach ( ['next','back','edit','submit','update','print','chooseFile','chooseAnotherFile'] as $key ) {
			if ( !isset(self::$config['customButton'][$key]) ) self::$config['customButton'][$key] = array();
			// use as button text when only string specified
			elseif ( is_string(self::$config['customButton'][$key]) ) self::$config['customButton'][$key] = array('text' => self::$config['customButton'][$key]);
			// default button text
			if ( !isset(self::$config['customButton'][$key]['text']) ) self::$config['customButton'][$key]['text'] = call_user_func(function() use ($key){
				if ( $key == 'chooseAnotherFile' ) return 'Choose AnotherFile';
				elseif ( $key == 'chooseFile' ) return 'Choose File';
				return ucfirst($key);
			});
		}
		if ( !isset(self::$config['customButton']['next'  ]['icon']) ) self::$config['customButton']['next'  ]['icon'] = 'fa fa-arrow-right ml-2';
		if ( !isset(self::$config['customButton']['back'  ]['icon']) ) self::$config['customButton']['back'  ]['icon'] = 'fa fa-arrow-left mr-1';
		if ( !isset(self::$config['customButton']['edit'  ]['icon']) ) self::$config['customButton']['edit'  ]['icon'] = 'fa fa-edit mr-1';
		if ( !isset(self::$config['customButton']['submit']['icon']) ) self::$config['customButton']['submit']['icon'] = 'fa fa-paper-plane mr-1';
		if ( !isset(self::$config['customButton']['update']['icon']) ) self::$config['customButton']['update']['icon'] = 'fa fa-file-import mr-1';
		if ( !isset(self::$config['customButton']['print' ]['icon']) ) self::$config['customButton']['print' ]['icon'] = 'fa fa-print mr-1';
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			obtain last step name
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
				</structure>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function lastStep() {
		$all = array_reverse(array_keys(self::$config['steps']));
		// validate
		if ( !isset($all[0]) ) {
			self::$error = 'Last step not found';
			return false;
		}
		// done!
		return $all[0];
	}




	/**
	<fusedoc>
		<description>
			getter & setter of webform working mode
			determine whether webform is editable
		</description>
		<io>
			<in>
				<string name="$val" optional="yes" />
			</in>
			<out>
				<!-- getter -->
				<string name="~return~" />
				<!-- setter -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function mode($val=null) {
		if ( empty($val) ) return self::$mode;
		self::$mode = strtolower(trim($val));
		return true;
	}




	/**
	<fusedoc>
		<description>
			move (multiple) uploaded files from temp to permanent directory
		</description>
		<io>
			<in>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
					<string name="uploadUrl" />
				</structure>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<stucture name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" comments="file|image|signature" />
						</structure>
					</structure>
				</structure>
				<!-- cached form data -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" />
					</structure>
				</structure>
				<!-- uploaded file in server -->
				<file path="~uploadDir~/tmp/~sessionID~/~uniqueFilename~" />
				<!-- uploaded file url -->
				<string value="~uploadDir~/tmp/~sessionID~/~uniqueFilename~" />
			</in>
			<out>
				<!-- re-located file in server -->
				<file path="~uploadDir~/~beanType~/~fieldName~/~uniqueFilename~" />
				<!-- re-located file url (return value) -->
				<string name="~return~" value="~uploadUrl/~beanType~/~fieldName~/~uniqueFilename~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function moveFileToPerm() {
		// unify slash & ensure trailing slash
		$uploadDir = str_replace('\\', '/', F::config('uploadDir'));
		$uploadUrl = str_replace('\\', '/', F::config('uploadUrl'));
		if ( substr($uploadDir, -1) != '/' ) $uploadDir .= '/';
		if ( substr($uploadUrl, -1) != '/' ) $uploadUrl .= '/';
		// load form data
		$formData = self::data();
		if ( $formData === false ) return false;
		// go through each field
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			$isFileAtTemp = ( in_array($cfg['format'], ['file','image','signature']) and isset($formData[$fieldName]) and stripos($formData[$fieldName], '/tmp/'.session_id().'/') !== false );
			// check available & format
			// ===> only move file when in temp directory
			if ( $isFileAtTemp ) {
					// determine server location of source file
					$sourceUrl  = $formData[$fieldName];
					$sourcePath = str_ireplace($uploadUrl, $uploadDir, $sourceUrl);
					$sourceDir  = dirname($sourcePath);
					// prepare url & server location of destination
					$targetUrl  = $uploadUrl.self::$config['beanType'].'/'.$fieldName.'/'.basename($sourceUrl);
					$targetPath = str_ireplace($uploadUrl, $uploadDir, $targetUrl);
					$targetDir  = dirname($targetPath);
					// create directory (when necessary)
					if ( !file_exists($targetDir) and !mkdir($targetDir, 0766, true) ) {
						self::$error = error_get_last()['message'];
						return false;
					}
					// commit to move file
					if ( !rename($sourcePath, $targetPath) ) {
						self::$error = error_get_last()['message'];
						return false;
					}
					// put new url to container
					$formData[$fieldName] = $targetUrl;
			} // if-file-at-format
		} // foreach-fieldConfig
		// update data in cache
		$cached = self::data($formData);
		if ( $cached === false ) return false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			obtain step name next to specified step
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$thisStep" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function nextStep($thisStep) {
		$all = array_keys(self::$config['steps']);
		$pos = array_search($thisStep, $all);
		// validate
		if ( $pos === false or !isset($all[$pos+1]) ) {
			self::$error = "Step next to [{$thisStep}] not found";
			return false;
		}
		// done!
		return $all[$pos+1];
	}



	/**
	<fusedoc>
		<description>
			send notification email
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<structure name="notification">
						<string name="fromName" />
						<string name="from" />
						<list name="to" delim=";," />
						<list name="cc" delim=";," />
						<list name="bcc" delim=";," />
						<string name="subject" />
						<string name="body" />
					</structure>
					<boolean name="writeLog" />
				</structure>
				<!-- parameter -->
				<number name="$entityID" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function notify($entityID) {
		// get config
		$cfg = self::$config['notification'];
		// validate recipient
		if ( empty($cfg['to']) ) {
			self::$error = 'Notification recipient not specified';
			return false;
		}
		// load form data
		$formData = self::data();
		if ( $formData === false ) return false;
		// prepare mail
		$mail = array(
			'from_name' => !empty($cfg['fromName']) ? $cfg['fromName'] : null,
			'from'      => !empty($cfg['from']) ? $cfg['from'] : null,
			'cc'        => !empty($cfg['cc']) ? $cfg['cc'] : null,
			'bcc'       => !empty($cfg['bcc']) ? $cfg['bcc'] : null,
			'subject'   => $cfg['subject'],
			'body'      => $cfg['body'],
		);
		// send to recipient (by email field or custom value)
		// ===> e.g. [ 'to' => ':email' ]
		// ===> e.g. [ 'to' => 'foo@bar.com' ]
		$mail['to'] = ( $cfg['to'][0] != ':' ) ? $cfg['to'] : call_user_func(function($emailField){
			$emailFieldValue = self::getNestedArrayValue($emailField);
			if ( $emailFieldValue === false ) return false;
			return $emailFieldValue;
		}, substr($cfg['to'], 1));
		// validate recipient email
		if ( empty($mail['to']) ) {
			self::$error = 'Email recipient not specified';
			if ( $cfg['to'][0] == ':' ) self::$error .= " ({$cfg['to']})";
			return false;
		}
		// prepare mapping of mask & data
		$masks = array();
		foreach ( $formData as $key => $val ) $masks["[[{$key}]]"] = $val;
		// replace mask in subject & body
		foreach ( $masks as $key => $val ) {
			if ( is_array($val) ) $val = implode('|', $val);  // checkbox value
			$mail['subject'] = str_ireplace($key, $val, $mail['subject']);
			$mail['body']    = str_ireplace($key, $val, $mail['body']);
		}
		// send email
		if ( Util::mail($mail) === false ) {
			self::$error = Util::error();
			return false;
		}
		// write log
		if ( !empty(self::$config['writeLog']) ) {
			$logged = Log::write([
				'action'      => 'SEND_NOTIFICATION',
				'entity_type' => self::$config['beanType'],
				'entity_id'   => $entityID,
				'remark'      => $mail,
			]);
			if ( $logged === false ) {
				self::$error = Log::error();
				return false;
			}
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			parse step row key and render or determine type
		</description>
		<io>
			<in>
				<string name="$stepRow" />
				<boolean name="$getType" default="false" />
			</in>
			<out>
				<string name="~return~" value="heading|line|output|fields" oncondition="when [getType] is true" />
				<string name="~return~" comments="display row in corresponding format" oncondition="when [getType] is false" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function parseStepRow($stepRow, $getType=false) {
		$stepRow = trim($stepRow);
		// heading
		if ( strlen($stepRow) != strlen(ltrim($stepRow, '#')) ) {
			$size = 'h'.( strlen($stepRow) - strlen(ltrim($stepRow, '#')) );
			$text = trim(ltrim($stepRow, '#'));
			return $getType ? 'heading' : "<div class='{$size}'>{$text}</div>";
		}
		// output
		if ( strlen($stepRow) and $stepRow[0] === '~' ) {
			$output = trim(substr($stepRow, 1));
			return $getType ? 'output' : ( strlen($output) ? "<div>{$output}</div>" : '' );
		}
		// line
		if ( trim($stepRow, '=-') === '' ) {
			return $getType ? 'line' : '<hr />';
		}
		// fields (render nothing)
		return $getType ? 'fields' : '';
	}




	/**
	<fusedoc>
		<description>
			obtain step name previous to specified step
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$thisStep" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function prevStep($thisStep) {
		$all = array_keys(self::$config['steps']);
		$pos = array_search($thisStep, $all);
		// validate
		if ( $pos === false or !isset($all[$pos-1]) ) {
			self::$error = "Step previous to [{$thisStep}] not found";
			return false;
		}
		// done!
		return $all[$pos-1];
	}




	/**
	<fusedoc>
		<description>
			render all steps at once
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
				</structure>
				<!-- parameter -->
				<structure name="$xfa" optional="yes" comments="exit points" />
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderAll($xfa=[]) {
		$editable = in_array(self::$mode, ['new','edit']);
		// prepare variable
		$fieldLayoutAll = self::$config['steps'];
		// exclude [confirm] step
		if ( isset($fieldLayoutAll['confirm']) ) unset($fieldLayoutAll['confirm']);
		// load data from cache
		$arguments['data'] = self::data();
		if ( $arguments['data'] === false ) return false;
		// display
		ob_start();
		$webform['config'] = self::$config;
		include F::appPath('view/webform/form.php');
		// done!
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			render form of specific step
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" />
					</structure>
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$step" />
				<structure name="$xfa" optional="yes" comments="exit points" />
			</in>
			<out>
				<string name="~return~" comments="form output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderStep($step, $xfa=[]) {
		$editable = ( in_array(self::$mode, ['new','edit']) and $step != 'confirm' );
		// validation
		if ( !self::stepExists($step) ) return false;
		// when [confirm] is simply true (no field specifed)
		// ===> display all fields & quit
		// ===> otherwise, display specified fields
		if ( $step == 'confirm' and self::$config['steps']['confirm'] === true ) {
			$original = self::$mode;
			self::$mode = 'view';
			$output = self::renderAll($xfa);
			self::$mode = $original;
			return $output;
		}
		// prepare variable
		$fieldLayout = self::$config['steps'][$step];
		// load data from cache
		$arguments['data'] = self::data();
		if ( $arguments['data'] === false ) return false;
		// display
		ob_start();
		$arguments['step'] = $step;
		$webform['config'] = self::$config;
		include F::appPath('view/webform/form.php');
		// done!
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			clean-up data
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" />
						</structure>
					</structure>
				</structure>
				<!-- parameter -->
				<structure name="$data" comments="data before cleansing">
					<mixed name="~fieldName~" />
				</struture>
			</in>
			<out>
				<structure name="~return~" comments="data after cleansed">
					<mixed name="~fieldName~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	private static function sanitize($data) {
		// go through each item
		foreach ( $data as $key => $val ) {
			if ( is_array($val) ) {
				// clean-up recursively
				$val = self::sanitize($val);
			} else {
				// trim space & remove tab
				$val = str_replace("\t", ' ', trim($val));
				// convert html tag (make it visible but harmless)
				// ===> except signature field (in order to keep SVG data)
				if ( isset(self::$config['fieldConfig'][$key]) and self::$config['fieldConfig'][$key]['format'] != 'signature' ) {
					$val = preg_replace ('/<([^>]*)>/', '[$1]', $val);
				}
			}
			// put into result
			$data[$key] = $val;
		}
		// done!
		return $data;
	}




	/**
	<fusedoc>
		<description>
			save submitted data into database
			===> send notification
			===> take snapshot
			===> write log
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
					<structure name="notification" optional="yes" />
					<boolean_or_string name="writeLog" optional="yes" />
					<string name="snapshot" optional="yes" comments="table name for snapshot" />
				</structure>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<number name="~return~" comments="last insert ID" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function save() {
		// validate all data before save
		$validated = self::validateAll();
		if ( $validated === false ) return false;
		// convert & upload signature
		$uploaded = self::uploadSignatureToTemp();
		if ( $uploaded === false ) return false;
		// move uploaded files to permanent location
		// ===> form data will be updated accordingly
		$moved = self::moveFileToPerm();
		if ( $moved === false ) return false;
		// create empty container or load from database
		if ( empty(self::$config['beanID']) ) $bean = ORM::new(self::$config['beanType']);
		else $bean = ORM::get(self::$config['beanType'], self::$config['beanID']);
		if ( $bean === false ) {
			self::$error = ORM::error();
			return false;
		}
		// put (updated) form data to container
		$formData = self::data();
		if ( $formData === false ) return false;
		foreach ( $formData as $key => $val ) $bean->{$key} = is_array($val) ? implode('|', $val) : $val;
		// add more info
		if ( empty($bean->created_on) ) $bean->created_on = date('Y-m-d H:i:s');
		else $bean->updated_on = date('Y-m-d H:i:s');
		$bean->disabled = false;
		// save to database
		$id = ORM::save($bean);
		if ( $id === false ) {
			self::$error = ORM::error();
			return false;
		}
		// send notification (when necessary)
		if ( !empty(self::$config['notification']) ) {
			$notified = self::notify($id);
			if ( $notified === false ) return false;
		}
		// take snapshot (when necessary)
		if ( !empty(self::$config['snapshot']) ) {
			$snapshotBean = ORM::new(self::$config['snapshot'], [
				'datetime'    => date('Y-m-d H:i:s'),
				'entity_type' => self::$config['beanType'],
				'entity_id'   => $id,
				'snapshot'    => call_user_func(function(){
					$original = self::$mode;
					self::$mode = 'view';
					$output = self::renderAll();
					self::$mode = $original;
					return $output;
				}),
			]);
			if ( $snapshotBean === false ) {
				self::$error = ORM::error();
				return false;
			}
			$snapshotID = ORM::save($snapshotBean);
			if ( $snapshotID === false ) {
				self::$error = ORM::error();
				return false;
			}
		}
		// write log (when necessary)
		if ( !empty(self::$config['writeLog']) ) {
			$logged = Log::write([
				'action' => call_user_func(function(){
					// user-specified action name
					if ( is_string(self::$config['writeLog']) ) return self::$config['writeLog'];
					// default action name
					return empty(self::$config['beanID']) ? 'SUBMIT_WEBFORM' : 'UPDATE_WEBFORM';
				}),
				'entity_type' => self::$config['beanType'],
				'entity_id' => $id,
			]);
			if ( $logged === false ) {
				self::$error = Log::error();
				return false;
			}
		}
		// done!
		return $id;
	}




	/**
	<fusedoc>
		<description>
			clear cache & pre-load data to webform
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
					<structure name="otherData" optional="yes">
						<mixed name="~otherFieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function start() {
		$formData = array();
		// clear cache (if any)
		$cleared = self::clear();
		if ( $cleared === false ) return false;
		// load from database (when necessary)
		if ( !empty(self::$config['beanID']) ) {
			$bean = ORM::get(self::$config['beanType'], self::$config['beanID']);
			if ( $bean === false ) {
				self::$error = ORM::error();
				return false;
			}
		}
		// move bean data into container
		// ===> only need relevant fields
		// ===> (no need for all fields of own bean)
		$beanData = !empty($bean->id) ? $bean->export() : [];
		foreach ( $beanData as $key => $val ) if ( isset(self::$config['fieldConfig'][$key]) ) $formData[$key] = $val;
		// move other data into container (when necessary)
		$otherData = !empty(self::$config['otherData']) ? self::$config['otherData'] : [];
		foreach ( $otherData as $key => $val ) $formData[$key] = $val;
		// put into cache
		self::data($formData);
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			check if specific step is valid
		</description>
		<io>
			<in>
				<string name="$step" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function stepExists($step) {
		if ( !isset(self::$config['steps'][$step]) or self::$config['steps'][$step] === false ) {
			self::$error = "Step [{$step}] not exists";
			return false;
		}
		return true;
	}




	/**
	<fusedoc>
		<description>
			obtain config of related fields in specific step
		</description>
		<io>
			<in>
				<string name="step" />
			</in>
			<out>
				<structure name="~return~">
					<mixed name="~fieldName~" comments="field config" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function stepFields($step) {
		$result = array();
		// validation
		if ( self::stepExists($step) === false ) return false;
		// go through field layout of specific step
		if ( is_array(self::$config['steps'][$step]) ) {
			foreach ( self::$config['steps'][$step] as $fieldNameList => $fieldWidthList ) {
				if ( self::parseStepRow($fieldNameList, true) == 'fields' ) {
					$fieldNameList = explode('|', $fieldNameList);
					foreach ( $fieldNameList as $fieldName ) {
						$fieldConfig = isset(self::$config['fieldConfig'][$fieldName]) ? self::$config['fieldConfig'][$fieldName] : array();
						if ( isset($fieldConfig['format']) and $fieldConfig['format'] != 'output' ) $result[$fieldName] = $fieldConfig;
					}
				}
			}
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			this is possible that user has opened multiple webforms
			===> (opened in different browser windows)
			===> use session to store one set of form data is not safe
			===> use session to store multiple set of form data and distinguish by token
			[known issue]
			===> this is still incapable to open multiple webforms to submit new form
			===> because both windows will share same session (token=[~beanType~::0])
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
				</structure>
			</in>
			<out>
				<string name="~return~" value="~beanType~::~beanID~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function token() {
		return self::$config['beanType'].':'.self::$config['beanID'];
	}




	/**
	<fusedoc>
		<description>
			perform (ajax) file upload of webform file/image fields
			===> upload to temp directory only
		</description>
		<io>
			<in>
				<!-- framework -->
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" example="/path/to/upload/dir/" />
					<string name="uploadUrl" example="https://my.domain.com/base/url/to/upload/dir/" />
				</structure>
				<!-- library -->
				<structure name="$libPath" scope="self">
					<string name="uploadFile" comments="server-side script of SimpleAjaxUploader library" />
				</structure>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" />
							<list name="filetype" delim="," />
							<string name="filesize" example="5MB|100KB|etc." />
						</structure>
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$uploaderID" comments="html elementID of button which applied simple-ajax-uploader" />
				<string name="$fieldName" comments="webform field name" />
				<string name="$originalName" comments="original filename" />
			</in>
			<out>
				<!-- return value -->
				<structure name="~return~">
					<boolean name="success" />
					<string name="msg" />
					<string name="filename" optional="yes" oncondition="when success" />
					<string name="baseUrl" optional="yes" oncondition="when success" />
					<string name="fileUrl" optional="yes" oncondition="when success" />
				</structure>
				<!-- uploaded file -->
				<file path="~uploadDir~/tmp/~sessionID~/~uniqueFilename~.~fileExt~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function uploadFileToTemp($uploaderID, $fieldName, $originalName) {
		// load library
		$lib = self::$libPath['uploadFile'];
		if ( !file_exists($lib) ) {
			self::$error = "Could not load [SimpleAjaxUploader] library (path={$lib})";
			return false;
		}
		require_once $lib;
		// validation
		$err = array();
		if ( !in_array(self::$config['fieldConfig'][$fieldName]['format'], ['file','image']) ) {
			$err[] = "Field [{$fieldName}] must be [format=file|image]";
		}
		// check if any error
		if ( !empty($err) ) {
			self::$error = implode("\n", $err);
			return false;
		}
		// determine target directory
		$uploadDir  = str_replace('\\', '/', F::config('uploadDir'));
		$uploadDir .= ( substr($uploadDir, -1) == '/' ) ? '' : '/';
		$uploadDir .= 'tmp/'.session_id().'/';
		$uploadBaseUrl  = str_replace('\\', '/', F::config('uploadUrl'));
		$uploadBaseUrl .= ( substr($uploadBaseUrl, -1) == '/' ) ? '' : '/';
		$uploadBaseUrl .= 'tmp/'.session_id().'/';
		// create directory (when necessary)
		if ( !file_exists($uploadDir) and !mkdir($uploadDir, 0766, true) ) {
			self::$error = error_get_last()['message'];
			return false;
		}
		// init object (specify [uploaderID] to know which DOM to update)
		$uploader = new FileUpload($uploaderID);
		// config : array of permitted file extensions (only allow image & doc by default)
		// ===> validate file type again on server-side
		$uploader->allowedExtensions = explode(',', self::$config['fieldConfig'][$fieldName]['filetype']);
		// config : max file upload size in bytes
		// ===> validate file size again on server-side
		// ===> please make sure php {upload_max_filesize} config is larger
		$uploader->sizeLimit = self::fileSizeInBytes(self::$config['fieldConfig'][$fieldName]['filesize']);
		// config : assign unique name to avoid overwrite
		$uuid = Util::uuid();
		if ( $uuid === false ) {
			self::$error = Util::error();
			return false;
		}
		$originalName = urldecode($originalName);
		$uploader->newFileName = pathinfo($originalName, PATHINFO_FILENAME).'_'.$uuid.'.'.pathinfo($originalName, PATHINFO_EXTENSION);
		// upload to specific directory
		$uploader->uploadDir = $uploadDir;
		$uploaded = $uploader->handleUpload();
		// validate upload result
		if ( !$uploaded ) {
			self::$error = $uploader->getErrorMsg();
			return false;
		}
		// success!
		return array(
			'success'    => true,
			'msg'        => 'File uploaded successfully',
			'baseUrl'    => $uploadBaseUrl,
			'fileUrl'    => $uploadBaseUrl.$uploader->getFileName(),
			'filename'   => $uploader->getFileName(),
			'isWebImage' => $uploader->isWebImage($uploader->uploadDir.$uploader->getFileName()),
		);
	}




	/**
	<fusedoc>
		<description>
			convert (multiple) fields of signature data to file
			===> upload to temp directory only
		</description>
		<io>
			<in>
				<!-- framework -->
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
					<string name="uploadUrl" />
				</structure>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" comments="signature" />
						</structure>
					</structure>
				</structure>
				<!-- form data -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~beanType~:~beanID~">
						<string name="~fieldName~" comments="signature data in svg-xml format" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- uploaded file -->
				<file path="~uploadDir~/tmp/~sessionID~/~uniqueFilename~.svg" />
				<!-- updated form data -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~beanType~:~beanID~">
						<string name="~fieldName~" value="~uploadUrl~/tmp/~sessionID~/~uniqueFilename~.svg" comments="file url" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function uploadSignatureToTemp() {
		// unify slash & ensure trailing slash
		$uploadDir = str_replace('\\', '/', F::config('uploadDir'));
		$uploadUrl = str_replace('\\', '/', F::config('uploadUrl'));
		if ( substr($uploadDir, -1) != '/' ) $uploadDir .= '/';
		if ( substr($uploadUrl, -1) != '/' ) $uploadUrl .= '/';
		// load form data
		$formData = self::data();
		if ( $formData === false ) return false;
		// go through each field
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			$isSignatureData = ( $cfg['format'] == 'signature' and isset($formData[$fieldName]) and substr($formData[$fieldName], -6) == '</svg>' );
			// check field-format & data-format
			// ===> only proceed when signature data
			// ===> (skip when empty or file url)
			if ( $isSignatureData ) {
				// determine unique file name
				$uuid = Util::uuid();
				if ( $uuid === false ) {
					self::$error = Util::error();
					return false;
				}
				$uniqueFilename = $uuid.'.svg';
				// determine file location
				$filePath = $uploadDir.'tmp/'.session_id().'/'.$uniqueFilename;
				$fileUrl  = $uploadUrl.'tmp/'.session_id().'/'.$uniqueFilename;
				$fileDir  = dirname($filePath);
				// create directory (when necessary)
				if ( !file_exists($fileDir) and !mkdir($fileDir, 0766, true) ) {
					self::$error = error_get_last()['message'];
					return false;
				}
				// turn signature into file
				if ( !file_put_contents($filePath, $formData[$fieldName]) ) {
					self::$error = error_get_last()['message'];
					return false;
				}
				// update form data container
				$formData[$fieldName] = $fileUrl;
			} // if-signature
		} // foreach-fieldConfig
		// update form data
		$updated = self::data($formData);
		if ( $updated === false ) return false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			validate data of specific step
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" comments="email|date|dropdown|radio|checkbox" />
							<boolean name="required" />
							<structure name="options" />
							<number name="maxlength" />
							<number name="minlength" />
							<number name="max" />
							<number name="min" />
						</structure>
					</structure>
				<!-- parameter -->
				<string name="$step" />
				<structure name="$data">
					<mixed name="~fieldName~" />
				</structure>
				<!-- reference -->
				<structure name="&$err" comments="more error info" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- more error info -->
				<structure name="$more">
					<string name="~fieldName~" comments="error message" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function validate($step, $data, &$err=[]) {
		$fieldConfig = self::stepFields($step);
		if ( $fieldConfig === false ) return false;
		// go through each field in specific step
		foreach ( $fieldConfig as $fieldName => $cfg ) {
			$fieldValue = isset($data[$fieldName]) ? $data[$fieldName] : '';
			// flatten options (when necessary)
			if ( !empty($cfg['options']) ) {
				$flattenOptions = array();
				foreach ( $cfg['options'] as $optValue => $optText ) {
					if ( is_array($optText) ) foreach ( $optText as $key => $val ) $flattenOptions[$key] = $val;
					else $flattenOptions[$optValue] = $optText;
				}
			}
			// check required
			if ( !empty($cfg['required']) and empty($fieldValue) and $fieldValue !== 0 ) {
				$err[$fieldName] = "Field [{$fieldName}] is required";
			}
			// check format : email
			if ( $cfg['format'] == 'email' and !empty($fieldValue) and !filter_var($fieldValue, FILTER_VALIDATE_EMAIL) ) {
				$err[$fieldName] = "Invalid email format in [{$fieldName}] ({$fieldValue})";
			}
			// check format : date
			if ( $cfg['format'] == 'date' and !empty($fieldValue) and DateTime::createFromFormat('Y-m-d', $fieldValue) === false ) {
				$err[$fieldName] = "Invalid date format in [{$fieldName}] ({$fieldValue})";
			}
			// check options : checkbox (multiple selection)
			if ( $cfg['format'] == 'checkbox' and $fieldValue !== '' ) {
				foreach ( $fieldValue as $selectedItem ) if ( !isset($flattenOptions[$selectedItem]) ) {
					$err[$fieldName] = "Value of [{$fieldName}] is invalid ({$selectedItem})";
				}
			}
			// check options : dropdown & radio (single selection)
			if ( in_array($cfg['format'], ['dropdown','radio']) and $fieldValue !== '' and !isset($flattenOptions[$fieldValue]) ) {
				$err[$fieldName] = "Value of [{$fieldName}] is invalid ({$fieldValue})";
			}
			// check length : max
			if ( !empty($cfg['maxlength']) and strlen($fieldValue) > $cfg['maxlength'] ) {
				$err[$fieldName] = "Length of [{$fieldName}] is too long (max={$cfg['maxlength']},now=".strlen($fieldValue).")";
			}
			// check length : min
			if ( !empty($cfg['minlength']) and strlen($fieldValue) > $cfg['minlength'] ) {
				$err[$fieldName] = "Length of [{$fieldName}] is too short (min={$cfg['minlength']},now=".strlen($fieldValue).")";
			}
			// check value : max
			if ( !empty($cfg['max']) and strlen($fieldValue) > $cfg['max'] ) {
				$err[$fieldName] = "Value of [{$fieldName}] is too large (max={$cfg['max']},now=".strlen($fieldValue).")";
			}
			// check value : min
			if ( !empty($cfg['min']) and strlen($fieldValue) > $cfg['min'] ) {
				$err[$fieldName] = "Value of [{$fieldName}] is too small (min={$cfg['min']},now=".strlen($fieldValue).")";
			}
		} // foreach-fieldConfig
		// check if any error
		if ( !empty($err) ) {
			self::$error = implode(PHP_EOL, $err);
			return false;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			validate all data in cache
		</description>
		<io>
			<in>
				<!-- parameter -->
				<structure name="$data">
					<mixed name="~fieldName~" />
				</structure>
				<!-- reference -->
				<structure name="&$err" comments="more error info" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- more error info -->
				<structure name="$more">
					<structure name="~stepName~">
						<string name="~fieldName~" comments="error message" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function validateAll(&$err=[]) {
		$data = self::data();
		if ( $data === false ) return false;
		// go through all steps
		foreach ( array_keys(self::$config['steps']) as $stepName ) {
			// validate each step
			$validated = self::validate($stepName, $data, $stepErr);
			// group error by step
			if ( $validated === false ) $err[$stepName] = $stepErr;
		}
		// check if any error
		if ( !empty($err) ) {
			self::$error = '';
			foreach ( $err as $stepName => $stepErr ) {
				self::$error .= '<h5>'.$stepName.'</h5>';
				self::$error .= implode(PHP_EOL, $stepErr);
			}
			return false;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			perform validation on webform config
		</description>
		<io>
			<in>
				<!-- webform config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<string name="layoutPath" comments="can be false but cannot be null" />
					<number name="beanID" />
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
					<structure name="notification">
						<string name="from" />
						<string name="to" />
						<string name="subject" />
						<string name="body" />
					</structure>
					<boolean_or_string name="writeLog" />
				</structure>
				<!-- framework config -->
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
					<string name="uploadUrl" />
				</structure>
				<!-- class -->
				<class name="Log" />
				<class name="Util" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function validateConfig() {
		// has any file-field?
		$hasFileField = false;
		if ( isset(self::$config['fieldConfig']) ) {
			foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
				if ( isset($cfg['format']) and in_array($cfg['format'], ['file','image','signature']) ) $hasFileField = true;
			}
		}
		// check bean type
		if ( empty(self::$config['beanType']) ) {
			self::$error = 'Webform config [beanType] is required';
			return false;
		} elseif ( strpos(self::$config['beanType'], '_') !== false ) {
			self::$error = 'Webform config [beanType] cannot contain underscore';
			return false;
		// check layout path (false is allowed)
		} elseif ( !isset(self::$config['layoutPath']) ) {
			self::$error = 'Webform config [layoutPath] is required';
			return false;
		// check field config
		} elseif ( empty(self::$config['fieldConfig']) ) {
			self::$error = 'Webform config [fieldConfig] is required';
			return false;
		// check uploader directory (when has file field)
		} elseif ( empty(F::config('uploadDir')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadDir] is required';
			return false;
		} elseif ( empty(F::config('uploadUrl')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadUrl] is required';
			return false;
		// check component
		} elseif ( !empty(F::config('captcha')) and !class_exists('Captcha') ) {
			self::$error = 'Class [Captcha] is required';
			return false;
		} elseif ( !empty(self::$config['writeLog']) and !class_exists('Log') ) {
			self::$error = 'Class [Log] is required';
			return false;
		} elseif ( !empty(self::$config['notification']) and !class_exists('Util') ) {
			self::$error = 'Class [Util] is required';
			return false;
		}
		// check bean ID
		if ( !empty(self::$config['beanID']) ) {
			$bean = ORM::get(self::$config['beanType'], self::$config['beanID']);
			if ( empty($bean->id) ) {
				self::$error = 'Record not found (id='.self::$config['beanID'].')';
				return false;
			}
		}
		// check field config : any missing
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			if ( is_array($fieldLayout) ) {
				foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
					if ( self::parseStepRow($fieldNameList, true) == 'fields' ) {
						$fieldNameList = explode('|', str_replace(',', '|', $fieldNameList));
						foreach ( $fieldNameList as $fieldName ) {
							if ( !empty($fieldName) and !isset(self::$config['fieldConfig'][$fieldName]) ) {
								self::$error = "Field config for [{$fieldName}] is required";
								return false;
							}
						} // foreach-fieldName
					} // if-parseStepRow-fields
				} // foreach-fieldLayout
			} // is-fieldLayout
		} // foreach-step
		// get columns of database table
		$columns = ORM::columns(self::$config['beanType']);
		if ( $columns === false ) {
			self::$error = ORM::error();
			return false;
		}
		// check each field config
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// field config : options
			if ( isset($cfg['format']) and in_array($cfg['format'], ['checkbox','radio']) and !isset($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] is required";
				return false;
			} elseif ( isset($cfg['options']) and $cfg['options'] !== false and !is_array($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] must be array";
				return false;
			}
			// field config : custom
			if ( isset($cfg['format']) and $cfg['format'] == 'custom' and !isset($cfg['customScript']) ) {
				self::$error = "Custom script for [{$fieldName}] is required";
				return false;
			} elseif ( isset($cfg['format']) and $cfg['format'] == 'custom' and !file_exists($cfg['customScript']) ) {
				self::$error = "Custom script for [{$fieldName}] not exists ({$cfg['customScript']})";
				return false;
			}
			// field config : nested field name
			$fieldNameArray = array_filter(explode('.', $fieldName));
			$isNestedField = ( count($fieldNameArray) > 1 );
			if ( isset($columns[$fieldNameArray[0].'_id']) ) {
				self::$error = "Field name [{$fieldNameArray[0]}.*] is forbidden (because clashing with associated object [{$fieldNameArray[0]}] of ORM)";
				return false;
			}
		}
		// check notification : any missing
		foreach ( ['from','to','subject','body'] as $item ) {
			if ( is_array(self::$config['notification']) and empty(self::$config['notification'][$item]) ) {
				self::$error = "Webform notification config [{$item}] is required";
				return false;
			}
		}
		// done!
		return true;
	}


} // class
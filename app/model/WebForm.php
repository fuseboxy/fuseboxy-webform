<?php
class Webform {


	private static $mode;
	// properties : webform config
	public static $config;
	// properties : library for corresponding methods
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
			fix field-layout which only specified field-name-list
			fix field-config which only specified field-name
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~">
							<list name="+" value="~fieldNameList~" optional="yes" delim="|" comments="when no key specified, value is field-name-list" />
						</structure>
					</structure>
					<structure name="fieldConfig">
						<string name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~">
							<list name="~fieldNameList~" value="~empty~" />
						</structure>
					</structure>
					<structure name="fieldConfig">
						<array name="~fieldName~" value="~empty~" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	*/
	private static function fixConfig() {
		// fix field-layout of each step
		// ===> when only field-name-list specified
		// ===> use field-name-list as key & apply empty field-width-list
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			// remove unnecessary step
			// ===> e.g. [confirm=false]
			if ( $fieldLayout === false ) {
				unset(self::$config['steps'][$stepName]);
			// turn true into empty array
			// ===> e.g. [confirm=true] >>> [confirm=array()]
			} elseif ( $fieldLayout === true ) {
				self::$config['steps'][$stepName] = array();
			// turn string into array
			// ===> e.g. [declare='col_1|col_2'] >>> [declare=array('col_1|col_2' => '')]
			} elseif ( is_string($fieldLayout) ) {
				self::$config['steps'][$stepName] = array($fieldLayout => '');
			// go through well-formatted field-layout
			// ===> make sure field-name-list is key & field-width-list is value
			} elseif ( is_array($fieldLayout) ) {
				self::$config['steps'][$stepName] = array();
				foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
					if ( is_numeric($fieldNameList) ) {
						$fieldNameList = $fieldWidthList;
						$fieldWidthList = '';
					}
					self::$config['steps'][$stepName][$fieldNameList] = $fieldWidthList;
				}
			// invalid format
			} else {
				self::$error = "Field layout of step [{$stepName}] is invalid";
				return false;
			}
		}
		// fix field-config
		// ===> when only field-name specified
		// ===> use field-name as key & apply empty config
		$arr = self::$config['fieldConfig'];
		self::$config['fieldConfig'] = array();
		foreach ( $arr as $fieldName => $config ) {
			if ( is_numeric($fieldName) ) {
				$fieldName = $config;
				$config = array();
			}
			self::$config['fieldConfig'][$fieldName] = $config;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default config
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
					<!-- default steps (when unspecified) -->
					<structure name="steps">
						<structure name="default" />
						<structure name="confirm" />
					</structure>
					<!-- default field config -->
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" default="text" />
							<string name="label" optional="yes" comments="derived from field name" />
							<string name="label-inline" optional="yes" comments="derived from field name" />
							<string name="placeholder" optional="yes" comments="derived from field name" />
						</structure>
						<!-- default for [format=file|image|signature] only -->
						<string name="filesize" default="10MB" />
						<list name="filetype" delim="," default="gif,jpg,jpeg,png,txt,doc,docx,pdf,ppt,pptx,xls,xlsx" />
						<string name="filesizeError" default="File cannot exceed {FILE_SIZE}" />
						<string name="filetypeError" default="Only file of {FILE_TYPE} is allowed" />
						<string name="buttonText" default="Choose File" />
						<string name="buttonAltText" default="Choose Another File" />
						<!-- attribute -->
						<string name="value" optional="yes" oncondition="when [beanID] specified" comments="force filling with this value even if field has value" />
					</structure>
					<!-- others -->
					<string name="saveSnapshot" default="snapshot" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig() {
		$fixed = self::fixConfig();
		if ( $fixed === false ) return false;
		// beanID : default
		// ===> zero stands for submitting new form
		// ===> non-zero stands for editing submitted form
		if ( empty(self::$config['beanID']) ) self::$config['beanID'] = 0;
		// field config : default
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// format : default
			if ( empty($cfg['format']) and isset($cfg['options']) ) {
				self::$config['fieldConfig'][$fieldName]['format'] = 'dropdown';
			} elseif ( empty($cfg['format']) ) {
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
				// button text : default
				if ( empty($cfg['buttonText']) ) self::$config['fieldConfig'][$fieldName]['buttonText'] = 'Choose File';
				// button alt-text : default
				if ( empty($cfg['buttonAltText']) ) self::$config['fieldConfig'][$fieldName]['buttonAltText'] = 'Choose Another File';
			}
		}
		// set default steps
		// ===> when none specified
		// ===> simply use all fields as specified in field-config
		if ( empty(self::$config['steps']) ) {
			self::$config['steps'] = array('default' => array_keys(self::$config['fieldConfig']));
		}
		// append [confirm] step (when necessary)
		if ( !isset(self::$config['steps']['confirm']) ) {
			self::$config['steps']['confirm'] = array();
		}
		// set default field-layout of [confirm] step (when necessary)
		if ( is_array(self::$config['steps']['confirm']) and empty(self::$config['steps']['confirm']) ) {
			self::$config['steps']['confirm'] = array();
			foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
				// copy field-layout of other steps
				if ( $stepName != 'confirm' ) foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
					self::$config['steps']['confirm'][$fieldNameList] = $fieldWidthList;
				}
			}
		}
		// default snapshot table
		if ( isset(self::$config['saveSnapshot']) and self::$config['saveSnapshot'] === true ) {
			self::$config['saveSnapshot'] = 'snapshot';
		}
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
			pre-load data to webform when edit
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
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function load() {
		// check mode
		$mode = self::mode();
		if ( $mode === false ) return false;
		// do nothing (when new)
		if ( $mode == 'new' ) return true;
		// load record from database (when edit)
		$bean = ORM::get(self::$config['beanType'], self::$config['beanID']);
		if ( $bean === false ) {
			self::$error = ORM::error();
			return false;
		}
		// validation
		if ( empty($bean->id) ) {
			self::$error = 'Record not found (id='.self::$config['beanID'].')';
			return false;
		}
		// put into cache
		self::data( $bean->export() );
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			determine mode of webform (new or edit)
		</description>
		<io>
			<in>
				<number name="$config" scope="self">
					<number name="beanID" optional="yes" />
				</number>
			</in>
			<out>
				<string name="~return~" comments="new|edit" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function mode() {
//return 'view';
		return empty(self::$config['beanID']) ? 'new' : 'edit';
	}




	/**
	<fusedoc>
		<description>
			move uploaded file from temp to permanent directory
		</description>
		<io>
			<in>
				<!-- uploaded file -->
				<file name="~uploadDir~/tmp/~uniqueFilename~" />
				<!-- parameter -->
				<string name="$fieldName" />
			</in>
			<out>
				<!-- return value -->
				<boolean name="~return~" />
				<!-- re-located file -->
				<file name="~uploadDir~/~beanType~/~fieldName~/~uniqueFilename~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function moveFileToPerm($fieldName) {
		// determine source directory


		// determine target directory


		// create directory (when necessary)


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
					<structure name="notification">
						<string name="fromName" />
						<string name="from" />
						<list name="to" delim=";," />
						<list name="cc" delim=";," />
						<list name="bcc" delim=";," />
						<string name="subject" />
						<string name="body" />
					</structure>
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function notify() {
		// get config
		$cfg = self::$config['notification'];
		// prepare mail
		$mail = array(
			'from_name' => !empty($cfg['fromName']) ? $cfg['fromName'] : null,
			'from'      => !empty($cfg['from'])     ? $cfg['from']     : null,
			'to'        => !empty($cfg['to'])       ? $cfg['to']       : null,
			'cc'        => !empty($cfg['cc'])       ? $cfg['cc']       : null,
			'bcc'       => !empty($cfg['bcc'])      ? $cfg['bcc']      : null,
			'subject'   => $cfg['subject'],
			'body'      => $cfg['body'],
		);
		// prepare mapping of mask & data
		$data = self::data();
		if ( $data === false ) return false;
		$masks = array();
		foreach ( $data as $key => $val ) $masks["[[{$key}]]"] = $val;
		// replace mask in subject & body
		foreach ( $masks as $key => $val ) {
			$mail['subject'] = str_ireplace($key, $val, $mail['subject']);
			$mail['body']    = str_ireplace($key, $val, $mail['body']);
		}
		// send email
		if ( Util::mail($mail) === false ) {
			self::$error = Util::error();
			return false;
		}
		// done!
		return true;
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
			render form of specific step
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="steps">
						<mixed name="~stepName~" />
					</structure>
				</structure>
				<!-- parameter -->
				<string name="$step" />
			</in>
			<out>
				<string name="~return~" comments="form output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function render($step) {
		// validation
		if ( !self::stepExists($step) ) return false;
		// determine mode
		$mode = self::mode();
		if ( $mode === false ) return false;
		// exit point
		$prevStep = self::prevStep($step);
		$nextStep = self::nextStep($step);
		if ( $prevStep !== false ) $xfa['back'] = F::command('controller').'.back&step='.$prevStep;
		if ( $nextStep !== false ) $xfa['next'] = F::command('controller').'.validate&step='.$step;
		else $xfa['submit'] = F::command('controller').'.save';
		// exit point (for ajax upload)
		$xfa['uploadHandler'] = F::command('controller').'.upload';
		$xfa['uploadProgress'] = F::command('controller').'.upload-progress';
		// prepare variables
		$fieldLayout = self::$config['steps'][$step];
		$fieldConfigAll = self::$config['fieldConfig'];
		// load data from cache
		$arguments['data'] = self::data();
		if ( $arguments['data'] === false ) return false;
		// display
		ob_start();
		include dirname(__DIR__).'/view/webform/form.php';
		// done!
		return ob_get_clean();
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
						<mixed name="~stepName~" />
					</structure>
				</structure>
			</in>
			<out>
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderAll() {

	}




	/**
	<fusedoc>
		<description>
			clean-up data
		</description>
		<io>
			<in>
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
				// trim spaces
				$val = trim($val);
				// remove tab
				$val = str_replace("\t", ' ', $val);
				// convert html tag (make it visible but harmless)
				$val = preg_replace ('/<([^>]*)>/', '[$1]', $val);
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
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
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
		// load cached ata
		$data = self::data();
		if ( $data === false ) return false;
		// put into container
		$bean = ORM::new(self::$config['beanType'], $data);
		if ( $bean === false ) {
			self::$error = ORM::error();
			return false;
		}
		// specify ID to update (when necessary)
		if ( !empty(self::$config['beanID']) ) $bean->id = self::$config['beanID'];
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
		// done!
		return $id;
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
			upload to temp directory only
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
				<file name="~uploadDir~/tmp/~uniqueFilename~.~fileExt~" />
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
		if ( !isset(self::$config['fieldConfig'][$fieldName]) ) {
			$err[] = "Field config for [{$fieldName}] is required";
		}
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
		$uploadDir .= 'tmp/';
		$uploadBaseUrl  = str_replace('\\', '/', F::config('uploadUrl'));
		$uploadBaseUrl .= ( substr($uploadBaseUrl, -1) == '/' ) ? '' : '/';
		$uploadBaseUrl .= 'tmp/';
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
		$originalName = urldecode($originalName);
		$uploader->newFileName = pathinfo($originalName, PATHINFO_FILENAME).'_'.date('YmdHis').'_'.uniqid().'.'.pathinfo($originalName, PATHINFO_EXTENSION);
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
			validate all steps
		</description>
	</fusedoc>
	*/
	public static function validateAll() {

	}




	/**
	<fusedoc>
		<description>
			validate data of specific step
		</description>
		<io>
			<in>
				<structure name="$data">
				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function validate($data) {
		// check required
		// check format
		// check fixed value
		// check custom rule

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
					<boolean name="writeLog" />
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
		$fixed = self::fixConfig();
		if ( $fixed === false ) return false;
		// has file field?
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
				self::$error = 'Webform record not found (id='.self::$config['beanID'].')';
				return false;
			}
		}
		// check field config : any missing
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
				$isDirectOutput = ( strlen($fieldNameList) and $fieldNameList[0] === '~' );
				$isHeading = ( strlen($fieldNameList) != strlen(ltrim($fieldNameList, '#')) );
				$isLine = ( !empty($fieldNameList) and trim(trim($fieldNameList), '=-') === '' );
				if ( !$isDirectOutput and !$isHeading and !$isLine ) {
					$fieldNameList = explode('|', $fieldNameList);
					foreach ( $fieldNameList as $fieldName ) {
						if ( !isset(self::$config['fieldConfig'][$fieldName]) ) {
							self::$error = "Field config for [{$fieldName}] is required";
							return false;
						}
					}
				}
			}
		}
		// check field config : options
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			if ( isset($cfg['format']) and in_array($cfg['format'], ['checkbox','radio']) and !isset($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] is required";
				return false;
			} elseif ( isset($cfg['options']) and !is_array($cfg['options']) ) {
				self::$error = "Options for [{$fieldName}] must be array";
				return false;
			}
		}
		// check notification : any missing
		foreach ( ['from','to','subject','body'] as $item ) {
			if ( !empty(self::$config['notification']) and empty(self::$config['notification'][$item]) ) {
				self::$error = "Webform notification config [{$item}] is required";
				return false;
			}
		}
		// done!
		return true;
	}


} // class
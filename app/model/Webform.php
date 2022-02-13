<?php
class Webform {


	// property : webform config
	public static $config;
	// property : record to process
	public static $bean;
	// property : webform working mode
	private static $mode = 'view';
	// property : source of data for [renderField] method : {progressData|beanData}
	private static $dataRender = 'progressData';
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
			save in-progress form data into database
			===> no data validation is needed
		</description>
		<io>
			<in>
				<structure name="$data" />
			</in>
			<out>
				<datetime name="~return~" comments="last saved time" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function autosave($data) {
//		$currentUser = Auth::user('username');
//		$currentYear = AcademicYear::current();
		// check data format
/*
		if ( !is_array($data) ) {
			self::$error = 'Invalid form data format';
			return false;
		}
*/

/***** WORK-IN-PROGRESS *****/


		// done!
		return date('Y-m-d H:i:s');
	}




	/**
	<fusedoc>
		<description>
			access data of initial bean passed to webform
		</description>
		<io>
			<in />
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function beanData() {
		// validation
		if ( empty(self::$bean) ) {
			self::$error = 'Bean not specified';
			return false;
		}
		// export data
		// ===> return array instead of object
		$result = Bean::export(self::$bean);
		if ( $result === false ) {
			self::$error = Bean::error();
			return false;
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			clear progress data of webform
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
	public static function clearProgress() {
		$token = self::token();
		if ( $token === false ) return false;
		if ( isset($_SESSION['webform'][$token]) ) unset($_SESSION['webform'][$token]);
		return true;
	}




	/**
	<fusedoc>
		<description>
			merge (cached & submitted) data recursively
		</description>
		<io>
			<in>
				<structure name="$baseData" comments="base data to merge into; can be nested array" />
				<structure name="$newData" comments="new data to merge; can be nested array" />
				<string name="$parentKey" comments="determine nested key in order to find corresponding field format" />
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function dataMerge($baseData, $newData, $parentKey=null) {
		// go through each item in submitted data
		foreach ( $newData as $key => $val ) {
			// determine field name & field format
			$fieldName = implode('.', array_filter([$parentKey, $key]));
			$fieldFormat = self::fieldFormat($fieldName);
			// when field [format=table]
			// ===> simply overwrite
			// ===> make sure no removed table row retained
			if ( $fieldFormat == 'table' ) {
				$baseData[$key] = $val;
			// when array value
			// ===> for field with nested field name (e.g. student.hkid)
			// ===> keep merging recursively
			} elseif ( is_array($val) ) {
				$baseData[$key] = self::dataMerge($baseData[$key] ?? [], $val, $fieldName);
			// when simple value
			// ===> simply overwrite
			} else {
				if ( !is_array($baseData) ) $baseData = array();
				$baseData[$key] = $val;
			}
		}
		// done!
		return $baseData;
	}




	/**
	<fusedoc>
		<description>
			clean-up (submitted) data recursively
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
					<mixed name="~key~" />
				</struture>
				<string name="$parentKey" comments="determine nested key in order to find corresponding field format" />
			</in>
			<out>
				<structure name="~return~" comments="data after cleansed">
					<mixed name="~key~" />
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	private static function dataSanitize($data, $parentKey=null) {
		// go through each item
		foreach ( $data as $key => $val ) {
			// determine field name & field format
			$fieldName = implode('.', array_filter([$parentKey, $key]));
			$fieldFormat = self::fieldFormat($fieldName);
			// when array value
			// ===> clean-up recursively
			if ( is_array($val) ) {
				$data[$key] = self::dataSanitize($val, $key);
			// when simple value
			// ===> do the clean-up
			} else {
				// trim space & remove tab
				$val = str_replace("\t", ' ', trim($val));
				// convert html tag (to avoid cross-site scripting)
				// ===> make the tag be visible but harmless
				// ===> do NOT perform the replace on signature field (in order to keep SVG data)
				if ( $fieldFormat != 'signature' ) $val = preg_replace ('/<([^>]*)>/', '[$1]', $val);
				// put into result
				$data[$key] = $val;
			}
		}
		// done!
		return $data;
	}




	/**
	<fusedoc>
		<description>
			obtain field config of specific field
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="~fieldName~" optional="yes" />
				</structure>
				<!-- parameter -->
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
			obtain field format of specific field
		</description>
		<io>
			<in>
				<string name="$fieldName" />
			</in>
			<out>
				<string name="~return~" example="text|dropdown|file|.." />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldFormat($fieldName) {
		$fieldConfig = self::fieldConfig($fieldName);
		if ( $fieldConfig === false ) return false;
		return $fieldConfig['format'];
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
			load record from [config-bean] (or database) to [self-bean] property (as original data)
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<object name="bean" optional="yes" />
					<structure name="bean" optional="yes">
						<string name="type" />
						<number name="id" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- property -->
				<object name="$bean" scope="self" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
						<number name="id" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initBeanData() {
		// when already specified
		// ===> simply do nothing
		if ( !empty(self::$bean) ) {
			return true;
		// when [object] passed to config
		// ===> use it directly
		} elseif ( is_object(self::$config['bean']) ) {
			self::$bean = self::$config['bean'];
			self::$config['bean']['type'] = Bean::type(self::$bean);
			self::$config['bean']['id'] = self::$bean->id;
		// when [type & id] passed to config
		// ===> load from database
		} elseif ( is_array(self::$config['bean']) ) {
			if ( empty(self::$config['bean']['id']) ) self::$bean = ORM::new(self::$config['bean']['type']);
			else self::$bean = ORM::get(self::$config['bean']['type'], self::$config['bean']['id']);
			if ( self::$bean === false ) return ORM::error();
		}
		// done!
		return true;
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
					<structure name="bean">
						<string name="type" />
						<number name="id" optional="yes" />
					</structure>
					<string name="retainParam" optional="yes" default="" format="query-string" />
					<!-- permission -->
					<boolean name="allowEdit" default="false" />
					<boolean name="allowPrint" default="false" />
					<boolean name="allowBack" default="true" />
					<boolean name="allowNext" default="true" />
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
						</structure>
					</structure>
					<!-- others settings -->
					<structure name="notification" optional="yes">
						<string name="to" default=":email" />
					</structure>
					<string name="snapshot" default="snapshot" comments="table to save snapshot; take no snapshot when false" />
					<string name="autosave" default="autosave" comments="table to save autosave; perform no autosave when false" />
					<string name="closed" comments="message to show when form closed" />
					<!-- default custom message -->
					<structure name="customMessage">
						<string name="closed" />
						<string name="completed" />
						<string name="neverSaved" />
						<string name="lastSavedAt" />
						<string name="lastSavedOn" />
					</structure>
					<!-- default custom button -->
					<structure name="customButton">
						<structure name="next|back|edit|submit|update|print|autosave|chooseFile|chooseAnother">
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
		// bean : load record
		if ( self::initConfig__fixBeanConfig() === false ) return false;
		// retain param : default & fix
		if ( self::initConfig__fixRetainParam() === false ) return false;
		// form permission : edit & print : default
		if ( self::initConfig__defaultFormPermission() === false ) return false;
		// form state : opened & closed : default
		if ( self::initConfig__defaultFormState() === false ) return false;
		// field config : field-name-only to empty-array
		self::$config['fieldConfig'] = self::initConfig__defaultEmptyConfig(self::$config['fieldConfig'] ?? []);
		// field config : default format
		self::$config['fieldConfig'] = self::initConfig__defaultFieldFormat(self::$config['fieldConfig']);
		// field config : default label/inline-label/placeholder
		self::$config['fieldConfig'] = self::initConfig__defaultFieldLabel(self::$config['fieldConfig']);
		// field config : default dropdown config
		self::$config['fieldConfig'] = self::initConfig__defaultDropdownConfig(self::$config['fieldConfig']);
		// field config : default file config
		self::$config['fieldConfig'] = self::initConfig__defaultFileConfig(self::$config['fieldConfig']);
		// field config : default table config
		if ( self::initConfig__defaultTableConfig() === false ) return false;
		// field config : table default value
		if ( self::initConfig__defaultTableValue() === false ) return false;
		// steps : default & fix
		if ( self::initConfig__defaultSteps() === false ) return false;
		// notification : default & fix
		if ( self::initConfig__defaultNotification() === false ) return false;
		// snapshot : default table name
		if ( isset(self::$config['snapshot']) and self::$config['snapshot'] === true ) self::$config['snapshot'] = 'snapshot';
		// autosave : default table name
		if ( isset(self::$config['autosave']) and self::$config['autosave'] === true ) self::$config['autosave'] = 'autosave';
		// custom button : default
		if ( self::initConfig__defaultCustomButton() === false ) return false;
		// custom message : default
		if ( self::initConfig__defaultCustomMessage() === false ) return false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default & fix format of custom buttons
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="customButton">
						<string name="~btnKey~" value="~btnText~" />
						<structure name="~btnKey~">
							<string name="text" optional="yes" comments="allow {false} to show no text" />
							<string name="icon" optional="yes" comments="allow {false} to show no icon" />
						</structure>
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="customButton">
						<structure name="~btnKey~">
							<string name="text" />
							<string name="icon" />
						</structure>
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="true" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultCustomButton() {
		self::$config['customButton'] = self::$config['customButton'] ?? [];
		// all default
		$default = array(
			'next'          => array('text' => 'Next', 'icon' => 'fa fa-arrow-right ml-2'),
			'back'          => array('text' => 'Back', 'icon' => 'fa fa-arrow-left mr-1'),
			'edit'          => array('text' => 'Edit', 'icon' => 'fa fa-edit mr-1'),
			'print'         => array('text' => 'Print', 'icon' => 'fa fa-print mr-1'),
			'submit'        => array('text' => 'Submit', 'icon' => 'fa fa-paper-plane mr-1'),
			'update'        => array('text' => 'Update', 'icon' => 'fa fa-file-import mr-1'),
			'autosave'      => array('text' => 'Autosave', 'icon' => false),
			'chooseFile'    => array('text' => 'Choose File', 'icon' => false),
			'chooseAnother' => array('text' => 'Choose Another File', 'icon' => false),
		);
		// apply default (if not specified)
		foreach ( $default as $btnKey => $btnConfig ) {
			self::$config['customButton'][$btnKey] = self::$config['customButton'][$btnKey] ?? $btnConfig;
		}
		// check each custom button
		foreach ( self::$config['customButton'] as $btnKey => $btnConfig ) {
			// fix button config format (use as button text when string)
			if ( is_string($btnConfig) ) $btnConfig = array('text' => $btnConfig);
			// apply default button text (when not specified)
			if ( !isset($btnConfig['text']) ) $btnConfig['text'] = $default[$btnKey]['text'];
			// apply custom icon (when not specified)
			if ( !isset($btnConfig['icon']) ) $btnConfig['icon'] = $default[$btnKey]['icon'];
			// put into result
			self::$config['customButton'][$btnKey] = $btnConfig;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default of custom messages
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="customMessage">
						<string name="closed|completed|neverSaved|lastSavedAt|lastSavedOn" optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="customMessage">
						<string name="closed|completed|neverSaved|lastSavedAt|lastSavedOn" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultCustomMessage() {
		self::$config['customMessage'] = self::$config['customMessage'] ?? [];
		// all default
		$default = array(
			'closed'      => 'Form was closed.',
			'completed'   => 'Your submission was received.',
			'neverSaved'  => 'Never saved',
			'lastSavedAt' => 'Last saved at ',
			'lastSavedOn' => 'Last saved on ',
		);
		// apply default (when necessary)
		foreach ( $default as $msgKey => $msgText ) {
			if ( empty(self::$config['customMessage'][$msgKey]) ) self::$config['customMessage'][$msgKey] = $msgText;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			fix field config for dropdown field
		</description>
		<io>
			<in>
				<structure name="~return~">
					<structure name="~fieldName~">
						<string name="format" optional="yes" comments="dropdown|checkbox|radio" />
					</structure>
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~fieldName~">
						<structure name="$options" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultDropdownConfig($fieldConfigList) {
		// go through config of each field
		foreach ( $fieldConfigList as $fieldName => $cfg ) {
			// add empty [options] when not specified
			if ( !isset($cfg['options']) and isset($cfg['format']) and in_array($cfg['format'], ['dropdown','checkbox','radio']) ) {
				$fieldConfigList[$fieldName]['options'] = array();
			// remove [options] when false (but allow empty array)
			} elseif ( isset($cfg['options']) and ( $cfg['options'] === false or $cfg['options'] === null ) ) {
				unset($fieldConfigList[$fieldName]['options']);
			}
		}
		// done!
		return $fieldConfigList;
	}




	/**
	<fusedoc>
		<description>
			fix field config with field-name specified only
			===> when only field-name specified, use field-name as key & apply empty config
			===> when false or null, remove field config
			===> when config is string, use as label
		</description>
		<io>
			<in>
				<structure name="$fieldConfigList">
					<string name="+" value="~fieldName~" optional="yes" />
					<string name="~fieldName~" value="~label" optional="yes" />
					<structure name="~fieldName~" optional="yes" />
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~fieldName~">
						<string name="label" optional="yes" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultEmptyConfig($fieldConfigList) {
		$result = array();
		// go through config of each field
		foreach ( $fieldConfigList as $fieldName => $cfg ) {
			// when field-name only
			// ===> assign empty config
			if ( is_numeric($fieldName) and is_string($cfg) ) {
				$fieldName = $cfg;
				$cfg = array();
			}
			// when config is true   ===> assign empty config
			// when config is string ===> use as label
			if ( $cfg === true ) $cfg = array();
			elseif ( is_string($cfg) ) $cfg = array('label' => $cfg);
			// when config is not false
			// ===> (allow empty array)
			// ===> put into result
			if ( $cfg !== false and $cfg !== null ) $result[$fieldName] = $cfg;
		}
		// done!
		return $result;
	}




	/**
	<fusedoc>
		<description>
			assign default format
		</description>
		<io>
			<in>
				<structure name="$fieldConfigList">
					<structure name="~fieldName~">
						<string name="format" optional="yes" default="text" />
					</structure>
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~fieldName~">
						<string name="format" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultFieldFormat($fieldConfigList) {
		// go through config of each field
		foreach ( $fieldConfigList as $fieldName => $cfg ) {
			if ( empty($cfg['format']) or $cfg['format'] === true ) {
				$fieldConfigList[$fieldName]['format'] = 'text';
			}
		}
		// done!
		return $fieldConfigList;
	}




	/**
	<fusedoc>
		<description>
			assign default label/inline-label/placeholder to multiple fields
			===> derived from field name
		</description>
		<io>
			<in>
				<structure name="$fieldConfigList">
					<structure name="~fieldName~">
						<boolean name="label" value="true" optional="yes" />
						<boolean name="placeholder" value="true" optional="yes" />
						<boolean name="inline-label" value="true" optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~fieldName~">
						<string name="label" />
						<string name="placeholder" optional="yes" />
						<string name="inline-label" optional="yes" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultFieldLabel($fieldConfigList) {
		// go through config of each field
		foreach ( $fieldConfigList as $fieldName => $cfg ) {
			// force label to be assigned
			if ( !isset($cfg['label']) ) $cfg['label'] = true;
			// derived from field name
			foreach ( ['label','label-inline','placeholder'] as $key ) {
				if ( isset($cfg[$key]) and $cfg[$key] === true ) {
					$fieldConfigList[$fieldName][$key] = implode(' ', array_map(function($word){
						return in_array($word, ['id','url']) ? strtoupper($word) : ucfirst($word);
					}, explode('_', $fieldName)));
				}
			}
		}
		// done!
		return $fieldConfigList;
	}




	/**
	<fusedoc>
		<description>
			assign default (esssential) config for file-related fields
		</description>
		<io>
			<in>
				<structure name="$fieldConfigList">
					<structure name="~fieldName~">
						<string name="format" optional="yes" />
						<structure name="options" optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<structure name="~return~">
					<structure name="~fieldName~">
						<string name="format" />
					</structure>
				</structure>
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultFileConfig($fieldConfigList) {
		// go through config of each field
		foreach ( $fieldConfigList as $fieldName => $cfg ) {
			// certain format only
			if ( isset($cfg['format']) and in_array($cfg['format'], ['file','image','signature']) ) {
				// file size : default
				if ( empty($cfg['filesize']) ) $fieldConfigList[$fieldName]['filesize'] = '10MB';
				// file type : default
				if ( empty($cfg['filetype']) ) $fieldConfigList[$fieldName]['filetype'] = in_array($cfg['format'], ['image','signature']) ? 'gif,jpg,jpeg,png' : 'gif,jpg,jpeg,png,txt,doc,docx,pdf,ppt,pptx,xls,xlsx';
				// file size error : default
				if ( empty($cfg['filesizeError']) ) $fieldConfigList[$fieldName]['filesizeError'] = 'File cannot exceed {FILE_SIZE}';
				// file type error : default
				if ( empty($cfg['filetypeError']) ) $fieldConfigList[$fieldName]['filetypeError'] = 'Only file of {FILE_TYPE} is allowed';
			}
		}
		// done!
		return $fieldConfigList;
	}




	/**
	<fusedoc>
		<description>
			determine default form permission
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<boolean name="allowEdit" optional="yes" />
					<boolean name="allowPrint" optional="yes" />
					<boolean name="allowBack" optional="yes" />
					<boolean name="allowNext" optional="yes" />
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<boolean name="allowEdit" />
					<boolean name="allowPrint" />
					<boolean name="allowBack" />
					<boolean name="allowNext" />
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultFormPermission() {
		self::$config['allowEdit']  = self::$config['allowEdit']  ?? false;
		self::$config['allowPrint'] = self::$config['allowPrint'] ?? false;
		self::$config['allowBack']  = self::$config['allowBack']  ?? true;
		self::$config['allowNext']  = self::$config['allowNext']  ?? true;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			determine default form state
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<boolean name="opened" optional="yes" />
					<boolean name="closed" optional="yes" />
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<boolean name="opened" />
					<boolean name="closed" />
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultFormState() {
		self::$config['opened'] = self::$config['opened'] ?? true;
		self::$config['closed'] = self::$config['closed'] ?? false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default & fix notification settings
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="notification">
						<list name="to" delim=";," optional="yes" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="notification">
						<list name="to" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="true" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultNotification() {
		self::$config['notification'] = self::$config['notification'] ?? false;
		// fix format (when necessary)
		if ( self::$config['notification'] === true ) self::$config['notification'] = array();
		// default [to] setting
		// ===> send to value of [email] field
		if ( !empty(self::$config['notification']) and !isset(self::$config['notification']['to']) ) {
			self::$config['notification']['to'] = ':email';
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			set default steps and fix field layout
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="steps">
						<structure name="~stepName~" optional="yes" />
					</structure>
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="~return~">
					<structure name="~stepName~" />
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultSteps() {
		self::$config['steps'] = self::$config['steps'] ?? [];
		// set default steps
		// ===> when none specified
		// ===> simply use all fields as specified in field-config
		if ( empty(self::$config['steps']) and !empty(self::$config['fieldConfig']) ) {
			self::$config['steps']['default'] = array_keys(self::$config['fieldConfig']);
		}
		// make sure fully associated array
		$arr = self::$config['steps'];
		self::$config['steps'] = array();
		foreach ( $arr as $stepName => $fieldLayout ) {
			if ( is_numeric($stepName) ) self::$config['steps'][$fieldLayout] = array();
			else self::$config['steps'][$stepName] = $fieldLayout;
		}
		// default having [confirm] step
		// ===> allow {false} to skip [confirm] step
		self::$config['steps']['confirm'] = self::$config['steps']['confirm'] ?? true;
		// fix [heading|line|output] of each step
		// ===> add trailing space to make sure it is unique
		// ===> avoid being overridden after convert to key
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			if ( is_array($fieldLayout) ) {
				foreach ( $fieldLayout as $i => $stepRow ) {
					if ( self::stepRowType($stepRow) != 'fields' ) {
						self::$config['steps'][$stepName][$i] = $stepRow.str_repeat(' ', $i);
					}
				}
			}
		} // foreach-step
		// fix field-layout of each step
		// ===> when only field-name-list specified
		// ===> use field-name-list as key & apply empty field-width-list
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			// remove false/null step
			// ===> e.g. [ 'my-step' => null  ]  >>>  (remove)
			// ===> e.g. [ 'confirm' => false ]  >>>  (remove)
			if ( $fieldLayout === false or $fieldLayout === null ) unset(self::$config['steps'][$stepName]);
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
			}
		} // foreach-step
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			assign default field format for each table field
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" value="table" optional="yes" />
							<structure name="tableRow" optional="yes">
								<structure name="~tableFieldName~" comments="single field in one cell">
									<string name="format" optional="yes" />
								</structure>
								<string name="+" value="~tableFieldName~" comments="single field in one cell; only field name specified" />
								<structure name="+" comments="multiple fields in one cell">
									<structure name="~tableFieldName~">
										<string name="format" optional="yes" />
									</structure>
									<string name="+" value="~tableFieldName~" comments="multiple fields in one cell; only field name specified" />
								</structure>
							</structure>
							<file name="tableHeaderScript" optional="yes" />
							<file name="tableRowScript" optional="yes" />
						</structure>
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<structure name="tableRow" optional="yes">
								<structure name="~tableFieldName~" comments="single field in one cell">
									<string name="format" />
								</structure>
								<structure name="+" comments="multiple fields in one cell">
									<structure name="~tableFieldName~">
										<string name="format" />
									</structure>
								</structure>
							</structure>
						</structure>
						<file name="tableHeaderScript" default="~appPath~/view/webform/input.table.header.php" />
						<file name="tableRowScript" default="~appPath~/view/webform/input.table.row.php" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultTableConfig() {
		// go through config of each field
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// proceed when table format only
			if ( isset($cfg['format']) and $cfg['format'] == 'table' ) {
				// fix table header script
				if ( empty($cfg['tableHeaderScript']) ) $cfg['tableHeaderScript'] = F::appPath('view/webform/input.table.header.php');
				if ( !is_file($cfg['tableHeaderScript']) ) {
					self::$error = "Table header script [{$cfg['tableHeaderScript']}] of field [{$fieldName}] not found";
					return false;
				}
				self::$config['fieldConfig'][$fieldName]['tableHeaderScript'] = $cfg['tableHeaderScript'];
				// fix table row script
				if ( empty($cfg['tableRowScript']) ) $cfg['tableRowScript'] = F::appPath('view/webform/input.table.row.php');
				if ( !is_file($cfg['tableRowScript']) ) {
					self::$error = "Table row script [{$cfg['tableRowScript']}] of field [{$fieldName}] not found";
					return false;
				}
				self::$config['fieldConfig'][$fieldName]['tableRowScript'] = $cfg['tableRowScript'];
				// fix table row config
				if ( isset($cfg['tableRow']) ) {
					// fix field of single-field-in-one-cell
					$cfg['tableRow'] = self::initConfig__defaultEmptyConfig($cfg['tableRow']);
					$cfg['tableRow'] = self::initConfig__defaultFieldFormat($cfg['tableRow']);
					self::$config['fieldConfig'][$fieldName]['tableRow'] = $cfg['tableRow'];
					// fix each field of multi-field-in-one-cell
					foreach ( self::$config['fieldConfig'][$fieldName]['tableRow'] as $tableCellIndex => $tableCellFieldConfigList ) {
						if ( is_numeric($tableCellIndex) ) {
							$tableCellFieldConfigList = self::initConfig__defaultEmptyConfig($tableCellFieldConfigList);
							$tableCellFieldConfigList = self::initConfig__defaultFieldFormat($tableCellFieldConfigList);
							self::$config['fieldConfig'][$fieldName]['tableRow'][$tableCellIndex] = $tableCellFieldConfigList;
							// workaround to fix (unknown) bug of dummy [format] attribute
							unset(self::$config['fieldConfig'][$fieldName]['tableRow'][$tableCellIndex]['format']);
						}
					} // foreach-tableRow
				} // if-isset-tableRow
			} // if-format-table
		} // foreach-fieldConfig
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			convert table json data into array
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" value="table" />
							<structure_or_object name="default" optional="yes" format="json-object|json-array">
								<structure_or_object name="~rowIndex~" />
							</structure>
						</structure>
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~">
							<string name="format" value="table" />
							<structure name="default" optional="yes" format="php-array">
								<structure name="~rowIndex~" />
							</structure>
						</structure>
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__defaultTableValue() {
		// go through each field
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// if table-field as default defined...
			if ( isset($cfg['format']) and $cfg['format'] == 'table' and !empty($cfg['default']) ) {
				// convert json to array (when necessary)
				if ( is_string($cfg['default']) ) self::$config['fieldConfig'][$fieldName]['default'] = json_decode($cfg['default'], true);
				// convert object to array (when necessary)
				if ( is_object($cfg['default']) ) self::$config['fieldConfig'][$fieldName]['default'] = (array)$cfg['default'];
				// convert each item to array (when necessary)
				foreach ( self::$config['fieldConfig'][$fieldName]['default'] as $rowIndex => $item ) {
					self::$config['fieldConfig'][$fieldName]['default'][$rowIndex] = (array)$item;
				}
			}
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			convert config to array (e.g. ['type' => 'foo', 'id' => 123])
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="bean" optional="yes">
						<string name="type" />
						<string name="id" />
					</structure>
					<string name="bean" optional="yes" example="foo:123" />
					<object name="bean" optional="yes" />
				</structure>
			</in>
			<out>
				<!-- property -->
				<object name="$bean" scope="self" optional="yes" />
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
						<number name="id" optional="yes" />
					</structure>
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixBeanConfig() {
		// validation
		if ( empty(self::$config['bean']) ) {
			self::$error = 'Webform config [bean] cannot be empty';
			return false;
		// when string
		// ===> parse config
		} elseif ( is_string(self::$config['bean']) ) {
			$beanConfig = explode(':', self::$config['bean']);
			self::$config['bean'] = array(
				'type' => $beanConfig[0],
				'id' => (int)( $beanConfig[1] ?? 0 ),
			);
		// when (bean) object
		// ===> extract info from object
		// ===> assign object to property as is
		// ===> (do NOT load from database because the object might be manipulated already)
		} elseif ( is_object(self::$config['bean']) ) {
			self::$bean = self::$config['bean'];
			self::$config['bean'] = array('type' => Bean::type(self::$bean), 'id' => self::$bean->id);
		// when array
		// ===> convert nothing
		// ===> assign empty type & id (when necessary)
		} elseif ( is_array(self::$config['bean']) ) {
			$beanConfig = self::$config['bean'];
			self::$config['bean'] = array(
				'type' => $beanConfig['type'] ?? '',
				'id' => (int)( $beanConfig['id'] ?? 0 ),
			);
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			convert retain param into a query string (with &-prefixed)
			===> easier to implement on xfa
			===> also check for reserved word
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<structure name="retainParam" optional="yes">
						<string name="*" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- fixed config -->
				<structure name="$config" scope="self">
					<string name="retainParam" default="" example="&foo=1&bar=2" />
				</structure>
				<!-- return value -->
				<string name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig__fixRetainParam() {
		// determine default value
		if ( empty(self::$config['retainParam']) ) self::$config['retainParam'] = '';
		// convert from data to query-string
		if ( is_array(self::$config['retainParam']) ) self::$config['retainParam'] = http_build_query(self::$config['retainParam']);
		// prepend [&] to make it easier to implement to xfa
		if ( !empty(self::$config['retainParam']) ) self::$config['retainParam'] = '&'.self::$config['retainParam'];
		// check for reserved word
		if ( strpos(self::$config['retainParam'], '&step=') !== false ) {
			self::$error = '<strong>step</strong> is a reserved parameter and not allowed in [retainParam]';
			return false;
		}
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			load record data to session cache (as progress data)
		</description>
		<io>
			<in>
				<!-- property -->
				<object name="$bean" scope="self" />
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~" />
				</structure>
				<!-- return value -->
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initProgressData() {
		$formData = array();
		// load bean data
		$beanData = self::beanData();
		if ( $beanData === false ) return false;
		// move bean data into progress
		// ===> only move those specified in [fieldConig] (instead of full bean data)
		foreach ( self::$config['fieldConfig'] as $nestedFieldName => $cfg ) {
			// for field name of nested-key (e.g. exam.TOEFL.xxx)
			// ===> simply copy data of top level (e.g. exam)
			$fieldName = explode('.', $nestedFieldName)[0];
			// copy from bean if data exists
			if ( !empty($beanData[$fieldName]) ) $formData[$fieldName] = $beanData[$fieldName];
		}
		// retain data
		$retained = self::progressData($formData);
		if ( $retained === false ) return false;
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
					<structure name="bean">
						<string name="type" />
					</structure>
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
		$formData = self::progressData();
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
					$targetUrl  = $uploadUrl.self::$config['bean']['type'].'/'.$fieldName.'/'.basename($sourceUrl);
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
		$cached = self::progressData($formData);
		if ( $cached === false ) return false;
		// done!
		return true;
	}




	/**
	<fusedoc>
		<description>
			access nested-array value (e.g. data[student][name]) by period-delimited-list (e.g. student.name)
		</description>
		<io>
			<in>
				<list name="$nestedKey" delim="." />
				<array name="$nestedArray" />
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function nestedArrayGet($nestedKey, $nestedArray) {
		$nestedKey = explode('.', $nestedKey);
		$result = $nestedArray;
		foreach ( $nestedKey as $key ) $result = $result[$key] ?? null;
		return $result;
	}




	/**
	<fusedoc>
		<description>
			update nested-array (e.g. data[student][name]) by period-delimited-list (e.g. student.name)
		</description>
		<io>
			<in>
				<list name="$nestedKey" delim="." />
				<array name="&$pointer" comments="nested array; pass by reference" />
				<mixed name="$newValue" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function nestedArraySet($nestedKey, &$pointer, $newValue) {
		// reach deeper of pointer according to nested-key
		$token = strtok($nestedKey, '.');
		while ( $token !== false ) {
			if ( !isset($pointer[$token]) ) $pointer[$token] = array();
			$pointer = &$pointer[$token];
			$token = strtok('.');
		}
		// assign new value
		$pointer = $newValue;
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
	public static function progressData($data=null) {
		$token = self::token();
		// init container
		$_SESSION['webform'][$token] = $_SESSION['webform'][$token] ?? array();
		// when getter
		// ===> return cached data right away
		if ( $data === null ) return $_SESSION['webform'][$token];
		// when setter
		// ===> clean-up data just submitted
		// ===> merge cached data with data just submitted
		$data = self::dataSanitize($data);
		if ( $data === false ) return false;
		$data = self::dataMerge($_SESSION['webform'][$token], $data);
		if ( $data === false ) return false;
		$_SESSION['webform'][$token] = $data;
		// done!
		return true;
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
		$all = self::$config['steps'];
		if ( isset($all['confirm']) ) unset($all['confirm']);
		// essential variables
		$webform  = self::$config;
		$formStep = implode(array_keys($all));
		// display multiple steps
		ob_start();
		foreach ( $all as $step => $fieldLayout ) {
			foreach ( $fieldLayout as $key => $val ) echo self::renderRow($key, $val);
			if ( $step != array_key_last($all) ) echo '<br /><br />';
		}
		$formBody = ob_get_clean();
		// done!
		ob_start();
		include F::appPath('view/webform/form.php');
		include F::appPath('view/webform/autosave.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			render specific field
		</description>
		<io>
			<in>
				<string name="$fieldName" />
				<structure name="$fieldConfig" optional="yes" />
				<structure name="$formData" optional="yes" />
			</in>
			<out>
				<string name="~return~" comments="output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderField($fieldName, $fieldConfig=null, $formData=null) {
		$editable = in_array(self::$mode, ['new','edit']);
		// simply display nothing (when empty field name)
		if ( empty($fieldName) ) return '';
		// obtain field config (when necessary)
		$fieldConfig = $fieldConfig ?? self::fieldConfig($fieldName);
		if ( $fieldConfig === false ) return F::alertOutput([ 'type' => 'warning', 'message' => self::error() ]);
		// load data from bean/progress (when necessary)
		if ( !isset($formData) and self::$dataRender == 'progressData' ) $formData = self::progressData();
		elseif ( !isset($formData) and self::$dataRender == 'beanData' ) $formData = self::beanData();
		if ( $formData === false ) return F::alertOutput([ 'type' => 'warning', 'message' => self::error() ]);
		// more essential variables
		$webform = self::$config;
		$fieldID = self::fieldName2fieldID($fieldName);
		$dataFieldName = self::fieldName2dataFieldName($fieldName);
		// determine value to show in field
		// ===> precedence: defined-value > submitted-value > default-value > empty
		$fieldValue = $fieldConfig['value'] ?? self::nestedArrayGet($fieldName, $formData) ?? $fieldConfig['default'] ?? '';
		// exit point : ajax upload
		if ( !F::is('*.view,*.confirm') and in_array($fieldConfig['format'], ['file','image','signature']) ) {
			$xfa['uploadHandler'] = F::command('controller').'.upload'.self::$config['retainParam'];
			$xfa['uploadProgress'] = F::command('controller').'.uploadProgress'.self::$config['retainParam'];
		}
		// exit point : dynamic table
		if ( !F::is('*.view,*.confirm') and $fieldConfig['format'] == 'table' ) {
			$xfa['appendRow'] = F::command('controller').'.appendRow'.self::$config['retainParam'];
			$xfa['removeRow'] = F::command('controller').'.removeRow'.self::$config['retainParam'];
		}
		// done!
		ob_start();
		include F::appPath('view/webform/input.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			render row in step
		</description>
		<io>
			<in>
				<string name="$stepRow" />
				<string name="$colWidth" optional="yes" example="2|2|8" />
			</in>
			<out>
				<string name="~return~" comments="display row in corresponding format" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function renderRow($stepRow, $colWidth=null) {
		$type = self::stepRowType($stepRow);
		if ( $type === false ) return F::alertOutput([ 'type' => 'warning', 'message' => self::error() ]);
		// fix variables
		$stepRow  = trim($stepRow);
		$colWidth = trim($colWidth);
		// determine method to invoke
		$class  = __CLASS__;
		$method = __FUNCTION__.'__'.$type;
		// done!
		return $class::$method($stepRow, $colWidth);
	}
	// heading
	// ===> (e.g.) ### Personal Details
	public static function renderRow__heading($stepRow) {
		$size = 'h'.(strlen($stepRow)-strlen(ltrim($stepRow, '#')));
		$text = trim(ltrim($stepRow, '#'));
		return "<div class='{$size}'>{$text}</div>";
	}
	// direct output
	// ===> (e.g.) ~~<br>
	public static function renderRow__output($stepRow) {
		return '<div>'.trim(ltrim(trim($stepRow), '~')).'</div>';
	}
	// horizontal line
	// ===> (e.g.) ---
	public static function renderRow__line($stepRow) {
		return '<hr />';
	}
	// field list
	// ===> (e.g.) aaa|bbb|ccc|ddd,eee|x.y.z
	public static function renderRow__fields($fieldNameList, $fieldWidthList) {
		// fix variables
		$fieldNameList = explode('|', $fieldNameList);
		if ( !is_array($fieldWidthList) ) $fieldWidthList = explode('|', $fieldWidthList);
		// capture output
		ob_start();
		?><div class="form-row"><?php
			foreach ( $fieldNameList as $i => $fieldNameSubList ) :
				$fieldWidth = !empty($fieldWidthList[$i]) ? "col-md-{$fieldWidthList[$i]}" : 'col-md';
				// determine column class
				// ===> example : "foo,bar,ab_cd,x.y.z"
				// ===> result  : "webform-col-foo-bar-ab_cd-x-y-z"
				$colClassName = 'webform-col-'.str_replace([',','.'], '-', $fieldNameSubList);
				// display column
				// ===> for example : "ddd,eee"
				// ===> show [ddd] and [eee] fields in same column vertically
				?><div class="webform-col col-12 <?php echo $colClassName; ?> <?php echo $fieldWidth; ?>"><?php
					$fieldNameSubList = explode(',', $fieldNameSubList);
					foreach ( $fieldNameSubList as $fieldName ) echo self::renderField($fieldName);
				?></div><?php
			endforeach; // foreach-fieldNameList
		?></div><?php
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
		// validation
		if ( !self::stepExists($step) ) return false;
		// when [confirm] is simply true (no step specifed)
		// ===> display all steps & quit
		// ===> otherwise, display specified step
		if ( $step == 'confirm' and self::$config['steps']['confirm'] === true ) {
			$original = self::$mode;
			self::$mode = 'view';
			$output = self::renderAll($xfa);
			self::$mode = $original;
			return $output;
		}
		// essential variables
		$webform = self::$config;
		$formStep = $step;
		// display single step
		ob_start();
		foreach ( self::$config['steps'][$step] as $key => $val ) echo self::renderRow($key, $val);
		$formBody = ob_get_clean();
		// done!
		ob_start();
		include F::appPath('view/webform/form.php');
		include F::appPath('view/webform/autosave.php');
		return ob_get_clean();
	}




	/**
	<fusedoc>
		<description>
			clear & init progress data
		</description>
		<io>
			<in />
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function resetProgress() {
		if ( self::clearProgress() === false ) return false;
		if ( self::initBeanData() === false ) return false;
		if ( self::initProgressData() === false ) return false;
		// done!
		return true;
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
				<object name="$bean" scope="self" />
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
					</structure>
					<structure name="customMessage">
						<string name="completed" />
					</structure>
				</structure>
				<!-- cache -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" />
					</structure>
				</structure>
			</in>
			<out>
				<structure name="$result">
					<string name="success" />
					<array name="warning" optional="yes">
						<string name="+" />
					</array>
					<number name="lastInsertID" />
				</structure>
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
		// load submitted data
		$formData = self::progressData();
		if ( $formData === false ) return false;
		// get columns of database table
		$columns = ORM::columns(self::$config['bean']['type']);
		if ( $columns === false ) {
			self::$error = ORM::error();
			return false;
		}
		// convert submitted data
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			$fieldValue = self::nestedArrayGet($fieldName, $formData);
			// when field not appear in submitted data
			if ( $fieldValue === null ) {
				// *IMPORTANT*
				// ===> simply do nothing
				// ===> do NOT assign null value to avoid removing data already in database
			// turn [checkbox] array-value into list
			} elseif ( $cfg['format'] == 'checkbox' and !empty($fieldValue) ) {
				self::nestedArraySet($fieldName, $formData, implode('|', $fieldValue));
			// turn [table] complex-value into json
			} elseif ( $cfg['format'] == 'table' and !empty($fieldValue) ) {
				self::nestedArraySet($fieldName, $formData, json_encode(array_values($fieldValue)));
			// turn empty [date] into null (to avoid database error)
			} elseif ( in_array($cfg['format'], ['date','datetime']) and $fieldValue === '' ) {
				self::nestedArraySet($fieldName, $formData, null);
			}
		}
		// move converted data into container
		// ===> could not use bean-import to avoid having error when array-value
		foreach ( $formData as $key => $val ) self::$bean->{$key} = $val;
		// fix any empty date(time) value
		// ===> to avoid database error
		foreach ( $columns as $colName => $colType ) {
			if ( in_array($colType, ['date','datetime']) and isset($bean->{$colName}) and $bean->{$colName} === '' ) {
				$bean->{$colName} = null;
			}
		}
		// force record enabled (when column exists)
		if ( isset(self::$bean->disabled) ) self::$bean->disabled = false;
		// save to database
		$id = ORM::save(self::$bean);
		if ( $id === false ) {
			self::$error = ORM::error();
			return false;
		}
		// put into result
		$result = array(
			'lastInsertID' => $id,
			'success' => self::$config['customMessage']['completed'],
			'warning' => array(),
		);
		// send notification
		if ( self::sendNotification($id) === false ) $result['warning'][] = self::error();
		// take snapshot (when necessary)
		if ( self::takeSnapshot($id) === false ) $result['warning'][] = self::error();
		// write log (when necessary)
		if ( self::writeSaveLog($id) === false ) $result['warning'][] = self::error();
		// clean-up
		if ( empty($result['warning']) ) unset($result['warning']);
		// done!
		return $result;
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
					<object name="bean" />
					<structure name="fieldConfig">
						<structure name="~fieldName~" />
					</structure>
					<structure name="notification">
						<string name="from_name" />
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
	public static function sendNotification($entityID) {
		$entityType = Bean::type(self::$bean);
		// when notification suppressed
		// ===> simply quit & do nothing
		if ( empty(self::$config['notification']) ) return true;
		// get config
		$cfg = self::$config['notification'];
		// validate recipient
		if ( empty($cfg['to']) ) {
			self::$error = 'Notification recipient not specified';
			return false;
		}
		// load bean data (saved data)
		// ===> load from database instead of access [self::$bean]
		// ===> because [self::$bean] seems to be the beginning data
		// ===> it might not get reloaded after record saved
		$bean = ORM::get($entityType, $entityID);
		if ( $bean === false ) {
			self::$error = ORM::error();
			return false;
		}
		$beanData = Bean::export($bean);
		if ( $beanData === false ) {
			self::$error = Bean::error();
			return false;
		}
		// load form data (progress data)
		$formData = self::progressData();
		if ( $formData === false ) return false;
		// prepare mail
		$mail = array(
			'from_name' => !empty($cfg['from_name']) ? $cfg['from_name'] : null,
			'from'      => !empty($cfg['from']) ? $cfg['from'] : null,
			'cc'        => !empty($cfg['cc']) ? $cfg['cc'] : null,
			'bcc'       => !empty($cfg['bcc']) ? $cfg['bcc'] : null,
			'subject'   => $cfg['subject'],
			'body'      => $cfg['body'],
		);
		// send to recipient (by email field or custom value)
		// ===> e.g. [ 'to' => ':email' ]
		// ===> e.g. [ 'to' => 'foo@bar.com' ]
		$mail['to'] = ( $cfg['to'][0] != ':' ) ? $cfg['to'] : call_user_func(function() use ($cfg, $formData){
			$emailField = substr($cfg['to'], 1);
			return self::nestedArrayGet($emailField, $formData);
		});
		// validate recipient email
		if ( empty($mail['to']) ) {
			self::$error = 'Email recipient not specified';
			if ( $cfg['to'][0] == ':' ) self::$error .= " ({$cfg['to']})";
			return false;
		}
		// prepare mapping of mask & data
		$masks = array();
		// use {{XXX}} to access bean data (which are data load from database)
		foreach ( $beanData as $fieldName => $fieldValue ) $masks['{{'.strtoupper($fieldName).'}}'] = $fieldValue;
		// use [[XXX]] to access form data (which are data stayed in session)
		foreach ( self::$config['fieldConfig'] as $fieldName => $fieldCfg ) {
			$fieldValue = self::nestedArrayGet($fieldName, $formData);
			if ( $fieldValue !== null ) $masks['[['.strtoupper($fieldName).']]'] = ( $fieldCfg['format'] == 'checkbox' ) ? implode('<br>', $fieldValue) : $fieldValue;
		}
		// mapping of mask & other data
		// ===> use ((XXX)) to access other common information (e.g. date)
		$masks = array_merge($masks, [
			'((YYYYMMDD))' => date('Y-m-d'),
			'((DATE))'     => date('j M Y'),
			'((YEAR))'     => date('Y'),
			'((MONTH))'    => date('n'),
			'((DAY))'      => date('j'),
		]);
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
		// write log
		if ( !empty(self::$config['writeLog']) ) {
			$logged = Log::write([
				'action'      => 'SEND_NOTIFICATION',
				'entity_type' => $entityType,
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
				if ( self::stepRowType($fieldNameList) == 'fields' ) {
					$fieldNameList = explode('|', str_replace(',', '|', $fieldNameList));
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
			determine type of step row
		</description>
		<io>
			<in>
				<string name="$stepRow" example="col_1|col_2|col_3,col_4" />
			</in>
			<out>
				<string name="~return~" value="heading|line|output|fields" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function stepRowType($stepRow) {
		$stepRow = trim($stepRow);
		if ( strlen($stepRow) and $stepRow[0] === '#' ) return 'heading';
		if ( strlen($stepRow) and $stepRow[0] === '~' ) return 'output';
		if ( strlen($stepRow) and trim($stepRow, '=-') === '' ) return 'line';
		return 'fields';
	}




	/**
	<fusedoc>
		<description>
			take snapshot of specific entity
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
					</structure>
					<boolean_or_string name="snapshot" comments="table to save the snapshot; do not take snapshot when false" />
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
	public static function takeSnapshot($entityID) {
		// when snapshot suppressed
		// ===> simply quit & do nothing
		if ( empty(self::$config['snapshot']) ) return true;
		// create container with snapshot output
		$snapshotBean = ORM::new(self::$config['snapshot'], [
			'datetime'    => date('Y-m-d H:i:s'),
			'entity_type' => self::$config['bean']['type'],
			'entity_id'   => $entityID,
			'body'        => call_user_func(function(){
				$original = self::$mode;
				self::$mode = 'view';
				$output = self::renderAll();
				self::$mode = $original;
				return $output;
			}),
		]);
		// save & check if any error
		if ( $snapshotBean === false or ORM::save($snapshotBean) === false ) {
			self::$error = ORM::error();
			return false;
		}
		// done!
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
			===> because both windows will share same session which [token=~beanType~:0]
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
						<number name="id" />
					</structure>
				</structure>
			</in>
			<out>
				<string name="~return~" value="~beanType~:~beanID~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function token() {
		return self::$config['bean']['type'].':'.self::$config['bean']['id'];
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
					<structure name="bean">
						<string name="type" />
					</structure>
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
		$formData = self::progressData();
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
		$updated = self::progressData($formData);
		if ( $updated === false ) return false;
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
		$data = self::progressData();
		if ( $data === false ) return false;
		// go through all steps
		foreach ( array_keys(self::$config['steps']) as $stepName ) {
			// validate each step
			$validated = self::validateStep($stepName, $data, $stepErr);
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
					<mixed name="bean" />
					<string name="layoutPath" comments="can be false but cannot be null" />
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
		// check bean config
		if ( empty(self::$config['bean']) ) {
			self::$error = 'Webform config [bean] is required';
			return false;
		} elseif ( strpos(self::$config['bean']['type'], '_') !== false ) {
			self::$error = 'Type of [bean] cannot contain underscore';
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
		// check field config : any missing
		foreach ( self::$config['steps'] as $stepName => $fieldLayout ) {
			if ( is_array($fieldLayout) ) {
				foreach ( $fieldLayout as $fieldNameList => $fieldWidthList ) {
					if ( self::stepRowType($fieldNameList) == 'fields' ) {
						$fieldNameList = explode('|', str_replace(',', '|', $fieldNameList));
						foreach ( $fieldNameList as $fieldName ) {
							if ( !empty($fieldName) and !isset(self::$config['fieldConfig'][$fieldName]) ) {
								self::$error = "Field config for [{$fieldName}] is required";
								return false;
							}
						} // foreach-fieldName
					} // if-stepRowType-fields
				} // foreach-fieldLayout
			} // is-fieldLayout
		} // foreach-step
		// get columns of database table
		$columns = ORM::columns(self::$config['bean']['type']);
		if ( $columns === false ) {
			self::$error = ORM::error();
			return false;
		}
		// check each field config
		foreach ( self::$config['fieldConfig'] as $fieldName => $cfg ) {
			// field config : options
			if ( isset($cfg['format']) and in_array($cfg['format'], ['checkbox','radio']) ) {
				if ( !isset($cfg['options']) ) {
					self::$error = "Field config [options] for [{$fieldName}] is required";
					return false;
				} elseif ( $cfg['options'] !== false and !is_array($cfg['options']) ) {
					self::$error = "Field config [options] for [{$fieldName}] must be array";
					return false;
				}
			}
			// field config : custom
			if ( isset($cfg['format']) and $cfg['format'] == 'custom' ) {
				if ( !isset($cfg['customScript']) ) {
					self::$error = "Field config [customScript] for [{$fieldName}] is required";
					return false;
				} elseif ( !file_exists($cfg['customScript']) ) {
					self::$error = "Script of [customScript] for [{$fieldName}] not exists ({$cfg['customScript']})";
					return false;
				}
			}
			// field config : table
			if ( isset($cfg['format']) and $cfg['format'] == 'table' ) {
				if ( !isset($cfg['tableRow']) ) {
					self::$error = "Field config [tableRow] for [{$fieldName}] is required";
					return false;
				} elseif ( !is_array($cfg['tableRow']) ) {
					self::$error = "Field config [tableRow] for [{$fieldName}] must be array";
					return false;
				}
			}
			// field config : nested field name
			$fieldNameArray = array_filter(explode('.', $fieldName));
			$isNestedField = ( count($fieldNameArray) > 1 );
			if ( isset($columns[$fieldNameArray[0].'_id']) ) {
				self::$error = "Field name [{$fieldNameArray[0]}.*] is forbidden (because clashing with associated object [{$fieldNameArray[0]}] of ORM)";
				return false;
			}
		} // foreach-fieldConfig
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
	public static function validateStep($step, $data, &$err=[]) {
		// consider fields specified in field-layout
		// ===> instead of consider fields specified in field-config
		// ===> to avoid checking fields which were not submitted
		// ===> (for convenience, some fields have field-config specified but hide by field-layout)
		$fieldConfig = self::stepFields($step);
		if ( $fieldConfig === false ) return false;
		// go through each field in specific step
		foreach ( $fieldConfig as $fieldName => $cfg ) {
			$fieldValue = self::nestedArrayGet($fieldName, $data);
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
			// ===> *IMPORTANT* easily to have issue when key is text with linebreak
			if ( $cfg['format'] == 'checkbox' and $fieldValue !== '' ) {
				$fieldValue = is_array($fieldValue) ? $fieldValue : explode('|', $fieldValue);
				foreach ( $fieldValue as $selectedItem ) {
					if ( !isset($flattenOptions[$selectedItem]) ) {
						$err[$fieldName] = "Value of [{$fieldName}] is invalid ({$selectedItem})";
					}
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
			display webform with data of initial bean
		</description>
		<io>
			<in>
				<structure name="$xfa" />
			</in>
			<out>
				<string name="~return~" comments="output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function view($xfa=[]) {
		$original = self::$dataRender;
		self::$dataRender = 'beanData';
		$output = self::renderAll($xfa);
		if ( $output === false ) return false;
		self::$dataRender = $original;
		return $output;
	}




	/**
	<fusedoc>
		<description>
			display webform with data in session
			===> for confirmation page
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<number name="id" />
					</structure>
					<boolean name="allowBack" />
				</structure>

			</in>
			<out>
				<string name="~return~" comments="output" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function viewProgress($xfa=[]) {
		$original = self::$dataRender;
		self::$dataRender = 'progressData';
		$output = self::renderStep('confirm', $xfa);
		if ( $output === false ) return false;
		self::$dataRender = $original;
		return $output;
	}




	/**
	<fusedoc>
		<description>
			write log after save
		</description>
		<io>
			<in>
				<!-- config -->
				<structure name="$config" scope="self">
					<structure name="bean">
						<string name="type" />
					</structure>
					<boolean_or_string name="writeLog" comments="custom action name; do not write log when false" />
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
	public static function writeSaveLog($entityID) {
		// when log suppressed
		// ===> simply quit & do nothing
		if ( empty(self::$config['writeLog']) ) return true;
		// create container for log
		$log = array(
			'entity_type' => self::$config['bean']['type'],
			'entity_id' => $entityID,
		);
		// apply custom action or default action
		if ( is_string(self::$config['writeLog']) ) $log['action'] = self::$config['writeLog'];
		else $log['action'] = empty(self::$bean->id) ? 'SUBMIT_WEBFORM' : 'UPDATE_WEBFORM';
		// save & check if any error
		if ( Log::write($log) === false ) {
			self::$error = Log::error();
			return false;
		}
		// done!
		return true;
	}


} // class
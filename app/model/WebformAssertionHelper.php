<?php /*
<fusedoc>
	<description>
		helper component mainly for accessing (yet-to-save) webform cached data and making assertion
		===> so that adjustment could be made on webform config according to user input
	</description>
</fusedoc>
*/
class WebformAssertionHelper {


	// property
	private static $config = array('beanType' => null, 'beanID' => 0);
	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			determine specific webform cached data to access
		</description>
		<io>
			<in>
				<string name="$beanType" scope="self" />
				<number name="$beanID" scope="self" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function init($beanType, $beanID=null) {
		// validation
		if ( empty($beanType) ) {
			self::$error = 'Property [beanType] is required';
			return false;
		}
		// set properties
		if ( !empty($beanType) ) self::$config['beanType'] = $beanType;
		if ( !empty($beanID)   ) self::$config['beanID']   = $beanID;
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
				<!-- config -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
				</structure>
			</in>
			<out>
				<structure name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function dataUnsaved() {
		$beanID = !empty($beanID) ? $beanID : 0;
		$token = self::$config['beanType'].':'.self::$config['beanID'];
		return $_SESSION['webform'][$token] ?? array();
	}




	/**
	<fusedoc>
		<description>
			check whether specific field contains certain string
			===> find in array or find in string
			===> check against unsaved form data
		</description>
		<io>
			<in>
				<array_or_string name="$fieldName" comments="could be nested field name" example="first_name|student.name" />
				<string name="$compareValue" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldContains($fieldName, $compareValue) {
		$fieldValue = self::fieldValue($fieldName);
		if ( $fieldValue === false ) throw new Exception(self::error());
		if ( is_array($fieldValue) ) return in_array($compareValue, $fieldValue);
		return ( strpos($fieldValue, $compareValue) !== false );
	}




	/**
	<fusedoc>
		<description>
			check whether specific field is empty
			===> check against unsaved form data
		</description>
		<io>
			<in>
				<string name="$fieldName" comments="could be nested field name" example="first_name|student.name" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldEmpty($fieldName) {
		$fieldValue = self::fieldValue($fieldName);
		if ( $fieldValue === false ) throw new Exception(self::error());
		return empty($fieldValue);
	}




	/**
	<fusedoc>
		<description>
			check whether specific field equals to certain value
			===> check against unsaved form data
		</description>
		<io>
			<in>
				<string name="$fieldName" comments="could be nested field name" example="first_name|student.name" />
				<string name="$compareValue" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldEqual($fieldName, $compareValue) {
		$fieldValue = self::fieldValue($fieldName);
		if ( $fieldValue === false ) throw new Exception(self::error());
		return ( $fieldValue == $compareValue );
	}




	/**
	<fusedoc>
		<description>
			check whether specific field is one of values in the array
			===> check against unsaved form data
		</description>
		<io>
			<in>
				<string name="$fieldName" comments="could be nested field name" example="first_name|student.name" />
				<array name="$array" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldInArray($fieldName, $array) {
		$fieldValue = self::fieldValue($fieldName);
		if ( $fieldValue === false ) throw new Exception(self::error());
		return in_array($fieldValue, $array);
	}




	/**
	<fusedoc>
		<description>
			access field value in cache
		</description>
		<io>
			<in>
				<!-- cached form data -->
				<structure name="webform" scope="$_SESSION">
					<structure name="~token~">
						<mixed name="~fieldName~" comments="could be nested field name" example="fullname|student.email" />
					</structure>
				</structure>
			</in>
			<out>
				<mixed name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function fieldValue($fieldName) {
		$data = self::dataUnsaved();
		if ( $data === false ) return false;
		return self::getNestedArrayValue($data, $fieldName);
	}




	/**
	<fusedoc>
		<description>
			access nested-array value (e.g. data[student][name]) by period-delimited-list (e.g. student.name)
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
		$nestedArray = $nestedArray ?: [];
		$nestedKey = explode('.', $nestedKey);
		$result = &$nestedArray;
		foreach ( $nestedKey as $key ) $result = &$result[$key] ?? null;
		return $result;
	}


} // class
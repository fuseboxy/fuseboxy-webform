<?php /*
<fusedoc>
	<description>
		component to access (yet-to-save) webform cached data and making assertion
		===> so that adjustment could be made on webform config according to user input
	</description>
</fusedoc>
*/
class WebformProgress {


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
				<string name="$beanType" />
				<number name="$beanID" />
			</in>
			<out>
				<!-- properties -->
				<structure name="$config" scope="self">
					<string name="beanType" />
					<number name="beanID" />
				</structure>
				<!-- return value -->
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
			check whether specific field contains certain string
			===> find in array or find in string
			===> check against unsaved form data
		</description>
		<io>
			<in>
				<array_or_string name="$fieldName" comments="could be nested field name" example="first_name|student.name" />
				<string name="$needle" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function assertContains($fieldName, $needle) {
		$haystack = self::fieldValue($fieldName);
		if ( $haystack === false ) throw new Exception(self::error());
		if ( is_array($haystack) ) return in_array($needle, $haystack);
		return ( strpos($haystack, $needle) !== false );
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
	public static function assertEmpty($fieldName) {
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
	public static function assertEqual($fieldName, $compareValue) {
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
				<array name="$haystack" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function assertInArray($fieldName, $haystack) {
		$needle = self::fieldValue($fieldName);
		if ( $needle === false ) throw new Exception(self::error());
		return in_array($needle, $haystack);
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
	public static function data() {
		$beanID = !empty($beanID) ? $beanID : 0;
		$token = self::$config['beanType'].':'.self::$config['beanID'];
		return $_SESSION['webform'][$token] ?? array();
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
		// obtain cached data
		$formData = self::data();
		if ( $formData === false ) return false;
		// search in nested array
		$result = Webform::nestedArrayGet($fieldName, $formData);
		if ( $result === false ) {
			self::$error = Webform::error();
			return false;
		}
		// done!
		return $result;
	}


} // class
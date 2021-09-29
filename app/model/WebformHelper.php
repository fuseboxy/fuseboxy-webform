<?php /*
<fusedoc>
	<description>
		helper component mainly for accessing webform cached data and making assertion
		===> so that adjustment on webform config could be made according to user input
	</description>
</fusedoc>
*/
class WebformHelper {


	// essential properties
	private static $beanType;
	private static $beanID;
	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<description>
			check agaist in-progress form data
			===> whether specific field contains certain string
			===> find in array or find in string
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
	public static function assertContains($fieldName, $compareValue) {
		$formData = self::data();
		if ( $formData === false ) throw new Exception(self::error());
		$fieldValue = self::getNestedArrayValue($formData, $fieldName);
		if ( is_array($fieldValue) ) return in_array($compareValue, $fieldValue);
		return ( strpos($fieldValue, $compareValue) !== false );
	}
	public static function assertNotContains($fieldName, $compareValue) {
		return !self::assertContains($fieldName, $compareValue);
	}




	/**
	<fusedoc>
		<description>
			check against in-progress form data
			===> whether specific field equals to certain value
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
		$formData = self::data();
		if ( $formData === false ) throw new Exception(self::error());
		return ( self::getNestedArrayValue($formData, $fieldName) == $compareValue );
	}
	public static function assertNotEqual($fieldName, $compareValue) {
		return !self::assertEqual($fieldName, $compareValue);
	}


} // class
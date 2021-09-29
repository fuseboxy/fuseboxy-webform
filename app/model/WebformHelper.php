<?php /*
<fusedoc>
	<description>
		helper component mainly for accessing (yet-to-save) webform cached data and making assertion
		===> so that adjustment could be made on webform config according to user input
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
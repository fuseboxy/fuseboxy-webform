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


} // class
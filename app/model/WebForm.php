<?php
class Webform {


	// properties : webform config
	public static $config;
	// properties : library for corresponding methods
	public static $libPath = array(
		'uploadFile'         => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/Uploader.php',
		'uploadFileProgress' => __DIR__.'/../../lib/simple-ajax-uploader/2.6.7/extras/uploadProgress.php',
	);




	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




	/**
	<fusedoc>
		<io>
			<in>
				<structure name="$data">
				</structure>
			</in>
			<out>
				<number name="~return~" comments="last insert ID" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function save($data) {

	}




	/**
	<fusedoc>
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

	}


} // class
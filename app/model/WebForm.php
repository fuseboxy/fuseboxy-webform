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
		<description>
			set default and fix config
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">

				</structure>
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function initConfig() {
		return true;
	}




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




	/**
	<fusedoc>
		<description>
			perform validation on webform config
		</description>
		<io>
			<in>
				<structure name="$config" scope="self">
					<string name="beanType" />
					<string name="layoutPath" comments="can be false but cannot be null" />
					<boolean name="writeLog" />
				</structure>
				<structure name="config" scope="$fusebox">
					<string name="uploadDir" />
					<string name="uploadUrl" />
				</structure>
				<class name="Log" />
			</in>
			<out>
				<boolean name="~return~" />
			</out>
		</io>
	</fusedoc>
	*/
	public static function validateConfig() {
		// has any file field?
		$hasFileField = false;
/*
		if ( isset(self::$config['fieldConfig']) ) {
			foreach ( self::$config['fieldConfig'] as $_key => $_field ) {
				if ( isset($_field['format']) and in_array($_field['format'], ['file','image']) ) {
					$hasFileField = true;
					break;
				}
			}
		}
*/
		// check bean type
		if ( empty(self::$config['beanType']) ) {
			self::$error = 'Webform config [beanType] is required';
			return false;
		} elseif ( strpos(self::$config['beanType'], '_') !== false ) {
			self::$error = 'Webform config [beanType] cannot contain underscore';
			return false;
		// check layout path
		} elseif ( !isset(self::$config['layoutPath']) ) {
			self::$error = 'Webform config [layoutPath] is required';
			return false;
		// check uploader directory (when has file field)
		} elseif ( empty(F::config('uploadDir')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadDir] is required';
			return false;
		} elseif ( empty(F::config('uploadUrl')) and $hasFileField ) {
			self::$error = 'Fusebox config [uploadUrl] is required';
			return false;
		// check log component
		} elseif ( !empty(self::$config['writeLog']) and !class_exists('Log') ) {
			self::$error = 'Class [Log] is required';
			return false;
		}
		// done!
		return true;
	}


} // class
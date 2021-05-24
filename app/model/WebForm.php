<?php
class WebForm


	// properties : webform config
	public static $config;
	// properties : library for corresponding methods
	public static $libPath = array();


	// get (latest) error message
	private static $error;
	public static function error() { return self::$error; }




} // class
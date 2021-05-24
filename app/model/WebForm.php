<?php
class WebForm


	// properties : webform config
	public static $config;
	// properties : library for corresponding methods
	public static $libPath = array();


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
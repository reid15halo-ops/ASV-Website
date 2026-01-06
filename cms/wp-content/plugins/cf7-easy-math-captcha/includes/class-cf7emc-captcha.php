<?php 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Captcha Class
 * Class to manage captchas
 */
class CF7EMC_Captcha{

	public static $numbers = array();

	// class constructor
	public function __construct(){

	}

	/**
	 * Get numbers
	 */
	public static function get_numbers() {
		return self::$numbers;
	}

	/**
	 * Generate random numbers
	 */
	public static function generate_random_numbers( $num = '' ){
		if( empty($num) ){
			$num = mt_rand( 1, 9 );
			if( !empty($num) ) return self::generate_random_numbers( $num );
		} else {
			$num2 = mt_rand( 0, (9 - $num) );
			$numbers = array( $num, $num2 );

			self::$numbers[] = $numbers;
			return $numbers;
		}
		return false;
	}
}
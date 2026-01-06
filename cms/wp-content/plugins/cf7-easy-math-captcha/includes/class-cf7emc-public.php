<?php 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Public Class
 *
 * Class to manage front side,
 * functionalities
 */
class CF7EMC_Public{

	// class constructor
	public function __construct(){
		add_action( 'init', array($this, 'init_user_sesstion') );
		add_action( 'wpcf7_init', array($this, 'cf7_add_captcha_tag') );

		add_filter( 'wpcf7_validate_mathcaptcha', array($this, 'validate_mathcaptcha_field'), 20, 2 );
	}

	/**
	 * Store unique session
	 * for user, with cookie
	 */
	public function init_user_sesstion() {
		if( empty($_COOKIE['cf7emc_user_token']) ) {
			setcookie( 'cf7emc_user_token', $this->generate_token(), time()+60*60*24, COOKIEPATH, COOKIE_DOMAIN );
		}
	}


	/**
	 * Add captcha tag
	 */
	public function cf7_add_captcha_tag() {
		if( function_exists( 'wpcf7_add_form_tag' ) ) {
			wpcf7_add_form_tag( 'mathcaptcha', 'cf7emc_math_captcha_handler', true );
		}
	}

	/**
	 * validate math captcha
	 */
	public function validate_mathcaptcha_field( $result, $tag ) {
		$name = $tag->name;
		if( empty($_POST[$name]) ) {
			$result->invalidate( $tag, esc_html__( 'Please enter the captcha value.', 'cf7emc' ) );
		} else {
			$token = isset( $_COOKIE['cf7emc_user_token'] ) ? esc_attr($_COOKIE['cf7emc_user_token']) : '';
			$numbers = get_transient( 'cf7emc_' . $tag->name . '_' . $token );

			if( $_POST[$name] != array_sum($numbers) ) {
				$result->invalidate( $tag, esc_html__( 'Captcha value is invalid, please enter valid value.', 'cf7emc' ) );
			}
		}

		return $result;
	}

	/**
	 * Generate token
	 */
	private function generate_token( $length = 20 ) {
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
		$token = '';
		for( $i = 0; $i < $length; $i ++  ) {
			$token .= substr( $chars, mt_rand( 0, strlen( $chars ) - 1 ), 1 );
		}

		return $token;
	}
}
return new CF7EMC_Public();
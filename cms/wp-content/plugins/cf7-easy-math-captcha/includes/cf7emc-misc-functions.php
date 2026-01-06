<?php
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Misc Function File
 * Manage plugins all misc functions
 */

/**
 * Captcha Handler
 */
function cf7emc_math_captcha_handler( $tag ) {

	$tag = new WPCF7_FormTag( $tag );

	if( empty($tag->name) ) return '';

	$validation_error = wpcf7_get_validation_error( $tag->name );
	$class = wpcf7_form_controls_class( $tag->type );

	// validation class
	if( $validation_error ) $class .= ' wpcf7-not-valid';

	$atts = array();
	$atts['size'] = 2;
	$atts['maxlength'] = 2;
	$atts['class'] = $tag->get_class_option( $class );
	$atts['id'] = $tag->get_option( 'id', 'id', true );
	$atts['tabindex'] = $tag->get_option( 'tabindex', 'int', true );
	$atts['aria-required'] = 'true';
	$atts['type'] = 'text';
	$atts['name'] = $tag->name;
	$atts['value'] = '';
	$atts = wpcf7_format_atts( $atts );

	$numbs = CF7EMC_Captcha::generate_random_numbers();
	$token = isset( $_COOKIE['cf7emc_user_token'] ) ? esc_attr($_COOKIE['cf7emc_user_token']) : '';
	
	if( empty($numbs) || empty($token) ) return;

	set_transient( 'cf7emc_' . $tag->name . '_' . $token, $numbs, 12 * HOUR_IN_SECONDS );

	$num1 = '<span class="num num1">' . $numbs[0] . '</span>';
	$num2 = '<span class="num num2">' . $numbs[1] . '</span>';
	$sign1 = '<span class="sign plus">+</span>';
	$sign2 = '<span class="sign equal">=</span>';

	return sprintf( '<span class="wpcf7-form-control-wrap cf7emc-captcha-field %1$s">' . $num1 . $sign1 . $num2 . $sign2 . '<input %2$s /></span>', $tag->name, $atts, $validation_error );
}
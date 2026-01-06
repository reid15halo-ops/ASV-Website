<?php
/**
 * Plugin Name: Easy Math Captcha for CF7
 * Plugin URI: https://wordpress.org/plugins/easy-match-captcha-cf7/
 * Description: Easy Math Captcha for Contact Form 7 is allows you to add simple math captcha to your form. this way you will get the spam protection.
 * Version: 1.0.0
 * Author: AlphaBPO
 * Author URI: http://www.alphabpo.com
 * Text Domain: cf7emc
 * Domain Path: languages
 *
 * License: GPLv2 or later
 * Domain Path: languages
 *
 * @package Easy Math Captcha for CF7
 * @category Core
 * @author Alpha BPO
 */

if( ! function_exists( 'cf7emc_fs' ) ) {
    // Create a helper function for easy SDK access.
	function cf7emc_fs() {
		global $cf7emc_fs;

		if( ! isset( $cf7emc_fs ) ) {
			// Include Freemius SDK.
			require_once dirname(__FILE__) . '/freemius/start.php';

			$cf7emc_fs = fs_dynamic_init( array(
				'id'                  => '5265',
				'slug'                => 'cf7-easy-math-captcha',
				'type'                => 'plugin',
				'public_key'          => 'pk_25474bcfc3636e193cd4cf6344468',
				'is_premium'          => false,
				'has_addons'          => false,
				'has_paid_plans'      => false,
				'menu'                => array(
					'first-path'     => 'plugins.php',
					'account'        => false,
					'contact'        => false,
					'support'        => false,
				),
			) );
		}
		return $cf7emc_fs;
	}

    // Init Freemius.
	cf7emc_fs();
    // Signal that SDK was initiated.
	do_action( 'cf7emc_fs_loaded' );
}

/**
 * Basic plugin definitions 
 */
if( !defined( 'CF7EMC_VERSION' ) ) {
	define( 'CF7EMC_VERSION', '1.0.0' ); // plugin version
}
if( !defined( 'CF7EMC_PLUGIN_DIR' ) ) {
	define( 'CF7EMC_PLUGIN_DIR', dirname( __FILE__ ) ); // plugin dir
}
if( !defined( 'CF7EMC_ADMIN_DIR' ) ) {
	define( 'CF7EMC_ADMIN_DIR', CF7EMC_PLUGIN_DIR . '/includes/admin' ); // plugin admin dir
}
if( !defined( 'CF7EMC_PLUGIN_URL' ) ) {
	define( 'CF7EMC_PLUGIN_URL', plugin_dir_url( __FILE__ ) ); // plugin url
}

/**
 * Load Text Domain
 */
function cf7emc_load_plugin_textdomain() {
	$locale = apply_filters( 'plugin_locale', get_locale(), 'cf7emc' );

	load_textdomain( 'cf7emc', WP_LANG_DIR . '/cf7-easy-math-captcha/cf7emc-' . $locale . '.mo' );
	load_plugin_textdomain( 'cf7emc', false, CF7EMC_PLUGIN_DIR . '/languages' );
}
add_action( 'load_plugins', 'cf7emc_load_plugin_textdomain' );

/**
 * Activation hook
 * Register plugin activation hook.
 */
register_activation_hook( __FILE__, 'cf7emc_plugin_install' );

/**
 * Deactivation hook
 * Register plugin deactivation hook.
 */
register_deactivation_hook( __FILE__, 'cf7emc_plugin_uninstall' );

/**
 * Plugin Setup Activation hook call back 
 *
 * Initial setup of the plugin setting default options 
 * and database tables creations.
 */
function cf7emc_plugin_install() {
	global $wpdb, $cf7emc_settings;

	$epvs_version = get_option( 'cf7emc_version' );
	if( empty($epvs_version) ) {
		
	}
}

/**
 * Plugin Setup (On Deactivation)
 *
 * Does the drop tables in the database and
 * delete  plugin options.
 */
function cf7emc_plugin_uninstall() {
	global $wpdb;
}

/**
 * Load functionalities,
 * in plugin loaded hook
 */
add_action( 'plugins_loaded', 'cf7emc_plugin_loaded', 20 );
function cf7emc_plugin_loaded() {

	// Check if contact form plugin is active
	if( defined('WPCF7_VERSION') ) {

		/**
		 * Include require files
		 */

		// Includes plugin functions
		require_once ( CF7EMC_PLUGIN_DIR . '/includes/cf7emc-misc-functions.php');

		// Captcha field to manage captcha codes
		require_once ( CF7EMC_PLUGIN_DIR . '/includes/class-cf7emc-captcha.php');

		// Includes public class file
		require_once ( CF7EMC_PLUGIN_DIR . '/includes/class-cf7emc-public.php');

		// Include admin side functionalities
		if( is_admin() ) {
			require_once( CF7EMC_ADMIN_DIR . '/class-cf7emc-admin.php' );
		}
	} else {
		function cf7emc_admin_warning() {
				echo '<div class="error">';
				echo "<p><strong>" . esc_html__( 'Easy Math Captcha for CF7 Extension needs the Contact Form 7 plugin installed and activated!', 'cf7emc' ) . "</strong></p>";
				echo '</div>';
			}
		add_action( 'admin_notices', 'cf7emc_admin_warning' );
	}
}
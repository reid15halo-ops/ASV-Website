<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Clmns_Settings_Tabs' ) ) {
	class Clmns_Settings_Tabs extends Bws_Settings_Tabs {
		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $clmns_options, $clmns_plugin_info;

			$tabs = array(
				'settings' 		=> array( 'label' => __( 'Settings', 'columns-bws' ) ),
				'misc' 			=> array( 'label' => __( 'Misc', 'columns-bws' ) ),
				'custom_code' 	=> array( 'label' => __( 'Custom Code', 'columns-bws' ) )
			);

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $clmns_plugin_info,
				'prefix' 			 => 'clmns',
				'default_options' 	 => clmns_get_options_default(),
				'options' 			 => $clmns_options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'columns-bws'
			) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {

			$message = $notice = $error = '';

			/* Takes all the changed settings on the plugin's admin page and saves them in array 'clmns_options'. */
			$this->options['add_bootstrap']	= isset( $_REQUEST['clmns_add_bootstrap'] ) ? 1 : 0;

			$message = __( 'Settings saved.', 'columns-bws' );

			update_option( 'clmns_options', $this->options );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Columns Settings', 'columns-bws' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table">
				<tr>
					<th scope="row"><?php _e( 'Bootstrap', 'columns-bws' ); ?></th>
					<td>
						<label>
							<input name='clmns_add_bootstrap' type='checkbox' value='1' <?php checked( 1, $this->options['add_bootstrap'] ); ?>/>
							<span class="bws_info"><?php _e( 'Enable if your theme doesn\'t use Bootstrap.', 'columns-bws' ); ?></span>
						</label>
					</td>
				</tr>
			</table>
		<?php }
	}
}
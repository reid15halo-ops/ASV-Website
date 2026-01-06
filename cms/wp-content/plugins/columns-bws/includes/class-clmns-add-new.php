<?php
/**
 * Displays the content on the plugin settings page
 */

if ( ! class_exists( 'Clmns_Single_Tabs' ) ) {
	class Clmns_Single_Tabs extends Bws_Settings_Tabs {

		private $clmns_id;
		
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
			global $clmns_plugin_info, $wpdb;

			$tabs = array(
				'display' 		=> array( 'label' => __( 'Columns', 'columns-bws' ) ),
				'settings' 		=> array( 'label' => __( 'Settings', 'columns-bws' ) )
			);

			/* Get slider ID. */
			$this->clmns_id = ! empty( $_REQUEST['clmns_id'] ) ? intval( $_REQUEST['clmns_id'] ) : "";

			if ( empty( $this->clmns_id ) ) {
				$options = clmns_get_options_default();
			} else {
				$column_single_setting = $wpdb->get_var( $wpdb->prepare( "SELECT `settings` FROM `" . $wpdb->prefix . "clmns_columns` WHERE `columns_id` = %d", $this->clmns_id ) );
			
				$options = unserialize( $column_single_setting );
			}

			parent::__construct( array(
				'plugin_basename' 	 => $plugin_basename,
				'plugins_info'		 => $clmns_plugin_info,
				'prefix' 			 => 'clmns',
				'default_options' 	 => clmns_get_options_default(),
				'options' 			 => $options,
				'tabs' 				 => $tabs,
				'wp_slug'			 => 'columns-bws'
			) );

			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
		}

		/**
		 * Save to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {
			global $wpdb;

			$message = $notice = $error = '';

			/* Handler for settings */
			$clmns_request_options = array();
			/* Set loop in slideshow. */
			$clmns_request_options['items']						= isset( $_POST['clmns_items'] ) ? intval( $_POST['clmns_items'] ) : 3;
			$clmns_request_options['align']						= ( isset( $_POST['clmns_align'] ) && in_array( $_POST['clmns_align'], array( 'center', 'left', 'right' ) ) ) ? $_POST['clmns_align'] : 'center';
			$clmns_request_options['column_style']				= ( isset( $_POST['clmns_column_style'] ) && in_array( $_POST['clmns_column_style'], array( 'default', 'with_border', 'with_background', 'with_shadow' ) ) ) ? $_POST['clmns_column_style'] : 'default';
			$clmns_request_options['border_color']				= isset( $_POST['clmns_border_color'] ) ? sanitize_hex_color( $_POST['clmns_border_color'] ) : '';
			$clmns_request_options['border_hover_color']		= isset( $_POST['clmns_border_hover_color'] ) ? sanitize_hex_color( $_POST['clmns_border_hover_color'] ) : '';
			$clmns_request_options['background_color']			= isset( $_POST['clmns_background_color'] ) ? sanitize_hex_color( $_POST['clmns_background_color'] ) : '';
			$clmns_request_options['background_hover_color']	= isset( $_POST['clmns_background_hover_color'] ) ? sanitize_hex_color( $_POST['clmns_background_hover_color'] ) : '';
			$clmns_request_options['shadow_hover_color']		= isset( $_POST['clmns_shadow_hover_color'] ) ? sanitize_hex_color( $_POST['clmns_shadow_hover_color'] ) : '';
			$clmns_request_options['image_position']			= ( isset( $_POST['clmns_image_position'] ) && in_array( $_POST['clmns_image_position'], array( 'above_title', 'left_title', 'left_title_desc', 'background_left', 'background_center' ) ) ) ? $_POST['clmns_image_position'] : 'above_title';
			$clmns_request_options['widget_background_style']	= ( isset( $_POST['clmns_widget_background_style'] ) && in_array( $_POST['clmns_widget_background_style'], array( 'color', 'image' ) ) ) ? $_POST['clmns_widget_background_style'] : 'color';			
			$clmns_request_options['widget_background_color']	= isset( $_POST['clmns_widget_background_color'] ) ? sanitize_hex_color( $_POST['clmns_widget_background_color'] ) : '';
			$clmns_request_options['widget_background_image']	= isset( $_POST['clmns_widget_background_image'] ) ? intval( $_POST['clmns_widget_background_image'] ) : '';
			$clmns_request_options['widget_background_opacity']	= isset( $_POST['clmns_widget_background_opacity'] ) ? floatval( $_POST['clmns_widget_background_opacity'] ) : 1;		
			
			$this->options = apply_filters( 'clmns_request_options', $clmns_request_options );
			$clmns_options = serialize( $clmns_request_options );

			/* Columns title */
			$column_title 	= sanitize_text_field( trim( wp_unslash( $_POST['clmns_column_title'] ) ) );
			if ( ! empty( $this->clmns_id ) ) {
				$wpdb->update( $wpdb->prefix . 'clmns_columns',
					array(
						'title' => $column_title,
						'settings' => $clmns_options
					),
					array( 'columns_id' => $this->clmns_id )
				);
			} else {
				$wpdb->insert( $wpdb->prefix . 'clmns_columns',
					array(
						'title' => $column_title,
						'datetime' => date( 'Y-m-d' ),
						'settings' => $clmns_options
					)
				);
				/* Get slider ID for new slider. */
				$this->clmns_id = $wpdb->insert_id;
			}

			/* Slide title\description\URL */
			if ( ! empty( $_POST['clmns'] ) ) {
				foreach ( $_POST['clmns']['title'] as $column_key => $column_value ) {
					$column_id = '';
					if ( '' == $_POST['clmns']['title'][ $column_key ] && '' == $_POST['clmns']['description'][ $column_key ] && '' == $_POST['clmns']['image'][ $column_key ] ) {
						continue;
					}

					if ( isset( $_POST['clmns']['id'] ) && isset( $_POST['clmns']['id'][ $column_key ] ) ) {
						$column_id = $wpdb->get_var( $wpdb->prepare(
							'SELECT `column_id` FROM `' . $wpdb->prefix . 'clmns_column` WHERE `column_id` = %d',
							$_POST['clmns']['id'][ $column_key ] ) );
					}
					
					if ( ! empty( $column_id ) ) {
						$wpdb->update( $wpdb->prefix . 'clmns_column',
							array(
								'attachment_id'		=> intval( $_POST['clmns']['image'][ $column_key ] ),
								'title'				=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['title'][ $column_key ]  ) ) ),
								'numeral_title'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['numeral_title'][ $column_key ]  ) ) ),
								'description'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['description'][ $column_key ] ) ) ),
								'display_button' 	=> isset( $_POST['clmns']['display_button'][ $column_key ] ) ? 1 : 0,
								'button_text'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['button_text'][ $column_key ] ) ) ),
								'button_link'		=> esc_url_raw( trim( wp_unslash( $_POST['clmns']['button_link'][ $column_key ] ) ) )
							),
							array( 'column_id' => $column_id )
						);
					} else {
						$wpdb->insert( $wpdb->prefix . 'clmns_column',
							array(
								'attachment_id'		=> intval( $_POST['clmns']['image'][ $column_key ] ),
								'title'				=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['title'][ $column_key ]  ) ) ),
								'numeral_title'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['numeral_title'][ $column_key ]  ) ) ),
								'description'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['description'][ $column_key ] ) ) ),
								'display_button' 	=> isset( $_POST['clmns']['display_button'][ $column_key ] ) ? 1 : 0,
								'button_text'		=> sanitize_text_field( trim( wp_unslash( $_POST['clmns']['button_text'][ $column_key ] ) ) ),
								'button_link'		=> esc_url_raw( trim( wp_unslash( $_POST['clmns']['button_link'][ $column_key ] ) ) ),
								'columns_id'		=> $this->clmns_id
							)
						);
					}
				}

				if ( isset( $_POST['clmns']['delete'] ) ) {
					foreach ( $_POST['clmns']['delete'] as $delete_column ) {
						$wpdb->delete( $wpdb->prefix . 'clmns_column',
							array( 'column_id' => intval( $delete_column ) ) 
						);
					}
				}
			}

			$message = __( 'Columns updated.', 'columns-bws' );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Displays the content of the "Settings" on the plugin settings page
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_content() {
			global $wpdb;

			$save_results = $this->save_all_tabs_options();
			$title = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "clmns_columns` WHERE `columns_id` = %d", $this->clmns_id ) ); ?>
			<h1>
				<?php /* Add page name and add new button to page */
				if ( ! empty( $this->clmns_id ) ) {
					echo __( 'Edit Columns', 'columns-bws' ) . '<a class="page-title-action" href="' . admin_url( 'admin.php?page=column-new.php' ) . '">' . __( 'Add New', 'columns-bws' ) . '</a>';
				} else {
					_e( 'Add New Columns', 'columns-bws' );
				} ?>
			</h1>
			<?php $this->display_messages( $save_results ); ?>
            <form class="bws_form" method="POST" action="admin.php?page=column-new.php&amp;clmns_id=<?php echo $this->clmns_id; ?>">
                <div id="poststuff">
                    <div id="post-body" class="metabox-holder columns-2">
                        <div id="post-body-content" style="position: relative;">
                        	<div id="titlediv">
								<div id="titlewrap">
									<input name="clmns_column_title" size="30" value="<?php echo esc_html( $title ); ?>" id="title" spellcheck="true" autocomplete="off" type="text" placeholder="<?php _e( 'Enter title here', 'columns-bws' ); ?>" />
								</div>
								<div class="inside"></div>
							</div>
							<?php $this->display_tabs(); ?>
                        </div><!-- #post-body-content -->
                        <div id="postbox-container-1" class="postbox-container">
                            <div class="meta-box-sortables ui-sortable">
                                <div id="submitdiv" class="postbox">
                                    <h3 class="hndle"><?php _e( 'Publish', 'columns-bws' ); ?></h3>
                                    <div class="inside">
                                        <div class="submitbox" id="submitpost">
                                            <div id="major-publishing-actions">
                                                <div id="publishing-action">
                                                    <input type="hidden" name="<?php echo $this->prefix; ?>_form_submit" value="submit" />
                                                    <input id="bws-submit-button" type="submit" class="button button-primary button-large" value="<?php echo ( isset( $_GET['clmns_id'] ) ) ? __( 'Update', 'columns-bws' ) : __( 'Publish', 'columns-bws' ); ?>" />
													<?php wp_nonce_field( $this->plugin_basename, 'bws_nonce_name' ); ?>
                                                </div>
                                                <div class="clear"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php if ( ! empty( $this->clmns_id ) ) { ?>
									<div class="postbox">
										<h3 class="hndle">
											<?php _e( 'Columns Shortcode', 'columns-bws' ); ?>
										</h3>
										<div class="inside">
											<?php _e( "Add Columns to your posts, pages, custom post types or widgets by using the following shortcode:", 'columns-bws' ); ?>
											<?php bws_shortcode_output( '[print_clmns id=' . $this->clmns_id . ']' ); ?>
										</div>
									</div>
								<?php } ?>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
		<?php }

		/**
		 *
		 */
		public function tab_display() { 
			global $wpdb;
			$columns = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM `" . $wpdb->prefix . "clmns_column` WHERE `columns_id` = %d", $this->clmns_id ), ARRAY_A );
			$current_column = 0; ?>
			<h3 class="bws_tab_label"><?php _e( 'Columns Content', 'columns-bws' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr />			
			<?php if ( ! empty( $columns ) ) {
				foreach ( $columns as $key => $column ) { 
					$current_column = $key + 1;
					$this->tab_column_display( $current_column, '', $column );
				}
			} else {
				$current_column++; 
				$this->tab_column_display( $current_column );				 
			} 
			$this->tab_column_display( '', 'clmns-clone hidden' ); ?>
			<input type="hidden" value="<?php echo $current_column ?>" id="clmns_current_column" />
			<div class="buttons">
				<a href="#" id="clmns-add-column" class="button hide-if-no-js"><?php _e( 'Add New Column', 'columns-bws' ); ?></a>
			</div>
			<div id="clmns-delete-column-ids"></div>					
		<?php }

		private function tab_column_display( $current_column, $class = '', $value = array() ) { ?>
			<div class="clmns-single-column <?php if ( '' != $class ) echo $class; ?>">
				<?php if ( ! empty( $value ) && isset( $value['column_id'] ) && 0 != $value['column_id'] ) { ?>
					<input type="hidden" name="clmns[id][]" value="<?php echo $value['column_id']; ?>" />
				<?php } ?>
				<div class="bws_tab_sub_label">
					<?php _e( 'Column', 'columns-bws' ); ?> <span class="clmns-column-number"><?php echo $current_column; ?></span>

					<span id="delete-link">
						<a class="delete hide-if-no-js clmns-delete-column" href="#"><?php _e( 'Delete', 'columns-bws' ); ?></a>
					</span>
				</div>
				<table class="form-table clmns_settings_form">
					<tr>
						<th><?php _e( 'Image', 'columns-bws' ); ?></th>
						<td>
							<div class="wp-media-buttons">
								<a href="#" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'columns-bws' ); ?></a>
							</div>
							<div class="clmns-image-block">
								<div class="clmns-image">
									<?php if ( ! empty( $value ) && isset( $value['attachment_id'] ) && 0 != $value['attachment_id'] ) {
										$url = wp_get_attachment_url( $value['attachment_id'] ) ;
										if ( '' != $url ) {
											echo '<img src="' . $url . '" /><span class="clmns-delete-image"><span class="dashicons dashicons-no-alt"></span></span>';
										}
									} ?>
								</div>
								<input class="clmns-image-id hide-if-js" type="text" name="clmns[image][]" value="<?php if( ! empty( $value ) && isset( $value['attachment_id'] ) && 0 != $value['attachment_id'] ) echo $value['attachment_id']; ?>" />									
							</div>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Numerical Value of Title', 'columns-bws' ); ?></th>
						<td>
							<input type="text" class="clmns-title" name="clmns[numeral_title][]" value="<?php if( ! empty( $value ) && isset( $value['numeral_title'] ) && '' != $value['numeral_title'] ) echo $value['numeral_title']; ?>" maxlength="255" />
							<p class="bws_info"><?php _e( 'Quantitative measure related to the title of the column.', 'columns-bws' ); ?></p>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Title', 'columns-bws' ); ?></th>
						<td>
							<input type="text" class="clmns-title" name="clmns[title][]" value="<?php if( ! empty( $value ) && isset( $value['title'] ) && '' != $value['title'] ) echo $value['title']; ?>" maxlength="255" />
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Description', 'columns-bws' ); ?></th>
						<td>
							<textarea class="clmns-description" name="clmns[description][]"><?php if( ! empty( $value ) && isset( $value['description'] ) && '' != $value['description'] ) echo $value['description']; ?></textarea>
						</td>
					</tr>
					<tr>
						<th><?php _e( 'Display Button', 'columns-bws' ); ?></th>
						<td>
							<input class="clmns_display_button" type="checkbox" name="clmns[display_button][<?php if ( ! empty( $current_column ) ) echo ($current_column - 1); ?>]" value="1" <?php checked( 1, ! empty( $value ) && isset( $value['display_button'] ) ? $value['display_button'] : '' ); ?> />
							<span class="bws_info"><?php _e( 'Enable to display custom button.', 'columns-bws' ); ?></span>
						</td>
					</tr>
					<tr class="clmns_button">
						<th><?php _e( 'Button Text', 'columns-bws' ); ?></th>
						<td>
							<input type="text" name="clmns[button_text][]" value="<?php if( ! empty( $value ) && isset( $value['button_text'] ) && '' != $value['button_text'] ) echo $value['button_text']; ?>" maxlength="255" />
						</td>
					</tr>
					<tr class="clmns_button">
						<th><?php _e( 'Button Link', 'columns-bws' ); ?></th>
						<td>
							<input type="text" name="clmns[button_link][]" value="<?php if( ! empty( $value ) && isset( $value['button_link'] ) && '' != $value['button_link'] ) echo $value['button_link']; ?>" maxlength="255" />
						</td>
					</tr>
				</table>
				<hr />
			</div>
		<?php }

		/**
		 *
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'Columns Settings', 'columns-bws' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<table class="form-table clmns_settings_form">
				<tr>
					<th><?php _e( 'Number of Columns', 'columns-bws' ); ?></th>
					<td>
						<input type="number" name="clmns_items" min="1" max="10" value="<?php echo isset( $this->options['items'] ) ? $this->options['items'] : ''; ?>" />
						<p class="bws_info"><?php _e( 'Number of columns in a row.', 'columns-bws' ); ?></p>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Column Alignment', 'columns-bws' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="clmns_align" value="left" <?php checked( $this->options['align'], 'left' ) ;?> /> <?php _e( 'Left', 'columns-bws' ); ?></label><br />
							<label><input type="radio" name="clmns_align" value="right" <?php checked( $this->options['align'], 'right' ) ;?> /> <?php _e( 'Right', 'columns-bws' ); ?></label><br />
							<label><input type="radio" name="clmns_align" value="center" <?php checked( $this->options['align'], 'center' ) ;?> /> <?php _e( 'Center', 'columns-bws' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Style', 'columns-bws' ); ?></th>
					<td>
						<select class="clmns-column-style" name="clmns_column_style">
							<option value="default" <?php selected( $this->options['column_style'], 'default' ); ?>><?php _e( 'Default', 'columns-bws' ); ?></option>
							<option value="with_border" <?php selected( $this->options['column_style'], 'with_border' ); ?>><?php _e( 'With Border', 'columns-bws' ); ?></option>
							<option value="with_background" <?php selected( $this->options['column_style'], 'with_background' ); ?>><?php _e( 'With Background', 'columns-bws' ); ?></option>
							<option value="with_shadow" <?php selected( $this->options['column_style'], 'with_shadow' ); ?>><?php _e( 'With Shadow (for hover)', 'columns-bws' ); ?></option>
						</select>
					</td>
				</tr>
				<tr class="clmns-column-style-child clmns_with_border <?php if( 'with_border' != $this->options['column_style'] ) echo 'hidden'; ?>">
					<th><?php _e( 'Border Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_border_color" type="text" id="clmns_border_color" value="<?php if( ! empty( $this->options['border_color'] ) && '' != $this->options['border_color'] ) echo $this->options['border_color']; ?>" />
					</td>
				</tr>
				<tr class="clmns-column-style-child clmns_with_border <?php if( 'with_border' != $this->options['column_style'] ) echo 'hidden'; ?>">
					<th><?php _e( 'Border Hover Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_border_hover_color" type="text" id="clmns_border_hover_color" value="<?php if( ! empty( $this->options['border_hover_color'] ) && '' != $this->options['border_hover_color'] ) echo $this->options['border_hover_color']; ?>" />
					</td>
				</tr>
				<tr class="clmns-column-style-child clmns_with_background <?php if( 'with_background' != $this->options['column_style'] ) echo 'hidden'; ?>">
					<th><?php _e( 'Background Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_background_color" type="text" id="clmns_background_color" value="<?php if( ! empty( $this->options['background_color'] ) && '' != $this->options['background_color'] ) echo $this->options['background_color']; ?>" />
					</td>
				</tr>
				<tr class="clmns-column-style-child clmns_with_background <?php if( 'with_background' != $this->options['column_style'] ) echo 'hidden'; ?>">
					<th><?php _e( 'Background Hover Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_background_hover_color" type="text" id="clmns_background_hover_color" value="<?php if( ! empty( $this->options['background_hover_color'] ) && '' != $this->options['background_hover_color'] ) echo $this->options['background_hover_color']; ?>" />
					</td>
				</tr>
				<tr class="clmns-column-style-child clmns_with_shadow <?php if( 'with_shadow' != $this->options['column_style'] ) echo 'hidden'; ?>">
					<th><?php _e( 'Shadow Hover Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_shadow_hover_color" type="text" id="clmns_shadow_hover_color" value="<?php if ( ! empty( $this->options['shadow_hover_color'] ) && '' != $this->options['shadow_hover_color'] ) echo $this->options['shadow_hover_color']; ?>" />
					</td>
				</tr>
				<tr>
					<th><?php _e( 'Image Position', 'columns-bws' ); ?></th>
					<td>
						<select class="clmns-image-position" name="clmns_image_position">
							<option value="above_title" <?php selected( $this->options['image_position'], 'above_title' ); ?>><?php _e( 'Above Title', 'columns-bws' ); ?></option>
							<option value="left_title" <?php selected( $this->options['image_position'], 'left_title' ); ?>><?php _e( 'Left Title', 'columns-bws' ); ?></option>
							<option value="left_title_desc" <?php selected( $this->options['image_position'], 'left_title_desc' ); ?>><?php _e( 'Left Title and Description', 'columns-bws' ); ?></option>
							<option value="background_left" <?php selected( $this->options['image_position'], 'background_left' ); ?>><?php _e( 'Background Left', 'columns-bws' ); ?></option>
							<option value="background_center" <?php selected( $this->options['image_position'], 'background_center' ); ?>><?php _e( 'Background Center', 'columns-bws' ); ?></option>
						</select>
					</td>
				</tr>
				<tr>
				<tr>
					<th><?php _e( 'Column Widget Background', 'columns-bws' ); ?></th>
					<td>
						<fieldset>
							<label><input type="radio" name="clmns_widget_background_style" value="color" class="bws_option_affect" data-affect-show=".clmns_column_style_color" data-affect-hide=".clmns_column_style_image" <?php checked( $this->options['widget_background_style'], 'color' ) ;?> /><?php _e( 'Color', 'columns-bws' ); ?></label>
							<br />
							<label><input type="radio" name="clmns_widget_background_style" value="image" class="bws_option_affect" data-affect-hide=".clmns_column_style_color" data-affect-show=".clmns_column_style_image" <?php checked( $this->options['widget_background_style'], 'image' ) ;?>/><?php _e( 'Image', 'columns-bws' ); ?></label>
						</fieldset>
					</td>
				</tr>
				<tr class="clmns_column_style_color">
					<th><?php _e( 'Color', 'columns-bws' ); ?></th>
					<td>
						<input name="clmns_widget_background_color" type="text" id="clmns_widget_background_color" value="<?php if ( ! empty( $this->options['widget_background_color'] ) ) echo $this->options['widget_background_color']; ?>" />
					</td>
				</tr>
				<tr class="clmns_column_style_image">
					<th><?php _e( 'Image', 'columns-bws' ); ?></th>
					<td>
						<div class="wp-media-buttons">
							<a href="#" class="button insert-media add_media hide-if-no-js"><span class="wp-media-buttons-icon"></span> <?php _e( 'Add Media', 'columns-bws' ); ?></a>
						</div>
						<div class="clmns-background-block">
							<div class="clmns-image">
								<?php if ( ! empty( $this->options['widget_background_image'] ) && 0 != $this->options['widget_background_image'] ) {
									$url = wp_get_attachment_url( $this->options['widget_background_image'] ) ;
									if ( '' != $url ) {
										echo '<img src="' . $url . '" /><span class="clmns-delete-image"><span class="dashicons dashicons-no-alt"></span></span>';
									}
								} ?>
							</div>
							<input class="clmns-background-image hide-if-js" type="text" name="clmns_widget_background_image" value="<?php if ( ! empty( $this->options['widget_background_image'] ) ) echo $this->options['widget_background_image']; ?>" />						
						</div>
					</td>
				</tr>	
				<tr>
					<th><?php _e( 'Column Widget Background Opacity', 'columns-bws' ); ?></th>
					<td>
						<input class="small-text" name="clmns_widget_background_opacity" type="text" id="clmns_widget_background_opacity" value="<?php if( ! empty( $this->options['widget_background_opacity'] ) && 0 != $this->options['widget_background_opacity'] ) echo $this->options['widget_background_opacity']; ?>" />
						<div id="clmns_slider"></div>						
					</td>
				</tr>
			</table>
		<?php }
	}
}
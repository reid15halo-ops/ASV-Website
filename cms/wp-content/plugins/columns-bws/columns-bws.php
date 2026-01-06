<?php
/*
Plugin Name: Columns by BestWebSoft
Plugin URI: https://bestwebsoft.com/products/wordpress/plugins/сolumns/
Description: Add columns with custom content to WordPress website pages, posts, widgets, etc.
Author: BestWebSoft
Text Domain: columns-bws
Domain Path: /languages
Version: 1.0.3
Author URI: https://bestwebsoft.com/
License: GPLv3 or later
*/

/*  © Copyright 2020 BestWebSoft  ( https://support.bestwebsoft.com )

	This program is free software; you can redistribute it and/or modify
	it under the terms of the GNU General Public License, version 2, as
	published by the Free Software Foundation.

	This program is distributed in the hope that it will be useful,
	but WITHOUT ANY WARRANTY; without even the implied warranty of
	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
	GNU General Public License for more details.

	You should have received a copy of the GNU General Public License
	along with this program; if not, write to the Free Software
	Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Add Wordpress page 'bws_plugins' and sub-page of this plugin to admin-panel.
 */
if ( ! function_exists( 'clmns_add_admin_menu' ) ) {
	function clmns_add_admin_menu() {
		/* Add custom menu page */
		$column_general_page = add_menu_page( 'Columns', 'Columns', 'manage_options', 'columns.php', 'clmns_table_page_render', 'none', 45.1 );
		add_submenu_page( 'columns.php', __( 'Columns', 'columns-bws' ), __( 'Columns', 'columns-bws' ), 'manage_options', 'columns.php', 'clmns_table_page_render' );

		if ( isset( $_GET['clmns_id'] ) ) {
			$column_page = add_submenu_page( 'columns.php', __( 'Edit Columns', 'columns-bws' ), __( 'Add New', 'columns-bws' ), 'manage_options', 'column-new.php', 'clmns_add_new_render' );
		} else {
			$column_page = add_submenu_page( 'columns.php', __( 'Add New', 'columns-bws' ), __( 'Add New', 'columns-bws' ), 'manage_options', 'column-new.php', 'clmns_add_new_render' );
		}

		add_submenu_page( 'columns.php', __( 'Columns Settings', 'columns-bws' ), __( 'Settings', 'columns-bws' ), 'manage_options', 'columns-settings.php', 'clmns_settings_page' );

		add_submenu_page( 'columns.php', 'BWS Panel', 'BWS Panel', 'manage_options', 'clmns-bws-panel', 'bws_add_menu_render' );

		/* Add help tabs */
		add_action( 'load-' . $column_general_page, 'clmns_add_tabs' );
		add_action( 'load-' . $column_general_page, 'clmns_screen_options' );
		add_action( 'load-' . $column_page, 'clmns_add_tabs' );
	}
}

if ( ! function_exists( 'clmns_column_parent_file' ) ) {
	function clmns_column_parent_file( $parent_file ) {
		global $plugin_page;
		if ( 'column-new.php' == $plugin_page && isset( $_GET['clmns_id'] ) && 0 < $_GET['clmns_id'] && '' != $_GET['clmns_id'] ) {
			$parent_file = 'columns.php';
		}
		return $parent_file;
	}
}

if ( ! function_exists( 'clmns_column_submenu_file' ) ) {
	function clmns_column_submenu_file( $submenu_file, $parent_file ) {
		global $plugin_page;
		if ( 'column-new.php' == $plugin_page && isset( $_GET['clmns_id'] ) && 0 < $_GET['clmns_id'] && '' != $_GET['clmns_id'] ) {
			$submenu_file = 'columns.php';
		}
		return $submenu_file;
	}
}

/**
 * Add localization.
 */
if ( ! function_exists( 'clmns_plugins_loaded' ) ) {
	function clmns_plugins_loaded() {
		/* Internationalization, first(!) */
		load_plugin_textdomain( 'columns-bws', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
	}
}

/**
 * Plugin initialization on frontend and backend.
 */
if ( ! function_exists( 'clmns_init' ) ) {
	function clmns_init() {
		global $clmns_plugin_info;

		if ( empty( $clmns_plugin_info ) ) {
			if ( ! function_exists( 'get_plugin_data' ) )
				require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
			$clmns_plugin_info = get_plugin_data( __FILE__ );
		}

		/* add general functions */
		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );

		/* check compatible with current WP version */
		bws_wp_min_version_check( plugin_basename( __FILE__ ), $clmns_plugin_info, '4.5' );

		/* Get/Register and check settings for plugin */
		if ( ! is_admin() || ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'columns.php', 'columns-settings.php', 'column-new.php' ) ) ) ) {
			clmns_settings();
		}
	}
}

/**
* Plugin initialization on backend.
*/
if ( ! function_exists ( 'clmns_admin_init' ) ) {
	function clmns_admin_init() {
		global $bws_plugin_info, $clmns_plugin_info, $bws_shortcode_list;
		
		/* Add variable for bws_menu. */
		if ( empty( $bws_plugin_info ) ) {
			$bws_plugin_info = array(
				'id' 		=> '895',
				'version' 	=> $clmns_plugin_info['Version']
			);
		}

		/* Add columns to global $bws_shortcode_list  */
		$bws_shortcode_list['clmns'] = array(
			'name' 			=> 'Columns',
			'js_function' 	=> 'clmns_shortcode_init'
		);
	}
}

/**
 * Register settings.
 */
if ( ! function_exists( 'clmns_settings' ) ) {
	function clmns_settings() {
		global $clmns_options, $clmns_plugin_info;
		$plugin_db_version = '0.1';

		/* Install the option defaults. */
		if ( ! get_option( 'clmns_options' ) ) {
			$option_defaults = clmns_get_options_default();
			add_option( 'clmns_options', $option_defaults );
		}

		/* Get options from the database. */
		$clmns_options = get_option( 'clmns_options' );

		if ( ! isset( $clmns_options['plugin_option_version'] ) || $clmns_options['plugin_option_version'] != $clmns_plugin_info['Version'] ) {
			$option_defaults = clmns_get_options_default();
			$clmns_options = array_merge( $option_defaults, $clmns_options );
			$clmns_options['plugin_option_version'] = $clmns_plugin_info['Version'];
			$update_option = true;
		}

		/**
		 * Update pugin database and options
		 */
		if ( ! isset( $clmns_options['plugin_db_version'] ) || $clmns_options['plugin_db_version'] != $plugin_db_version ) {
			clmns_create_table();
			$clmns_options['plugin_db_version'] = $plugin_db_version;
			$update_option = true;
		}

		if ( isset( $update_option ) ) {
			update_option( 'clmns_options', $clmns_options );
		}
	}
}

/**
 * Get Plugin default options
 */
if ( ! function_exists( 'clmns_get_options_default' ) ) {
	function clmns_get_options_default() {
		global $clmns_plugin_info;

		$default_options = array(
			'plugin_option_version'		=> $clmns_plugin_info["Version"],
			'display_settings_notice'	=> 1,
			'suggest_feature_banner'	=> 1,
			/* end deneral options */
			'add_bootstrap'				=> 1,
			'items'						=> 3,
			'align'						=> 'left',
			'column_style'				=> 'default',
			'border_color'				=> '#E6E6E6',
			'border_hover_color'		=> '#2b3b4a',
			'background_color'			=> '#E6E6E6',
			'background_hover_color'	=> '#2b3b4a',
			'shadow_hover_color'		=> '#2b3b4a',
			'image_position'			=> '',
			'widget_background_style'	=> 'color',
			'widget_background_color'	=> '#2b3b4a',
			'widget_background_image'	=> '',
			'widget_background_opacity'	=> 1
		);
		return $default_options;
	}
}

/**
 * Function for plugin activation.
 */
if ( ! function_exists( 'clmns_plugin_activate' ) ) {
	function clmns_plugin_activate() {
		global $wpdb;
		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				register_uninstall_hook( __FILE__, 'clmns_plugin_uninstall' );
				restore_current_blog();
			}
			switch_to_blog( $old_blog );
			return;
		}

		register_uninstall_hook( __FILE__, 'clmns_plugin_uninstall' );
	}
}

/**
 * Function to create a new tables in database.
 */
if ( ! function_exists( 'clmns_create_table' ) ) {
	function clmns_create_table() {
		global $wpdb;

		/* Require db Delta */
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

		/* Create table for columns */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "clmns_columns` (
			`columns_id` INT NOT NULL AUTO_INCREMENT,
			`datetime` DATE NOT NULL,
			`title` VARCHAR( 255 ) NOT NULL,
			`settings` BLOB NOT NULL,
			PRIMARY KEY (columns_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );

		/* Create table for column */
		$sql = "CREATE TABLE IF NOT EXISTS `" . $wpdb->prefix . "clmns_column` (
			`column_id` INT NOT NULL AUTO_INCREMENT,
			`attachment_id` INT NOT NULL,
			`title` VARCHAR( 255 ) NOT NULL,
			`numeral_title` VARCHAR( 255 ) NOT NULL,
			`description` text NOT NULL,
			`display_button` TINYINT(1),
			`button_text` VARCHAR( 255 ) NOT NULL,
			`button_link` VARCHAR( 255 ) NOT NULL,
			`columns_id` INT NOT NULL,
			PRIMARY KEY (column_id)
			) ENGINE=InnoDB DEFAULT CHARSET=utf8;";
		/* Call dbDelta */
		dbDelta( $sql );
	}
}

/* Function create filter for custom slides sorting */
if ( ! function_exists( 'clmns_edit_attachment_join' ) ) {
	function clmns_edit_attachment_join( $join_paged_statement ) {
		global $wpdb;

		$join_paged_statement = "LEFT JOIN `" . $wpdb->prefix . "clmns_column` ON `" . $wpdb->prefix . "clmns_column`.`attachment_id` = `" . $wpdb->prefix . "posts`.`ID`";

		return $join_paged_statement;
	}
}

/**
 * Extends WP_List_Table and WP_Media_List_Table classes.
 */
if ( ! class_exists( 'WP_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );

if ( ! class_exists( 'WP_Media_List_Table' ) )
	require_once( ABSPATH . 'wp-admin/includes/class-wp-media-list-table.php' );

if ( ! class_exists( 'Clmns_List_Table' ) ) {
	/* WP_List_Table extends for render of columns table */
	class Clmns_List_Table extends WP_List_Table {

		/* Declare constructor */
		function __construct() {
			parent::__construct( array(
				'singular'	=> __( 'column', 'columns-bws' ),
				'plural'	=> __( 'columns', 'columns-bws' ),
			) );
		}

		/**
		 * Declare column renderer
		 *
		 * @param $item - row (key, value array)
		 * @param $column_name - string (key)
		 * @return HTML
		 */
		function column_default( $item, $column_name ) {
			switch ( $column_name ) {
				case 'shortcode':
					bws_shortcode_output( '[print_clmns id=' . $item['columns_id'] . ']' );
					break;
				case 'datetime':
				case 'title':
					return $item[ $column_name ];
					break;
				default:
					return print_r( $item, true ) ;
			}
		}

		/**
		 * Render column with actions
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_title( $item ) {
			$actions = array(
				'edit'		=> sprintf( '<a href="?page=column-new.php&clmns_id=%d">%s</a>', $item['columns_id'], __( 'Edit', 'columns-bws' ) ),
				'delete'	=> sprintf( '<a href="?page=%s&action=delete&clmns_id=%s">%s</a>', esc_html( $_REQUEST['page'] ), $item['columns_id'], __( 'Delete', 'columns-bws' ) ),
			);

			$title = empty( $item['title'] ) ? '(' . __( 'no title', 'columns-bws' ) . ')' : $item['title']; 

			return sprintf(
				'<strong><a href="?page=column-new.php&clmns_id=%d">%s</strong></a>%s',
				$item['columns_id'], $title, $this->row_actions( $actions )
			);
		}

		/**
		 * Checkbox column renders
		 *
		 * @param $item - row (key, value array)
		 * @return HTML
		 */
		function column_cb( $item ) {
			return sprintf(
				'<input type="checkbox" name="clmns_id[]" value="%s" />',
				$item['columns_id']
			);
		}

		/**
		 * Return columns to display in table
		 *
		 * @return array
		 */
		function get_columns() {
			$columns = array(
				'cb'			=> '<input type="checkbox" />',
				'title'			=> __( 'Title', 'columns-bws' ),
				'shortcode'		=> __( 'Shortcode', 'columns-bws' ),
				'datetime'		=> __( 'Date', 'columns-bws' )	
			);
			return $columns;
		}

		function no_items() {
			_e( 'No Columns Found', 'columns-bws' );
		}

		/* Generate the table navigation above or below the table */
		function display_tablenav( $which )  { ?>
			<div class="tablenav <?php echo esc_attr( $which ); ?>">
				<div class="alignleft actions bulkactions">
					<?php $this->bulk_actions( $which ); ?>
				</div>
				<?php $this->pagination( $which ); ?>
				<br class="clear" />
			</div>
		<?php }


		/**
		 * Return array of bulk actions if has any
		 *
		 * @return array
		 */
		function get_bulk_actions() {
			$actions = array(
				'delete' => __( 'Delete', 'columns-bws' )
			);
			return $actions;
		}

		/**
		 * Processes bulk actions
		 *
		 */
		function process_bulk_action() {
			global $wpdb;

			$column_deleted_id = isset( $_REQUEST['clmns_id'] ) ? (array) $_REQUEST['clmns_id'] : array();

			$column_deleted_id = array_map( 'intval', $column_deleted_id );

			if ( 'delete' === $this->current_action() ) {
				/* If deleted some slider */
				if ( ! empty( $column_deleted_id ) && is_array( $column_deleted_id ) ) {
					/* If delete more 1 slider */
					foreach ( $column_deleted_id as $column_id ) {
						$wpdb->delete( $wpdb->prefix . 'clmns_columns', array( 'columns_id' => $column_id ) );
						$wpdb->delete( $wpdb->prefix . 'clmns_column', array( 'columns_id' => $column_id ) );
					}
				} elseif ( ! empty( $column_deleted_id ) ) {
					$wpdb->delete( $wpdb->prefix . 'clmns_columns', array( 'columns_id' => $column_deleted_id ) );
					$wpdb->delete( $wpdb->prefix . 'clmns_column', array( 'columns_id' => $column_deleted_id ) );
				}
			}
		}

		/**
		 * Get rows from database and prepare them to be showed in table
		 */
		function prepare_items() {
			global $wpdb;

			$columns 	= $this->get_columns();
			$hidden 	= array();
			$sortable 	= $this->get_sortable_columns();

			/* Configure table headers, defined in this methods */
			$this->_column_headers = array( $columns, $hidden, $sortable, 'title' );

			/* Process bulk action if any */
			$this->process_bulk_action();

			$per_page_option 	= get_current_screen()->get_option( 'per_page' );
			$current_page 		= $this->get_pagenum();

			/* Display selected category  */
			$search = ( isset( $_POST['s'] ) ) ? '%' . stripslashes( sanitize_text_field( $_POST['s'] ) ) . '%' : '%%';

			/* Show all categories */
			$per_page_query = get_user_meta( get_current_user_id(), $per_page_option['option'] );

			$per_page_value = intval( implode( ',', $per_page_query ) );

			$per_page = ! empty( $per_page_value ) ? $per_page_value : $per_page_option['default'];

			/* Prepare query params, as usual current page, order by and order direction */
			$paged = isset( $_REQUEST['paged'] ) ? max( 0, intval( $_REQUEST['paged'] * $per_page ) - $per_page ) : 0;
			/* Will be used in pagination settings */
			$total_items = $wpdb->get_var( "SELECT COUNT( columns_id ) FROM  `" . $wpdb->prefix . "clmns_columns`" );

			/* Show all slider categories */
			$this->items = $wpdb->get_results( $wpdb->prepare(
				"SELECT `columns_id`, `title`, `datetime` 
				FROM `" . $wpdb->prefix . "clmns_columns`
				WHERE `title` LIKE %s 
				LIMIT %d OFFSET %d", 
				$search, $per_page, $paged
			), ARRAY_A );

			/* Сonfigure pagination */
			$this->set_pagination_args( array(
				'total_items'	=> intval( $total_items ), /* total items defined above */
				'per_page'		=> $per_page, /* per page constant defined at top of method */
				'total_pages'	=> ceil( $total_items / $per_page ) /* calculate pages count */
			) );
		}
	}
}

/**
 * Function will render slider page.
 */
if ( ! function_exists( 'clmns_table_page_render' ) ) {
	function clmns_table_page_render() {
		$column_table = new Clmns_List_Table();
		$column_table->prepare_items();

		$message = ''; ?>
		<form class="bws_form clmns_form" method="POST" action="admin.php?page=columns.php">
			<?php if ( 'delete' === $column_table->current_action() ) {
				$message =  __( 'Columns deleted.', 'columns-bws' );
			} ?>
			<div class="wrap">
				<?php printf(
					'<h1> %s <a class="add-new-h2" href="%s" >%s</a></h1>',
					esc_html__( 'Columns', 'columns-bws' ),
					esc_url( admin_url( 'admin.php?page=column-new.php' ) ),
					esc_html__( 'Add New', 'columns-bws' )
				); ?>
				<div id="clmns_settings_message" class="notice is-dismissible updated below-h2 fade" <?php if ( "" == $message ) echo 'style="display:none"'; ?>>
					<p><strong><?php echo $message; ?></strong></p>
				</div>
				<?php $column_table->search_box( __( 'Search Columns', 'columns-bws' ), 'clmns_slider' );
				$column_table->display(); ?>
			</div>
			<input type="hidden" name="clmns_form_submit" value="submit" />
			<?php wp_nonce_field( plugin_basename( __FILE__ ), 'clmns_nonce_form_name' ); ?>
		</form>
	<?php }
}

/* Function formed content of the plugin's admin page. */
if ( ! function_exists( 'clmns_settings_page' ) ) {
	function clmns_settings_page() {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) )
			require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-clmns-settings.php' );
		$page = new Clmns_Settings_Tabs( plugin_basename( __FILE__ ) ); ?>
		<div class="wrap">
			<h1>Columns <?php _e( 'Settings', 'columns-bws' ); ?></h1>
			<?php $page->display_content(); ?>
		</div>
	<?php }
}

/**
 * Function will render new slider page.
 */
if ( ! function_exists( 'clmns_add_new_render' ) ) {
	function clmns_add_new_render( $item ) {
		if ( ! class_exists( 'Bws_Settings_Tabs' ) )
			require_once( dirname( __FILE__ ) . '/bws_menu/class-bws-settings.php' );
		require_once( dirname( __FILE__ ) . '/includes/class-clmns-add-new.php' );
		$page = new Clmns_Single_Tabs( plugin_basename( __FILE__ ) ); ?>
		<div class="wrap clmns_wrap">
			<?php $page->display_content(); ?>
		</div>
	<?php }
}

/**
 *	Add columns shortcode to BWS plugin shortcode menu.
 */
if ( ! function_exists( 'clmns_shortcode_button_content' ) ) {
	function clmns_shortcode_button_content( $content ) {
		global $wpdb; ?>
		<div id="clmns" style="display:none;">
			<fieldset>
				<label for="clmns_shortcode_list">
					<?php $column_id_array = $wpdb->get_col( "SELECT `columns_id` FROM `" . $wpdb->prefix . "clmns_columns`" );
					if ( ! empty( $column_id_array ) ) { ?>
						<select name="clmns_list" id="clmns_shortcode_list" style="max-width: 350px;">
							<?php foreach ( $column_id_array as $column_id ) {
								/* Get columns title from DB */
								$column_title = $wpdb->get_var( $wpdb->prepare( "SELECT `title` FROM `" . $wpdb->prefix . "clmns_columns` WHERE `columns_id` = %d", $column_id ) );
								/* If columns don't have title, display "no title" */
								if ( empty( $column_title ) ) {
									$column_title = '(' . __( 'no title', 'columns-bws' ) . ')';
								}
								/* Get columns date from DB */
								$column_date = $wpdb->get_var( $wpdb->prepare( "SELECT `datetime` FROM `" . $wpdb->prefix . "clmns_columns` WHERE `columns_id` = %d", $column_id ) ); ?>
								<option value="<?php echo $column_id; ?>"><?php echo $column_title; ?> (<?php echo $column_date; ?>)</option>
							<?php } ?>
						</select>
					<?php } else { ?>
						<span class="title"><?php _e( 'Sorry, no columns found.', 'columns-bws' ); ?></span>
					<?php } ?>
				</label>
				<br/>
			</fieldset>
			<?php foreach ( $column_id_array as $column_id ) {
				echo '<input class="bws_default_shortcode" type="hidden" name="default" value="[print_clmns id=' . $column_id . ']" />';
			}
			$script = "function clmns_shortcode_init() {
					( function( $ ) {
						$( '.mce-reset #clmns_shortcode_list, .mce-reset #clmns_display_short, .mce-reset .clmns_radio_shortcode_list' ).on( 'click', function() {
							var clmns_list = $( '.mce-reset #clmns_shortcode_list option:selected' ).val();
							var shortcode = '[print_clmns id=' + clmns_list + ']';
							$( '.mce-reset #bws_shortcode_display' ).text( shortcode );
						});
					} )(jQuery);
				}";

			wp_register_script( 'clmns_bws_shortcode_button', '' );
			wp_enqueue_script( 'clmns_bws_shortcode_button' );
			wp_add_inline_script( 'clmns_bws_shortcode_button', sprintf( $script ) ); ?>
			<div class="clear"></div>
		</div>
	<?php }
}

/**
 *	Shortcodes content output function
 */
if ( ! function_exists ( 'clmns_shortcode' ) ) {
	function clmns_shortcode( $attr ) { 
		global $wpdb, $clmns_options;
		$dark_class = $background_block = $opacity = $column_class = '';

		$shortcode_attributes = shortcode_atts( array( 'id' => '' ), $attr );
		extract( $shortcode_attributes );

		ob_start();

		if ( empty( $clmns_options ) ) {
			$clmns_options = get_option( 'clmns_options' );
		}

		/* Get media ID for columns shortcode */
		if ( ! empty( $id ) ) {
			$column_content = $wpdb->get_results( $wpdb->prepare(
				"SELECT * FROM `" . $wpdb->prefix . "clmns_column` WHERE `columns_id` = %d 
				ORDER BY `column_id`",
				$id
			), ARRAY_A );
		}

		/* Get columns settings */
		$column_single_setting = $wpdb->get_var( $wpdb->prepare( "SELECT `settings` FROM `" . $wpdb->prefix . "clmns_columns` WHERE `columns_id` = %d", $id ) );
		$column_single_settings = unserialize( $column_single_setting );

		/* If this shortcode with columns ID */
		if ( ! empty( $column_content ) ) {
			/* Display images and images attributes from columns. */
			
			if ( 1 != $column_single_settings['widget_background_opacity'] ) {
				$opacity = 'opacity: ' . $column_single_settings['widget_background_opacity'];
			}

			if ( $column_single_settings['widget_background_style'] == 'image' ) {
				if ( '' != $column_single_settings['widget_background_image'] ) {
					$url = wp_get_attachment_url( $column_single_settings['widget_background_image'] ) ;
					if ( '' != $url ) { 
						$background_block = '<div class="clmns-background-wrapper" style="background-image: url(' . $url . '); ' . $opacity . '"></div>';
					}
				} 
			} else {
				if ( '' != $column_single_settings['widget_background_color'] ) {
					if ( 1 != $column_single_settings['widget_background_opacity'] ) {
						$values = str_replace( '#', '', $column_single_settings['widget_background_color'] );
						switch ( strlen( $values ) ) {
							case 3;
								list( $r, $g, $b ) = sscanf( $values, "%1s%1s%1s" );
								$background_color = hexdec( "$r$r" ) . ',' . hexdec( "$g$g" ) . ',' . hexdec( "$b$b" );
								break;
							case 6;
								list( $r, $g, $b ) = sscanf( $values, "%02x%02x%02x");
								$background_color = $r . "," . $g . "," . $b;
								break;
							default:
								$background_color = '';
								break;
						} 
					}
				}
				$background_block = '<div class="clmns-background-wrapper" style="background-color: ' . ( ( 1 != $column_single_settings['widget_background_opacity'] ) ? 'rgba(' . $background_color . ',' . $column_single_settings['widget_background_opacity'] . ')' : $column_single_settings['widget_background_color'] ). '"></div>';
			}
			switch ( $column_single_settings['items'] ) {
				case 2:
					$column_class = 'col-sm-6';
					break;
				case 3:
					$column_class = 'col-sm-6 col-md-4';
					break;
				case 4:
					$column_class = 'col-sm-6 col-md-3';
					break;
				case 6:
					$column_class = 'col-sm-6 col-md-3 col-lg-2';
					break;
			}
			if ( 'default' != $column_single_settings['column_style'] ) {
				switch ( $column_single_settings['column_style'] ) {
					case 'with_border':
						$style = '
							#clmns-wrapper-' . $id . ' .clmns-columns-bws-wrapper .clmns-bws-column:hover {
								background: none;
							} 
							#clmns-wrapper-' . $id . ' .clmns-column-border-wrapper {
								border: 1px solid ' . $column_single_settings['border_color'] . ';
								padding: 80px 40px;
							}
							#clmns-wrapper-' . $id . ' .clmns-column-border-wrapper:hover {
								border: 1px solid ' . $column_single_settings['border_hover_color'] . ';
							}
						';
						break;
					case 'with_background':
						$style = '
							#clmns-wrapper-' . $id . ' .clmns-columns-bws-wrapper .clmns-bws-column:hover {
								background: none;
							}
							#clmns-wrapper-' . $id . ' .clmns-column-background-wrapper {
								background-color: ' . $column_single_settings['background_color'] . ';
								padding: 80px 40px;
							}
							#clmns-wrapper-' . $id . ' .clmns-column-background-wrapper:hover {
								background-color: ' . $column_single_settings['background_hover_color'] . ';
							}
						';
						break;
					case 'with_shadow':
						$values = str_replace( '#', '', $column_single_settings['shadow_hover_color'] );
						switch ( strlen( $values ) ) {
							case 3;
								list( $r, $g, $b ) = sscanf( $values, "%1s%1s%1s" );
								$background_color = hexdec( "$r$r" ) . ',' . hexdec( "$g$g" ) . ',' . hexdec( "$b$b" );
								break;
							case 6;
								list( $r, $g, $b ) = sscanf( $values, "%02x%02x%02x");
								$background_color = $r . "," . $g . "," . $b;
								break;
							default:
								$background_color = '';
								break;
						} 
						$style = '
							#clmns-wrapper-' . $id . ' .clmns-columns-bws-wrapper .clmns-bws-column:hover {
								background: none;
							}
							#clmns-wrapper-' . $id . ' .clmns-column-background-wrapper {
								padding: 80px 40px;
							}
							#clmns-wrapper-' . $id . ' .clmns-column-background-wrapper:hover {
								-webkit-box-shadow: 0px 0px 35px 0px rgba(' . $background_color . ',0.1);
								-moz-box-shadow: 0px 0px 35px 0px rgba(' . $background_color . ',0.1);
								box-shadow: 0px 0px 35px 0px rgba(' . $background_color . ',0.1);
							}
						';
						
						break;
				}
				echo '<style>' . $style . '</style>';
			}
			echo '<div id="clmns-wrapper-' . $id . '" class="clmns-columns-bws-wrapper columns-bws-icon-' . $column_single_settings['image_position'] . '-wrapper row text-' . $column_single_settings['align'] . ' d-flex justify-content-center ' . $dark_class . '">';
			echo $background_block;				

				foreach ( $column_content as $column ) {
					/* Column title */
					$column_title 				= $column['title'];
					/* Column numeral */
					$column_numeral 			= $column['numeral_title'];
					/* Column description */
					$column_description 	= $column['description'];
					/* Get image for media ID */
					
					$column_attachment	= ( ! empty( $column['attachment_id'] ) ) ? wp_get_attachment_thumb_url( $column['attachment_id'] ) : false; ?>
					<div class="clmns-bws-column <?php echo $column_class; ?>">
						<div class="clmns-column-background-wrapper <?php echo 'columns-bws-icon-' . $column_single_settings['image_position'] . '-wrapper' ?> clmns-column-border-wrapper" <?php if ( ! empty( $column_attachment ) && ( 'background_left' == $column_single_settings['image_position'] || 'background_center' == $column_single_settings['image_position'] ) ) echo 'style="background-image: url(' . $column_attachment . ')"'; ?>>
							<?php if( 'left_title' == $column_single_settings['image_position'] || 'left_title_desc' == $column_single_settings['image_position'] ) { ?>
								<div class="d-flex justify-content-start">
							<?php }
							if ( ! empty( $column_attachment ) && 'background_left' != $column_single_settings['image_position'] && 'background_center' != $column_single_settings['image_position'] ) { ?>
								<div class="columns-bws-column-icon columns-bws-column-icon-top"><img src="<?php echo $column_attachment; ?>" alt="icon 1" /></div>
							<?php }
							if( 'left_title_desc' == $column_single_settings['image_position'] ) { ?>
								<div>
							<?php } ?>
							<div class="columns-bws-column-title">
								<?php if ( ! empty( $column_numeral ) ) {
									echo '<div class="numerals">' . wp_specialchars_decode( htmlspecialchars_decode( $column_numeral ) ) . '</div> ';
								}
								echo wp_specialchars_decode( htmlspecialchars_decode( $column_title ) ); ?>
							</div>
							<?php if ( 'left_title' == $column_single_settings['image_position'] ) { ?>
								</div>
							<?php } ?>						
							<div class="columns-bws-column-text"><?php echo wp_specialchars_decode( htmlspecialchars_decode( $column_description ) ); ?></div>
							<?php if( 1 == $column['display_button'] ) { ?>
								<a class="button clmns-widget-link" href="<?php echo esc_url( $column['button_link'] ); ?>"><?php echo esc_html( $column['button_text'] ); ?></a>
							<?php }
							if ( 'left_title_desc' == $column_single_settings['image_position'] ) { ?>
									</div>
								</div>
							<?php } ?>
						</div>
					</div>
				<?php }
			echo '</div>';

		/* If nothing found. */
		} else {
			echo '<div class="columns-bws-icon-' . $column_single_settings['image_position'] . '-wrapper text-' . $column_single_settings['align'] . ' d-flex justify-content-center"><p class="not_found">' . __( 'Sorry, nothing found.', 'columns-bws' ) . '</p></div>';
		}
		$settings = ! empty( $column_single_settings ) ? $column_single_settings : false;
		do_action( 'clmns_after_content', $shortcode_attributes, maybe_unserialize( $settings ) );

		$column_output = ob_get_clean();
		return $column_output;
	}
}

/**
 * Add style to dashboard
 */
if ( ! function_exists ( 'clmns_admin_head' ) ) {
	function clmns_admin_head() {
		wp_enqueue_style( 'clmns_stylesheet_icon', plugins_url( '/css/style-icon.css', __FILE__ ) );

		if ( isset( $_GET['page'] ) && $_GET['page'] == 'columns.php' ) {
			wp_enqueue_style( 'clmns_stylesheet', plugins_url( '/css/style.css', __FILE__ ) );
		} else if ( isset( $_GET['page'] ) && $_GET['page'] == 'column-new.php' ) {
			wp_enqueue_style( 'editor-buttons' );
			wp_enqueue_style( 'clmns_stylesheet', plugins_url( '/css/style.css', __FILE__ ) );
			
			wp_enqueue_script( 'jquery-ui-sortable' );
			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( 'jquery-ui-slider' );

			wp_enqueue_media();
			add_thickbox();
			
			wp_enqueue_script( 'clmns_script', plugins_url( 'js/admin-script.js', __FILE__ ), array( 'jquery', 'jquery-ui-slider' ) );
			
			/* Plugin script */
			wp_localize_script( 'clmns_script', 'clmns_vars',
				array(
					'clmns_nonce'				=> wp_create_nonce( plugin_basename( __FILE__ ), 'clmns_ajax_nonce_field' ),
					'clmns_add_nonce'			=> wp_create_nonce( plugin_basename( __FILE__ ), 'clmns_ajax_add_nonce' ),
					'warnBulkDelete'			=> __( "You are about to remove these items from this slider.\n 'Cancel' to stop, 'OK' to delete.", 'columns-bws' ),
					'warnSingleDelete'			=> __( "You are about to remove this item from the slider.\n 'Cancel' to stop, 'OK' to delete.", 'columns-bws' ),
					'wp_media_title'			=> __( 'Insert Media', 'columns-bws' ),
					'wp_media_button'			=> __( 'Insert', 'columns-bws' ),
					'no_items'					=> __( 'No images found', 'columns-bws' )
				)
			);

			bws_enqueue_settings_scripts();
		} elseif ( isset( $_GET['page'] ) && in_array( $_GET['page'], array( 'columns-settings.php', 'column-new.php' ) ) ) {
			bws_enqueue_settings_scripts();
			bws_plugins_include_codemirror();
		}
	}
}

/**
 * List of JavaScript / CSS files
 * @return void
 */
if ( ! function_exists( 'clmns_register_scripts' ) ) {
	function clmns_register_scripts() {
		global $clmns_options;

		if ( 1 == $clmns_options['add_bootstrap'] ) {
			wp_enqueue_style( 'bootstrap', plugins_url( 'css/bootstrap.min.css', __FILE__ ) );
			wp_enqueue_script( 'bootstrap', plugins_url( 'js/bootstrap.min.js', __FILE__ ), array(), false, true );
		}

		/* Include dashicons */
		wp_enqueue_style( 'dashicons' );
		/* Plugin style */
		wp_enqueue_style( 'clmns_stylesheet', plugins_url( 'css/frontend_style.css', __FILE__ ) );
		/* Include jquery */
		wp_enqueue_script( 'jquery' );
		/* Frontend script */
		wp_enqueue_script( 'clmns_front_script', plugins_url( 'js/script.js', __FILE__ ) );
	}
}


if ( ! function_exists ( 'clmns_screen_options' ) ) {
	function clmns_screen_options() {
		clmns_add_tabs();
		$args = array(
			'label'		=> __( 'Columns per page', 'columns-bws' ),
			'default'	=> 10,
			'option'	=> 'clmns_per_page',
		);
		add_screen_option( 'per_page', $args );
	}
}

/**
 * Function to set up table screen options.
 */
if ( ! function_exists( 'clmns_set_screen_options' ) ) {
	function clmns_set_screen_options( $status, $option, $value ) {
		if ( 'clmns_per_page' == $option ) {
			return $value;
		}
		return $status;
	}
}

if ( ! function_exists( 'clmns_plugin_banner' ) ) {
	function clmns_plugin_banner() {
		global $hook_suffix, $clmns_plugin_info;
		if ( 'plugins.php' == $hook_suffix && ! is_network_admin() ) {
			bws_plugin_banner_to_settings( $clmns_plugin_info, 'clmns_options', 'columns-bws', 'admin.php?page=columns-settings.php', 'admin.php?page=column-new.php' );
		}
		if ( isset( $_REQUEST['page'] ) && 'columns-settings.php' == $_REQUEST['page'] ) {
			bws_plugin_suggest_feature_banner( $clmns_plugin_info, 'clmns_options', 'columns-bws' );
		}
	}
}

/* Functions creates other links on plugins page. */
if ( ! function_exists( 'clmns_action_links' ) ) {
	function clmns_action_links( $links, $file ) {
		if ( ! is_network_admin() ) {
			/* Static so we don't call plugin_basename on every plugin row. */
			static $this_plugin;
			if ( ! $this_plugin )
				$this_plugin = plugin_basename( __FILE__ );
			if ( $file == $this_plugin ) {
				$settings_link = '<a href="admin.php?page=columns-settings.php">' . __( 'Settings', 'columns-bws' ) . '</a>';
				array_unshift( $links, $settings_link );
			}
		}
		return $links;
	}
}
/* End function clmns_action_links */

if ( ! function_exists( 'clmns_links' ) ) {
	function clmns_links( $links, $file ) {
		$base = plugin_basename( __FILE__ );
		if ( $file == $base ) {
			if ( ! is_network_admin() )
				$links[]	=	'<a href="admin.php?page=columns-settings.php">' . __( 'Settings', 'columns-bws' ) . '</a>';
			$links[]	=	'<a href="https://wordpress.org/plugins/columns-bws/faq/" target="_blank">' . __( 'FAQ', 'columns-bws' ) . '</a>';
			$links[]	=	'<a href="https://support.bestwebsoft.com">' . __( 'Support', 'columns-bws' ) . '</a>';
		}
		return $links;
	}
}
/* End function clmns_links */

/**
 * Add help tab to plugins page.
 */
if ( ! function_exists( 'clmns_add_tabs' ) ) {
	function clmns_add_tabs() {
		$screen = get_current_screen();
		$args 	= array(
			'id' 		=> 'clmns',
			'section' 	=> ''
		);
		bws_help_tab( $screen, $args );
	}
}

/**
 * Perform at uninstall
 */
if ( ! function_exists( 'clmns_plugin_uninstall' ) ) {
	function clmns_plugin_uninstall() {
		global $wpdb;

		if ( function_exists( 'is_multisite' ) && is_multisite() ) {
			$old_blog = $wpdb->blogid;
			/* Get all blog ids */
			$blogids = $wpdb->get_col( "SELECT `blog_id` FROM $wpdb->blogs" );
			foreach ( $blogids as $blog_id ) {
				switch_to_blog( $blog_id );
				$wpdb->query( "DROP TABLE IF EXISTS  `" . $wpdb->prefix . "clmns_columns`, `" . $wpdb->prefix . "clmns_column` " );
				delete_option( 'clmns_options' );
			}
			switch_to_blog( $old_blog );
		} else {
			$wpdb->query( "DROP TABLE IF EXISTS  `" . $wpdb->prefix . "clmns_columns`, `" . $wpdb->prefix . "clmns_column` " );
			delete_option( 'clmns_options' );
		}

		require_once( dirname( __FILE__ ) . '/bws_menu/bws_include.php' );
		bws_include_init( plugin_basename( __FILE__ ) );
		bws_delete_plugin( plugin_basename( __FILE__ ) );
	}
}

register_activation_hook( __FILE__, 'clmns_plugin_activate' );

add_action( 'admin_menu', 'clmns_add_admin_menu' );
add_filter( 'parent_file', 'clmns_column_parent_file' );
add_filter( 'submenu_file', 'clmns_column_submenu_file', 10, 2 );
add_action( 'init', 'clmns_init' );
add_action( 'admin_init', 'clmns_admin_init' );

add_action( 'plugins_loaded', 'clmns_plugins_loaded' );

add_action( 'admin_enqueue_scripts', 'clmns_admin_head' );
add_action( 'wp_enqueue_scripts', 'clmns_register_scripts' );

/* Additional links on the plugin page */
add_filter( 'plugin_row_meta', 'clmns_links', 10, 2 );
add_filter( 'plugin_action_links', 'clmns_action_links', 10, 2 );

add_filter( 'set-screen-option', 'clmns_set_screen_options', 10, 3 );

/* Adding banner */
add_action( 'admin_notices', 'clmns_plugin_banner' );

/* custom filter for bws button in tinyMCE */
add_filter( 'bws_shortcode_button_content', 'clmns_shortcode_button_content' );

add_shortcode( 'print_clmns', 'clmns_shortcode' );
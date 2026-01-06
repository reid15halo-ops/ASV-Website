<?php 
// Exit if accessed directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Admin Class
 * 
 * Class to manage admin,
 * functionalities
 */
class CF7EMC_Admin{

	// class constructor
	public function __construct(){
		add_action( 'admin_init', array($this, 'add_tag_generator_mathcaptcha'), 15 );
	}

	/**
	 * math captcha tag generator
	 */
	public function add_tag_generator_mathcaptcha(){
		if( function_exists( 'wpcf7_add_tag_generator' ) ) {
			wpcf7_add_tag_generator( 'mathcaptcha', esc_html__( 'Math Captcha', 'cf7emc' ), 'cf7emc-mathcaptcha', array($this, 'tag_pane_form_mathcaptcha') );
		}
	}

	/**
	 * tag form panel
	 */
	public function tag_pane_form_mathcaptcha( $contact_form ){
		echo '
			<div class="control-box">
				<fieldset>
					<table class="form-table">
						<tbody>
							<tr>
								<th scope="row">
									<label for="tag-generator-panel-mathcaptcha-name">' . esc_html__( 'Name', 'contact-form-7' ) . '</label>
								</th>
								<td>
									<input type="text" name="name" class="tg-name oneline" id="tag-generator-panel-mathcaptcha-name" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="tag-generator-panel-mathcaptcha-id">' . esc_html__( 'Id attribute', 'contact-form-7' ) . '</label>
								</th>
								<td>
									<input type="text" name="id" class="idvalue oneline option" id="tag-generator-panel-mathcaptcha-id" />
								</td>
							</tr>
							<tr>
								<th scope="row">
									<label for="tag-generator-panel-mathcaptcha-class">' . esc_html__( 'Class attribute', 'contact-form-7' ) . '</label>
								</th>
								<td>
									<input type="text" name="class" class="classvalue oneline option" id="tag-generator-panel-mathcaptcha-class" />
								</td>
							</tr>
						</tbody>
					</table>
				</fieldset>
			</div>
			<div class="insert-box">
				<input type="text" name="mathcaptcha" class="tag code" readonly="readonly" onfocus="this.select();">
				<div class="submitbox">
					<input type="button" class="button button-primary insert-tag" value="' . esc_attr__( 'Insert Tag', 'contact-form-7' ) . '">
				</div>
				<br class="clear">
			</div>';
	}
}

return new CF7EMC_Admin();
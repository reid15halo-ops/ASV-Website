<?php
/**
 *endanger About Theme
 *
 * @package Endanger
 */

//about theme info
add_action( 'admin_menu', 'endanger_abouttheme' );
function endanger_abouttheme() {    	
	add_theme_page( __('About Theme Info', 'endanger'), __('About Theme Info', 'endanger'), 'edit_theme_options', 'endanger_guide', 'endanger_mostrar_guide');   
} 

//guidline for about theme
function endanger_mostrar_guide() { 
?>
<div class="wrap-GT">
	<div class="gt-left">
   		   <div class="heading-gt">
			  <h3><?php esc_html_e('About Theme Info', 'endanger'); ?></h3>
		   </div>
          <p><?php esc_html_e('Endanger is a Free Adventure WordPress theme. It is Perfect for all Corporate, Professional, personal, sport, news and any type of business. It is user friendly customizer options and Compatible in WordPress Latest Version. also Compatible with WooCommerce, Nextgen gallery ,Contact Form 7 and many WordPress popular plugins.','endanger'); ?></p>
<div class="heading-gt"> <?php esc_html_e('Theme Features', 'endanger'); ?></div>
 

<div class="col-2">
  <h4><?php esc_html_e('Theme Customizer', 'endanger'); ?></h4>
  <div class="description"><?php esc_html_e('The built-in customizer panel quickly change aspects of the design and display changes live before saving them.', 'endanger'); ?></div>
</div>

<div class="col-2">
  <h4><?php esc_html_e('Responsive Ready', 'endanger'); ?></h4>
  <div class="description"><?php esc_html_e('The themes layout will automatically adjust and fit on any screen resolution and looks great on any device. Fully optimized for iPhone and iPad.', 'endanger'); ?></div>
</div>

<div class="col-2">
<h4><?php esc_html_e('Cross Browser Compatible', 'endanger'); ?></h4>
<div class="description"><?php esc_html_e('Our themes are tested in all mordern web browsers and compatible with the latest version including Chrome,Firefox, Safari, Opera, IE11 and above.', 'endanger'); ?></div>
</div>

<div class="col-2">
<h4><?php esc_html_e('E-commerce', 'endanger'); ?></h4>
<div class="description"><?php esc_html_e('Fully compatible with WooCommerce plugin. Just install the plugin and turn your site into a full featured online shop and start selling products.', 'endanger'); ?></div>
</div>

</div><!-- .gt-left -->
	
	<div class="gt-right">			
			<div>
            <hr />  				
				<a href="https://gracethemes.com/documentation/endanger-doc/#homepage-lite" target="_blank"><?php esc_html_e('Documentation', 'endanger'); ?></a>
              
				<hr />  
                <ul>
                 <li><?php esc_html_e('Theme Customizer', 'endanger'); ?></li>
                 <li><?php esc_html_e('Responsive Ready', 'endanger'); ?></li>
                 <li><?php esc_html_e('Cross Browser Compatible', 'endanger'); ?></li>
                 <li><?php esc_html_e('E-commerce', 'endanger'); ?></li>
                 <li><?php esc_html_e('Contact Form 7 Plugin Compatible', 'endanger'); ?></li>  
                 <li><?php esc_html_e('User Friendly', 'endanger'); ?></li> 
                 <li><?php esc_html_e('Translation Ready', 'endanger'); ?></li>
                 <li><?php esc_html_e('Many Other Plugins  Compatible', 'endanger'); ?></li>   
                </ul>              
               
			</div>		
	</div><!-- .gt-right-->
    <div class="clear"></div>
</div><!-- .wrap-GT -->
<?php } ?>
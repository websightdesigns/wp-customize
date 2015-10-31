<?php
/**
 * @package WP_Customize
 * @version 0.9
 */
/*
Plugin Name: WP-Customize
Description: This plugin allows you to customize the WordPress login page and set your own footer for the WordPress Admin.
Author: WebSight Designs
Version: 0.9
Author URI: http://websightdesigns.com/
License: GPL2
*/

/*
Copyright 2013  WebSight Designs  (email : http://websightdesigns.com/contact/)

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

// enqueue javascript for admin pages
function wsd_admin_scripts() {
    wp_enqueue_script( 'jquery' );
    wp_enqueue_style( 'wsd-customize-spectrum', plugin_dir_url(__FILE__) . 'spectrum.css', null, '1.7.0' );
    wp_enqueue_script( 'wsd-customize-spectrum-js', plugin_dir_url(__FILE__) . 'spectrum.js', array( 'jquery' ), '1.7.0', true );
    wp_enqueue_script( 'wsd-customize-js', plugin_dir_url(__FILE__) . 'script.js', array( 'wsd-customize-spectrum-js' ), '0.9', true );
}
add_action( 'admin_enqueue_scripts', 'wsd_admin_scripts' );

// add a new admin menu item
function wsd_add_pages() {
	// Add a new submenu under Settings:
	add_options_page(__('Customize','wsd-menu'), __('Customize','wsd-menu'), 'manage_options', 'settings', 'wsd_settings_page');
}
add_action('admin_menu', 'wsd_add_pages');

// wsd_settings_page() displays the page content for the Test settings submenu
function wsd_settings_page() {
	//must check that the user has the required capability
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	// variables for the field and option names
	$opt_name = 'wsd_admin_footer_contents';
	$hidden_field_name = 'wsd_submit_hidden';
	$data_field_name = 'wsd_admin_footer_contents';
	// Read in existing option value from database
	$opt_val = get_option( $opt_name );
	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		// Save the posted value in the database
		update_option('wsd_admin_logo', htmlentities(stripslashes($_POST['wsd_admin_logo'])));
		update_option('wsd_admin_logo_width', htmlentities(stripslashes($_POST['wsd_admin_logo_width'])));
		update_option('wsd_admin_logo_height', htmlentities(stripslashes($_POST['wsd_admin_logo_height'])));
		update_option('wsd_admin_logo_area_width', htmlentities(stripslashes($_POST['wsd_admin_logo_area_width'])));
		update_option('wsd_admin_logo_area_height', htmlentities(stripslashes($_POST['wsd_admin_logo_area_height'])));
		update_option('wsd_admin_bgcolor', htmlentities(stripslashes($_POST['wsd_admin_bgcolor'])));
		update_option('wsd_admin_linkcolor', htmlentities(stripslashes($_POST['wsd_admin_linkcolor'])));
		update_option('wsd_admin_linkhovercolor', htmlentities(stripslashes($_POST['wsd_admin_linkhovercolor'])));
		update_option('wsd_admin_loginstyles', htmlentities(stripslashes($_POST['wsd_admin_loginstyles'])));
		update_option('wsd_admin_footer_contents', htmlentities(stripslashes($_POST['wsd_admin_footer_contents'])));
		update_option('wsd_hide_register_forgot_links', htmlentities(stripslashes($_POST['wsd_hide_register_forgot_links'])));
		update_option('wsd_hide_back_link', htmlentities(stripslashes($_POST['wsd_hide_back_link'])));
		update_option('wsd_remember_me_by_default', htmlentities(stripslashes($_POST['wsd_remember_me_by_default'])));
		update_option('wsd_custom_error_message', htmlentities(stripslashes($_POST['wsd_custom_error_message'])));
		update_option('wsd_remove_login_shake', htmlentities(stripslashes($_POST['wsd_remove_login_shake'])));
		// Put an settings updated message on the screen
		?><div class="updated fade"><p><strong><?php _e('Settings saved.', 'wsd-menu' ); ?></strong></p></div><?php
	}
    ?>
    <div class="wrap">
    <?php screen_icon(); ?>
    <h2>Customize</h2>
    <form name="wsd_customize" method="post" action="">
    	<?php //settings_fields('myoption-group'); ?>
    	<?php //do_settings_fields('myoption-group'); ?>
    	<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<hr />
		<h3>WordPress Admin Login</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Custom Logo URL:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wsd_admin_logo" value="<?php
					echo (isset( $_POST['wsd_admin_logo'] ) && $_POST['wsd_admin_logo'] ? $_POST['wsd_admin_logo'] : html_entity_decode(get_option('wsd_admin_logo', htmlentities(get_option('wsd_admin_logo')))) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Image Size:", 'wsd-menu'); ?> </th>
				<td>
				Width: <input type="text" size="5" name="wsd_admin_logo_width" value="<?php
					echo (isset( $_POST['wsd_admin_logo_width'] ) && $_POST['wsd_admin_logo_width'] ? $_POST['wsd_admin_logo_width'] : html_entity_decode(get_option('wsd_admin_logo_width', htmlentities(get_option('wsd_admin_logo_width')))) );
				?>"> px.&nbsp;&nbsp;&nbsp;&nbsp;Height: <input type="text" size="5" name="wsd_admin_logo_height" value="<?php
					echo (isset( $_POST['wsd_admin_logo_height'] ) && $_POST['wsd_admin_logo_height'] ? $_POST['wsd_admin_logo_height'] : html_entity_decode(get_option('wsd_admin_logo_height', htmlentities(get_option('wsd_admin_logo_height')))) );
				?>"> px.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Area Width:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="5" name="wsd_admin_logo_area_width" value="<?php
					echo (isset( $_POST['wsd_admin_logo_area_width'] ) && $_POST['wsd_admin_logo_area_width'] ? $_POST['wsd_admin_logo_area_width'] : html_entity_decode(get_option('wsd_admin_logo_area_width', htmlentities(get_option('wsd_admin_logo_area_width')))) );
				?>"> px.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Area Height:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="5" name="wsd_admin_logo_area_height" value="<?php
					echo (isset( $_POST['wsd_admin_logo_area_height'] ) && $_POST['wsd_admin_logo_area_height'] ? $_POST['wsd_admin_logo_area_height'] : html_entity_decode(get_option('wsd_admin_logo_area_height', htmlentities(get_option('wsd_admin_logo_area_height')))) );
				?>"> px.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Color:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wsd_admin_bgcolor" name="wsd_admin_bgcolor" value="<?php
					echo (isset( $_POST['wsd_admin_bgcolor'] ) && $_POST['wsd_admin_bgcolor'] ? $_POST['wsd_admin_bgcolor'] : html_entity_decode(get_option('wsd_admin_bgcolor', '000')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Links Text Color:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wsd_admin_linkcolor" name="wsd_admin_linkcolor" value="<?php
					echo (isset( $_POST['wsd_admin_linkcolor'] ) && $_POST['wsd_admin_linkcolor'] ? $_POST['wsd_admin_linkcolor'] : html_entity_decode(get_option('wsd_admin_linkcolor', 'fff')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Links Text Hover Color:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wsd_admin_linkhovercolor" name="wsd_admin_linkhovercolor" value="<?php
					echo (isset( $_POST['wsd_admin_linkhovercolor'] ) && $_POST['wsd_admin_linkhovercolor'] ? $_POST['wsd_admin_linkhovercolor'] : html_entity_decode(get_option('wsd_admin_linkhovercolor', 'cfcfcf')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Hide the Register and Forgot Password links?", 'wsd-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wsd_hide_register_forgot_links" name="wsd_hide_register_forgot_links" value="1"<?php
					if(
						(
							isset( $_POST['wsd_hide_register_forgot_links'] )
							&& $_POST['wsd_hide_register_forgot_links'] == "1"
						) || (
							html_entity_decode(get_option('wsd_hide_register_forgot_links')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Hide the Back to Blog link?", 'wsd-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wsd_hide_back_link" name="wsd_hide_back_link" value="1"<?php
					if(
						(
							isset( $_POST['wsd_hide_back_link'] )
							&& $_POST['wsd_hide_back_link'] == "1"
						) || (
							html_entity_decode(get_option('wsd_hide_back_link')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Check remember me by default?", 'wsd-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wsd_remember_me_by_default" name="wsd_remember_me_by_default" value="1"<?php
					if(
						(
							isset( $_POST['wsd_remember_me_by_default'] )
							&& $_POST['wsd_remember_me_by_default'] == "1"
						) || (
							html_entity_decode(get_option('wsd_remember_me_by_default')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Remove the login form shake?", 'wsd-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wsd_remove_login_shake" name="wsd_remove_login_shake" value="1"<?php
					if(
						(
							isset( $_POST['wsd_remove_login_shake'] )
							&& $_POST['wsd_remove_login_shake'] == "1"
						) || (
							html_entity_decode(get_option('wsd_remove_login_shake')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Custom Error Message:", 'wsd-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wsd_custom_error_message" value="<?php
					echo (isset( $_POST['wsd_custom_error_message'] ) && $_POST['wsd_custom_error_message'] ? $_POST['wsd_custom_error_message'] : html_entity_decode(get_option('wsd_custom_error_message', htmlentities(get_option('wsd_custom_error_message')))) );
				?>">
				</td>
			</tr>

			<tr valign="top">
				<th scope="row"><?php _e("Custom Login Page CSS:", 'wsd-menu'); ?> </th>
				<td>
				<textarea cols="42" rows="8" name="wsd_admin_loginstyles"><?php
					echo html_entity_decode(get_option('wsd_admin_loginstyles',htmlentities(get_option('wsd_admin_loginstyles'))));
				?></textarea>
				</td>
			</tr>
		</table>
		<hr />
		<h3>WordPress Admin Footer</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Admin Footer HTML:", 'wsd-menu'); ?> </th>
				<td>
				<textarea cols="42" rows="4" name="wsd_admin_footer_contents"><?php
					echo html_entity_decode(get_option('wsd_admin_footer_contents',htmlentities(get_option('wsd_admin_footer_contents'))));
				?></textarea>
				</td>
			</tr>
		</table>
		<hr />
		<?php submit_button(); ?>
	</form>
	</div>
	<?php
}

/**
 * Set a new footer in the WordPress Admin
 */
function wsd_remove_footer_admin () {
	$wsd_footer_default_value = 'Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.';
	if(get_option('wsd_admin_footer_contents') == "") {
		echo $wsd_footer_default_value;
	} else {
		echo html_entity_decode(get_option('wsd_admin_footer_contents', htmlentities($wsd_footer_default_value)));
	}
}
add_filter('admin_footer_text', 'wsd_remove_footer_admin');

/**
 * Add a custom logo to the WordPress Admin header
 */
function wsd_custom_logo() {
	echo '<style type="text/css">
		#header-logo, #login h1 a {
			background-image: url(' . html_entity_decode(get_option('wsd_admin_logo')) . ') !important;
			background-size: ' . html_entity_decode(get_option('wsd_admin_logo_width')) . 'px ' . html_entity_decode(get_option('wsd_admin_logo_height')) . 'px !important;
			height: ' . html_entity_decode(get_option('wsd_admin_logo_area')) . 'px !important;
		}
	</style>';
}
add_action('admin_head', 'wsd_custom_logo');

/**
 * Hide the register and forgot password links from the login form
 */
function wsd_hide_register_forgot_links() {
	if( html_entity_decode(get_option('wsd_hide_register_forgot_links')) ) {
		echo '<style type="text/css">
			p#nav {
				display: none;
			}
		</style>';
	}
}
add_action('login_head', 'wsd_hide_register_forgot_links');

/**
 * Hide the Back to Blog link from the login form
 */
function wsd_hide_back_link() {
	if( html_entity_decode(get_option('wsd_hide_back_link')) ) {
		echo '<style type="text/css">
			p#backtoblog {
				display: none;
			}
		</style>';
	}
}
add_action('login_head', 'wsd_hide_back_link');

/**
 * Add a custom logo to the WordPress Admin login page header
 */
function wsd_custom_login_logo() {
	echo '<style type="text/css">';
	if( html_entity_decode(get_option('wsd_admin_logo')) ) {
		echo '#login h1 a {
			background-image:url(' . html_entity_decode(get_option('wsd_admin_logo')) . ') !important;
			background-size: ' . html_entity_decode(get_option('wsd_admin_logo_width')) . 'px ' . html_entity_decode(get_option('wsd_admin_logo_height')) . 'px !important;
			height: ' . html_entity_decode(get_option('wsd_admin_logo_area_height')) . 'px !important;
			width: ' . html_entity_decode(get_option('wsd_admin_logo_area_width')) . 'px !important;
		}';
	}
	if( html_entity_decode(get_option('wsd_admin_bgcolor', '000')) ) {
		echo 'body { background-color:' . html_entity_decode(get_option('wsd_admin_bgcolor', '000')) . ' !important; }';
	}
	if( html_entity_decode(get_option('wsd_admin_linkcolor', 'fff')) ) {
		echo '#login #nav a, #login #backtoblog a { color:' . html_entity_decode(get_option('wsd_admin_linkcolor', 'fff')) . ' !important; text-shadow: none !important; }';
	}
	if( html_entity_decode(get_option('wsd_admin_linkhovercolor', 'cfcfcf')) ) {
		echo '#login #nav a:hover, #login #backtoblog a:hover { color: ' . html_entity_decode(get_option('wsd_admin_linkhovercolor', 'cfcfcf')) . ' !important; text-shadow: none !important; }';
	}
	echo '</style>';

}
add_action('login_head', 'wsd_custom_login_logo');

/**
 * Add custom custom CSS styles to the Wordpress Admin login page header
 */
if( get_option('wsd_admin_loginstyles') ) {
	function wsd_custom_login_styles() {
		?><style type="text/css">
			<?php echo get_option('wsd_admin_loginstyles'); ?>
		</style><?php
	}
	add_action('login_head', 'wsd_custom_login_styles');
}

/**
 * Check the "Remember me" checkbox by default
 */
if( get_option('wsd_remember_me_by_default') ) {
	function login_checked_remember_me() {
		add_filter( 'login_footer', 'rememberme_checked' );
	}
	add_action( 'init', 'login_checked_remember_me' );
	function rememberme_checked() {
		echo "<script>document.getElementById('rememberme').checked = true;</script>";
	}
}

/**
 * Remove the login shake from the login form
 */
if( get_option('wsd_remove_login_shake') ) {
	function wsd_remove_login_shake() {
		remove_action('login_head', 'wp_shake_js', 12);
	}
	add_action('login_head', 'wsd_remove_login_shake');
}

/**
 * Change default error message
 */
function wsd_custom_error_message() {
	return ( get_option('wsd_custom_error_message') ? html_entity_decode(get_option('wsd_custom_error_message')) : 'Incorrect login details. Please try again.' );
}
add_filter('login_errors', 'wsd_custom_error_message');

/**
 * Set a custom URL for the WordPress Admin login form to redirect to
 */
function wsd_custom_login_url() {
	return site_url();
}
add_filter('login_headerurl', 'wsd_custom_login_url');

/**
 * Set a custom WordPress Admin login page header title
 */
function wsd_login_header_title() {
	return get_bloginfo('name');
}
add_filter('login_headertitle', 'wsd_login_header_title');
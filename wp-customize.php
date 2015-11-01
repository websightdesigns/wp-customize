<?php
/**
 * @package WP_Customize
 * @version 1.0
 */

/*
Plugin Name: WP-Customize
Description: This plugin allows you to customize the WordPress login page and set your own footer for the WordPress Admin.
Author: WebSight Designs
Version: 1.0
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



if ( ! defined( 'ABSPATH' ) ) {
	die();
}

if ( version_compare( $GLOBALS['wp_version'], '3.1', '<' ) ) {
	return;
}



/* Runs when plugin is activated */
register_activation_hook(__FILE__,'wp_customize_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'wp_customize_remove' );

function wp_customize_install() {

	global $wpdb;

	// set up variables for the login page
	$the_page_title = 'Log In';
	$the_page_name = 'login';

	// the menu entry...
	delete_option("wp_customize_page_title");
	add_option("wp_customize_page_title", $the_page_title, '', 'yes');
	// the slug...
	delete_option("wp_customize_page_name");
	add_option("wp_customize_page_name", $the_page_name, '', 'yes');
	// the id...
	delete_option("wp_customize_page_id");
	add_option("wp_customize_page_id", '0', '', 'yes');

	$the_page = get_page_by_title( $the_page_title );

	if ( ! $the_page ) {

		// Create post object
		$new_post = array();
		$new_post['post_title'] = $the_page_title;
		$new_post['post_name'] = $the_page_name;
		$new_post['post_content'] = "";
		$new_post['post_status'] = 'publish';
		$new_post['post_type'] = 'page';
		$new_post['comment_status'] = 'closed';
		$new_post['ping_status'] = 'closed';
		$new_post['post_category'] = array(1); // the default 'Uncategorized'

		// Insert the post into the database
		$the_page_id = wp_insert_post( $new_post );

	} else {
		// the plugin may have been previously active and the page may just be trashed...

		$the_page_id = $the_page->ID;

		//make sure the page is not trashed...
		$the_page->post_status = 'publish';
		$the_page_id = wp_update_post( $the_page );

	}

	// create the new page
	delete_option( 'wp_customize_page_id' );
	add_option( 'wp_customize_page_id', $the_page_id );
	// assign the page template to the new page
	update_post_meta( $the_page_id, "_wp_page_template", "template-login.php" );

	/**
	 * Configure server name
	 */
	$servername = str_replace(".", "\.", $_SERVER['SERVER_NAME']);

	/**
	 * Configure apache rewrite rules
	 */
	$rewrite_rules = <<< EOD
<IfModule mod_rewrite.c>
RewriteEngine on
RewriteCond %{REQUEST_METHOD} POST
RewriteCond %{HTTP_REFERER} !^http://(.*)?$servername [NC]
RewriteCond %{REQUEST_URI} ^(.*)?wp-login\.php(.*)$ [OR]
RewriteCond %{REQUEST_URI} ^(.*)?wp-admin$
RewriteRule ^(.*)$ /login/ [R=301,L]
</IfModule>
EOD;

	// add our rewrite rules to Apache .htaccess
	insert_apache_rewrite_rules( $rewrite_rules );

}

function wp_customize_remove() {

	global $wpdb;

	$the_page_title = get_option( "wp_customize_page_title" );
	$the_page_name = get_option( "wp_customize_page_name" );

	//  the id of our page...
	$the_page_id = get_option( 'wp_customize_page_id' );
	if( $the_page_id ) {

		wp_delete_post( $the_page_id ); // this will trash, not delete

	}

	// delete the page
	delete_option("wp_customize_page_title");
	delete_option("wp_customize_page_name");
	delete_option("wp_customize_page_id");

	// remove our rewrite rules from Apache .htaccess
	remove_apache_rewrite_rules();

}

/**
 * Insert apache rewrite rules
 */
function insert_apache_rewrite_rules( $rewrite_rules, $marker = 'WP-Customize', $before = '# BEGIN WordPress' ) {
	// get path to htaccess file
	$htaccess_file = get_home_path() . '.htaccess';
	// check if htaccess file exists
	$htaccess_exists = file_exists( $htaccess_file );
	// if htaccess file exists, get htaccess contents
	$htaccess_content = $htaccess_exists ? file_get_contents( $htaccess_file ) : '';
	// remove any previously added rules from htaccess contents, to avoid duplication
	$htaccess_content = preg_replace( "/# BEGIN $marker.*# END $marker\n*/is", '', $htaccess_content );

	// add new rules to htaccess contents
	if ( $before && $rewrite_rules ) {

		$rewrite_rules = is_array( $rewrite_rules ) ? implode( "\n", $rewrite_rules ) : $rewrite_rules;
		$rewrite_rules = trim( $rewrite_rules, "\r\n " );

		if ( $rewrite_rules ) {
			// No WordPress rules? (as in multisite)
			if ( false === strpos( $htaccess_content, $before ) ) {
				// The new content needs to be inserted at the begining of the file.
				$htaccess_content = "# BEGIN $marker\n$rewrite_rules\n# END $marker\n\n\n$htaccess_content";
			}
			else {
				// The new content needs to be inserted before the WordPress rules.
				$rewrite_rules = "# BEGIN $marker\n$rewrite_rules\n# END $marker\n\n\n$before";
				$htaccess_content = str_replace( $before, $rewrite_rules, $htaccess_content );
			}
		}
	}

	// Update the .htaccess file
	return (bool) file_put_contents( $htaccess_file , $htaccess_content );
}

/**
 * Remove apache rewrite rules
 */
function remove_apache_rewrite_rules( $marker = 'WP-Customize' ) {
	// get path to htaccess file
	$htaccess_file = get_home_path() . '.htaccess';
	// check if htaccess file exists
	$htaccess_exists = file_exists( $htaccess_file );
	// if htaccess file exists, get htaccess contents
	$htaccess_content = $htaccess_exists ? file_get_contents( $htaccess_file ) : '';
	// remove the added rules from htaccess contents
	$htaccess_content = preg_replace( "/# BEGIN $marker.*# END $marker\n*/is", '', $htaccess_content );

	// Update the .htaccess file
	return (bool) file_put_contents( $htaccess_file , $htaccess_content );
}



// create page template
class PageTemplater {

	/**
	 * A reference to an instance of this class.
	 */
	private static $instance;

	/**
	 * The array of templates that this plugin tracks.
	 */
	protected $templates;


	/**
	 * Returns an instance of this class.
	 */
	public static function get_instance() {

		if( null == self::$instance ) {
			self::$instance = new PageTemplater();
		}

		return self::$instance;

	}

	/**
	 * Initializes the plugin by setting filters and administration functions.
	 */
	private function __construct() {

		$this->templates = array();

		// Add a filter to the attributes metabox to inject template into the cache.
		add_filter(
			'page_attributes_dropdown_pages_args',
			 array( $this, 'register_project_templates' )
		);

		// Add a filter to the save post to inject out template into the page cache
		add_filter(
			'wp_insert_post_data',
			array( $this, 'register_project_templates' )
		);

		// Add a filter to the template include to determine if the page has our
		// template assigned and return it's path
		add_filter(
			'template_include',
			array( $this, 'view_project_template')
		);

		// Add your templates to this array.
		$this->templates = array(
			'template-login.php' => 'Log In'
		);

	}


	/**
	 * Adds our template to the pages cache in order to trick WordPress
	 * into thinking the template file exists where it doens't really exist.
	 */

	public function register_project_templates( $atts ) {

		// Create the key used for the themes cache
		$cache_key = 'page_templates-' . md5( get_theme_root() . '/' . get_stylesheet() );

		// Retrieve the cache list.
		// If it doesn't exist, or it's empty prepare an array
		$templates = wp_get_theme()->get_page_templates();
		if ( empty( $templates ) ) {
			$templates = array();
		}

		// New cache, therefore remove the old one
		wp_cache_delete( $cache_key , 'themes');

		// Now add our template to the list of templates by merging our templates
		// with the existing templates array from the cache.
		$templates = array_merge( $templates, $this->templates );

		// Add the modified cache to allow WordPress to pick it up for listing
		// available templates
		wp_cache_add( $cache_key, $templates, 'themes', 1800 );

		return $atts;

	}

	/**
	 * Checks if the template is assigned to the page
	 */
	public function view_project_template( $template ) {

		global $post;
		// ob_start(); echo "<pre>"; var_dump($post); echo "</pre>"; $dump = ob_get_clean(); echo $dump;

		if (!isset($this->templates[get_post_meta(
			$post->ID, '_wp_page_template', true
		)] ) ) {
			return $template;
		}

		$file = plugin_dir_path(__FILE__). get_post_meta(
			$post->ID, '_wp_page_template', true
		);

		// Just to be safe, we check if the file exist first
		if( file_exists( $file ) ) {
			return $file;
		} else {
			echo $file;
		}

		return $template;

	}


}

add_action( 'plugins_loaded', array( 'PageTemplater', 'get_instance' ) );



// enqueue javascript for admin pages
function wpcustomize_admin_scripts() {
	wp_enqueue_script( 'jquery' );
	wp_enqueue_script('media-upload');
	wp_enqueue_script('thickbox');
	wp_enqueue_style('thickbox');
	wp_enqueue_style( 'wp-customize-spectrum', plugin_dir_url(__FILE__) . 'spectrum.css', null, '1.7.0' );
	wp_enqueue_script( 'wp-customize-spectrum-js', plugin_dir_url(__FILE__) . 'spectrum.js', array( 'jquery' ), '1.7.0', true );
	wp_enqueue_script( 'wp-customize-js', plugin_dir_url(__FILE__) . 'script.js', array( 'wp-customize-spectrum-js', 'jquery', 'media-upload', 'thickbox' ), '0.9', true );
}
add_action( 'admin_enqueue_scripts', 'wpcustomize_admin_scripts' );

// add a new admin menu item
function wpcustomize_add_pages() {
	// Add a new submenu under Settings:
	add_options_page(__('Customize','wp-customize-menu'), __('Customize','wp-customize-menu'), 'manage_options', 'settings', 'wpcustomize_settings_page');
}
add_action('admin_menu', 'wpcustomize_add_pages');

// wpcustomize_settings_page() displays the page content for the Test settings submenu
function wpcustomize_settings_page() {
	//must check that the user has the required capability
	if (!current_user_can('manage_options')) {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	// variables for the field and option names
	$opt_name = 'wpcustomize_admin_footer_contents';
	$hidden_field_name = 'wpcustomize_submit_hidden';
	$data_field_name = 'wpcustomize_admin_footer_contents';
	// Read in existing option value from database
	$opt_val = get_option( $opt_name );
	// set default checkbox values
	$wpcustomize_hide_register_forgot_links = ( isset( $_POST['wpcustomize_hide_register_forgot_links'] ) && $_POST['wpcustomize_hide_register_forgot_links'] == "1" ? "1" : "0" );
	$wpcustomize_hide_back_link = ( isset( $_POST['wpcustomize_hide_back_link'] ) && $_POST['wpcustomize_hide_back_link'] == "1" ? "1" : "0" );
	$wpcustomize_remember_me_by_default = ( isset( $_POST['wpcustomize_remember_me_by_default'] ) && $_POST['wpcustomize_remember_me_by_default'] == "1" ? "1" : "0" );
	$wpcustomize_remove_login_shake = ( isset( $_POST['wpcustomize_remove_login_shake'] ) && $_POST['wpcustomize_remove_login_shake'] == "1" ? "1" : "0" );
	$wpcustomize_admin_login_redirect = ( isset( $_POST['wpcustomize_admin_login_redirect'] ) && $_POST['wpcustomize_admin_login_redirect'] == "1" ? "1" : "0" );
	// See if the user has posted us some information
	// If they did, this hidden field will be set to 'Y'
	if( isset($_POST[ $hidden_field_name ]) && $_POST[ $hidden_field_name ] == 'Y' ) {
		// Save the posted value in the database
		update_option('wpcustomize_admin_page_title', htmlentities(stripslashes($_POST['wpcustomize_admin_page_title'])));
		update_option('wpcustomize_admin_logo_image_url', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_image_url'])));
		update_option('wpcustomize_admin_logo_link_url', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_link_url'])));
		update_option('wpcustomize_admin_logo_title', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_title'])));
		update_option('wpcustomize_admin_logo_width', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_width'])));
		update_option('wpcustomize_admin_logo_height', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_height'])));
		update_option('wpcustomize_admin_logo_area_width', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_area_width'])));
		update_option('wpcustomize_admin_logo_area_height', htmlentities(stripslashes($_POST['wpcustomize_admin_logo_area_height'])));
		update_option('wpcustomize_admin_bgcolor', htmlentities(stripslashes($_POST['wpcustomize_admin_bgcolor'])));
		update_option('wpcustomize_admin_linkcolor', htmlentities(stripslashes($_POST['wpcustomize_admin_linkcolor'])));
		update_option('wpcustomize_admin_linkhovercolor', htmlentities(stripslashes($_POST['wpcustomize_admin_linkhovercolor'])));
		update_option('wpcustomize_admin_loginstyles', htmlentities(stripslashes($_POST['wpcustomize_admin_loginstyles'])));
		update_option('wpcustomize_admin_footer_contents', htmlentities(stripslashes($_POST['wpcustomize_admin_footer_contents'])));
		update_option('wpcustomize_hide_register_forgot_links', htmlentities(stripslashes($wpcustomize_hide_register_forgot_links)));
		update_option('wpcustomize_hide_back_link', htmlentities(stripslashes($wpcustomize_hide_back_link)));
		update_option('wpcustomize_remember_me_by_default', htmlentities(stripslashes($wpcustomize_remember_me_by_default)));
		update_option('wpcustomize_custom_error_message', htmlentities(stripslashes($_POST['wpcustomize_custom_error_message'])));
		update_option('wpcustomize_remove_login_shake', htmlentities(stripslashes($wpcustomize_remove_login_shake)));
		update_option('wpcustomize_admin_login_redirect', htmlentities(stripslashes($wpcustomize_admin_login_redirect)));
		update_option('wpcustomize_admin_login_redirect_url', htmlentities(stripslashes($_POST['wpcustomize_admin_login_redirect_url'])));
		update_option('wpcustomize_admin_login_background_url', htmlentities(stripslashes($_POST['wpcustomize_admin_login_background_url'])));
		update_option('wpcustomize_admin_login_background_repeat', htmlentities(stripslashes($_POST['wpcustomize_admin_login_background_repeat'])));
		update_option('wpcustomize_admin_login_background_position', htmlentities(stripslashes($_POST['wpcustomize_admin_login_background_position'])));
		update_option('wpcustomize_admin_login_background_attachment', htmlentities(stripslashes($_POST['wpcustomize_admin_login_background_attachment'])));
		update_option('wpcustomize_admin_login_background_size', htmlentities(stripslashes($_POST['wpcustomize_admin_login_background_size'])));
		// Put an settings updated message on the screen
		?><div class="updated fade"><p><strong><?php _e('Settings saved.', 'wp-customize-menu' ); ?></strong></p></div><?php
	}
	?>
	<div class="wrap">
	<?php screen_icon(); ?>
	<h2>Customize</h2>
	<form name="wpcustomize_customize" method="post" action="">
		<?php //settings_fields('myoption-group'); ?>
		<?php //do_settings_fields('myoption-group'); ?>
		<input type="hidden" name="<?php echo $hidden_field_name; ?>" value="Y">
		<hr />
		<h3>WordPress Admin Login</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Login Page Title:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wpcustomize_admin_page_title" value="<?php
					echo (isset( $_POST['wpcustomize_admin_page_title'] ) && $_POST['wpcustomize_admin_page_title'] ? $_POST['wpcustomize_admin_page_title'] : html_entity_decode(get_option('wpcustomize_admin_page_title', htmlentities(get_option('wpcustomize_admin_page_title')))) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Logo Image:</th>
				<td>
					<label for="wpcustomize_admin_logo_image_url">
						<input id="wpcustomize_admin_logo_image_url" type="text" size="36" name="wpcustomize_admin_logo_image_url" value="<?php
							echo (isset( $_POST['wpcustomize_admin_logo_image_url'] ) && $_POST['wpcustomize_admin_logo_image_url'] ? $_POST['wpcustomize_admin_logo_image_url'] : html_entity_decode(get_option('wpcustomize_admin_logo_image_url', htmlentities(get_option('wpcustomize_admin_logo_image_url')))) );
						?>" />
						<input id="wpcustomize_admin_logo_image_url_button" type="button" class="button" value="Upload Image" />
						<br />Enter a URL or upload an image for the logo.
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Link URL:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wpcustomize_admin_logo_link_url" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_link_url'] ) && $_POST['wpcustomize_admin_logo_link_url'] ? $_POST['wpcustomize_admin_logo_link_url'] : html_entity_decode(get_option('wpcustomize_admin_logo_link_url', htmlentities(get_option('wpcustomize_admin_logo_link_url')))) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Title Attribute:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wpcustomize_admin_logo_title" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_title'] ) && $_POST['wpcustomize_admin_logo_title'] ? $_POST['wpcustomize_admin_logo_title'] : html_entity_decode(get_option('wpcustomize_admin_logo_title', htmlentities(get_option('wpcustomize_admin_logo_title')))) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Image Size:", 'wp-customize-menu'); ?> </th>
				<td>
				Width: <input type="text" class="smallinput" size="5" name="wpcustomize_admin_logo_width" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_width'] ) && $_POST['wpcustomize_admin_logo_width'] ? $_POST['wpcustomize_admin_logo_width'] : html_entity_decode(get_option('wpcustomize_admin_logo_width', htmlentities(get_option('wpcustomize_admin_logo_width')))) );
				?>"> px.&nbsp;&nbsp;&nbsp;&nbsp;Height: <input type="text" class="smallinput" size="5" name="wpcustomize_admin_logo_height" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_height'] ) && $_POST['wpcustomize_admin_logo_height'] ? $_POST['wpcustomize_admin_logo_height'] : html_entity_decode(get_option('wpcustomize_admin_logo_height', htmlentities(get_option('wpcustomize_admin_logo_height')))) );
				?>"> px.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Logo Area Size:", 'wp-customize-menu'); ?> </th>
				<td>
				Width: <input type="text" class="smallinput" size="5" name="wpcustomize_admin_logo_area_width" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_area_width'] ) && $_POST['wpcustomize_admin_logo_area_width'] ? $_POST['wpcustomize_admin_logo_area_width'] : html_entity_decode(get_option('wpcustomize_admin_logo_area_width', htmlentities(get_option('wpcustomize_admin_logo_area_width')))) );
				?>"> px.&nbsp;&nbsp;&nbsp;&nbsp;Height: <input type="text" class="smallinput" size="5" name="wpcustomize_admin_logo_area_height" value="<?php
					echo (isset( $_POST['wpcustomize_admin_logo_area_height'] ) && $_POST['wpcustomize_admin_logo_area_height'] ? $_POST['wpcustomize_admin_logo_area_height'] : html_entity_decode(get_option('wpcustomize_admin_logo_area_height', htmlentities(get_option('wpcustomize_admin_logo_area_height')))) );
				?>"> px.
				</td>
			</tr>
			<tr valign="top">
				<th scope="row">Background Image:</th>
				<td>
					<label for="wpcustomize_admin_login_background_url">
						<input id="wpcustomize_admin_login_background_url" type="text" size="36" name="wpcustomize_admin_login_background_url" value="<?php
							echo (isset( $_POST['wpcustomize_admin_login_background_url'] ) && $_POST['wpcustomize_admin_login_background_url'] ? $_POST['wpcustomize_admin_login_background_url'] : html_entity_decode(get_option('wpcustomize_admin_login_background_url', htmlentities(get_option('wpcustomize_admin_login_background_url')))) );
						?>" />
						<input id="wpcustomize_admin_login_background_url_button" type="button" class="button" value="Upload Image" />
						<br />Enter a URL or upload an image for the logo.
					</label>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Repeat:", 'wp-customize-menu'); ?> </th>
				<td>
					<select name="wpcustomize_admin_login_background_repeat">
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_repeat'] ) && $_POST['wpcustomize_admin_login_background_repeat'] == "repeat") || get_option('wpcustomize_admin_login_background_repeat') == "repeat" ) echo ' selected="selected"'; ?>>repeat</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_repeat'] ) && $_POST['wpcustomize_admin_login_background_repeat'] == "repeat-x") || get_option('wpcustomize_admin_login_background_repeat') == "repeat-x" ) echo ' selected="selected"'; ?>>repeat-x</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_repeat'] ) && $_POST['wpcustomize_admin_login_background_repeat'] == "repeat-y") || get_option('wpcustomize_admin_login_background_repeat') == "repeat-y" ) echo ' selected="selected"'; ?>>repeat-y</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_repeat'] ) && $_POST['wpcustomize_admin_login_background_repeat'] == "no-repeat") || get_option('wpcustomize_admin_login_background_repeat') == "no-repeat" ) echo ' selected="selected"'; ?>>no-repeat</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Position:", 'wp-customize-menu'); ?> </th>
				<td>
					<select name="wpcustomize_admin_login_background_position">
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "left top") || get_option('wpcustomize_admin_login_background_attachment') == "left top" ) echo ' selected="selected"'; ?>>left top</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "left center") || get_option('wpcustomize_admin_login_background_attachment') == "left center" ) echo ' selected="selected"'; ?>>left center</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "left bottom") || get_option('wpcustomize_admin_login_background_attachment') == "left bottom" ) echo ' selected="selected"'; ?>>left bottom</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "right top") || get_option('wpcustomize_admin_login_background_attachment') == "right top" ) echo ' selected="selected"'; ?>>right top</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "right center") || get_option('wpcustomize_admin_login_background_attachment') == "right center" ) echo ' selected="selected"'; ?>>right center</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "right bottom") || get_option('wpcustomize_admin_login_background_attachment') == "right bottom" ) echo ' selected="selected"'; ?>>right bottom</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "center top") || get_option('wpcustomize_admin_login_background_attachment') == "center top" ) echo ' selected="selected"'; ?>>center top</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "center center") || get_option('wpcustomize_admin_login_background_attachment') == "center center" ) echo ' selected="selected"'; ?>>center center</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_position'] ) && $_POST['wpcustomize_admin_login_background_position'] == "center bottom") || get_option('wpcustomize_admin_login_background_attachment') == "center bottom" ) echo ' selected="selected"'; ?>>center bottom</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Attachment:", 'wp-customize-menu'); ?> </th>
				<td>
					<select name="wpcustomize_admin_login_background_attachment">
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_attachment'] ) && $_POST['wpcustomize_admin_login_background_attachment'] == "scroll") || get_option('wpcustomize_admin_login_background_attachment') == "scroll" ) echo ' selected="selected"'; ?>>scroll</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_attachment'] ) && $_POST['wpcustomize_admin_login_background_attachment'] == "fixed") || get_option('wpcustomize_admin_login_background_attachment') == "fixed" ) echo ' selected="selected"'; ?>>fixed</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_attachment'] ) && $_POST['wpcustomize_admin_login_background_attachment'] == "local") || get_option('wpcustomize_admin_login_background_attachment') == "local" ) echo ' selected="selected"'; ?>>local</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Size:", 'wp-customize-menu'); ?> </th>
				<td>
					<select name="wpcustomize_admin_login_background_size">
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_size'] ) && $_POST['wpcustomize_admin_login_background_size'] == "auto") || get_option('wpcustomize_admin_login_background_attachment') == "auto" ) echo ' selected="selected"'; ?>>auto</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_size'] ) && $_POST['wpcustomize_admin_login_background_size'] == "contain") || get_option('wpcustomize_admin_login_background_attachment') == "contain" ) echo ' selected="selected"'; ?>>contain</option>
						<option<?php if( (isset( $_POST['wpcustomize_admin_login_background_size'] ) && $_POST['wpcustomize_admin_login_background_size'] == "cover") || get_option('wpcustomize_admin_login_background_attachment') == "cover" ) echo ' selected="selected"'; ?>>cover</option>
					</select>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Background Color:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wpcustomize_admin_bgcolor" name="wpcustomize_admin_bgcolor" value="<?php
					echo (isset( $_POST['wpcustomize_admin_bgcolor'] ) && $_POST['wpcustomize_admin_bgcolor'] ? $_POST['wpcustomize_admin_bgcolor'] : html_entity_decode(get_option('wpcustomize_admin_bgcolor', '000')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Links Text Color:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wpcustomize_admin_linkcolor" name="wpcustomize_admin_linkcolor" value="<?php
					echo (isset( $_POST['wpcustomize_admin_linkcolor'] ) && $_POST['wpcustomize_admin_linkcolor'] ? $_POST['wpcustomize_admin_linkcolor'] : html_entity_decode(get_option('wpcustomize_admin_linkcolor', 'fff')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Links Text Hover Color:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="6" id="wpcustomize_admin_linkhovercolor" name="wpcustomize_admin_linkhovercolor" value="<?php
					echo (isset( $_POST['wpcustomize_admin_linkhovercolor'] ) && $_POST['wpcustomize_admin_linkhovercolor'] ? $_POST['wpcustomize_admin_linkhovercolor'] : html_entity_decode(get_option('wpcustomize_admin_linkhovercolor', 'cfcfcf')) );
				?>">
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Custom Error Message:", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="text" size="42" name="wpcustomize_custom_error_message" value="<?php
					echo (isset( $_POST['wpcustomize_custom_error_message'] ) && $_POST['wpcustomize_custom_error_message'] ? $_POST['wpcustomize_custom_error_message'] : html_entity_decode(get_option('wpcustomize_custom_error_message', htmlentities(get_option('wpcustomize_custom_error_message')))) );
				?>"><br>
				(Default: Incorrect login details. Please try again.)
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Redirect on login?", 'wp-customize-menu'); ?> </th>
				<td>
					<input type="checkbox" id="wpcustomize_admin_login_redirect" name="wpcustomize_admin_login_redirect" value="1"<?php
						if(
							(
								isset( $_POST['wpcustomize_admin_login_redirect'] )
								&& $_POST['wpcustomize_admin_login_redirect'] == "1"
							) || (
								html_entity_decode(get_option('wpcustomize_admin_login_redirect')) == "1"
							)
						) {
							echo ' checked="checked"';
						}
					?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Redirect URL:", 'wp-customize-menu'); ?> </th>
				<td>
					<input type="text" size="42" name="wpcustomize_admin_login_redirect_url" value="<?php
						echo (isset( $_POST['wpcustomize_admin_login_redirect_url'] ) && $_POST['wpcustomize_admin_login_redirect_url'] ? $_POST['wpcustomize_admin_login_redirect_url'] : html_entity_decode(get_option('wpcustomize_admin_login_redirect_url', htmlentities(get_option('wpcustomize_admin_login_redirect_url')))) );
					?>"><br>
					(Leave blank to redirect to the Site URL)
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Hide the Register and Forgot Password links?", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wpcustomize_hide_register_forgot_links" name="wpcustomize_hide_register_forgot_links" value="1"<?php
					if(
						(
							isset( $_POST['wpcustomize_hide_register_forgot_links'] )
							&& $_POST['wpcustomize_hide_register_forgot_links'] == "1"
						) || (
							html_entity_decode(get_option('wpcustomize_hide_register_forgot_links')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Hide the Back to Blog link?", 'wp-customize-menu'); ?> </th>
				<td>
				<input type="checkbox" id="wpcustomize_hide_back_link" name="wpcustomize_hide_back_link" value="1"<?php
					if(
						(
							isset( $_POST['wpcustomize_hide_back_link'] )
							&& $_POST['wpcustomize_hide_back_link'] == "1"
						) || (
							html_entity_decode(get_option('wpcustomize_hide_back_link')) == "1"
						)
					) {
						echo ' checked="checked"';
					}
				?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Check remember me by default?", 'wp-customize-menu'); ?> </th>
				<td>
					<input type="checkbox" id="wpcustomize_remember_me_by_default" name="wpcustomize_remember_me_by_default" value="1"<?php
						if(
							(
								isset( $_POST['wpcustomize_remember_me_by_default'] )
								&& $_POST['wpcustomize_remember_me_by_default'] == "1"
							) || (
								html_entity_decode(get_option('wpcustomize_remember_me_by_default')) == "1"
							)
						) {
							echo ' checked="checked"';
						}
					?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Remove the login form shake?", 'wp-customize-menu'); ?> </th>
				<td>
					<input type="checkbox" id="wpcustomize_remove_login_shake" name="wpcustomize_remove_login_shake" value="1"<?php
						if(
							(
								isset( $_POST['wpcustomize_remove_login_shake'] )
								&& $_POST['wpcustomize_remove_login_shake'] == "1"
							) || (
								html_entity_decode(get_option('wpcustomize_remove_login_shake')) == "1"
							)
						) {
							echo ' checked="checked"';
						}
					?>>
				</td>
			</tr>
			<tr valign="top">
				<th scope="row"><?php _e("Custom Login Page CSS:", 'wp-customize-menu'); ?> </th>
				<td>
					<textarea cols="42" rows="8" name="wpcustomize_admin_loginstyles"><?php
						echo html_entity_decode(get_option('wpcustomize_admin_loginstyles',htmlentities(get_option('wpcustomize_admin_loginstyles'))));
					?></textarea>
				</td>
			</tr>
		</table>
		<hr />
		<h3>WordPress Admin Footer</h3>
		<table class="form-table">
			<tr valign="top">
				<th scope="row"><?php _e("Admin Footer HTML:", 'wp-customize-menu'); ?> </th>
				<td>
					<textarea cols="42" rows="8" name="wpcustomize_admin_footer_contents"><?php
						echo html_entity_decode(get_option('wpcustomize_admin_footer_contents',htmlentities(get_option('wpcustomize_admin_footer_contents'))));
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
 * Add custom CSS to the admin document head
 */
function wpcustomize_admin_styles() {
	echo '<style type="text/css">';

	echo 'form[name="wpcustomize_customize"] input[type="text"],
	form[name="wpcustomize_customize"] textarea {
		width: 100%;
		padding: 6px 8px;
	}
	form[name="wpcustomize_customize"] input.smallinput[type="text"] {
		width: 50px;
	}';

	echo '</style>';
}
add_action('admin_head', 'wpcustomize_admin_styles');

/**
 * Add a custom logo to the WordPress Admin login page header
 */
function wpcustomize_custom_login_logo() {
	echo '<style type="text/css">';
	if( get_option('wpcustomize_admin_logo') ) {
		echo '#login h1 a {
			background-image:url(' . html_entity_decode(get_option('wpcustomize_admin_logo')) . ') !important;
			background-size: ' . html_entity_decode(get_option('wpcustomize_admin_logo_width')) . 'px ' . html_entity_decode(get_option('wpcustomize_admin_logo_height')) . 'px !important;
			height: ' . html_entity_decode(get_option('wpcustomize_admin_logo_area_height')) . 'px !important;
			width: ' . html_entity_decode(get_option('wpcustomize_admin_logo_area_width')) . 'px !important;
		}';
	}
	if( get_option('wpcustomize_admin_bgcolor', '#000') ) {
		echo 'body { background-color:' . html_entity_decode(get_option('wpcustomize_admin_bgcolor', '#000')) . ' !important; }';
	}
	if( get_option('wpcustomize_admin_linkcolor', '#fff') ) {
		echo '#login #nav a, #login #backtoblog a { color:' . html_entity_decode(get_option('wpcustomize_admin_linkcolor', '#fff')) . ' !important; text-shadow: none !important; }';
	}
	if( get_option('wpcustomize_admin_linkhovercolor', '#cfcfcf') ) {
		echo '#login #nav a:hover, #login #backtoblog a:hover { color: ' . html_entity_decode(get_option('wpcustomize_admin_linkhovercolor', '#cfcfcf')) . ' !important; text-shadow: none !important; }';
	}
	if( get_option('wpcustomize_admin_login_background_url') ) {
		echo 'body {
			background-image: url(' . html_entity_decode(get_option('wpcustomize_admin_login_background_url')) . ') !important;
			background-repeat: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_repeat')) . ';
			background-position: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_position')) . ';
			background-attachment: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_attachment')) . ';
			background-size: ' . html_entity_decode(get_option('wpcustomize_admin_login_background_size')) . ';
		}';
	}
	echo '</style>';
}
add_action('login_head', 'wpcustomize_custom_login_logo');

/**
 * Add custom custom CSS styles to the Wordpress Admin login page header
 */
if( get_option('wpcustomize_admin_loginstyles') ) {
	function wpcustomize_custom_login_styles() {
		?><style type="text/css">
			<?php echo get_option('wpcustomize_admin_loginstyles'); ?>
		</style><?php
	}
	add_action('login_head', 'wpcustomize_custom_login_styles');
}

/**
 * Hide the register and forgot password links from the login form
 */
function wpcustomize_hide_register_forgot_links() {
	if( get_option('wpcustomize_hide_register_forgot_links') ) {
		echo '<style type="text/css">
			p#nav {
				display: none;
			}
		</style>';
	}
}
add_action('login_head', 'wpcustomize_hide_register_forgot_links');

/**
 * Hide the Back to Blog link from the login form
 */
function wpcustomize_hide_back_link() {
	if( get_option('wpcustomize_hide_back_link') ) {
		echo '<style type="text/css">
			p#backtoblog {
				display: none;
			}
		</style>';
	}
}
add_action('login_head', 'wpcustomize_hide_back_link');

/**
 * Check the "Remember me" checkbox by default
 */
if( get_option('wpcustomize_remember_me_by_default') ) {
	function wpcustomize_login_checked_remember_me() {
		add_filter( 'login_footer', 'rememberme_checked' );
	}
	add_action( 'init', 'wpcustomize_login_checked_remember_me' );

	function rememberme_checked() {
		echo "<script>document.getElementById('rememberme').checked = true;</script>";
	}
}

/**
 * Remove the login shake from the login form
 */
if( get_option('wpcustomize_remove_login_shake') ) {
	function wpcustomize_remove_login_shake() {
		remove_action('login_head', 'wp_shake_js', 12);
	}
	add_action('login_head', 'wpcustomize_remove_login_shake');
}

/**
 * Change default error message
 */
function wpcustomize_custom_error_message() {
	return ( get_option('wpcustomize_custom_error_message') ? html_entity_decode(get_option('wpcustomize_custom_error_message')) : 'Incorrect login details. Please try again.' );
}
add_filter('login_errors', 'wpcustomize_custom_error_message');

/**
 * Filter the URL of the header logo on the WordPress login page
 */
function wpcustomize_custom_login_url() {
	if( get_option('wpcustomize_admin_logo_link_url') ) {
		return get_option('wpcustomize_admin_logo_link_url');
	} else {
		return site_url();
	}
}
add_filter('login_headerurl', 'wpcustomize_custom_login_url');

/**
 * Filter the title attribute of the header logo on the WordPress login page
 */
function wpcustomize_login_header_title() {
	if( get_option('wpcustomize_admin_logo_title') ) {
		return get_option('wpcustomize_admin_logo_title');
	} else {
		return get_bloginfo('name');
	}
}
add_filter('login_headertitle', 'wpcustomize_login_header_title');

/**
 * Set a new footer in the WordPress Admin
 */
function wpcustomize_remove_footer_admin () {
	$wpcustomize_footer_default_value = 'Thank you for creating with <a href="http://wordpress.org/">WordPress</a>.';
	if(get_option('wpcustomize_admin_footer_contents') == "") {
		echo $wpcustomize_footer_default_value;
	} else {
		echo html_entity_decode(get_option('wpcustomize_admin_footer_contents', htmlentities($wpcustomize_footer_default_value)));
	}
}
add_filter('admin_footer_text', 'wpcustomize_remove_footer_admin');

/**
 * Redirect user after successful login
 */
function wpcustomize_login_redirect( $redirect_to, $request, $user ) {
	//is there a user to check?
	global $user;
	if ( isset( $user->roles ) && is_array( $user->roles ) ) {
		if( get_option('wpcustomize_admin_login_redirect') == "1" ) {
			if( get_option('wpcustomize_admin_login_redirect_url') ) {
				return get_option('wpcustomize_admin_login_redirect_url');
			} else {
				return site_url();
			}
		}
	} else {
		return $redirect_to;
	}
}
add_filter( 'login_redirect', 'wpcustomize_login_redirect', 10, 3 );

/**
 * Redirect visits to wp-login.php to our custom login page template
 */
function wpcustomize_login(){
	global $pagenow;
	if ( ( 'wp-login.php' == $pagenow ) && $_SERVER['REQUEST_METHOD'] != 'POST' && ( !is_user_logged_in() ) ) {
		wp_redirect('/login/');
		exit();
	} elseif( ( 'wp-login.php' == $pagenow ) && $_SERVER['REQUEST_METHOD'] == 'POST' && ( !is_user_logged_in() ) ) {
		// wp_redirect('/login/');
		// exit();
	}
}
add_action('init','wpcustomize_login');

/**
 * Set a custom WordPress Admin login page header title
 */
// add_filter('login_headertitle', create_function(false,"return 'URL Title';"));

/**
 * Empty login credentials
 */
function wpcustomize_verify_username_password( $user, $username, $password ) {
	$login_page  = home_url( '/login/' );
	if( $username == "" || $password == "" ) {
		wp_redirect( $login_page . "?login_error" );
		exit;
	}
}
add_filter( 'authenticate', 'wpcustomize_verify_username_password', 1, 3);

/**
 * Incorrect login credentials
 */
function wpcustomize_login_failed( $username ) {
	//redirect to custom login page and append login error flag
	$login_page  = home_url( '/login/' );
	wp_redirect( $login_page . '?login_error' );
	exit;
}
add_action( 'wp_login_failed', 'wpcustomize_login_failed' );

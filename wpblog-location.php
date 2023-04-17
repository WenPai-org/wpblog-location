<?php
/*
* Plugin Name: WPblog Location
* Plugin URI: https://wpblog.cn/download
* Description: Display user account IP address attribution information in comments and articles.
* Author: WPfanyi
* Author URI: https://wpfanyi.com
* Text Domain: wpblog-location
* Domain Path: /languages
* Version: 1.0
* License: GPLv2 or later
* License URI: http://www.gnu.org/licenses/gpl-2.0.html
*
* WP blog Location is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 2 of the License, or
* any later version.
*
* WP blog Location is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*/


if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}


// Load required files
require_once plugin_dir_path( __FILE__ ) . 'includes/Reader.php';
require_once plugin_dir_path( __FILE__ ) . 'includes/functions.php';

// Enqueue plugin CSS and dashicons CSS
add_action( 'wp_enqueue_scripts', 'wpblog_location_enqueue_css' );
function wpblog_location_enqueue_css() {
    wp_enqueue_style( 'wpblog_location_css', plugin_dir_url( __FILE__ ) . 'assets/css/location.css' );
    wp_enqueue_style( 'dashicons' );
}


// Load translation
add_action( 'init', 'wpblog_load_textdomain' );
function wpblog_load_textdomain() {
	load_plugin_textdomain( 'wpblog-location', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
}


// Add settings page to WordPress admin menu
add_action( 'admin_menu', 'wpblog_location_add_settings_page' );
function wpblog_location_add_settings_page() {
    add_options_page(
        __( 'IP Location Settings', 'wpblog-location' ), // Page title
        __( 'IP Location', 'wpblog-location' ), // Menu name
        'manage_options', // User capability
        'wpblog-location', // Page ID
        'wpblog_location_settings_page' // Callback function
    );
    // Add new settings field to control author location display
    add_settings_field(
        'wpblog_location_show_author_location', // Field ID
        __( 'Show author location on post pages', 'wpblog-location' ), // Field title
        'wpblog_location_show_author_location_callback', // Callback function
        'wpblog_location_settings', // Settings page ID
        'wpblog_location_section' // Settings page section ID
    );
}

// Callback function to display the "Show author location" settings field
function wpblog_location_show_author_location_callback() {
    $show_author_location = get_option( 'wpblog_location_show_author_location', false );
    echo '<input type="checkbox" name="wpblog_location_show_author_location" value="1" ' . checked( 1, $show_author_location, false ) . ' />';
}


// Callback function to display the settings page HTML
function wpblog_location_settings_page() {
    // Check user permissions
    if ( ! current_user_can( 'manage_options' ) ) {
        wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpblog-location' ) );
    }

    // Handle form submission
    if ( isset( $_POST['wpblog_location_save_settings'] ) ) {
        $show_post_location = isset($_POST['show_post_location']) ? true : false;
        $show_comment_location = isset($_POST['show_comment_location']) ? true : false;
        $show_author_location = isset($_POST['wpblog_location_show_author_location']) ? true : false;
        update_option('wpblog_location_show_author_location', $show_author_location);
        update_option('wpblog_location_show_post_location', $show_post_location);
        update_option('wpblog_location_show_comment_location', $show_comment_location);

        update_option('wpblog_location_display_info', $_POST['wpblog_location_display_info']);
        // Display success message
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Settings saved.</strong></p></div>';
    }

    // Get current options
    $show_post_location = get_option( 'wpblog_location_show_post_location', false );
    $show_comment_location = get_option( 'wpblog_location_show_comment_location', true );

    // Render HTML
?>
<div class="wrap">
    <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
    <h2><?php esc_html_e( 'WordPress Blog User IP address attribution', 'wpblog-location' ); ?></h2>
    <p><?php esc_html_e( '1. Display WordPress user IP address attribution and city location information, More information at', 'wpblog-location' ); ?> <a href="https://wpblog.cn" target="_blank" rel="noopener">WPblog.cn</a></p>
    <p><?php esc_html_e( '2. You can display the author or publisher location anywhere on your website. The shortcode is', 'wpblog-location' ); ?> <code>[wpblog_location]</code> <code>[wpblog_author_location]</code> </p>
    <form method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Post/pages', 'wpblog-location' ); ?></th>
                    <td><label><input type="checkbox" name="show_post_location" value="1" <?php checked( $show_post_location, true ); ?>> <?php esc_html_e( 'Show location', 'wpblog-location' ); ?></label></td>
                </tr>
                <tr>
                    <th scope="row"><?php esc_html_e( 'Comments', 'wpblog-location' ); ?></th>
                    <td><label><input type="checkbox" name="show_comment_location" value="1" <?php checked( $show_comment_location, true ); ?>> <?php esc_html_e( 'Show location', 'wpblog-location' ); ?></label></td>
                </tr>
            </tbody>
        </table>
        <?php submit_button( __( 'Save Changes', 'wpblog-location' ), 'primary', 'wpblog_location_save_settings' ); ?>
    </form>
</div>
<?php
}

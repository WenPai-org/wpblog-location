<?php
/*
Plugin Name: WPblog Location
Plugin URI: https://wpblog.cn/location
Description: Display user account IP address information in comments and articles.
Version: 1.0
Author: WPblog.cn
Author URI: https://wpblog.cn
License: GPL2
*/


// 防止直接访问插件文件
if (!defined('ABSPATH')) exit;

// 加载所需文件
require_once plugin_dir_path(__FILE__) . 'includes/Reader.php';
require_once plugin_dir_path(__FILE__) . 'includes/functions.php';

// 加载插件 CSS 和 dashicons CSS
add_action('wp_enqueue_scripts', 'wpsite_location_enqueue_css');
function wpsite_location_enqueue_css() {
    wp_enqueue_style('wpsite_location_css', plugin_dir_url(__FILE__) . 'assets/css/location.css');
    wp_enqueue_style('dashicons');
}

// 添加设置页面到 WordPress 后台菜单
add_action('admin_menu', 'wpsite_location_add_settings_page');
function wpsite_location_add_settings_page() {
    add_options_page(
        'WPSite Location Settings', // 页面标题
        'Location', // 菜单名称
        'manage_options', // 用户权限
        'wpsite-location', // 页面 ID
        'wpsite_location_settings_page' // 回调函数
    );
    // 添加新的设置字段以控制作者位置的显示
    add_settings_field(
        'wpsite_location_show_author_location', // 字段 ID
        'Show author location on post pages', // 字段标题
        'wpsite_location_show_author_location_callback', // 回调函数
        'wpsite_location_settings', // 所属设置页面 ID
        'wpsite_location_section' // 所属设置页面章节 ID
    );
}

// 回调函数，显示“显示作者位置”设置字段
function wpsite_location_show_author_location_callback() {
    $show_author_location = get_option('wpsite_location_show_author_location', false);
    echo '<input type="checkbox" name="wpsite_location_show_author_location" value="1" ' . checked(1, $show_author_location, false) . ' />';
}

// 回调函数，显示设置页面 HTML
function wpsite_location_settings_page() {
    if (!current_user_can('manage_options')) {
        wp_die(__('You do not have sufficient permissions to access this page.'));
    }

    // 处理表单提交
    if (isset($_POST['wpsite_location_save_settings'])) {
        $show_post_location = isset($_POST['show_post_location']) ? true : false;
        $show_comment_location = isset($_POST['show_comment_location']) ? true : false;
        $show_author_location = isset($_POST['wpsite_location_show_author_location']) ? true : false;
        update_option('wpsite_location_show_author_location', $show_author_location);
        update_option('wpsite_location_show_post_location', $show_post_location);
        update_option('wpsite_location_show_comment_location', $show_comment_location);
        // 添加这一行以保存“显示位置信息”选项
        update_option('wpsite_location_display_info', $_POST['wpsite_location_display_info']);
        // 显示保存成功提示
        echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"><p><strong>Settings saved.</strong></p></div>';
    }

    // 获取当前插件设置选项
    $show_post_location = get_option('wpsite_location_show_post_location', false);
    $show_comment_location = get_option('wpsite_location_show_comment_location', false);

// 渲染设置页面 HTML
?>
<div class="wrap">
    <h1>Location Settings</h1>
    <form method="post" action="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row">Show location on post pages</th>
                    <td><label><input type="checkbox" name="show_post_location" value="1" <?php if ($show_post_location) echo 'checked'; ?>> Show location</label></td>
                </tr>
                <tr>
                    <th scope="row">Show location in comments</th>
                    <td><label><input type="checkbox" name="show_comment_location" value="1" <?php if ($show_comment_location) echo 'checked'; ?>> Show location</label></td>
                </tr>
            </tbody>
        </table>
        <p class="submit"><input type="submit" name="wpsite_location_save_settings" class="button-primary" value="Save Changes"></p>
    </form>
</div>
    <?php
}

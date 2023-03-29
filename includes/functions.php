<?php


function get_user_city($ip)
{
  $reader = new Reader(__DIR__ . '/ipipfree.ipdb');
  try {
    return $reader->find($ip) ? $reader->find($ip)[1] : false;
  } catch (\Throwable $th) {
    return false;
  }
}

if (!function_exists('wpsite_location_handle_comment')) {
    function wpsite_location_handle_comment($comment_text) {
        $comment_ID = get_comment_ID();
        $comment = get_comment($comment_ID);
        $show_comment_location = get_option('wpsite_location_show_comment_location', false);

        if ($show_comment_location && $comment->comment_author_IP && get_user_city($comment->comment_author_IP)) {
            $comment_text .= '<div class="post-comment-location"><span class="dashicons dashicons-location"></span>来自' . get_user_city($comment->comment_author_IP) . '</div>';
        }

        return $comment_text;
    }
}
add_filter('comment_text', 'wpsite_location_handle_comment');

if (!function_exists('wpsite_location_handle_edit_post')) {
    function wpsite_location_handle_edit_post($post_id) {
        $onlineip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        if (!$onlineip) return;
        update_post_meta($post_id, 'wpsite_location_ip', $onlineip);
    }
}

if (!function_exists('wpsite_location_handle_post_content')) {
    function wpsite_location_handle_post_content($content) {
        global $post;
        $show_author_location = get_option('wpsite_location_show_author_location', false);

        if ($show_author_location && get_post_meta($post->ID, 'wpsite_location_ip', true)) {
            $location_info = '<div class="post-author-location"><span class="dashicons dashicons-location"></span>作者来自' . get_user_city(get_post_meta($post->ID, 'wpsite_location_ip', true)) . '</div>';
            $content = $location_info . $content;
        }

        return $content;
    }
}

function wpsite_location_handle_post_content_end($content) {
    global $post;
    $location_info = '';
    $show_post_location = get_option('wpsite_location_show_post_location', false);

    if (get_option('wpsite_location_show', true) && get_post_meta($post->ID, 'wpsite_location_ip', true) && $show_post_location) {
        $location_info = '<div class="post-author-location-end"><span class="dashicons dashicons-location"></span>作者来自' . get_user_city(get_post_meta($post->ID, 'wpsite_location_ip', true)) . '</div>';
    }

    return $content . $location_info;
}

add_filter('the_content', 'wpsite_location_handle_post_content');
add_filter('the_content', 'wpsite_location_handle_post_content_end');


// 添加显示作者地理位置简码
function wpsite_location_shortcode($atts) {
    $a = shortcode_atts( array(
        'ip' => ''
    ), $atts );
    
    $ip = $a['ip'] ? $a['ip'] : filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    $city = get_user_city($ip);
    if ($city) {
        return '<div class="post-comment-location"><span class="dashicons dashicons-location"></span>来自' . $city . '</div>';
    } else {
        return '';
    }
}
add_shortcode( 'wpsite_location', 'wpsite_location_shortcode' );



// 添加显示作者地理位置简码
function wpsite_author_location_shortcode() {
    $ip = get_post_meta(get_the_ID(), 'wpsite_location_ip', true);
    $city = get_user_city($ip);
    
    if ($city) {
        return '<div class="post-author-location"><span class="dashicons dashicons-location"></span>作者来自' . $city . '</div>';
    } else {
        return '';
    }
}
add_shortcode( 'wpsite_author_location', 'wpsite_author_location_shortcode' );

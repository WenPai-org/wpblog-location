<?php

// Function to get user city based on IP address
function get_user_city($ip)
{
  $reader = new Reader(__DIR__ . '/ipipfree.ipdb');
  try {
    return $reader->find($ip) ? $reader->find($ip)[1] : false;
  } catch (\Throwable $th) {
    return false;
  }
}


// Function to handle comment display
if (!function_exists('wpblog_location_handle_comment')) {
    function wpblog_location_handle_comment($comment_text) {
        $comment_ID = get_comment_ID();
        $comment = get_comment($comment_ID);
        $show_comment_location = get_option('wpblog_location_show_comment_location', false);

        if ($show_comment_location && $comment->comment_author_IP && get_user_city($comment->comment_author_IP)) {
            $comment_text .= '<div class="post-comment-location"><span class="dashicons dashicons-location"></span>' . esc_html__( 'From', 'wpblog-location' ) . '' . get_user_city($comment->comment_author_IP) . '</div>';
        }

        return $comment_text;
    }
}
add_filter('comment_text', 'wpblog_location_handle_comment');


// Function to handle post editing
if (!function_exists('wpblog_location_handle_edit_post')) {
    function wpblog_location_handle_edit_post($post_id) {
        $onlineip = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
        if (!$onlineip) return;
        update_post_meta($post_id, 'wpblog_location_ip', $onlineip);
    }
}

add_action('save_post', 'wpblog_location_handle_edit_post');


// Function to handle adding author location information to post content
if (!function_exists('wpblog_location_handle_post_content')) {
    function wpblog_location_handle_post_content($content) {
        global $post;
        $show_author_location = get_option('wpblog_location_show_author_location', false);

        if ($show_author_location && get_post_meta($post->ID, 'wpblog_location_ip', true)) {
            $location_info = '<div class="post-author-location"><span class="dashicons dashicons-location"></span>' . __('Author from', 'wpblog_location') . '' . get_user_city(get_post_meta($post->ID, 'wpblog_location_ip', true)) . '</div>';
            $content = $location_info . $content;
        }

        return $content;
    }
}


// Function to handle adding post location information to post content
function wpblog_location_handle_post_content_end($content) {
    global $post;
    $location_info = '';
    $show_post_location = get_option('wpblog_location_show_post_location', false);

    if (get_option('wpblog_location_show', true) && get_post_meta($post->ID, 'wpblog_location_ip', true) && $show_post_location) {
       $location_info = '<div class="post-author-location"><span class="dashicons dashicons-location"></span>' . __('Author from', 'wpblog_location') . '' . get_user_city(get_post_meta($post->ID, 'wpblog_location_ip', true)) . '</div>';
    }

    return $content . $location_info;
}

add_filter('the_content', 'wpblog_location_handle_post_content');
add_filter('the_content', 'wpblog_location_handle_post_content_end');


// Add a shortcode to show the author location
function wpblog_location_shortcode($atts) {
    $a = shortcode_atts( array(
        'ip' => ''
    ), $atts );

    $ip = $a['ip'] ? $a['ip'] : filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP);
    $city = get_user_city($ip);
    if ($city) {
        return '<div class="post-comment-location"><span class="dashicons dashicons-location"></span>' . esc_html__( 'From', 'wpblog_location' ) . '' . $city . '</div>';

    } else {
        return '';
    }
}
add_shortcode( 'wpblog_location', 'wpblog_location_shortcode' );


// Add a shortcode to show the post author location
function wpblog_author_location_shortcode() {
    $ip = get_post_meta(get_the_ID(), 'wpblog_location_ip', true);
    $city = get_user_city($ip);

    if ($city) {
        return '<div class="post-author-location"><span class="dashicons dashicons-location"></span>' . esc_html__( 'Author From', 'wpblog_location' ) . '' . $city . '</div>';
    } else {
        return '';
    }
}
add_shortcode( 'wpblog_author_location', 'wpblog_author_location_shortcode' );

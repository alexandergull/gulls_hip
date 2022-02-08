<?php
/*
Plugin Name: Gull's Hidden IP field (HIP)
Plugin URI: https://github.com/alexandergull/gulls_hip
Description: This plugin compare frontend user IP address with backend one.
Version: 1.0
Author: Alexander Gull
Author URI: @alexandergull
*/

/*  Copyright 2022  Alexander Gull  (email: hi@gullvps.ru)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

/**
 * Better way to use your own salt on each WP instance.
 */
$hip_salt = 'a28n3y7fb304j23455';


/**
 * Main logic. Hooked before comment is processed.
 * Calls wp_die if md5 results inner/outer IP addresses are not equal.
 * Else proceeds to post_comment
 * @return array
 */
function check_sender_ip($comment_data) {

    global $hip_salt;

    $comment_data['hidden_ip_md5'] = isset($_POST['hiphash']) ? $_POST['hiphash'] : null;
    $salted_php_ip = md5($hip_salt . $comment_data['comment_author_IP']);

    if ( $salted_php_ip !== $comment_data['hidden_ip_md5'] ){

        $die_cry = '<pre>Комментарий отклонён';

        if ($comment_data['hidden_ip_md5'] == '') {
            $die_cry .= '. Проверьте, включен ли JavaScript.<pre>';
        } else
            $die_cry .= '. Адрес поддельный.<pre>';

        wp_die($die_cry);
    }

    return $comment_data;
}

/**
 * Adds a new hidden form to a native WP comment form.
 * @return array of comment form fields HTML
 */
function add_hidden_ip_field_to_comment_form($fields) {

    $fields['hiphash'] = '<input id="hiphash" name="hiphash" type="hidden" value="">';
    return $fields;
}

/**
 * Inline script to listen DOM loading flag.
 */
function set_hidden_ip_js(){

    global $hip_salt;

    $salted_php_ip = md5($hip_salt . $_SERVER['REMOTE_ADDR']);
    $script = "document.addEventListener(\"DOMContentLoaded\", listen_hip('$salted_php_ip'));";
    wp_add_inline_script('hidden-ip', $script);

}

/**
 * Own directory script to set salted frontend IP.
 */
function add_hip_scripts(){
    $url = plugin_dir_url(__FILE__) . "/js/set_hip.js";
    wp_enqueue_script ('hidden-ip', $url, false, null, true);
}

add_action( 'wp_enqueue_scripts', 'add_hip_scripts' );
add_action( 'wp_footer', 'set_hidden_ip_js' );
add_filter( 'comment_form_default_fields', 'add_hidden_ip_field_to_comment_form', 25 );
add_filter( 'preprocess_comment', 'check_sender_ip', 1, 1 );






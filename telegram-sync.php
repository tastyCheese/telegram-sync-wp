<?php

/*
* Plugin Name: Telegram Sync
*/

require 'settings_page.php';

$jal_db_version = "1.0";

function jal_install() {
    global $wpdb;
    global $jal_db_version;

    $table_name = $wpdb->prefix."telegram";

    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE $table_name (
		post_id mediumint(9) NOT NULL UNIQUE,
		telegram_post_id mediumint(9) NOT NULL UNIQUE,
		PRIMARY KEY  (post_id)
	) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php';
    dbDelta( $sql );

    add_option( 'jal_db_version', $jal_db_version );
}

function plain_text($text): array|string|null
{
    $new_text = strip_tags($text, '<br><p><li>');
    $new_text = preg_replace ('/<[^>]*>/', PHP_EOL, $new_text);

    return preg_replace('/\n{2,}/', PHP_EOL, $new_text);
}

function add_post($postID): void
{
    $options = get_option( 'telegram_sync_plugin_options' );
    $token = $options['api_key'];
    $chat_id = $options['channel_id'];

    global $wpdb;

    $table_name = $wpdb->prefix."telegram";

    $telegram_post_id = $wpdb->get_results("SELECT telegram_post_id FROM $table_name WHERE post_id = $postID");

    $title = get_post($postID)->post_title;
    $content = plain_text(get_post($postID)->post_content);

    $params = array(
        'chat_id' => $chat_id,
        'text' => "<b>$title</b>$content",
        'parse_mode' => "HTML"
    );

    if (count($telegram_post_id) == 0) {
        $link = "https://api.telegram.org/bot$token/sendMessage?";

        $telegram_post_id = json_decode(file_get_contents($link.http_build_query($params)))->result->message_id;
        $wpdb->insert($table_name, array('post_id' => $postID, 'telegram_post_id' => $telegram_post_id));
    } else {
        $link = "https://api.telegram.org/bot$token/editMessageText?";

        $params = array(
            'chat_id' => $chat_id,
            'message_id' => $telegram_post_id[0]->telegram_post_id,
            'text' => "<b>$title</b>$content",
            'parse_mode' => 'HTML'
        );

        file_get_contents($link.http_build_query($params));
    }
}

function remove_post($postID): void
{
    $options = get_option( 'telegram_sync_plugin_options' );
    $token = $options['api_key'];
    $chat_id = $options['channel_id'];

    global $wpdb;

    $table_name = $wpdb->prefix."telegram";

    $telegram_post_id = $wpdb->get_results("SELECT telegram_post_id FROM $table_name WHERE post_id = $postID");

    if (count($telegram_post_id) != 0) {
        $link = "https://api.telegram.org/bot$token/deleteMessage?";

        $params = array(
            'chat_id' => $chat_id,
            'message_id' => $telegram_post_id[0]->telegram_post_id
        );

        file_get_contents($link . http_build_query($params));
    }
}

register_activation_hook( __FILE__, 'jal_install' );
add_action('edit_post', 'add_post');
add_action('deleted_post', 'remove_post');

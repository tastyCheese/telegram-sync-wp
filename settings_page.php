<?php

function telegram_sync_add_settings_page() {
    add_options_page( 'Telegram sync options', 'Telegram Sync', 'manage_options', 'telegram-sync-plugin', 'telegram_sync_render_plugin_settings_page' );
}
add_action( 'admin_menu', 'telegram_sync_add_settings_page' );

function telegram_sync_render_plugin_settings_page() {
    ?>
    <h2>Example Plugin Settings</h2>
    <form action="options.php" method="post">
        <?php
        settings_fields( 'telegram_sync_plugin_options' );
        do_settings_sections( 'telegram_sync_plugin' ); ?>
        <input name="submit" class="button button-primary" type="submit" value="<?php esc_attr_e( 'Save' ); ?>" />
    </form>
    <?php
}

function telegram_sync_register_settings() {
    register_setting( 'telegram_sync_plugin_options', 'telegram_sync_plugin_options');
    add_settings_section( 'api_settings', 'API Settings', 'telegram_sync_plugin_section_text', 'telegram_sync_plugin' );

    add_settings_field( 'telegram_sync_plugin_setting_api_key', 'API Key', 'telegram_sync_plugin_setting_api_key', 'telegram_sync_plugin', 'api_settings' );
    add_settings_field( 'telegram_sync_plugin_setting_channel_id', 'Channel ID', 'telegram_sync_plugin_setting_channel_id', 'telegram_sync_plugin', 'api_settings' );
}
add_action( 'admin_init', 'telegram_sync_register_settings' );

function telegram_sync_plugin_section_text() {
    echo '<p>Here you can set all the options for using the API</p>';
}

function telegram_sync_plugin_setting_api_key() {
    $options = get_option( 'telegram_sync_plugin_options' );
    echo "<input id='telegram_sync_plugin_setting_api_key' name='telegram_sync_plugin_options[api_key]' type='text' value='" . esc_attr( $options['api_key'] ) . "' />";
}

function telegram_sync_plugin_setting_channel_id() {
    $options = get_option( 'telegram_sync_plugin_options' );
    echo "<input id='telegram_sync_plugin_setting_channel_id' name='telegram_sync_plugin_options[channel_id]' type='text' value='" . esc_attr( $options['channel_id'] ) . "' />";
}

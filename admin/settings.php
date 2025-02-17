<?php
add_action('admin_menu', 'mng_kargo_add_settings_page');
function mng_kargo_add_settings_page() {
    add_menu_page(
        'MNG Kargo Ayarları',
        'MNG Kargo',
        'manage_options',
        'mng-kargo-settings',
        'mng_kargo_render_settings_page'
    );
}

function mng_kargo_render_settings_page() {
    ?>
    <div class="wrap">
        <h2>MNG Kargo API Ayarları</h2>
        <form method="post" action="options.php">
            <?php
            settings_fields('mng_kargo_options');
            do_settings_sections('mng-kargo-settings');
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

add_action('admin_init', 'mng_kargo_register_settings');
function mng_kargo_register_settings() {
    register_setting('mng_kargo_options', 'mng_client_id');
    register_setting('mng_kargo_options', 'mng_client_secret');

    add_settings_section(
        'mng_api_section',
        'API Kimlik Bilgileri',
        '',
        'mng-kargo-settings'
    );

    add_settings_field(
        'mng_client_id',
        'Client ID',
        'mng_client_id_callback',
        'mng-kargo-settings',
        'mng_api_section'
    );

    add_settings_field(
        'mng_client_secret',
        'Client Secret',
        'mng_client_secret_callback',
        'mng-kargo-settings',
        'mng_api_section'
    );
}

function mng_client_id_callback() {
    echo '<input name="mng_client_id" type="text" value="' . esc_attr(get_option('mng_client_id')) . '" class="regular-text">';
}

function mng_client_secret_callback() {
    echo '<input name="mng_client_secret" type="password" value="' . esc_attr(get_option('mng_client_secret')) . '" class="regular-text">';
}
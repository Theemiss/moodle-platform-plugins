<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a settings page for the plugin.
    $settings = new admin_settingpage('local_tekouin', get_string('pluginname', 'local_tekouin'));

    // Add API URL setting.
    $settings->add(new admin_setting_configtext(
        'local_tekouin/apiurl',
        get_string('apiurl', 'local_tekouin'),
        get_string('apiurl_desc', 'local_tekouin'),
        '',
        PARAM_URL
    ));

    // Add API Key setting.
    $settings->add(new admin_setting_configtext(
        'local_tekouin/apikey',
        get_string('apikey', 'local_tekouin'),
        get_string('apikey_desc', 'local_tekouin'),
        '',
        PARAM_ALPHANUM
    ));

    // Add Organization ID setting.
    $settings->add(new admin_setting_configtext(
        'local_tekouin/orgid',
        get_string('orgid', 'local_tekouin'),
        get_string('orgid_desc', 'local_tekouin'),
        '',
        PARAM_ALPHANUM
    ));

    // Add the settings page to the "localplugins" category.
    $ADMIN->add('localplugins', $settings);

    // Add a custom admin page for checking API status.
    $ADMIN->add('reports', new admin_externalpage(
        'local_tekouin_status',
        get_string('checkapistatus', 'local_tekouin'),
        new moodle_url('/local/tekouin/admin/index.php')
    ));
}
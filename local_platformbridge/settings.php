<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Create a settings page for the plugin.
    $settings = new admin_settingpage('local_platformbridge', get_string('pluginname', 'local_platformbridge'));

    // Add API URL setting.
    $settings->add(new admin_setting_configtext(
        'local_platformbridge/apiurl',
        get_string('apiurl', 'local_platformbridge'),
        get_string('apiurl_desc', 'local_platformbridge'),
        '',
        PARAM_URL
    ));

    // Add API Key setting.
    $settings->add(new admin_setting_configtext(
        'local_platformbridge/apikey',
        get_string('apikey', 'local_platformbridge'),
        get_string('apikey_desc', 'local_platformbridge'),
        '',
        PARAM_ALPHANUM
    ));

    // Add Organization ID setting.
    $settings->add(new admin_setting_configtext(
        'local_platformbridge/orgid',
        get_string('orgid', 'local_platformbridge'),
        get_string('orgid_desc', 'local_platformbridge'),
        '',
        PARAM_ALPHANUM
    ));

    // Add the settings page to the "localplugins" category.
    $ADMIN->add('localplugins', $settings);

    // Add a custom admin page for checking API status.
    $ADMIN->add('reports', new admin_externalpage(
        'local_platformbridge_status',
        get_string('checkapistatus', 'local_platformbridge'),
        new moodle_url('/local/platformbridge/admin/index.php')
    ));
}
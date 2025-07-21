<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_oneclickexport', get_string('pluginname', 'local_oneclickexport'));
    $ADMIN->add('localplugins', $settings);
    
    // Default export settings
    $settings->add(new admin_setting_heading(
        'local_oneclickexport/defaults',
        get_string('exportdefaults', 'local_oneclickexport'),
        ''
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includeusers',
        get_string('includeusers', 'backup'),
        get_string('includeusers_desc', 'local_oneclickexport'),
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includecomments',
        get_string('includecomments', 'backup'),
        get_string('includecomments_desc', 'local_oneclickexport'),
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includelogs',
        get_string('includelogs', 'backup'),
        get_string('includelogs_desc', 'local_oneclickexport'),
        0
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includeroleassignments',
        get_string('includeroleassignments', 'backup'),
        get_string('includeroleassignments_desc', 'local_oneclickexport'),
        0
    ));
    
    // Retention settings
    $settings->add(new admin_setting_heading(
        'local_oneclickexport/retention',
        get_string('retentionsettings', 'local_oneclickexport'),
        get_string('retentionsettings_desc', 'local_oneclickexport')
    ));
    
    $settings->add(new admin_setting_configduration(
        'local_oneclickexport/logretention',
        get_string('logretention', 'local_oneclickexport'),
        get_string('logretention_desc', 'local_oneclickexport'),
        30 * DAYSECS
    ));
    
    // UI settings
    $settings->add(new admin_setting_heading(
        'local_oneclickexport/ui',
        get_string('uisettings', 'local_oneclickexport'),
        ''
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/showondashboard',
        get_string('showondashboard', 'local_oneclickexport'),
        get_string('showondashboard_desc', 'local_oneclickexport'),
        1
    ));
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/showinnavigation',
        get_string('showinnavigation', 'local_oneclickexport'),
        get_string('showinnavigation_desc', 'local_oneclickexport'),
        1
    ));
}
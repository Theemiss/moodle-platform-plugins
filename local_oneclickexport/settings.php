<?php

/**
 * Plugin settings for the OneClickExport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>

 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_oneclickexport', get_string('pluginname', 'local_oneclickexport'));
    $ADMIN->add('localplugins', $settings);

    $settings->add(new admin_setting_heading(
        'local_oneclickexport/defaults',
        get_string('exportdefaults', 'local_oneclickexport'),
        get_string('exportdefaults_desc', 'local_oneclickexport')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includeusers',
        get_string('includeusers', 'local_oneclickexport'),
        get_string('includeusers_desc', 'local_oneclickexport'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includecomments',
        get_string('includecomments', 'local_oneclickexport'),
        get_string('includecomments_desc', 'local_oneclickexport'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includelogs',
        get_string('includelogs', 'local_oneclickexport'),
        get_string('includelogs_desc', 'local_oneclickexport'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includeroleassignments',
        get_string('includeroleassignments', 'local_oneclickexport'),
        get_string('includeroleassignments_desc', 'local_oneclickexport'),
        0
    ));
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includecalendarevents',
        get_string('includecalendarevents', 'local_oneclickexport'),
        get_string('includecalendarevents_desc', 'local_oneclickexport'),
        0
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/includeuserscompletion',
        get_string('includeuserscompletion', 'local_oneclickexport'),
        get_string('includeuserscompletion_desc', 'local_oneclickexport'),
        0
    ));
    $settings->add(new admin_setting_heading(
        'local_oneclickexport/retention',
        get_string('retentionsettings', 'local_oneclickexport'),
        get_string('retentionsettings_desc', 'local_oneclickexport')
    ));

    $settings->add(new admin_setting_configduration(
        'local_oneclickexport/logretention',
        get_string('logretention', 'local_oneclickexport'),
        get_string('logretention_desc', 'local_oneclickexport'),
        30 * DAYSECS,
        DAYSECS
    ));

    $settings->add(new admin_setting_heading(
        'local_oneclickexport/ui',
        get_string('uisettings', 'local_oneclickexport'),
        get_string('uisettings_desc', 'local_oneclickexport')
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/showondashboard',
        get_string('showondashboard', 'local_oneclickexport'),
        get_string('showondashboard_desc', 'local_oneclickexport'),
        1
    ));
    $settings->add(new admin_setting_configselect(
        'local_oneclickexport/courses_per_page',
        get_string('courses_per_page', 'local_oneclickexport'),
        get_string('courses_per_page_desc', 'local_oneclickexport'),
        50, // Default value
        [10 => '10', 20 => '20', 50 => '50', 100 => '100']
    ));

    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/showinnavigation',
        get_string('showinnavigation', 'local_oneclickexport'),
        get_string('showinnavigation_desc', 'local_oneclickexport'),
        1
    ));

    $ADMIN->add('reports', new admin_externalpage(
        'local_oneclickexport_report',
        get_string('exportreport', 'local_oneclickexport'),
        new moodle_url('/local/oneclickexport/admin_report.php'),
        'moodle/site:config'
    ));
}

<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Plugin settings for the OneClickExport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2023 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    // Main settings page
    $settings = new admin_settingpage('local_oneclickexport', get_string('pluginname', 'local_oneclickexport'));
    $ADMIN->add('localplugins', $settings);
    
    // Default export settings
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
        30 * DAYSECS,
        DAYSECS
    ));
    
    // UI settings
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
    
    $settings->add(new admin_setting_configcheckbox(
        'local_oneclickexport/showinnavigation',
        get_string('showinnavigation', 'local_oneclickexport'),
        get_string('showinnavigation_desc', 'local_oneclickexport'),
        1
    ));
    
    // Add report link to admin tree
    $ADMIN->add('reports', new admin_externalpage(
        'local_oneclickexport_report',
        get_string('exportreport', 'local_oneclickexport'),
        new moodle_url('/local/oneclickexport/admin_report.php'),
        'moodle/site:config'
    ));
    // Add these to your plugin settings to make settings configurable
$settings->add(new admin_setting_configcheckbox(
    'local_oneclickexport/include_calendarevents',
    get_string('includecalendarevents', 'local_oneclickexport'),
    get_string('includecalendarevents_desc', 'local_oneclickexport'),
    0
));

$settings->add(new admin_setting_configcheckbox(
    'local_oneclickexport/include_userscompletion',
    get_string('includeuserscompletion', 'local_oneclickexport'),
    get_string('includeuserscompletion_desc', 'local_oneclickexport'),
    0
));
}
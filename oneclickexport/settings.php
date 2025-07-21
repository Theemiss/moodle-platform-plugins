<?php
defined('MOODLE_INTERNAL') || die();

if ($hassiteconfig) {
    $settings = new admin_settingpage('local_oneclickexport', get_string('pluginname', 'local_oneclickexport'));
    $ADMIN->add('localplugins', $settings);
    
    // Add settings here if needed for future enhancements
}
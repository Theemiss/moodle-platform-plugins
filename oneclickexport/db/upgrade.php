<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_oneclickexport_upgrade($oldversion) {
    global $DB;
    
    $dbman = $DB->get_manager();
    
    if ($oldversion < 2023072300) {
        // Future upgrade tasks can go here
        upgrade_plugin_savepoint(true, 2023072300, 'local', 'oneclickexport');
    }
    
    return true;
}
<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_oneclickexport_install() {
    global $DB;
    
    // No need to manually assign capabilities here
    // Moodle will handle this automatically from access.php
    
    return true;
}
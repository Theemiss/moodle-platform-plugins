<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_oneclickexport_install() {
    global $CFG;
    
    // Set default capabilities for standard roles
    $roles = ['manager', 'editingteacher'];
    foreach ($roles as $role) {
        assign_capability(
            'local/oneclickexport:export',
            CAP_ALLOW,
            get_archetype_roles($role)->id,
            context_system::instance()->id
        );
    }
    
    return true;
}
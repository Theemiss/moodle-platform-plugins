<?php
defined('MOODLE_INTERNAL') || die();

function xmldb_local_oneclickexport_upgrade($oldversion)
{
    global $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2025072300) {
        # Add any upgrade logic here if needed
        # TODO: Add upgrade logic for  local_oneclickexport Bulk  Export & Asynchronous Export
    }

    return true;
}

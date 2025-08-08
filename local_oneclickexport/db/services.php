<?php

/**
 * Capability definitions for the oneclickexport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */



$functions = [
    'local_oneclickexport_export_course' => [
        'classname'   => 'local_oneclickexport\external\export_course',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Exports a course to MBZ Asynchronously',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_list_courses' => [
        'classname'   => 'local_oneclickexport\external\list_courses',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Lists visible courses for the current user',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/course:view',
    ],
    'local_oneclickexport_export_log' => [
        'classname'   => 'local_oneclickexport\external\export_log',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves the export log for the current user',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_bulk_export' => [
        'classname'   => 'local_oneclickexport\external\bulk_export',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Initiates a bulk export of selected courses',
        'type'        => 'write',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_get_export_status' => [
        'classname'   => 'local_oneclickexport\external\get_export_status',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves the status of a specific export',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_get_bulk_export_status' => [
        'classname'   => 'local_oneclickexport\external\get_bulk_export_status',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves the status of a bulk export',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_get_recent_exports' => [
        'classname'   => 'local_oneclickexport\external\get_recent_exports',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves recent exports for the current user',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_get_export_statistics' => [
        'classname'   => 'local_oneclickexport\external\get_export_statistics',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves export statistics for the current user',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
    'local_oneclickexport_get_export_downloads' => [
        'classname'   => 'local_oneclickexport\external\get_export_downloads',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Retrieves download URLs for exports',
        'type'        => 'read',
        'ajax'        => true,
        'capabilities' => 'moodle/backup:backupcourse',
    ],
];

$services = [
    'One Click Export API' => [
        'functions' => [
            'local_oneclickexport_export_course',
            'local_oneclickexport_list_courses',
            'local_oneclickexport_export_log',
            'local_oneclickexport_bulk_export',
            'local_oneclickexport_get_export_status',
            'local_oneclickexport_get_bulk_export_status',
            'local_oneclickexport_get_recent_exports',
            'local_oneclickexport_get_export_statistics',
            'local_oneclickexport_get_export_downloads',
        ],
        'shortname' => 'oneclickexport',  
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];

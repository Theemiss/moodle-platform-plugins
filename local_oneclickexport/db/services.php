<?php

/**
 * Capability definitions for the oneclickexport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@dardev.net>
 */
$functions = [
    'local_oneclickexport_export_course' => [
        'classname'   => 'local_oneclickexport\external\export_course',
        'methodname'  => 'execute',
        'classpath'   => '',
        'description' => 'Exports a course to MBZ',
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
];

$services = [
    'One Click Export API' => [
        'functions' => ['local_oneclickexport_export_course', 'local_oneclickexport_list_courses'],
        'restrictedusers' => 0,
        'enabled' => 1,
    ],
];

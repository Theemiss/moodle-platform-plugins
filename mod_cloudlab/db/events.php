<?php
defined('MOODLE_INTERNAL') || die();

$events = [
    [
        'eventname'   => '\mod_cloudlab\event\course_module_viewed',
        'includefile' => '/mod/cloudlab/event/observer/course_module_viewed.php',
        'internal'    => false,
        'capability'  => 'mod/cloudlab:view',
    ],
];

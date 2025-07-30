<?php
defined('MOODLE_INTERNAL') || die();

$events = [
    [
        'eventname'   => '\mod_tekouinlab\event\course_module_viewed',
        'includefile' => '/mod/tekouinlab/event/observer/course_module_viewed.php',
        'internal'    => false,
        'capability'  => 'mod/tekouinlab:view',
    ],
];

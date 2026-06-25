<?php

namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->libdir . '/externallib.php');

use external_function_parameters;
use external_multiple_structure;
use external_single_structure;
use external_value;
use external_api;
use context_system;


class list_courses extends external_api
{

    public static function execute_parameters()
    {
        return new external_function_parameters([]);
    }

    public static function execute()
    {
        global $USER;

        self::validate_context(context_system::instance());

        $courses = enrol_get_users_courses($USER->id, true, 'id,fullname,shortname,visible');
        $results = [];

        foreach ($courses as $course) {
            if (!$course->visible) {
                continue;
            }
            $results[] = [
                'id' => $course->id,
                'shortname' => $course->shortname,
                'fullname' => $course->fullname,
            ];
        }

        return $results;
    }

    public static function execute_returns()
    {
        return new external_multiple_structure(
            new external_single_structure([
                'id' => new external_value(PARAM_INT, 'Course ID'),
                'shortname' => new external_value(PARAM_TEXT, 'Course short name'),
                'fullname' => new external_value(PARAM_TEXT, 'Course full name'),
            ])
        );
    }
}

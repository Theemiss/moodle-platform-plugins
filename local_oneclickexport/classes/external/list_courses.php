<?php

namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');

/**
 * External API for listing courses. This class provides methods to retrieve
 * a list of courses based on search criteria and user permissions.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class list_courses extends \external_api
{
    public static function execute_parameters()
    {
        return new \external_function_parameters([
            'search' => new \external_value(PARAM_TEXT, 'Search term', VALUE_DEFAULT, ''),
            'limit' => new \external_value(PARAM_INT, 'Number of results to return', VALUE_DEFAULT, 20),
            'offset' => new \external_value(PARAM_INT, 'Result offset', VALUE_DEFAULT, 0)
        ]);
    }
    public static function execute($search = '', $limit = 20, $offset = 0)
    {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'search' => $search,
            'limit' => $limit,
            'offset' => $offset
        ]);

        $courses = \core_course_category::search_courses([
            'search' => $params['search'],
            'mycourses' => false,
            'limit' => $params['limit'],
            'offset' => $params['offset']
        ]);

        $result = [];
        foreach ($courses as $course) {
            $context = \context_course::instance($course->id);
            if (has_capability('local/oneclickexport:export', $context)) {
                $result[] = [
                    'id' => $course->id,
                    'fullname' => $course->fullname,
                    'shortname' => $course->shortname,
                    'visible' => $course->visible
                ];
            }
        }

        return $result;
    }

    public static function execute_returns()
    {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Course ID'),
                'fullname' => new \external_value(PARAM_TEXT, 'Course full name'),
                'shortname' => new \external_value(PARAM_TEXT, 'Course short name'),
                'visible' => new \external_value(PARAM_BOOL, 'Course visibility')
            ])
        );
    }
}

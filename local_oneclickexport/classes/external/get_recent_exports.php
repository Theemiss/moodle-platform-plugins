<?php

namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for retrieving recent export operations. This class allows users
 * to fetch their recent export logs within a specified time frame.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class get_recent_exports extends \external_api
{
    public static function execute_parameters()
    {
        return new \external_function_parameters([
            'limit' => new \external_value(PARAM_INT, 'Number of results to return', VALUE_DEFAULT, 5),
            'days' => new \external_value(PARAM_INT, 'Number of days to look back', VALUE_DEFAULT, 7)
        ]);
    }

    public static function execute($limit = 5, $days = 7)
    {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'limit' => $limit,
            'days' => $days
        ]);

        $since = time() - ($params['days'] * 24 * 60 * 60);
        $logs = \local_oneclickexport_logging::get_export_history(null, $USER->id, $params['limit'], 0, $since);

        $result = [];
        foreach ($logs as $log) {
            $result[] = [
                'id' => $log->id,
                'courseid' => $log->courseid,
                'courseshortname' => $log->courseshortname ?? '',
                'timecreated' => $log->timecreated,
                'status' => $log->status,
                'filesize' => $log->filesize ?? 0,
                'exporttype' => $log->exporttype
            ];
        }

        return $result;
    }

    public static function execute_returns()
    {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Log ID'),
                'courseid' => new \external_value(PARAM_INT, 'Course ID'),
                'courseshortname' => new \external_value(PARAM_TEXT, 'Course short name'),
                'timecreated' => new \external_value(PARAM_INT, 'Creation timestamp'),
                'status' => new \external_value(PARAM_TEXT, 'Export status'),
                'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
                'exporttype' => new \external_value(PARAM_TEXT, 'Export type (single or bulk)')
            ])
        );
    }
}

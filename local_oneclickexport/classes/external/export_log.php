<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for exporting logs of export operations. This class retrieves
 * the export logs for a user, allowing them to see their export history.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
class export_log extends \external_api {
public static function execute_parameters() {
    return new \external_function_parameters([
        'limit' => new \external_value(PARAM_INT, 'Number of results to return', VALUE_DEFAULT, 10),
        'offset' => new \external_value(PARAM_INT, 'Result offset', VALUE_DEFAULT, 0)
    ]);
}

    public static function execute($limit = 10, $offset = 0) {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'limit' => $limit,
            'offset' => $offset
        ]);

        $logs = \local_oneclickexport_logging::get_export_history(null, $USER->id, $params['limit'], $params['offset']);

        $result = [];
        foreach ($logs as $log) {
            $result[] = [
                'id' => $log->id,
                'courseid' => $log->courseid,
                'courseshortname' => $log->courseshortname ?? '',
                'timecreated' => $log->timecreated,
                'timemodified' => $log->timemodified,
                'status' => $log->status,
                'filesize' => $log->filesize ?? 0,
                'exporttype' => $log->exporttype
            ];
        }

        return $result;
    }

    public static function execute_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'id' => new \external_value(PARAM_INT, 'Log ID'),
                'courseid' => new \external_value(PARAM_INT, 'Course ID'),
                'courseshortname' => new \external_value(PARAM_TEXT, 'Course short name'),
                'timecreated' => new \external_value(PARAM_INT, 'Creation timestamp'),
                'timemodified' => new \external_value(PARAM_INT, 'Modification timestamp'),
                'status' => new \external_value(PARAM_TEXT, 'Export status'),
                'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
                'exporttype' => new \external_value(PARAM_TEXT, 'Export type (single or bulk)')
            ])
        );
    }
}
<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for exporting the status of export operations. This class retrieves
 * the status of a specific export operation based on its log ID.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */


class get_export_status extends \external_api {
    public static function execute_parameters() {
        return new \external_function_parameters([
            'logid' => new \external_value(PARAM_INT, 'Export log ID')
        ]);
    }

    public static function execute($logid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'logid' => $logid
        ]);

        $log = $DB->get_record('local_oneclickexport_log', ['id' => $params['logid']]);
        if (!$log || $log->userid != $USER->id) {
            throw new \moodle_exception('invalidexportlog', 'local_oneclickexport');
        }

        $result = [
            'id' => $log->id,
            'status' => $log->status,
            'timecreated' => $log->timecreated,
            'timemodified' => $log->timemodified,
            'filesize' => $log->filesize ?? 0,
            'fileid' => $log->fileid ?? 0,
            'exporttype' => $log->exporttype
        ];

        if ($log->exporttype == 'bulk') {
            $summary = \local_oneclickexport_logging::get_bulk_export_summary($log->id);
            $result['summary'] = [
                'total' => $summary->total_courses,
                'completed' => $summary->completed_courses,
                'failed' => $summary->error_courses,
                'pending' => $summary->pending_courses,
                'processing' => $summary->processing_courses
            ];
        }

        return $result;
    }

    public static function execute_returns() {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Log ID'),
            'status' => new \external_value(PARAM_TEXT, 'Export status'),
            'timecreated' => new \external_value(PARAM_INT, 'Creation timestamp'),
            'timemodified' => new \external_value(PARAM_INT, 'Modification timestamp'),
            'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
            'fileid' => new \external_value(PARAM_INT, 'File ID if completed'),
            'exporttype' => new \external_value(PARAM_TEXT, 'Export type (single or bulk)'),
            'summary' => new \external_single_structure([
                'total' => new \external_value(PARAM_INT, 'Total courses'),
                'completed' => new \external_value(PARAM_INT, 'Completed courses'),
                'failed' => new \external_value(PARAM_INT, 'Failed courses'),
                'pending' => new \external_value(PARAM_INT, 'Pending courses'),
                'processing' => new \external_value(PARAM_INT, 'Processing courses')
            ], 'Bulk export summary', VALUE_OPTIONAL)
        ]);
    }
}
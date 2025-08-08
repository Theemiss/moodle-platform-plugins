<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for bulk export status retrieval. This class provides methods
 * to check the status of a bulk export operation.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class get_bulk_export_status extends \external_api {
    public static function execute_parameters() {
        return new \external_function_parameters([
            'logid' => new \external_value(PARAM_INT, 'Bulk export log ID')
        ]);
    }

    public static function execute($logid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'logid' => $logid
        ]);

        $log = $DB->get_record('local_oneclickexport_log', ['id' => $params['logid'], 'exporttype' => 'bulk']);
        if (!$log || $log->userid != $USER->id) {
            throw new \moodle_exception('invalidexportlog', 'local_oneclickexport');
        }

        $summary = \local_oneclickexport_logging::get_bulk_export_summary($log->id);
        $details = \local_oneclickexport_logging::get_bulk_export_details($log->id);

        $course_details = [];
        foreach ($details as $detail) {
            $course_details[] = [
                'courseid' => $detail->courseid,
                'coursename' => $detail->coursefullname ?? $detail->coursename,
                'status' => $detail->status,
                'timecreated' => $detail->timecreated,
                'timemodified' => $detail->timemodified,
                'filesize' => $detail->filesize ?? 0,
                'error' => $detail->error ?? ''
            ];
        }

        return [
            'id' => $log->id,
            'status' => $log->status,
            'timecreated' => $log->timecreated,
            'timemodified' => $log->timemodified,
            'filesize' => $log->filesize ?? 0,
            'fileid' => $log->fileid ?? 0,
            'summary' => [
                'total' => $summary->total_courses,
                'completed' => $summary->completed_courses,
                'failed' => $summary->error_courses,
                'pending' => $summary->pending_courses,
                'processing' => $summary->processing_courses,
                'total_filesize' => $summary->total_filesize ?? 0
            ],
            'courses' => $course_details
        ];
    }

    public static function execute_returns() {
        return new \external_single_structure([
            'id' => new \external_value(PARAM_INT, 'Log ID'),
            'status' => new \external_value(PARAM_TEXT, 'Export status'),
            'timecreated' => new \external_value(PARAM_INT, 'Creation timestamp'),
            'timemodified' => new \external_value(PARAM_INT, 'Modification timestamp'),
            'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
            'fileid' => new \external_value(PARAM_INT, 'File ID if completed'),
            'summary' => new \external_single_structure([
                'total' => new \external_value(PARAM_INT, 'Total courses'),
                'completed' => new \external_value(PARAM_INT, 'Completed courses'),
                'failed' => new \external_value(PARAM_INT, 'Failed courses'),
                'pending' => new \external_value(PARAM_INT, 'Pending courses'),
                'processing' => new \external_value(PARAM_INT, 'Processing courses'),
                'total_filesize' => new \external_value(PARAM_INT, 'Total size of all files')
            ]),
            'courses' => new \external_multiple_structure(
                new \external_single_structure([
                    'courseid' => new \external_value(PARAM_INT, 'Course ID'),
                    'coursename' => new \external_value(PARAM_TEXT, 'Course name'),
                    'status' => new \external_value(PARAM_TEXT, 'Export status'),
                    'timecreated' => new \external_value(PARAM_INT, 'Creation timestamp'),
                    'timemodified' => new \external_value(PARAM_INT, 'Modification timestamp'),
                    'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
                    'error' => new \external_value(PARAM_TEXT, 'Error message if failed')
                ])
            )
        ]);
    }
}
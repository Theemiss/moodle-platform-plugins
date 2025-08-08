<?php

namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');

/**
 * External API for bulk export functionality. This class is responsible for
 * executing the bulk export process and returning the results.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
class bulk_export extends \external_api
{
    public static function execute_parameters()
    {
        return new \external_function_parameters([
            'courseids' => new \external_multiple_structure(
                new \external_value(PARAM_INT, 'Course ID'),
                'List of course IDs to export'
            ),
            'settings' => new \external_single_structure([
                'users' => new \external_value(PARAM_BOOL, 'Include users', VALUE_OPTIONAL, false),
                'comments' => new \external_value(PARAM_BOOL, 'Include comments', VALUE_OPTIONAL, false),
                'logs' => new \external_value(PARAM_BOOL, 'Include logs', VALUE_OPTIONAL, false),
                'calendarevents' => new \external_value(PARAM_BOOL, 'Include calendar events', VALUE_OPTIONAL, false),
                'userscompletion' => new \external_value(PARAM_BOOL, 'Include user completion', VALUE_OPTIONAL, false),
                'roleassignments' => new \external_value(PARAM_BOOL, 'Include role assignments', VALUE_OPTIONAL, false),
            ], 'Backup settings', VALUE_OPTIONAL, [])
        ]);
    }

    public static function execute($courseids, $settings)
    {
        global $USER, $CFG, $DB;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseids' => $courseids,
            'settings' => $settings
        ]);

        if (empty($params['courseids'])) {
            throw new \invalid_parameter_exception('At least one course ID must be provided');
        }

        $unique_courseids = array_unique($params['courseids']);
        if (count($unique_courseids) !== count($params['courseids'])) {
            debugging('Duplicate course IDs were removed from the export list', DEBUG_DEVELOPER);
        }

        debugging('Attempting bulk export for courses: ' . implode(', ', $unique_courseids), DEBUG_DEVELOPER);

        foreach ($unique_courseids as $courseid) {
            try {
                if (!$DB->record_exists('course', ['id' => $courseid])) {
                    throw new \moodle_exception('invalidcourseid', 'error', '', $courseid);
                }

                $context = \context_course::instance($courseid);
                self::validate_context($context);
                require_capability('local/oneclickexport:export', $context);
            } catch (\Exception $e) {
                debugging('Course validation failed for ID ' . $courseid . ': ' . $e->getMessage(), DEBUG_DEVELOPER);
                throw $e;
            }
        }

        \core_php_time_limit::raise();
        raise_memory_limit(MEMORY_EXTRA);

        try {
            $tempdir = make_temp_directory('oneclickexport/' . uniqid());
            if (!is_writable($tempdir)) {
                throw new \moodle_exception('tempdirnotwritable', 'local_oneclickexport');
            }
        } catch (\Exception $e) {
            throw new \moodle_exception('tempdircreationfailed', 'local_oneclickexport', '', $e->getMessage());
        }

        try {
            $logid = \local_oneclickexport_logging::log_bulk_export_start($USER->id, $unique_courseids);
        } catch (\Exception $e) {
            throw new \moodle_exception('logcreationfailed', 'local_oneclickexport', '', $e->getMessage());
        }

        try {
            foreach ($unique_courseids as $courseid) {
                $task = new \local_oneclickexport\task\generate_mbz();
                $task->set_custom_data((object)[
                    'userid' => $USER->id,
                    'bulklogid' => $logid,
                    'courseid' => $courseid,
                    'tempdir' => $tempdir,
                    'settings' => $params['settings']
                ]);
                \core\task\manager::queue_adhoc_task($task);
            }
        } catch (\Exception $e) {
            \local_oneclickexport_logging::log_course_export_error($logid, $courseid, $e->getMessage());
            throw new \moodle_exception('taskcreationfailed', 'local_oneclickexport', '', $e->getMessage());
        }

        return [
            'success' => true,
            'logid' => $logid,
            'course_count' => count($unique_courseids)
        ];
    }
    public static function execute_returns()
    {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL, 'True if export was queued successfully'),
            'logid' => new \external_value(PARAM_INT, 'Export log ID'),
            'course_count' => new \external_value(PARAM_INT, 'Number of courses in export')
        ]);
    }
}

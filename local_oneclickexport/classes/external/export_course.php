<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for exporting a single course. This class handles the export
 * process and returns the result.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
class export_course extends \external_api
{
    public static function execute_parameters()
    {
        return new \external_function_parameters([
            'courseid' => new \external_value(PARAM_INT, 'Course ID to export')
        ]);
    }

    public static function execute($courseid)
    {
        global $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'courseid' => $courseid
        ]);

        $context = \context_course::instance($params['courseid']);
        self::validate_context($context);
        require_capability('local/oneclickexport:export', $context);

        $logid = \local_oneclickexport_logging::log_export_start($params['courseid'], $USER->id, 'single');

        $task = new \local_oneclickexport\task\generate_single_mbz();
        $task->set_custom_data([
            'courseid' => $params['courseid'],
            'userid' => $USER->id,
            'logid' => $logid
        ]);
        \core\task\manager::queue_adhoc_task($task);

        return [
            'success' => true,
            'logid' => $logid
        ];
    }

    public static function execute_returns()
    {
        return new \external_single_structure([
            'success' => new \external_value(PARAM_BOOL, 'True if export was queued successfully'),
            'logid' => new \external_value(PARAM_INT, 'Export log ID')
        ]);
    }
}
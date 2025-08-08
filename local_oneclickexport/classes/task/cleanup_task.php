<?php

namespace local_oneclickexport\task;

defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task for cleaning up old export logs and files.
 * This task runs periodically to remove export records older than a specified retention period.
 *
 * @package    local_oneclickexport
 * @category   task
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class cleanup_task extends \core\task\scheduled_task
{
    public function get_name()
    {
        return get_string('cleanuptask', 'local_oneclickexport');
    }


    public function execute()
    {
        require_once(__DIR__ . '/../../classes/logging.php');
        /**
         * Return the name of this task.
         * @return string
         */

        $retentiondays = get_config('local_oneclickexport', 'logretention');
        if (empty($retentiondays) || !is_numeric($retentiondays)) {
            $retentiondays = 30; // Default retention period
        }

        $result = \local_oneclickexport_logging::cleanup_old_exports($retentiondays);

        if ($result === false) {
            throw new \moodle_exception('cleanupfailed', 'local_oneclickexport');
        }

        mtrace("Cleaned up $result old export records and associated files.");
    }
}

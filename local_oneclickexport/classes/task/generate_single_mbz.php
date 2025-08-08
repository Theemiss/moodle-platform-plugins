<?php

namespace local_oneclickexport\task;

require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');

/**
 * Task for generating a single course export in MBZ format. This task is queued
 * for each individual course export operation.
 *
 * @package    local_oneclickexport
 * @category   task
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class generate_single_mbz extends \core\task\adhoc_task {

    public function execute() {
        global $DB, $CFG;

        $data = $this->get_custom_data();
        $courseid = $data->courseid;
        $userid = $data->userid;
        $logid = $data->logid;
        $settings = $data->settings ?? [];

        try {
            if (!$DB->record_exists('local_oneclickexport_log', ['id' => $logid])) {
                throw new \moodle_exception('logrecordnotfound', 'local_oneclickexport');
            }

            $DB->set_field('local_oneclickexport_log', 'status', 'processing', ['id' => $logid]);
            $DB->set_field('local_oneclickexport_log', 'timemodified', time(), ['id' => $logid]);

            $file = \local_oneclickexport_backup_course($courseid, $userid, $settings, $logid);

            $record = new \stdClass();
            $record->id = $logid;
            $record->filesize = $file->get_filesize();
            $record->fileid = $file->get_id();
            $record->status = 'completed';
            $record->timemodified = time();
            
            if (!$DB->update_record('local_oneclickexport_log', $record)) {
                throw new \moodle_exception('logupdatefailed', 'local_oneclickexport');
            }

        } catch (\Exception $e) {
            if (!empty($logid) && $DB->record_exists('local_oneclickexport_log', ['id' => $logid])) {
                $record = new \stdClass();
                $record->id = $logid;
                $record->status = 'failed';
                $record->timemodified = time();
                $record->error = $e->getMessage();
                
                $DB->update_record('local_oneclickexport_log', $record);
            }
            
            throw $e;
        }
    }
}
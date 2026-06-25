<?php
defined('MOODLE_INTERNAL') || die();

/* 
* Task to clean up old export logs and files.
* This task runs periodically to remove export records older than a specified retention period.
*/

class local_oneclickexport_task_cleanup extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('taskcleanup', 'local_oneclickexport');
    }
    
    public function execute() {
        global $DB;
        
        $retention = get_config('local_oneclickexport', 'logretention');
        $cutoff = time() - $retention;
        
        $oldlogs = $DB->get_records_select(
            'local_oneclickexport_log',
            'timecreated < ?',
            [$cutoff]
        );
        
        $fs = get_file_storage();
        foreach ($oldlogs as $log) {
            if ($log->fileid) {
                $fs->delete_area_files(
                    context_system::instance()->id,
                    'backup',
                    'course',
                    $log->fileid
                );
            }
        }
        
        $DB->delete_records_select(
            'local_oneclickexport_log',
            'timecreated < ?',
            [$cutoff]
        );
        
        mtrace("Deleted " . count($oldlogs) . " old export records");
    }
}
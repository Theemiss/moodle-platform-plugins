<?php
defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_task_cleanup extends \core\task\scheduled_task {
    public function get_name() {
        return get_string('taskcleanup', 'local_oneclickexport');
    }
    
    public function execute() {
        global $DB;
        
        $retention = get_config('local_oneclickexport', 'logretention');
        $cutoff = time() - $retention;
        
        // Get old records
        $oldlogs = $DB->get_records_select(
            'local_oneclickexport_log',
            'timecreated < ?',
            [$cutoff]
        );
        
        // Delete associated files
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
        
        // Delete log records
        $DB->delete_records_select(
            'local_oneclickexport_log',
            'timecreated < ?',
            [$cutoff]
        );
        
        mtrace("Deleted " . count($oldlogs) . " old export records");
    }
}
<?php
defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_logging {
    public static function log_export_start($courseid, $userid) {
        global $DB;
        
        $record = new stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->timecreated = time();
        $record->status = 'started';
        
        return $DB->insert_record('local_oneclickexport_log', $record);
    }
    
    public static function log_export_complete($logid, $file, $status = 'completed') {
        global $DB;
        
        $record = new stdClass();
        $record->id = $logid;
        $record->filesize = $file->get_filesize();
        $record->status = $status;
        $record->fileid = $file->get_id();
        $record->timemodified = time();
        
        $DB->update_record('local_oneclickexport_log', $record);
    }
    
    public static function get_export_history($courseid = null, $userid = null, $limit = 10) {
        global $DB, $USER;
        
        $params = [];
        $conditions = [];
        
        if ($courseid) {
            $conditions[] = 'courseid = :courseid';
            $params['courseid'] = $courseid;
        }
        
        if ($userid) {
            $conditions[] = 'userid = :userid';
            $params['userid'] = $userid;
        }
        
        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';
        
        $sql = "SELECT l.*, c.shortname as courseshortname, u.firstname, u.lastname
                FROM {local_oneclickexport_log} l
                JOIN {course} c ON c.id = l.courseid
                JOIN {user} u ON u.id = l.userid
                {$where}
                ORDER BY l.timecreated DESC
                LIMIT {$limit}";
                
        return $DB->get_records_sql($sql, $params);
    }
}
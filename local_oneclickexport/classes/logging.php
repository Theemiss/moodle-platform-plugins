<?php

/**
 * Logging class for the OneClickExport plugin.
 *
 * This class handles logging of export events, including start, completion,
 * and cleanup of old logs.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@dardev.net>
 */

defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_logging
{
    public static function log_export_start($courseid, $userid)
    {
        global $DB;

        $record = new stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->timecreated = time();
        $record->status = 'started';

        return $DB->insert_record('local_oneclickexport_log', $record);
    }

    public static function log_export_complete($logid, $file, $status = 'completed')
    {
        global $DB;

        $record = new stdClass();
        $record->id = $logid;
        $record->filesize = $file->get_filesize();
        $record->status = $status;
        $record->fileid = $file->get_id();
        $record->timemodified = time();

        $DB->update_record('local_oneclickexport_log', $record);
    }
    public static function count_export_history($courseid = null, $userid = null)
    {
        global $DB;

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

        return $DB->count_records_sql(
            "SELECT COUNT(l.id)
             FROM {local_oneclickexport_log} l
             {$where}",
            $params
        );
    }
    public static function cleanup_old_logs($days = 30)
    {
        global $DB;
        $oldest = time() - ($days * 24 * 60 * 60);
        $DB->delete_records_select('local_oneclickexport_log', 'timecreated < ?', [$oldest]);
    }
    public static function get_export_history($courseid = null, $userid = null, $limit = 0, $offset = 0)
    {
        global $DB;

        $params = [];
        $conditions = [];

        if ($courseid) {
            $conditions[] = 'l.courseid = :courseid';
            $params['courseid'] = $courseid;
        }

        if ($userid) {
            $conditions[] = 'l.userid = :userid';
            $params['userid'] = $userid;
        }

        $where = $conditions ? 'WHERE ' . implode(' AND ', $conditions) : '';

        $sql = "SELECT l.*, c.shortname as courseshortname, u.firstname, u.lastname
                FROM {local_oneclickexport_log} l
                JOIN {course} c ON c.id = l.courseid
                JOIN {user} u ON u.id = l.userid
                {$where}
                ORDER BY l.timecreated DESC";

        return $DB->get_records_sql($sql, $params, $offset, $limit);
    }
}

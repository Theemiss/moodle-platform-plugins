<?php

/**
 * Logging class for the OneClickExport plugin.
 *
 * This class handles logging of export events, including start, completion,
 * and cleanup of old logs. Now supports bulk exports with detailed course tracking.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_logging
{
    public static function log_export_start($courseid, $userid, $exporttype = 'single')
    {
        global $DB;

        $record = new stdClass();
        $record->courseid = $courseid;
        $record->userid = $userid;
        $record->timecreated = time();
        $record->status = 'started';
        $record->exporttype = $exporttype;

        $logid = $DB->insert_record('local_oneclickexport_log', $record);
        return $logid;
    }

    public static function log_bulk_export_start($userid, $courseids)
    {
        global $DB;

        $record = new stdClass();
        $record->courseid = 0; // No specific course for bulk export
        $record->userid = $userid;
        $record->timecreated = time();
        $record->status = 'processing';
        $record->exporttype = 'bulk';

        $logid = $DB->insert_record('local_oneclickexport_log', $record);

        foreach ($courseids as $courseid) {
            $course = $DB->get_record('course', ['id' => $courseid], 'id, fullname, shortname');
            if ($course) {
                $detail = new stdClass();
                $detail->logid = $logid;
                $detail->courseid = $course->id;
                $detail->coursename = $course->fullname;
                $detail->courseshortname = $course->shortname;
                $detail->status = 'pending';
                $detail->timecreated = time();
                $detail->timemodified = time();

                $DB->insert_record('local_oneclickexport_log_details', $detail);
            }
        }

        return $logid;
    }



    public static function log_course_export_complete($logid, $courseid, $file, $status = 'completed', $error = null)
    {
        global $DB;

        if (empty($logid) || !is_number($logid)) {
            throw new invalid_parameter_exception('Invalid log ID');
        }
        if (empty($courseid) || !is_number($courseid)) {
            throw new invalid_parameter_exception('Invalid course ID');
        }

        $transaction = $DB->start_delegated_transaction();
        try {
            debugging("Logging export completion for course {$courseid} in log {$logid}", DEBUG_DEVELOPER);

            $record = new stdClass();
            $record->logid = $logid;
            $record->courseid = $courseid;
            $record->status = $status;
            $record->filesize = $file ? $file->get_filesize() : 0;
            $record->fileid = $file ? $file->get_id() : null;
            $record->timemodified = time();
            $record->error = $error;

            $existing = $DB->get_record('local_oneclickexport_log_details', [
                'logid' => $logid,
                'courseid' => $courseid
            ]);

            if ($existing) {
                $record->id = $existing->id;
                debugging("Updating existing record {$record->id}", DEBUG_DEVELOPER);
                $DB->update_record('local_oneclickexport_log_details', $record);
            } else {
                $record->timecreated = time();
                debugging("Creating new record", DEBUG_DEVELOPER);
                $DB->insert_record('local_oneclickexport_log_details', $record);
            }

            $transaction->allow_commit();
            return true;
        } catch (Exception $e) {
            $transaction->rollback($e);
            debugging("Failed to log export completion: " . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
    public static function log_course_export_start($logid, $courseid)
    {
        global $DB;

        $detail = $DB->get_record('local_oneclickexport_log_details', [
            'logid' => $logid,
            'courseid' => $courseid
        ]);

        if (!$detail) {
            throw new moodle_exception('detailrecordnotfound', 'local_oneclickexport');
        }

        $record = new stdClass();
        $record->id = $detail->id;
        $record->status = 'processing';
        $record->timemodified = time();

        $DB->update_record('local_oneclickexport_log_details', $record);
    }

    public static function log_course_export_error($logid, $courseid, $error)
    {
        global $DB;

        $existing = $DB->get_record('local_oneclickexport_log_details', [
            'logid' => $logid,
            'courseid' => $courseid
        ]);

        if ($existing) {
            $record = new \stdClass();
            $record->id = $existing->id;
            $record->status = 'error';
            $record->error = $error;
            $record->timemodified = time();

            $DB->update_record('local_oneclickexport_log_details', $record);
        } else {
            $record = new \stdClass();
            $record->logid = $logid;
            $record->courseid = $courseid;
            $record->status = 'error';
            $record->error = $error;
            $record->timecreated = time();
            $record->timemodified = time();

            $DB->insert_record('local_oneclickexport_log_details', $record);
        }
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
                LEFT JOIN {course} c ON c.id = l.courseid
                JOIN {user} u ON u.id = l.userid
                {$where}
                ORDER BY l.timecreated DESC";

        return $DB->get_records_sql($sql, $params, $offset, $limit);
    }

    public static function get_bulk_export_details($logid)
    {
        global $DB;

        $sql = "SELECT ld.*, c.fullname as coursefullname
                FROM {local_oneclickexport_log_details} ld
                LEFT JOIN {course} c ON c.id = ld.courseid
                WHERE ld.logid = :logid
                ORDER BY ld.timecreated ASC";

        return $DB->get_records_sql($sql, ['logid' => $logid]);
    }

    public static function get_bulk_export_summary($logid)
    {
        global $DB;

        $sql = "SELECT 
                    COUNT(*) as total_courses,
                    SUM(CASE WHEN status = 'completed' THEN 1 ELSE 0 END) as completed_courses,
                    SUM(CASE WHEN status = 'error' THEN 1 ELSE 0 END) as error_courses,
                    SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_courses,
                    SUM(CASE WHEN status = 'processing' THEN 1 ELSE 0 END) as processing_courses,
                    SUM(filesize) as total_filesize
                FROM {local_oneclickexport_log_details}
                WHERE logid = :logid";

        return $DB->get_record_sql($sql, ['logid' => $logid]);
    }

    public static function update_bulk_export_status($logid)
    {
        global $DB;

        $summary = self::get_bulk_export_summary($logid);

        if ($summary->total_courses == $summary->completed_courses) {
            $status = 'completed';
        } else if ($summary->error_courses > 0 && ($summary->completed_courses + $summary->error_courses) == $summary->total_courses) {
            $status = 'completed_with_errors';
        } else if ($summary->processing_courses > 0 || $summary->pending_courses > 0) {
            $status = 'processing';
        } else {
            $status = 'failed';
        }

        $record = new stdClass();
        $record->id = $logid;
        $record->status = $status;
        $record->timemodified = time();

        $DB->update_record('local_oneclickexport_log', $record);
    }
    public static function cleanup_old_exports($days = 30)
    {
        global $DB, $CFG;

        $oldest = time() - ($days * 24 * 60 * 60);
        $fs = get_file_storage();
        $context = context_system::instance();

        $transaction = $DB->start_delegated_transaction();

        try {
            $oldlogs = $DB->get_records_select(
                'local_oneclickexport_log',
                'timecreated < ?',
                [$oldest],
                'id, fileid'
            );

            $logids = array_keys($oldlogs);

            if (!empty($logids)) {
                foreach ($oldlogs as $log) {
                    if ($log->fileid) {
                        $file = $fs->get_file_by_id($log->fileid);
                        if ($file) {
                            $file->delete();
                        }
                    }
                }

                $fs->delete_area_files_select(
                    $context->id,
                    'local_oneclickexport',
                    'bulk',
                    'itemid IN (' . implode(',', $logids) . ')'
                );

                $fs->delete_area_files_select(
                    $context->id,
                    'local_oneclickexport',
                    'backup',
                    'itemid IN (' . implode(',', $logids) . ')'
                );

                list($insql, $inparams) = $DB->get_in_or_equal($logids);
                $DB->delete_records_select(
                    'local_oneclickexport_log_details',
                    "logid $insql",
                    $inparams
                );

                $DB->delete_records_select(
                    'local_oneclickexport_log',
                    "id $insql",
                    $inparams
                );
            }

            $transaction->allow_commit();
            return count($logids);
        } catch (Exception $e) {
            $transaction->rollback($e);
            debugging("Failed to cleanup old exports: " . $e->getMessage(), DEBUG_DEVELOPER);
            return false;
        }
    }
}

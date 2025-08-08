<?php

namespace local_oneclickexport\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');


/**
 * Task for generating a course export in MBZ format. This task is queued
 * for each course in a bulk export operation.
 *
 * @package    local_oneclickexport
 * @category   task
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
class generate_mbz extends \core\task\adhoc_task
{

    public function execute()
    {
        global $DB, $USER;

        $data = $this->get_custom_data();

        $required = ['userid', 'bulklogid', 'courseid', 'tempdir', 'settings'];
        foreach ($required as $prop) {
            if (!property_exists($data, $prop)) {
                throw new \moodle_exception('missingdata', 'local_oneclickexport', '', $prop);
            }
        }

        $courseid = $data->courseid;
        $userid = $data->userid;
        $bulklogid = $data->bulklogid;
        $tempdir = $data->tempdir;
        $settings = (array)$data->settings;

        try {
            if ($courseid == 0) {
                $this->finalize_export($data);
                return;
            }

            $backup_settings = [];
            if (!empty($settings['users'])) {
                $backup_settings['users'] = 1;
            }
            if (!empty($settings['comments'])) {
                $backup_settings['comments'] = 1;
            }
            if (!empty($settings['logs'])) {
                $backup_settings['logs'] = 1;
            }
            if (!empty($settings['calendarevents'])) {
                $backup_settings['calendarevents'] = 1;
            }
            if (!empty($settings['userscompletion'])) {
                $backup_settings['userscompletion'] = 1;
            }
            if (!empty($settings['roleassignments'])) {
                $backup_settings['roleassignments'] = 1;
            }

            $backupfile = local_oneclickexport_backup_course($courseid, $userid, $backup_settings, $bulklogid);

            $DB->set_field('local_oneclickexport_log', 'timemodified', time(), ['id' => $bulklogid]);

            $this->check_if_last_task($bulklogid, $tempdir, $userid);
        } catch (\Exception $e) {
            \local_oneclickexport_logging::update_bulk_export_status($bulklogid);
            throw $e;
        }
    }

protected function finalize_export($data) {
    global $DB, $CFG;

    $fs = get_file_storage();
    $context = \context_system::instance();

    if (!is_dir($data->tempdir) || !is_writable($data->tempdir)) {
        throw new \moodle_exception('tempdirnotwritable', 'local_oneclickexport', '', $data->tempdir);
    }

    $files = $fs->get_area_files(
        $context->id,
        'local_oneclickexport',
        'backup',
        $data->bulklogid,
        'timemodified DESC',
        false
    );

    if (empty($files)) {
        throw new \moodle_exception('nobackupfiles', 'local_oneclickexport');
    }

    $zipname = 'bulk_export_' . date('Ymd_His') . '.zip';
    $tempzip = $data->tempdir . '/' . $zipname;

    $zip = new \ZipArchive();
    $tempfiles = [];
    $success = false;

    try {
        if ($zip->open($tempzip, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \moodle_exception('cannotcreatezip', 'local_oneclickexport');
        }

        foreach ($files as $file) {
            $filename = $file->get_filename();
            $counter = 1;
            
            while ($zip->locateName($filename) !== false) {
                $pathinfo = pathinfo($file->get_filename());
                $filename = $pathinfo['filename'] . '_' . $counter . '.' . $pathinfo['extension'];
                $counter++;
            }

            $temppath = $file->copy_content_to_temp();
            $tempfiles[] = $temppath;

            if (!$zip->addFile($temppath, $filename)) {
                throw new \moodle_exception('cannotaddtozip', 'local_oneclickexport');
            }
        }

        if (!$zip->close()) {
            throw new \moodle_exception('cannotclosezip', 'local_oneclickexport');
        }
        $zip = null; 

        if (!file_exists($tempzip) || filesize($tempzip) == 0) {
            throw new \moodle_exception('emptyzipcreated', 'local_oneclickexport');
        }

        $file_record = [
            'contextid' => $context->id,
            'component' => 'local_oneclickexport',
            'filearea' => 'bulk',
            'itemid' => $data->bulklogid,
            'filepath' => '/',
            'filename' => $zipname,
            'userid' => $data->userid
        ];

        $storedfile = $fs->create_file_from_pathname($file_record, $tempzip);

        $DB->update_record('local_oneclickexport_log', [
            'id' => $data->bulklogid,
            'filesize' => $storedfile->get_filesize(),
            'fileid' => $storedfile->get_id(),
            'timemodified' => time(),
            'status' => 'completed'
        ]);

        $success = true;
    } finally {
        if (isset($zip) && $zip instanceof \ZipArchive) {
            @$zip->close();
        }

        foreach ($tempfiles as $temppath) {
            if (file_exists($temppath)) {
                @unlink($temppath);
            }
        }

        if (file_exists($tempzip) && !$success) {
            @unlink($tempzip);
        }

        remove_dir($data->tempdir);
    }
}
protected function check_if_last_task($bulklogid, $tempdir, $userid) {
    global $DB;

    $pending = $DB->count_records('local_oneclickexport_log_details', [
        'logid' => $bulklogid,
        'status' => 'pending'
    ]);

    $processing = $DB->count_records('local_oneclickexport_log_details', [
        'logid' => $bulklogid,
        'status' => 'processing'
    ]);

    $sql = "SELECT COUNT(*) 
            FROM {task_adhoc} 
            WHERE classname = :classname
            AND " . $DB->sql_compare_text('customdata') . " LIKE :bulklogid
            AND " . $DB->sql_compare_text('customdata') . " NOT LIKE :finaltask";

    $params = [
        'classname' => '\local_oneclickexport\task\generate_mbz',
        'bulklogid' => '%"bulklogid":' . $bulklogid . '%',
        'finaltask' => '%"courseid":0%'
    ];

    $remaining_tasks = $DB->count_records_sql($sql, $params);

    if ($pending == 0 && $processing == 0 && $remaining_tasks <= 1) {
        sleep(2);

        $final_check = $DB->count_records_sql($sql, $params);
        if ($final_check <= 1) {
            $finaltask = new \local_oneclickexport\task\generate_mbz();
            $finaltask->set_custom_data((object)[
                'userid' => $userid,
                'bulklogid' => $bulklogid,
                'courseid' => 0,
                'tempdir' => $tempdir,
                'settings' => (object)[]
            ]);
            
            \core\task\manager::queue_adhoc_task($finaltask, true); // true = high priority
            
            \local_oneclickexport_logging::update_bulk_export_status($bulklogid);
        }
    }
}
}

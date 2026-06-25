<?php

/* 
*  TODO: Async event for export started
*  This event is triggered when a one-click export for a course is started.
*/
namespace local_oneclickexport\task;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

class generate_mbz extends \core\task\adhoc_task {
    
    public function execute() {
        global $CFG, $DB;
        
        $data = $this->get_custom_data();
        
        if (empty($data->userid) || empty($data->courseid)) {
            throw new \moodle_exception('invalidtaskdata', 'local_oneclickexport');
        }

        $userid = (int)$data->userid;
        $courseid = (int)$data->courseid;
        $settings = isset($data->settings) ? (array)$data->settings : [];
        $isbulk = !empty($data->bulklogid);
        $zipfile = $data->zipfile ?? null;
        $tempdir = $data->tempdir ?? null;

        try {
            $bc = new \backup_controller(
                \backup::TYPE_1COURSE,
                $courseid,
                \backup::FORMAT_MOODLE,
                \backup::INTERACTIVE_NO,
                \backup::MODE_GENERAL,
                $userid
            );

            $this->apply_backup_settings($bc, $settings);

            // Execute backup
            $bc->execute_plan();
            $results = $bc->get_results();
            
            if (empty($results['backup_destination']) || !($results['backup_destination'] instanceof \stored_file)) {
                throw new \moodle_exception('backupfailed', 'local_oneclickexport');
            }

            $file = $results['backup_destination'];
            
            if ($isbulk && $zipfile && $tempdir) {
                $this->process_bulk_export($file, $zipfile, $tempdir, $data);
                
                $this->update_progress($data);
                
                if ($data->current >= $data->total) {
                    $this->finalize_bulk_export($zipfile, $tempdir, $data);
                }
            } else {
                $this->process_single_export($file, $data);
            }

            $bc->destroy();
            $this->handle_export_success($data);
            
        } catch (\Exception $e) {
            if (isset($bc) && $bc instanceof \backup_controller) {
                $bc->destroy();
            }
            $this->handle_export_failure($e, $data);
            throw $e;
        }
    }

    protected function apply_backup_settings(\backup_controller $bc, array $settings) {
        $plan = $bc->get_plan();
        
        foreach ($settings as $name => $value) {
            if ($plan->setting_exists($name)) {
                $setting = $plan->get_setting($name);
                if ($setting->get_status() == \base_setting::NOT_LOCKED) {
                    $setting->set_value($value);
                }
            }
        }
    }

    protected function process_bulk_export(\stored_file $file, string $zipfile, string $tempdir, \stdClass $data) {
        // More robust temp directory validation
        $tempdir = realpath($tempdir);
        if ($tempdir === false || !is_dir($tempdir) || !is_writable($tempdir)) {
            throw new \moodle_exception('invalidtempdir', 'local_oneclickexport', '', $tempdir);
        }

        // Verify zip file path is within the temp directory for security
        $zipfile = realpath($zipfile);
        if ($zipfile === false || strpos($zipfile, $tempdir) !== 0) {
            throw new \moodle_exception('invalidzippath', 'local_oneclickexport');
        }

        $zip = new \ZipArchive();
        $res = $zip->open($zipfile, \ZipArchive::CREATE);
        
        if ($res !== true) {
            throw new \moodle_exception('cannotopenzip', 'local_oneclickexport', '', $res);
        }

        try {
            $tempfile = $file->copy_content_to_temp();
            if ($tempfile === false) {
                throw new \moodle_exception('cannotcreatetempfile', 'local_oneclickexport');
            }

            $filename = 'course_' . $data->courseid . '_' . date('Ymd-His') . '.mbz';
            
            if (!$zip->addFile($tempfile, $filename)) {
                throw new \moodle_exception('cannotaddtozip', 'local_oneclickexport');
            }

            if (!$zip->close()) {
                throw new \moodle_exception('cannotclosezip', 'local_oneclickexport');
            }
        } finally {
            // Ensure cleanup happens even if an exception occurs
            if (isset($tempfile)) {
                @unlink($tempfile);
            }
            $file->delete();
        }
    }

    protected function update_progress(\stdClass $data) {
        global $DB;
        
        if (!empty($data->bulklogid)) {
            $progress = round(($data->current / $data->total) * 100);
            $DB->set_field('local_oneclickexport_log', 'progress', $progress, ['id' => $data->bulklogid]);
            $DB->set_field('local_oneclickexport_log', 'timemodified', time(), ['id' => $data->bulklogid]);
        }
    }

    protected function finalize_bulk_export(string $zipfile, string $tempdir, \stdClass $data) {
        global $DB, $USER;
        
        $zipfile = realpath($zipfile);
        if ($zipfile === false || !file_exists($zipfile)) {
            throw new \moodle_exception('zipfilenotfound', 'local_oneclickexport');
        }

        // Store the final ZIP file
        $context = \context_user::instance($USER->id);
        $fs = get_file_storage();
        
        $filerecord = [
            'contextid' => $context->id,
            'component' => 'local_oneclickexport',
            'filearea' => 'bulkexports',
            'itemid' => $data->bulklogid,
            'filepath' => '/',
            'filename' => basename($zipfile)
        ];
        
        // Delete existing file if any
        $fs->delete_area_files(
            $filerecord['contextid'],
            $filerecord['component'],
            $filerecord['filearea'],
            $filerecord['itemid']
        );
        
        $file = $fs->create_file_from_pathname($filerecord, $zipfile);
        
        // Update the log record
        $DB->update_record('local_oneclickexport_log', [
            'id' => $data->bulklogid,
            'filesize' => $file->get_filesize(),
            'fileid' => $file->get_id(),
            'status' => 'completed',
            'progress' => 100,
            'timemodified' => time()
        ]);
        
        // Cleanup temporary files
        @unlink($zipfile);
        @rmdir($tempdir);
    }

    protected function handle_export_success(\stdClass $data) {
        global $DB;
        
        if (empty($data->bulklogid)) {
            // For single exports, log the success
            $DB->insert_record('local_oneclickexport_log', [
                'courseid' => $data->courseid,
                'userid' => $data->userid,
                'timecreated' => time(),
                'filesize' => 0, // Will be updated by process_single_export
                'status' => 'completed',
                'timemodified' => time()
            ]);
        }
    }

    protected function handle_export_failure(\Exception $exception, \stdClass $data) {
        global $DB;
        
        if (!empty($data->bulklogid)) {
            // Cleanup any partial files
            if (!empty($data->zipfile)) {
                @unlink($data->zipfile);
            }
            if (!empty($data->tempdir)) {
                @rmdir($data->tempdir);
            }
            
            $DB->update_record('local_oneclickexport_log', [
                'id' => $data->bulklogid,
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'timemodified' => time()
            ]);
        } else {
            // Log failure for single export
            $DB->insert_record('local_oneclickexport_log', [
                'courseid' => $data->courseid,
                'userid' => $data->userid,
                'timecreated' => time(),
                'filesize' => 0,
                'status' => 'failed',
                'error' => $exception->getMessage(),
                'fileid' => null,
                'timemodified' => time()
            ]);
        }
        
        mtrace("Export failed for course {$data->courseid}: " . $exception->getMessage());
    }
}
<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');

use external_function_parameters;
use external_value;
use external_single_structure;
use external_api;
use context_course;
use backup_controller;
use backup;
use moodle_exception;
use stored_file;
use file_storage;
use context_user;
use moodle_url;
use context_system;

class export_course extends external_api {

    public static function execute_parameters() {
        return new external_function_parameters([
            'courseid' => new external_value(PARAM_INT, 'ID of the course to export'),
        ]);
    }

    public static function execute($courseid) {
        global $CFG, $DB;

        $params = self::validate_parameters(self::execute_parameters(), ['courseid' => $courseid]);
        $courseid = $params['courseid'];

        $course = get_course($courseid);
        $context = context_course::instance($courseid);
        self::validate_context($context);
        require_capability('moodle/backup:backupcourse', $context);

        raise_memory_limit(MEMORY_HUGE);
        @set_time_limit(0);

        $bc = null;
        $fs = get_file_storage();
        $systemcontext = context_system::instance();

        try {
            $bc = new backup_controller(
                backup::TYPE_1COURSE,
                $courseid,
                backup::FORMAT_MOODLE,
                backup::INTERACTIVE_NO,
                backup::MODE_GENERAL,
                2 // Admin user
            );

            // Configure backup settings
            $bc->get_plan()->get_setting('users')->set_value(0);
            $bc->get_plan()->get_setting('anonymize')->set_value(0);
            $bc->get_plan()->get_setting('activities')->set_value(1);
            $bc->get_plan()->get_setting('blocks')->set_value(1);
            $bc->get_plan()->get_setting('filters')->set_value(1);

            $filename = 'backup_' . $course->shortname . '_' . date('Ymd-His') . '.mbz';
            $filename = clean_filename($filename);
            $bc->get_plan()->get_setting('filename')->set_value($filename);

            $bc->execute_plan();
            $results = $bc->get_results();
            
            if (!isset($results['backup_destination']) || !$results['backup_destination'] instanceof stored_file) {
                throw new moodle_exception('backupfailed', 'local_oneclickexport');
            }

            $file = $results['backup_destination'];

            $filerecord = [
                'contextid' => $systemcontext->id,
                'component' => 'local_oneclickexport',
                'filearea' => 'public_backups',
                'itemid' => 0,
                'filepath' => '/',
                'filename' => $filename,
            ];

            $fs->delete_area_files($systemcontext->id, 'local_oneclickexport', 'public_backups');

            $newfile = $fs->create_file_from_storedfile($filerecord, $file);
            $file->delete();

            if (!$fs->file_exists($systemcontext->id, 'local_oneclickexport', 'public_backups', 0, '/', $filename)) {
                throw new moodle_exception('filenotcreated', 'local_oneclickexport');
            }

            // Generate direct download URL
            $url = moodle_url::make_pluginfile_url(
                $newfile->get_contextid(),
                $newfile->get_component(),
                $newfile->get_filearea(),
                $newfile->get_itemid(),
                $newfile->get_filepath(),
                $newfile->get_filename(),
                true
            );

            return [
                'status' => 'success',
                'message' => get_string('backupcreated', 'local_oneclickexport'),
                'filename' => $filename,
                'downloadurl' => $url->out(false),
                'filesize' => $newfile->get_filesize(),
            ];

        } catch (Exception $e) {
            if ($bc) {
                $bc->destroy();
            }
            throw new moodle_exception('backuperror', 'local_oneclickexport', '', $e->getMessage());
        }
    }

    public static function execute_returns() {
        return new external_single_structure([
            'status' => new external_value(PARAM_TEXT, 'Status of the operation'),
            'message' => new external_value(PARAM_TEXT, 'Human-readable result'),
            'filename' => new external_value(PARAM_TEXT, 'Name of the backup file'),
            'downloadurl' => new external_value(PARAM_URL, 'Public download link'),
            'filesize' => new external_value(PARAM_INT, 'Size of the backup file in bytes'),
        ]);
    }
}
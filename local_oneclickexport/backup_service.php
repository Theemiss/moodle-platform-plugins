<?php
// local/oneclickexport/backup_service.php

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

/**
 * Perform course backup and return file object.
 *
 * @param int $courseid
 * @param int $userid
 * @return stored_file
 * @throws moodle_exception
 */
function local_oneclickexport_backup_course(int $courseid, int $userid): stored_file {
    global $USER;

    $course = get_course($courseid);
    $context = context_course::instance($courseid);
    require_capability('local/oneclickexport:export', $context);

    raise_memory_limit(MEMORY_HUGE);
    @set_time_limit(0);

    $fs = get_file_storage();
    $bc = null;
    $backupid = null;

    try {
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $userid
        );

        $plan = $bc->get_plan();
        $settings = [
            'users' => 0,
            'anonymize' => 0,
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1
        ];

        foreach ($settings as $name => $value) {
            try {
                $plan->get_setting($name)->set_value($value);
            } catch (Exception $e) {
                debugging("Setting {$name} not available", DEBUG_DEVELOPER);
            }
        }

        $filename = clean_filename($course->shortname . '-backup-' . date('Ymd-His') . '.mbz');
        $plan->get_setting('filename')->set_value($filename);

        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];

        if (!$file) {
            throw new moodle_exception('nobackupfile', 'local_oneclickexport');
        }

        return $file;

    } catch (Exception $e) {
        throw new moodle_exception('backuperror', 'local_oneclickexport', '', $e->getMessage());
    } finally {
        if ($bc) {
            $bc->destroy();
        }
        if (!empty($backupid)) {
            $fs->delete_area_files(context_system::instance()->id, 'backup', 'course', $backupid);
        }
    }
}

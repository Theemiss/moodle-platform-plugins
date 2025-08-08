<?php
/**
 * Backup service for the OneClickExport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * Perform course backup and return file object stored in plugin area.
 *
 * @param int $courseid
 * @param int $userid
 * @param array $settings Optional backup settings
 * @param int $logid Optional log ID for bulk exports
 * @return stored_file
 * @throws moodle_exception
 */
function local_oneclickexport_backup_course(int $courseid, int $userid, array $settings = [], int $logid = null): stored_file
{
    global $DB;

    $course = get_course($courseid);
    $context = context_course::instance($courseid);
    require_capability('local/oneclickexport:export', $context);

    raise_memory_limit(MEMORY_HUGE);
    @set_time_limit(0);

    $fs = get_file_storage();
    $bc = null;
    $backupid = null;
    $export = null;

   
    if ($logid) {
        $export = $DB->get_record('local_oneclickexport_log', ['id' => $logid]);

        if ($export && $export->exporttype === 'bulk') {
            local_oneclickexport_logging::log_course_export_start($logid, $courseid);
        }
    }

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

        $default_settings = [
            'users' => get_config('local_oneclickexport', 'includeusers') ? 1 : 0,
            'anonymize' => 0,
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'comments' => get_config('local_oneclickexport', 'includecomments') ? 1 : 0,
            'logs' => get_config('local_oneclickexport', 'includelogs') ? 1 : 0,
            'calendarevents' => get_config('local_oneclickexport', 'includecalendarevents') ? 1 : 0,
            'userscompletion' => get_config('local_oneclickexport', 'includeuserscompletion') ? 1 : 0,
            'roleassignments' => get_config('local_oneclickexport', 'includeroleassignments') ? 1 : 0
        ];

        if (!empty($settings)) {
            $default_settings = array_merge($default_settings, $settings);
        }

        foreach ($default_settings as $name => $value) {
            try {
                $plan->get_setting($name)->set_value($value);
            } catch (Exception $e) {
                debugging("Setting {$name} not available: " . $e->getMessage(), DEBUG_DEVELOPER);
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

        $contextid = context_system::instance()->id;
        $file_record = [
            'contextid' => $contextid,
            'component' => 'local_oneclickexport',
            'filearea' => 'backup',
            'itemid' => $logid ?: 0, 
            'filepath' => '/',
            'filename' => $filename,
        ];

        $plugin_file = $fs->create_file_from_storedfile($file_record, $file);

        $file->delete();

        debugging("Backup file stored in plugin area: {$plugin_file->get_filename()}", DEBUG_DEVELOPER);

        if ($logid && $export && $export->exporttype === 'bulk') {
            debugging("Logging completion for bulk export log ID: {$logid}", DEBUG_DEVELOPER);
            local_oneclickexport_logging::log_course_export_complete($logid, $courseid, $file);
        }

        return $plugin_file;
    } catch (Exception $e) {
        if ($logid && $export && $export->exporttype === 'bulk') {
            local_oneclickexport_logging::log_course_export_error($logid, $courseid, $e->getMessage());
        }

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


/**
 * Get backup settings from form data.
 *
 * @param object $formdata Form data object
 * @return array Backup settings
 */
function local_oneclickexport_get_backup_settings($formdata): array
{
    $settings = [];

    $setting_mapping = [
        'includeusers' => 'users',
        'includecomments' => 'comments',
        'includelogs' => 'logs',
        'includecalendarevents' => 'calendarevents',
        'includeuserscompletion' => 'userscompletion',
        'includeroleassignments' => 'roleassignments'
    ];

    foreach ($setting_mapping as $form_field => $backup_setting) {
        if (isset($formdata->$form_field) && $formdata->$form_field) {
            $settings[$backup_setting] = 1;
        }
    }

    return $settings;
}

<?php
// Detect if we're running in CLI mode
define('CLI_SCRIPT', isset($argc) && is_numeric($argc));

if (CLI_SCRIPT) {
    require(__DIR__.'/../../config.php');
    require_once($CFG->libdir.'/clilib.php');
    require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');

    // CLI options
    list($options, $unrecognized) = cli_get_params([
        'courseid' => false,
        'courseshortname' => '',
        'destination' => '',
        'help' => false,
    ], ['h' => 'help']);

    if ($options['help'] || !($options['courseid'] || $options['courseshortname'])) {
        $help = "Perform backup of the given course.\n\nOptions:\n--courseid=INTEGER Course ID\n--courseshortname=STRING Course shortname\n--destination=STRING Backup destination path\n-h, --help Print this help\n";
        echo $help;
        die;
    }

    // Find course
    if ($options['courseid']) {
        $course = $DB->get_record('course', ['id' => $options['courseid']], '*', MUST_EXIST);
    } else {
        $course = $DB->get_record('course', ['shortname' => $options['courseshortname']], '*', MUST_EXIST);
    }
    
    $userid = get_admin()->id;
} else {
    // Web interface mode
    require_once(__DIR__.'/../../config.php');
    require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

    $courseid = required_param('id', PARAM_INT);
    $course = get_course($courseid);
    require_login($course);
    $context = context_course::instance($course->id);
    require_capability('local/oneclickexport:export', $context);
    $userid = $USER->id;
}

// Common setup for both CLI and web
@set_time_limit(0);
raise_memory_limit(MEMORY_HUGE);

$bc = null;
$backupid = null;
$fs = get_file_storage();

try {
    // Initialize backup controller
    $bc = new backup_controller(
        backup::TYPE_1COURSE,
        $course->id,
        backup::FORMAT_MOODLE,
        CLI_SCRIPT ? backup::INTERACTIVE_YES : backup::INTERACTIVE_NO,
        backup::MODE_GENERAL,
        $userid
    );

    // Configure settings
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
            if (CLI_SCRIPT) {
                mtrace("Notice: Setting {$name} not available");
            } else {
                debugging("Setting {$name} not available", DEBUG_DEVELOPER);
            }
        }
    }

    // Set filename
    $format = $bc->get_format();
    $type = $bc->get_type();
    $id = $bc->get_id();
    $filename = backup_plan_dbops::get_default_backup_filename($format, $type, $id, 0, 0);

    if (!CLI_SCRIPT) {
        $filename = clean_filename($course->shortname . '-backup-' . date('Ymd-His') . '.mbz');
    }
    $plan->get_setting('filename')->set_value($filename);

    // Execute backup
    if (CLI_SCRIPT) {
        $bc->finish_ui();
        mtrace("Starting backup for course: {$course->fullname} (ID: {$course->id})");
    }
    
    $bc->execute_plan();
    $results = $bc->get_results();
    $file = $results['backup_destination'];

    if (CLI_SCRIPT) {
        // Handle CLI destination
        if (!empty($options['destination'])) {
            $dir = rtrim($options['destination'], '/');
            if (is_dir($dir) && is_writable($dir)) {
                $target = $dir.'/'.$filename;
                if ($file->copy_content_to($target)) {
                    $file->delete();
                    mtrace("Backup saved to: {$target}");
                } else {
                    mtrace("Failed to save backup to destination");
                }
            } else {
                mtrace("Destination directory not writable");
            }
        } else {
            mtrace("Backup completed. File available in course backup area");
        }
    } else {
        // Handle web download
        if ($file) {
            send_stored_file($file, 0, 0, true, [
                'filename' => $filename,
                'fullpath' => true
            ]);
        } else {
            throw new moodle_exception('nobackupfile', 'local_oneclickexport');
        }
    }

} catch (Exception $e) {
    if (CLI_SCRIPT) {
        mtrace("Backup failed: " . $e->getMessage());
        exit(1);
    } else {
        throw new moodle_exception('backuperror', 'local_oneclickexport', '', $e->getMessage());
    }
} finally {
    if ($bc) {
        $bc->destroy();
    }
    if (!empty($backupid)) {
        $fs->delete_area_files(context_system::instance()->id, 'backup', 'course', $backupid);
    }
}
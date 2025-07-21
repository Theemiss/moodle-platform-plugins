<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
require_capability('local/oneclickexport:export', $context);

// Create backup controller
$bc = new backup_controller(
    backup::TYPE_1COURSE,
    $course->id,
    backup::FORMAT_MOODLE,
    backup::INTERACTIVE_NO,
    backup::MODE_GENERAL,
    $USER->id
);

// Get backup plan and settings
$plan = $bc->get_plan();

// Define default settings with fallback checks
$settings = [
    'users' => ['default' => 0, 'min' => 0, 'max' => 1],
    'anonymize' => ['default' => 0, 'min' => 0, 'max' => 1],
    'role_assignments' => ['default' => 0, 'min' => 0, 'max' => 1],
    'activities' => ['default' => 1, 'min' => 0, 'max' => 1],
    'blocks' => ['default' => 1, 'min' => 0, 'max' => 1],
    'filters' => ['default' => 1, 'min' => 0, 'max' => 1],
    'comments' => ['default' => 0, 'min' => 0, 'max' => 1],
    'completion_information' => ['default' => 0, 'min' => 0, 'max' => 1],
    'logs' => ['default' => 0, 'min' => 0, 'max' => 1],
    'histories' => ['default' => 0, 'min' => 0, 'max' => 1],
    'calendarevents' => ['default' => 0, 'min' => 0, 'max' => 1], // Added for newer Moodle versions
    'userscompletion' => ['default' => 0, 'min' => 0, 'max' => 1] // Added for newer Moodle versions
];

foreach ($settings as $name => $config) {
    try {
        $setting = $plan->get_setting($name);
        if ($setting) {
            // Validate before setting
            $value = $config['default'];
            $value = max($config['min'], min($config['max'], $value));
            $setting->set_value($value);
        }
    } catch (Exception $e) {
        // Skip if setting doesn't exist
        debugging("Setting {$name} not available in this Moodle version", DEBUG_DEVELOPER);
        continue;
    }
}

// Execute backup
try {
    $bc->execute_plan();
    $backupid = $bc->get_backupid();
    
    // Get the backup file
    $fs = get_file_storage();
    $files = $fs->get_area_files(
        context_system::instance()->id,
        'backup',
        'course',
        $backupid,
        'filename',
        false
    );

    if (empty($files)) {
        throw new moodle_exception('nobackupfile', 'local_oneclickexport');
    }

    $file = reset($files);
    
    // Generate filename
    $filename = clean_filename($course->shortname . '-backup-' . date('Ymd-His') . '.mbz');
    
    // Send file with proper headers
    send_stored_file($file, 0, 0, true, ['filename' => $filename]);
    
} catch (Exception $e) {
    // Proper error handling
    $bc->destroy();
    throw new moodle_exception('backuperror', 'local_oneclickexport', '', $e->getMessage());
} finally {
    // Clean up
    if (isset($backupid)) {
        $fs->delete_area_files(context_system::instance()->id, 'backup', 'course', $backupid);
    }
    $bc->destroy();
}
<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
require_login($course);

$context = context_course::instance($course->id);
require_capability('local/oneclickexport:export', $context);

// Set up backup controller
$bc = new backup_controller(
    backup::TYPE_1COURSE,
    $course->id,
    backup::FORMAT_MOODLE,
    backup::INTERACTIVE_NO,
    backup::MODE_GENERAL,
    $USER->id
);

// Configure with default settings
$settings = [
    'users' => 0,               // Don't include users
    'anonymize' => 0,            // Don't anonymize
    'role_assignments' => 0,     // Don't include role assignments
    'activities' => 1,           // Include activities
    'blocks' => 1,               // Include blocks
    'filters' => 1,              // Include filters
    'comments' => 0,             // Don't include comments
    'completion_information' => 0,
    'logs' => 0,
    'histories' => 0,
];

foreach ($settings as $name => $value) {
    $bc->get_plan()->get_setting($name)->set_value($value);
}

// Create temp dir and execute backup
$backupid = $bc->get_backupid();
$tempdir = $CFG->tempdir.'/backup/'.$backupid;
check_dir_exists($tempdir);

$bc->execute_plan();
$bc->destroy();

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

// Send the file
send_stored_file($file, 0, 0, true, [
    'filename' => clean_filename($course->shortname . '-backup-' . date('Ymd-His')) . '.mbz'
]);

// Clean up
$fs->delete_area_files(context_system::instance()->id, 'backup', 'course', $backupid);
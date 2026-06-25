<?php

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/oneclickexport:export', $context);

$file = local_oneclickexport_backup_course($courseid, $USER->id);
$filename = $file->get_filename();

send_stored_file($file, 0, 0, true, [
    'filename' => $filename,
    'fullpath' => true
]);

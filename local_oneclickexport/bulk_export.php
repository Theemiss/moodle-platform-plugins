<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->dirroot.'/local/oneclickexport/classes/form/bulk_export.php');

// Proper context initialization
$context = context_system::instance();
$PAGE->set_context($context);

require_login();
require_capability('local/oneclickexport:bulkexport', $context);

$PAGE->set_url(new moodle_url('/local/oneclickexport/bulk_export.php'));
$PAGE->set_title(get_string('bulkexport', 'local_oneclickexport'));
$PAGE->set_heading(get_string('bulkexport', 'local_oneclickexport'));
$PAGE->set_pagelayout('admin');

$form = new local_oneclickexport_bulk_export_form();

if ($form->is_cancelled()) {
    redirect(new moodle_url('/'));
} else if ($data = $form->get_data()) {
    // Process bulk export
    $total = count($data->courses);
    $current = 0;
    
    $zipname = 'bulk_export_' . date('Ymd_His') . '.zip';
    $tempdir = $CFG->tempdir . '/bulkexport_' . time();
    check_dir_exists($tempdir);
    $tempzip = $tempdir . '/' . $zipname;
    
    // Create empty file first to avoid deprecation warning
    file_put_contents($tempzip, '');
    
    $zip = new ZipArchive();
    if ($zip->open($tempzip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new moodle_exception('cannotcreatezip', 'local_oneclickexport');
    }
    
    foreach ($data->courses as $courseid) {
        $current++;
        $course = get_course($courseid);
        
        // Create adhoc task for each course
        $task = new local_oneclickexport_task_generate_mbz();
        $task->set_custom_data([
            'userid' => $USER->id,
            'courseid' => $courseid,
            'settings' => [
                'users' => !empty($data->includeusers),
                'comments' => !empty($data->includecomments),
                'logs' => !empty($data->includelogs)
            ],
            'bulk' => true,
            'zip' => $tempzip,
            'progress' => [$current, $total]
        ]);
        \core\task\manager::queue_adhoc_task($task);
    }
    
    $zip->close();
    
    // Store the zip file and notify user when complete
    $fs = get_file_storage();
    $fileinfo = [
        'contextid' => context_user::instance($USER->id)->id,
        'component' => 'user',
        'filearea' => 'private',
        'itemid' => 0,
        'filepath' => '/',
        'filename' => $zipname
    ];
    
    $fs->create_file_from_pathname($fileinfo, $tempzip);
    
    // Clean up temp directory
    remove_dir($tempdir);
    
    // Notify user
    $message = new \core\message\message();
    $message->component = 'local_oneclickexport';
    $message->name = 'bulkexportcomplete';
    $message->userfrom = \core_user::get_noreply_user();
    $message->userto = $USER;
    $message->subject = get_string('bulkexportcomplete', 'local_oneclickexport');
    $message->fullmessage = get_string('bulkexportready', 'local_oneclickexport', $total);
    $message->fullmessageformat = FORMAT_PLAIN;
    $message->fullmessagehtml = '<p>'.get_string('bulkexportready', 'local_oneclickexport', $total).'</p>';
    $message->smallmessage = get_string('bulkexportsmall', 'local_oneclickexport');
    
    message_send($message);
    
    redirect(new moodle_url('/local/oneclickexport/admin_report.php'), 
        get_string('bulkexportstarted', 'local_oneclickexport', $total));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bulkexport', 'local_oneclickexport'));
$form->display();
echo $OUTPUT->footer();
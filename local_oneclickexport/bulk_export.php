<?php
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_once($CFG->dirroot . '/local/oneclickexport/classes/form/bulk_export_form.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/task/generate_mbz.php');

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
    $total = count($data->courses);

    if ($total === 0) {
        redirect(
            $PAGE->url,
            get_string('nocoursesselected', 'local_oneclickexport'),
            null,
            \core\output\notification::NOTIFY_ERROR
        );
    }

    $tempdir = make_temp_directory('bulkexport_' . time());
    if (!is_dir($tempdir) || !is_writable($tempdir)) {
        throw new moodle_exception('cannotcreatetempdir', 'local_oneclickexport');
    }

    $zipname = 'course_backups_' . date('Ymd_His') . '.zip';
    $tempzip = $tempdir . '/' . $zipname;

    $zip = new ZipArchive();
    if ($zip->open($tempzip, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
        throw new moodle_exception('cannotcreatezip', 'local_oneclickexport');
    }
    $zip->close();
    # TODDO : Add logic to handle the zip file creation and course export for each course
    $bulklogid = $DB->insert_record('local_oneclickexport_log', [
        'courseid' => 0, // 0 indicates bulk export
        'userid' => $USER->id,
        'timecreated' => time(),
        'filesize' => 0,
        'status' => 'processing',
        'fileid' => null,
        'timemodified' => time(),
        'progress' => 0,
        'total' => $total
    ]);

    $common_data = [
        'userid' => $USER->id,
        'bulklogid' => $bulklogid,
        'settings' => [
            'users' => !empty($data->includeusers),
            'comments' => !empty($data->includecomments),
            'logs' => !empty($data->includelogs)
        ],
        'zipfile' => $tempzip,
        'tempdir' => $tempdir,
        'total' => $total
    ];

    // Queue tasks for each course
    foreach ($data->courses as $index => $courseid) {
        $task = new \local_oneclickexport\task\generate_mbz();
        $task->set_custom_data(array_merge($common_data, [
            'courseid' => $courseid,
            'current' => $index + 1
        ]));
        \core\task\manager::queue_adhoc_task($task);
    }

    redirect(
        new moodle_url('/local/oneclickexport/admin_report.php'),
        get_string('bulkexportstarted', 'local_oneclickexport', $total),
        null,
        \core\output\notification::NOTIFY_SUCCESS
    );
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('bulkexport', 'local_oneclickexport'));
$form->display();
echo $OUTPUT->footer();

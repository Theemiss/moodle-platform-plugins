<?php

/**
 * Single export status page for the OneClickExport plugin.
 * This page displays the status of a single export operation.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

$logid = required_param('id', PARAM_INT);

$export = $DB->get_record('local_oneclickexport_log', ['id' => $logid]);
if (!$export) {
    throw new moodle_exception('exportnotfound', 'local_oneclickexport');
}

if ($export->userid != $USER->id && !has_capability('moodle/site:config', context_system::instance())) {
    throw new moodle_exception('nopermission', 'local_oneclickexport');
}

$course = get_course($export->courseid);
$context = context_course::instance($export->courseid);

$PAGE->set_context($context);
$PAGE->set_url(new moodle_url('/local/oneclickexport/single_export_status.php', ['id' => $logid]));
$PAGE->set_title(get_string('export_status', 'local_oneclickexport'));
$PAGE->set_heading(get_string('export_status', 'local_oneclickexport'));

$PAGE->navbar->add($course->fullname, new moodle_url('/course/view.php', ['id' => $course->id]));
$PAGE->navbar->add(get_string('export_status', 'local_oneclickexport'));

echo $OUTPUT->header();

echo html_writer::start_tag('div', ['class' => 'card']);
echo html_writer::start_tag('div', ['class' => 'card-header']);
echo html_writer::tag('h3', get_string('export_status', 'local_oneclickexport'));
echo html_writer::end_tag('div');

echo html_writer::start_tag('div', ['class' => 'card-body']);

echo html_writer::start_tag('div', ['class' => 'row mb-3']);
echo html_writer::start_tag('div', ['class' => 'col-md-6']);
echo html_writer::tag('strong', get_string('course', 'local_oneclickexport') . ': ');
echo html_writer::tag('span', $course->fullname);
echo html_writer::end_tag('div');
echo html_writer::start_tag('div', ['class' => 'col-md-6']);
echo html_writer::tag('strong', get_string('started', 'local_oneclickexport') . ': ');
echo html_writer::tag('span', userdate($export->timecreated));
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

$status_icons = [
    'started' => 'i/scheduled',
    'processing' => 'i/loading',
    'completed' => 'i/valid',
    'failed' => 'i/invalid',
    'completed_with_errors' => 'i/warning'
];

$status_classes = [
    'started' => 'text-info',
    'processing' => 'text-warning',
    'completed' => 'text-success',
    'failed' => 'text-danger',
    'completed_with_errors' => 'text-warning'
];

echo html_writer::start_tag('div', ['class' => 'row mb-3']);
echo html_writer::start_tag('div', ['class' => 'col-12']);
echo html_writer::tag('strong', get_string('status', 'local_oneclickexport') . ': ');
echo html_writer::tag(
    'span',
    $OUTPUT->pix_icon($status_icons[$export->status] ?? 'i/help', '') . ' ' .
        get_string($export->status, 'local_oneclickexport'),
    ['class' => $status_classes[$export->status] ?? '']
);
echo html_writer::end_tag('div');
echo html_writer::end_tag('div');

if ($export->timemodified > $export->timecreated) {
    $duration = $export->timemodified - $export->timecreated;
    echo html_writer::start_tag('div', ['class' => 'row mb-3']);
    echo html_writer::start_tag('div', ['class' => 'col-md-6']);
    echo html_writer::tag('strong', get_string('duration', 'local_oneclickexport') . ': ');
    echo html_writer::tag('span', format_time($duration));
    echo html_writer::end_tag('div');
    echo html_writer::end_tag('div');
}

if ($export->status == 'completed' && $export->fileid) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($export->fileid);

    if ($file && $file->get_filesize() > 0) {
        echo html_writer::start_tag('div', ['class' => 'row mb-3']);
        echo html_writer::start_tag('div', ['class' => 'col-md-6']);
        echo html_writer::tag('strong', get_string('filesize', 'local_oneclickexport') . ': ');
        echo html_writer::tag('span', display_size($export->filesize));
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        $url = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            true
        );

        echo html_writer::start_tag('div', ['class' => 'row']);
        echo html_writer::start_tag('div', ['class' => 'col-12']);
        echo html_writer::link(
            $url,
            $OUTPUT->pix_icon('i/download', '') . ' ' . get_string('download', 'local_oneclickexport'),
            ['class' => 'btn btn-success']
        );
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');
    } else {
        echo html_writer::start_tag('div', ['class' => 'alert alert-warning']);
        echo html_writer::tag(
            'span',
            $OUTPUT->pix_icon('i/warning', '') . ' ' . get_string('file_not_found', 'local_oneclickexport')
        );
        echo html_writer::end_tag('div');
    }
}

if ($export->status == 'failed') {
    $details = $DB->get_record('local_oneclickexport_log_details', ['logid' => $logid]);
    if ($details && $details->error) {
        echo html_writer::start_tag('div', ['class' => 'alert alert-danger']);
        echo html_writer::tag('strong', get_string('error', 'local_oneclickexport') . ': ');
        echo html_writer::tag('span', $details->error);
        echo html_writer::end_tag('div');
    }
}

echo html_writer::end_tag('div');
echo html_writer::end_tag('div'); 

if ($export->status == 'started' || $export->status == 'processing') {
    echo html_writer::script('
        setTimeout(function() {
            window.location.reload();
        }, 5000);
    ');
}

echo html_writer::start_tag('div', ['class' => 'mt-3']);
echo html_writer::link(
    new moodle_url('/course/view.php', ['id' => $course->id]),
    $OUTPUT->pix_icon('i/back', '') . ' ' . get_string('backtocourse', 'local_oneclickexport'),
    ['class' => 'btn btn-secondary']
);
echo html_writer::end_tag('div');

echo $OUTPUT->footer();

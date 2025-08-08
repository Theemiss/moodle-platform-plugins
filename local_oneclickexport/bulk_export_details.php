<?php

/**
 * Bulk export details page for the OneClickExport plugin.
 *
 * This page displays detailed information about a bulk export operation,
 * including the status, summary, and individual course export details.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

$id = required_param('id', PARAM_INT);
$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/oneclickexport:bulkexport', $context);

$export = $DB->get_record('local_oneclickexport_log', ['id' => $id, 'exporttype' => 'bulk']);
if (!$export) {
    throw new moodle_exception('invalidexportid', 'local_oneclickexport');
}

$PAGE->set_url(new moodle_url('/local/oneclickexport/bulk_export_details.php', ['id' => $id]));
$PAGE->set_title(get_string('exportdetails', 'local_oneclickexport'));
$PAGE->set_heading(get_string('exportdetails', 'local_oneclickexport'));
$PAGE->set_pagelayout('admin');

$PAGE->navbar->add(get_string('exportreports', 'local_oneclickexport'), new moodle_url('/local/oneclickexport/admin_report.php'));
$PAGE->navbar->add(get_string('exportdetails', 'local_oneclickexport'));

echo $OUTPUT->header();

$user = $DB->get_record('user', ['id' => $export->userid]);
$summarydata = [
    'user' => html_writer::link(
        new moodle_url('/user/profile.php', ['id' => $user->id]),
        fullname($user)
    ),
    'timecreated' => userdate($export->timecreated, get_string('strftimedatetimeshort')),
    'timemodified' => userdate($export->timemodified, get_string('strftimedatetimeshort')),
    'status' => get_string($export->status, 'local_oneclickexport'),
    'size' => $export->filesize ? display_size($export->filesize) : '-',
];

$duration = '-';
if ($export->status == 'completed' || $export->status == 'failed' || $export->status == 'completed_with_errors') {
    $duration_seconds = $export->timemodified - $export->timecreated;
    if ($duration_seconds > 0) {
        if ($duration_seconds < 60) {
            $duration = $duration_seconds . ' seconds';
        } else if ($duration_seconds < 3600) {
            $duration = round($duration_seconds / 60, 1) . ' minutes';
        } else {
            $duration = round($duration_seconds / 3600, 1) . ' hours';
        }
    }
}
$summarydata['duration'] = $duration;

if ($export->status == 'completed' && $export->fileid) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($export->fileid);
    if ($file) {
        $summarydata['download'] = html_writer::link(
            moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                true
            ),
            $OUTPUT->pix_icon('i/export', '') . ' ' . get_string('download', 'local_oneclickexport'),
            ['class' => 'btn btn-success']
        );
    }
}

$summarytable = new html_table();
$summarytable->attributes['class'] = 'generaltable';
foreach ($summarydata as $key => $value) {
    $summarytable->data[] = [
        get_string($key, 'local_oneclickexport'),
        $value
    ];
}

echo html_writer::tag('h3', get_string('exportsummary', 'local_oneclickexport'), []);
echo html_writer::table($summarytable);

$coursedetails = local_oneclickexport_logging::get_bulk_export_details($id);
$summary = local_oneclickexport_logging::get_bulk_export_summary($id);

if ($coursedetails) {
    echo html_writer::tag('h3', get_string('coursedetails', 'local_oneclickexport'), []);
    
    $total_courses = $summary->total_courses;
    $completed_courses = $summary->completed_courses;
    $error_courses = $summary->error_courses;
    $pending_courses = $summary->pending_courses;
    $processing_courses = $summary->processing_courses;
    
    $progress_percentage = $total_courses > 0 ? round((($completed_courses + $error_courses) / $total_courses) * 100, 1) : 0;
    
    echo html_writer::start_div('progress mb-3');
    $completed_width = $total_courses > 0 ? ($completed_courses / $total_courses) * 100 : 0;
    $error_width = $total_courses > 0 ? ($error_courses / $total_courses) * 100 : 0;
    
    if ($completed_width > 0) {
        echo html_writer::div('', 'progress-bar bg-success', [
            'style' => "width: {$completed_width}%",
            'title' => get_string('completed_courses', 'local_oneclickexport') . ': ' . $completed_courses
        ]);
    }
    
    if ($error_width > 0) {
        echo html_writer::div('', 'progress-bar bg-danger', [
            'style' => "width: {$error_width}%",
            'title' => get_string('error_courses', 'local_oneclickexport') . ': ' . $error_courses
        ]);
    }
    echo html_writer::end_div();
    
    $progress_text = get_string('progress_text', 'local_oneclickexport', [
        'completed' => $completed_courses,
        'total' => $total_courses,
        'percentage' => $progress_percentage
    ]);
    echo html_writer::tag('p', $progress_text, ['class' => 'text-muted mb-3']);
    
    echo html_writer::start_div('row mb-4');
    
    $stats = [
        ['count' => $total_courses, 'label' => 'total_courses', 'class' => 'primary'],
        ['count' => $completed_courses, 'label' => 'completed_courses', 'class' => 'success'],
        ['count' => $error_courses, 'label' => 'error_courses', 'class' => 'danger'],
        ['count' => $pending_courses + $processing_courses, 'label' => 'pending_courses', 'class' => 'warning']
    ];
    
    foreach ($stats as $stat) {
        echo html_writer::start_div('col-md-3');
        echo html_writer::start_div('card text-center p-3');
        echo html_writer::tag('h4', $stat['count'], ['class' => 'text-' . $stat['class']]);
        echo html_writer::tag('p', get_string($stat['label'], 'local_oneclickexport'), ['class' => 'text-muted']);
        echo html_writer::end_div();
        echo html_writer::end_div();
    }
    
    echo html_writer::end_div();
    
    if ($summary->total_filesize > 0) {
        $file_size_content = html_writer::tag('strong', get_string('total_filesize', 'local_oneclickexport') . ': ');
        $file_size_content .= display_size($summary->total_filesize);
        $file_size_content .= html_writer::empty_tag('br');
        $file_size_content .= html_writer::tag('small', 
            get_string('average_filesize', 'local_oneclickexport') . ': ' . display_size($summary->total_filesize / $total_courses), 
            ['class' => 'text-muted']
        );
        echo html_writer::div($file_size_content, 'alert alert-info mb-3');
    }
    
    $coursetable = new html_table();
    $coursetable->head = [
        get_string('course', 'local_oneclickexport'),
        get_string('shortname', 'local_oneclickexport'),
        get_string('status', 'local_oneclickexport'),
        get_string('filesize', 'local_oneclickexport'),
        get_string('timecreated', 'local_oneclickexport'),
        get_string('duration', 'local_oneclickexport'),
        get_string('error', 'local_oneclickexport')
    ];
    $coursetable->attributes['class'] = 'generaltable';
    
    foreach ($coursedetails as $course) {
        $statusclass = '';
        $status_icon = '';
        switch ($course->status) {
            case 'completed':
                $statusclass = 'text-success';
                $status_icon = '✓';
                break;
            case 'error':
                $statusclass = 'text-danger';
                $status_icon = '✗';
                break;
            case 'processing':
                $statusclass = 'text-warning';
                $status_icon = '⟳';
                break;
            case 'pending':
                $statusclass = 'text-muted';
                $status_icon = '⏳';
                break;
        }
        
        $course_duration = '-';
        if ($course->status == 'completed' || $course->status == 'error') {
            $course_duration_seconds = $course->timemodified - $course->timecreated;
            if ($course_duration_seconds > 0) {
                if ($course_duration_seconds < 60) {
                    $course_duration = $course_duration_seconds . 's';
                } else {
                    $course_duration = round($course_duration_seconds / 60, 1) . 'm';
                }
            }
        }
        
        $coursetable->data[] = [
            html_writer::link(
                new moodle_url('/course/view.php', ['id' => $course->courseid]),
                $course->coursename
            ),
            $course->courseshortname,
            html_writer::tag('span', $status_icon . ' ' . get_string($course->status, 'local_oneclickexport'), ['class' => $statusclass]),
            $course->filesize ? display_size($course->filesize) : '-',
            userdate($course->timecreated, get_string('strftimedatetimeshort')),
            $course_duration,
            $course->error ? html_writer::tag('span', $course->error, ['class' => 'text-danger small']) : '-'
        ];
    }
    
    echo html_writer::table($coursetable);
    
    if ($export->status != 'processing') {
        echo html_writer::tag('h4', get_string('export_timeline', 'local_oneclickexport'), []);
        echo html_writer::start_div('timeline');
        
        $timeline_events = [
            ['time' => $export->timecreated, 'event' => 'export_started', 'icon' => '▶'],
            ['time' => $export->timemodified, 'event' => 'export_' . $export->status, 'icon' => '✓']
        ];
        
        foreach ($timeline_events as $event) {
            echo html_writer::start_div('timeline-item');
            echo html_writer::tag('span', $event['icon'], ['class' => 'timeline-icon']);
            echo html_writer::start_div('timeline-content');
            echo html_writer::tag('strong', get_string($event['event'], 'local_oneclickexport'));
            echo html_writer::empty_tag('br');
            echo html_writer::tag('small', userdate($event['time'], get_string('strftimedatetimeshort')), ['class' => 'text-muted']);
            echo html_writer::end_div();
            echo html_writer::end_div();
        }
        
        echo html_writer::end_div();
    }
    
} else {
    echo html_writer::div(get_string('nocoursedetails', 'local_oneclickexport'), 'alert alert-info');
}

echo html_writer::tag('style', '
.timeline {
    position: relative;
    padding-left: 30px;
    margin: 20px 0;
}
.timeline-item {
    position: relative;
    margin-bottom: 20px;
}
.timeline-icon {
    position: absolute;
    left: -35px;
    background: #007bff;
    color: white;
    border-radius: 50%;
    width: 25px;
    height: 25px;
    text-align: center;
    line-height: 25px;
    font-size: 12px;
}
.timeline-content {
    background: #f8f9fa;
    padding: 10px;
    border-radius: 5px;
    border-left: 3px solid #007bff;
}
/* /local/oneclickexport/styles.css */
.oneclickexport-table {
    width: 100%;
}

.oneclickexport-table th {
    vertical-align: middle !important;
}

.oneclickexport-table td {
    vertical-align: middle !important;
}

.oneclickexport-table .badge {
    font-size: 90%;
    padding: 0.4em 0.6em;
}

.oneclickexport-table .actions-col {
    white-space: nowrap;
}

.oneclickexport-table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

@media (max-width: 768px) {
    .oneclickexport-table {
        display: block;
        overflow-x: auto;
    }
}
');

echo $OUTPUT->footer();
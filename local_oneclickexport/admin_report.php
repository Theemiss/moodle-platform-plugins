<?php

/**
 * Admin report for One Click Export logs.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once($CFG->libdir . '/tablelib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/oneclickexport:bulkexport', $context);

$PAGE->set_url(new moodle_url('/local/oneclickexport/admin_report.php'));
$PAGE->set_title(get_string('exportreports', 'local_oneclickexport'));
$PAGE->set_heading(get_string('exportreports', 'local_oneclickexport'));
$PAGE->set_pagelayout('admin');

$delete = optional_param('delete', 0, PARAM_INT);
if ($delete && confirm_sesskey()) {
    $export = $DB->get_record('local_oneclickexport_log', ['id' => $delete]);
    if ($export) {
        if ($export->fileid) {
            $fs = get_file_storage();
            $file = $fs->get_file_by_id($export->fileid);
            if ($file) {
                $file->delete();
            }
        }
        
        if ($export->exporttype == 'bulk') {
            $DB->delete_records('local_oneclickexport_log_details', ['logid' => $export->id]);
        }
        
        $DB->delete_records('local_oneclickexport_log', ['id' => $export->id]);
        
        redirect($PAGE->url, get_string('exportdeleted', 'local_oneclickexport'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

$totalexports = $DB->count_records('local_oneclickexport_log');
$bulkexports = $DB->count_records('local_oneclickexport_log', ['exporttype' => 'bulk']);
$singleexports = $DB->count_records('local_oneclickexport_log', ['exporttype' => 'single']);
$completedexports = $DB->count_records('local_oneclickexport_log', ['status' => 'completed']);
$processingexports = $DB->count_records('local_oneclickexport_log', ['status' => 'processing']);
$failedexports = $DB->count_records('local_oneclickexport_log', ['status' => 'failed']);
$totalsize = $DB->get_field_sql("SELECT COALESCE(SUM(filesize), 0) FROM {local_oneclickexport_log} WHERE filesize > 0");
$weekago = time() - (7 * 24 * 60 * 60);
$recentexports = $DB->count_records_select('local_oneclickexport_log', 'timecreated > ?', [$weekago]);

$columns = [
    'timecreated' => get_string('timecreated', 'local_oneclickexport'),
    'user' => get_string('user', 'local_oneclickexport'),
    'type' => get_string('exporttype', 'local_oneclickexport'),
    'courses' => get_string('courses', 'local_oneclickexport'),
    'status' => get_string('status', 'local_oneclickexport'),
    'size' => get_string('size', 'local_oneclickexport'),
    'duration' => get_string('duration', 'local_oneclickexport'),
    'actions' => get_string('actions', 'local_oneclickexport')
];

$headers = [
    get_string('timecreated', 'local_oneclickexport'),
    get_string('user', 'local_oneclickexport'),
    get_string('exporttype', 'local_oneclickexport'),
    get_string('courses', 'local_oneclickexport'),
    get_string('status', 'local_oneclickexport'),
    get_string('size', 'local_oneclickexport'),
    get_string('duration', 'local_oneclickexport'),
    get_string('actions', 'local_oneclickexport')
];

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exportreports', 'local_oneclickexport'));

echo html_writer::start_div('row mb-4');

echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card text-center p-3');
echo html_writer::tag('h4', $totalexports, ['class' => 'text-primary mb-0']);
echo html_writer::tag('p', get_string('totalexports', 'local_oneclickexport'), ['class' => 'text-muted mb-0']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card text-center p-3');
echo html_writer::tag('h4', $bulkexports, ['class' => 'text-info mb-0']);
echo html_writer::tag('p', get_string('bulkexports', 'local_oneclickexport'), ['class' => 'text-muted mb-0']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card text-center p-3');
echo html_writer::tag('h4', $completedexports, ['class' => 'text-success mb-0']);
echo html_writer::tag('p', get_string('completedexports', 'local_oneclickexport'), ['class' => 'text-muted mb-0']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::start_div('col-md-3');
echo html_writer::start_div('card text-center p-3');
echo html_writer::tag('h4', display_size($totalsize), ['class' => 'text-warning mb-0']);
echo html_writer::tag('p', get_string('totalsize', 'local_oneclickexport'), ['class' => 'text-muted mb-0']);
echo html_writer::end_div();
echo html_writer::end_div();

echo html_writer::end_div();

if ($recentexports > 0) {
    echo html_writer::div(
        get_string('recentactivity', 'local_oneclickexport', $recentexports), 
        'alert alert-info mb-3'
    );
}


echo html_writer::div(
    html_writer::link(
        new moodle_url('/local/oneclickexport/bulk_export.php'),
        $OUTPUT->pix_icon('i/export', '') . ' ' . get_string('newexport', 'local_oneclickexport'),
        ['class' => 'btn btn-primary mb-3']
    ),
    'mb-3'
);

echo html_writer::start_div('card');
echo html_writer::start_div('card-header d-flex justify-content-between align-items-center');
echo html_writer::tag('h4', get_string('exportlog', 'local_oneclickexport'), ['class' => 'mb-0']);
echo html_writer::end_div();
echo html_writer::start_div('card-body p-0');

$table = new flexible_table('local_oneclickexport_reports');
$table->define_columns(array_keys($columns));
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'table table-hover table-striped mb-0');
$table->set_attribute('id', 'oneclickexport-logs-table');
$table->column_style('size', 'text-align', 'right');
$table->column_style('duration', 'text-align', 'right');
$table->column_style('actions', 'text-align', 'right');
$table->column_class('actions', 'actions-col');
$table->setup();


$exports = $DB->get_records('local_oneclickexport_log', null, 'timecreated DESC');

$users = [];
if (!empty($exports)) {
    $userids = array_column($exports, 'userid');
    list($useridsql, $userparams) = $DB->get_in_or_equal($userids);
    $users = $DB->get_records_sql("SELECT * FROM {user} WHERE id $useridsql", $userparams);
    $users = array_combine(array_column($users, 'id'), $users);
}

foreach ($exports as $export) {
    $user = $users[$export->userid] ?? null;
    if (!$user) {
        continue;
    }
    
    $userlink = new moodle_url('/user/profile.php', ['id' => $user->id]);

    if ($export->exporttype == 'bulk') {
        $summary = local_oneclickexport_logging::get_bulk_export_summary($export->id);
        $numcourses = $summary ? $summary->total_courses : 0;
        $completed = $summary ? $summary->completed_courses : 0;
        $failed = $summary ? $summary->error_courses : 0;
        $exporttype = html_writer::tag('span', get_string('bulk', 'local_oneclickexport'), 
            ['class' => 'badge badge-info']);
        
        $successrate = $numcourses > 0 ? round(($completed / $numcourses) * 100, 1) : 0;
        $coursesinfo = $numcourses . ' ' . get_string('courses', 'local_oneclickexport');
        if ($numcourses > 0) {
            $coursesinfo .= html_writer::tag('div', 
                "($successrate% " . get_string('success', 'local_oneclickexport') . ")",
                ['class' => 'small text-muted']
            );
        }
    } else {
        $numcourses = 1;
        $completed = ($export->status == 'completed') ? 1 : 0;
        $failed = ($export->status == 'failed') ? 1 : 0;
        $exporttype = html_writer::tag('span', get_string('single', 'local_oneclickexport'), 
            ['class' => 'badge badge-secondary']);
        $coursesinfo = "1 " . get_string('course');
    }

    $size = '-';
    if ($export->filesize) {
        $size = html_writer::tag('div', display_size($export->filesize), 
            ['class' => 'text-right']);
    }

    $duration = '-';
    if ($export->status == 'completed' || $export->status == 'failed') {
        $durationseconds = $export->timemodified - $export->timecreated;
        if ($durationseconds > 0) {
            $durationvalue = '';
            if ($durationseconds < 60) {
                $durationvalue = $durationseconds . 's';
            } else if ($durationseconds < 3600) {
                $durationvalue = round($durationseconds / 60, 1) . 'm';
            } else {
                $durationvalue = round($durationseconds / 3600, 1) . 'h';
            }
            $duration = html_writer::tag('div', $durationvalue, 
                ['class' => 'text-right']);
        }
    }

    $statusclass = '';
    $statusicon = '';
    switch ($export->status) {
        case 'completed':
            $statusclass = 'badge-success';
            $statusicon = 'fa-check';
            break;
        case 'completed_with_errors':
            $statusclass = 'badge-warning';
            $statusicon = 'fa-exclamation-triangle';
            break;
        case 'processing':
            $statusclass = 'badge-info';
            $statusicon = 'fa-sync-alt';
            break;
        case 'failed':
            $statusclass = 'badge-danger';
            $statusicon = 'fa-times';
            break;
        case 'started':
            $statusclass = 'badge-secondary';
            $statusicon = 'fa-play';
            break;
    }
    
    $status = html_writer::tag('span', 
        $OUTPUT->pix_icon($statusicon, '', 'fontawesome', ['class' => 'mr-1']) . 
        get_string($export->status, 'local_oneclickexport'), 
        ['class' => 'badge ' . $statusclass]
    );
    
    if ($export->exporttype == 'bulk' && $export->status == 'processing') {
        $status .= html_writer::tag('div', 
            "($completed/$numcourses)", 
            ['class' => 'small text-muted mt-1']
        );
    }

    $actions = html_writer::start_tag('div', ['class' => 'd-flex justify-content-end']);

    if ($export->status == 'completed' && $export->fileid) {
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($export->fileid);
        if ($file && $file->get_filesize() > 0) {
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                true
            );
            $actions .= html_writer::link(
                $url,
                $OUTPUT->pix_icon('i/download', '') . ' ' . get_string('download', 'local_oneclickexport'),
                ['class' => 'btn btn-sm btn-outline-success mr-1']
            );
        }
    }

    if ($export->exporttype == 'bulk') {
        $actions .= html_writer::link(
            new moodle_url('/local/oneclickexport/bulk_export_details.php', ['id' => $export->id]),
            $OUTPUT->pix_icon('i/info', '') . ' ' . get_string('details', 'local_oneclickexport'),
            ['class' => 'btn btn-sm btn-outline-info mr-1']
        );
    }
    
    if (has_capability('moodle/site:config', $context)) {
        $actions .= html_writer::link(
            new moodle_url($PAGE->url, ['delete' => $export->id, 'sesskey' => sesskey()]),
            $OUTPUT->pix_icon('i/trash', '') . ' ' . get_string('delete'),
            ['class' => 'btn btn-sm btn-outline-danger']
        );
    }

    $actions .= html_writer::end_tag('div');

    $table->add_data([
        userdate($export->timecreated, get_string('strftimedatetimeshort')),
        html_writer::link($userlink, fullname($user)),
        $exporttype,
        $coursesinfo,
        $status,
        $size,
        $duration,
        $actions
    ]);
}

$table->print_html();

echo html_writer::end_div(); 
echo html_writer::end_div(); 

echo html_writer::tag('style', '
#oneclickexport-logs-table {
    width: 100%;
    margin-bottom: 0;
}

#oneclickexport-logs-table th {
    border-top: none;
    vertical-align: middle;
    background-color: #f8f9fa;
}

#oneclickexport-logs-table td {
    vertical-align: middle;
}

#oneclickexport-logs-table .badge {
    font-size: 90%;
    padding: 0.4em 0.6em;
}

#oneclickexport-logs-table .actions-col {
    white-space: nowrap;
}

#oneclickexport-logs-table .btn-sm {
    padding: 0.25rem 0.5rem;
    font-size: 0.875rem;
    line-height: 1.5;
}

.card-header {
    background-color: #f8f9fa;
    border-bottom: 1px solid rgba(0,0,0,.125);
}

@media (max-width: 768px) {
    #oneclickexport-logs-table {
        display: block;
        overflow-x: auto;
    }
    
    .card-body {
        padding: 0;
    }
}
');

echo $OUTPUT->footer();
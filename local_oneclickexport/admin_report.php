<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

$context = context_system::instance();
$PAGE->set_context($context);
require_login();
require_capability('local/oneclickexport:bulkexport', $context);

$PAGE->set_url(new moodle_url('/local/oneclickexport/admin_report.php'));
$PAGE->set_title(get_string('exportreports', 'local_oneclickexport'));
$PAGE->set_heading(get_string('exportreports', 'local_oneclickexport'));
$PAGE->set_pagelayout('admin');

// Define table columns
$columns = [
    'timecreated' => get_string('timecreated', 'local_oneclickexport'),
    'user' => get_string('user'),
    'numcourses' => get_string('numcourses', 'local_oneclickexport'),
    'status' => get_string('status'),
    'size' => get_string('size'),
    'actions' => get_string('actions')
];

// Define table headers
$headers = [
    get_string('timecreated', 'local_oneclickexport'),
    get_string('user'),
    get_string('numcourses', 'local_oneclickexport'),
    get_string('status'),
    get_string('size'),
    get_string('actions')
];

// Create and configure the table
$table = new flexible_table('local_oneclickexport_reports');
$table->define_columns(array_keys($columns));
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable admintable');
$table->setup();

// Get all bulk exports (parent records where courseid = 0)
$exports = $DB->get_records('local_oneclickexport_log', ['courseid' => 0], 'timecreated DESC');

foreach ($exports as $export) {
    $user = $DB->get_record('user', ['id' => $export->userid]);
    $userlink = new moodle_url('/user/profile.php', ['id' => $user->id]);
    
    // Get count of courses in this bulk export
    $numcourses = $DB->count_records_select('local_oneclickexport_log', 
        "userid = ? AND timecreated = ? AND courseid > 0", 
        [$export->userid, $export->timecreated]
    );
    
    // Format size
    $size = $export->filesize ? display_size($export->filesize) : '-';
    
    // Format status
    $status = get_string($export->status, 'local_oneclickexport');
    if ($export->status == 'processing') {
        $completed = $DB->count_records_select('local_oneclickexport_log', 
            "userid = ? AND timecreated = ? AND status = ? AND courseid > 0",
            [$export->userid, $export->timecreated, 'completed']
        );
        $status .= " ($completed/$numcourses)";
    }
    
    // Action buttons
    $actions = '';
    if ($export->status == 'completed' && $export->fileid) {
        $fs = get_file_storage();
        $file = $fs->get_file_by_id($export->fileid);
        if ($file) {
            $url = moodle_url::make_pluginfile_url(
                $file->get_contextid(),
                $file->get_component(),
                $file->get_filearea(),
                $file->get_itemid(),
                $file->get_filepath(),
                $file->get_filename(),
                true
            );
            $actions .= html_writer::link($url, 
                $OUTPUT->pix_icon('i/export', '') . ' ' . get_string('download'),
                ['class' => 'btn btn-secondary']
            );
        }
    }
    
    $actions .= html_writer::link(
        new moodle_url('/local/oneclickexport/bulk_export_details.php', ['id' => $export->id]),
        $OUTPUT->pix_icon('i/info', '') . ' ' . get_string('details'),
        ['class' => 'btn btn-primary ml-1']
    );
    
    // Add row to table
    $table->add_data([
        userdate($export->timecreated),
        html_writer::link($userlink, fullname($user)),
        $numcourses,
        $status,
        $size,
        $actions
    ]);
}

// Display page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exportreports', 'local_oneclickexport'));

// Add link to start new export
echo html_writer::link(
    new moodle_url('/local/oneclickexport/bulk_export.php'),
    $OUTPUT->pix_icon('i/export', '') . ' ' . get_string('newexport', 'local_oneclickexport'),
    ['class' => 'btn btn-primary mb-3']
);

// Display the table
$table->print_html();

echo $OUTPUT->footer();
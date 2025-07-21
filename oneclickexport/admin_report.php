<?php
require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_oneclickexport_report');

$context = context_system::instance();
require_capability('moodle/site:config', $context);

$PAGE->set_url(new moodle_url('/local/oneclickexport/admin_report.php'));
$PAGE->set_title(get_string('exportreport', 'local_oneclickexport'));
$PAGE->set_heading(get_string('exportreport', 'local_oneclickexport'));

$table = new flexible_table('oneclick-export-report');
$table->define_columns(['course', 'user', 'time', 'status', 'size', 'actions']);
$table->define_headers([
    get_string('course'),
    get_string('user'),
    get_string('time'),
    get_string('status'),
    get_string('size'),
    get_string('actions')
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable');
$table->setup();

$history = local_oneclickexport_logging::get_export_history(null, null, 50);

foreach ($history as $record) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($record->fileid);
    
    $row = [
        format_string($record->courseshortname),
        fullname($record),
        userdate($record->timecreated),
        get_string($record->status, 'local_oneclickexport'),
        display_size($record->filesize),
    ];
    
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
        $row[] = html_writer::link($url, get_string('download'));
    } else {
        $row[] = '';
    }
    
    $table->add_data($row);
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exportreport', 'local_oneclickexport'));
$table->print_html();
echo $OUTPUT->footer();
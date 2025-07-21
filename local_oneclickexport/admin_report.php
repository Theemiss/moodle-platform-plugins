<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Export history report for OneClickExport plugin.
 *
 * @package    local_oneclickexport
 * @copyright  2023 Your Name <your@email.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__.'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->libdir.'/tablelib.php');

// Set up the page
admin_externalpage_setup('local_oneclickexport_report');
$context = context_system::instance();
$PAGE->set_context($context);

// Prepare the table
$table = new flexible_table('oneclick-export-report');
$table->define_columns(['course', 'user', 'time', 'status', 'size', 'actions']);
$table->define_headers([
    get_string('course', 'local_oneclickexport'),
    get_string('user'),
    get_string('time'),
    get_string('status'),
    get_string('size'),
    get_string('actions')
]);
$table->define_baseurl($PAGE->url);
$table->set_attribute('class', 'generaltable admin_table');
$table->column_style('actions', 'text-align', 'center');
$table->setup();

// Get export history with pagination
$perpage = 30;
$page = optional_param('page', 0, PARAM_INT);
$count = local_oneclickexport_logging::count_export_history();
$history = local_oneclickexport_logging::get_export_history(null, null, $perpage, $page * $perpage);

// Add data to table
foreach ($history as $record) {
    $fs = get_file_storage();
    $file = $fs->get_file_by_id($record->fileid);
    
    $row = [
        html_writer::link(
            new moodle_url('/course/view.php', ['id' => $record->courseid]),
            format_string($record->courseshortname)
        ),
        html_writer::link(
            new moodle_url('/user/profile.php', ['id' => $record->userid]),
            fullname($record)
        ),
        userdate($record->timecreated, get_string('strftimedatetime')),
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
        $row[] = html_writer::link($url, get_string('download'), ['class' => 'btn btn-secondary']);
    } else {
        $row[] = '';
    }
    
    $table->add_data($row);
}

// Add pagination
$baseurl = new moodle_url($PAGE->url, ['page' => $page]);
$pagingbar = $OUTPUT->paging_bar($count, $page, $perpage, $baseurl);

// Output the page
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('exportreport', 'local_oneclickexport'));

// Add filter form if needed
// echo $filterform->render();

echo $pagingbar;
$table->print_html();
echo $pagingbar;

echo $OUTPUT->footer();
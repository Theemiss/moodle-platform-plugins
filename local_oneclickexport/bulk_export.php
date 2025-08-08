<?php

/**
 * Bulk export page for the OneClickExport plugin.
 *
 * This page allows users to select multiple courses and initiate a bulk export operation.
 *
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->libdir . '/adminlib.php');

require_once($CFG->dirroot . '/local/oneclickexport/classes/form/bulk_export_form.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/task/generate_mbz.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');

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

    $unique = 'export_' . $USER->id . '_' . time();
    $tempdir = make_backup_temp_directory($unique, true);
    
    if (!is_dir($tempdir) || !is_writable($tempdir)) {
        throw new moodle_exception('tempdirnotwritable', 'local_oneclickexport', '', $tempdir);
    }

    $bulklogid = local_oneclickexport_logging::log_bulk_export_start($USER->id, $data->courses);

    $backup_settings = local_oneclickexport_get_backup_settings($data);

    $common_data = (object)[
        'userid' => $USER->id,
        'bulklogid' => $bulklogid,
        'tempdir' => $tempdir,
        'settings' => (object)$backup_settings
    ];

    foreach ($data->courses as $courseid) {
        $task = new \local_oneclickexport\task\generate_mbz();
        $custom_data = clone $common_data;
        $custom_data->courseid = $courseid;
        $task->set_custom_data($custom_data);
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
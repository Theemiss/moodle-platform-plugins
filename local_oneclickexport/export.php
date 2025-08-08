<?php

/**
 * Export page for the OneClickExport plugin.
 * This page handles the export of a single course to an MBZ file.
 *
 * @package    local_oneclickexport
 * @category   export
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');

$courseid = required_param('id', PARAM_INT);
$course = get_course($courseid);
require_login($course);
$context = context_course::instance($courseid);
require_capability('local/oneclickexport:export', $context);

$logid = local_oneclickexport_logging::log_export_start($courseid, $USER->id, 'single');

$task = new \local_oneclickexport\task\generate_single_mbz();
$task->set_custom_data((object)[
    'courseid' => $courseid,
    'userid' => $USER->id,
    'logid' => $logid,
    'settings' => []
]);
\core\task\manager::queue_adhoc_task($task);

$statusurl = new moodle_url('/local/oneclickexport/single_export_status.php', ['id' => $logid]);
redirect($statusurl, get_string('export_queued', 'local_oneclickexport'), 3);

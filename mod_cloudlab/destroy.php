<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/cloudlab/locallib.php');

$labid = required_param('labid', PARAM_INT); // The lab ID
$userid = required_param('userid', PARAM_INT); // The user ID
$courseid = required_param('courseid', PARAM_INT); // The course ID
$id = required_param('id', PARAM_INT); // The activity ID
// Check if the current user has the required permissions to destroy the lab session
require_login($courseid);
$PAGE->set_url(new moodle_url('/mod/cloudlab/destroy.php', array('labid' => $labid, 'userid' => $userid, 'courseid' => $courseid)));
$context = context_course::instance($courseid);
$can_manage = has_capability('mod/cloudlab:view', $context);

if (!$can_manage) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('permissiondenied', 'mod_cloudlab'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}

// Call the function to destroy the lab session
$response = destroy_lab_session($labid, $userid, $courseid);

if ($response['status'] === 'success') {
    redirect(new moodle_url('/mod/cloudlab/view.php', array('id' => $id)), $response['message'], null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification($response['message'], 'notifyproblem');
    echo $OUTPUT->footer();
}
?>

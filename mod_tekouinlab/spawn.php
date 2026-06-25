<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php');

// Get parameters
$id = required_param('id', PARAM_INT); // Course Module ID
$userid = $USER->id;

// Get course, course module, and tekouinlab instance information
$cm = get_coursemodule_from_id('tekouinlab', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tekouinlab = $DB->get_record('tekouinlab', array('id' => $cm->instance), '*', MUST_EXIST);

// Check if the user is logged in and has the correct capability
require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/tekouinlab:view', $context);

// Set the page URL
$PAGE->set_url('/mod/tekouinlab/spawn.php', array('id' => $cm->id));
$PAGE->set_context($context);
$PAGE->set_title($course->fullname);
$PAGE->set_heading($course->fullname);

// Call the API function to spawn the lab session
$spawn_result = tekouinlab_spawn_session($tekouinlab, $userid, $course->id);

// Output page header
echo $OUTPUT->header();

if ($spawn_result['status'] == 'error') {
    echo $OUTPUT->notification($spawn_result['message'], 'notifyerror');
} else {
    echo $OUTPUT->notification($spawn_result['message'], 'notifysuccess');
}

// Redirect to the lab session's view page
$session_url = new moodle_url('/mod/tekouinlab/view.php', array('id' => $cm->id));
redirect($session_url);

echo $OUTPUT->footer();

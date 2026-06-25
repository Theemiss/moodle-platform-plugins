<?php
require_once('../../config.php');
global $DB, $CFG;

// Get data from the external lab.
$userid = required_param('userid', PARAM_INT); // Moodle user ID.
$grade = required_param('grade', PARAM_FLOAT); // Grade value.
$feedback = optional_param('feedback', '', PARAM_TEXT); // Optional feedback.

// Ensure the user and activity exist.
if (!$user = $DB->get_record('user', ['id' => $userid])) {
    throw new moodle_exception('invaliduserid', 'tekouinvm');
}

if (!$tekouinvm = $DB->get_record('tekouinvm', ['id' => $id])) {
    throw new moodle_exception('invalidactivityid', 'tekouinvm');
}

// Prepare the grade data.
$grades = [
    $userid => [
        'userid' => $userid,
        'rawgrade' => $grade,
        'feedback' => $feedback,
    ],
];

// Update the grade in the gradebook.
$result = grade_update('mod/tekouinvm', $tekouinvm->course, 'mod', 'tekouinvm', $tekouinvm->id, 0, $grades);

if ($result === GRADE_UPDATE_OK) {
    echo 'Grade updated successfully.';
} else {
    echo 'Failed to update grade.';
}
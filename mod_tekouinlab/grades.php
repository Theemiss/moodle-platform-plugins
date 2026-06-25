<?php

require_once('../../config.php');
require_once($CFG->libdir.'/gradelib.php');

$cmid = required_param('id', PARAM_INT); // Course module ID
$cm = get_coursemodule_from_id('tekouinlab', $cmid);
$context = context_module::instance($cm->id);

require_login($cm->course, false, $cm);

if (!has_capability('gradereport/grader:view', $context)) {
    print_error('nopermission', 'error');
}

$PAGE->set_url('/mod/tekouinlab/grades.php', ['id' => $cmid]);
$PAGE->set_context($context);
$PAGE->set_title(get_string('viewgrades', 'mod_tekouinlab'));

echo $OUTPUT->header();

// Fetch and display the grades for the lab here (depending on your grading setup)
// Fetch the grades for the lab
$grades = fetch_lab_grades($cm->instance);

// Check if there are any grades
if ($grades) {
    foreach ($grades as $grade) {
        echo 'User ID: ' . $grade->userid . ' - Grade: ' . $grade->rawgrade . '<br>';
    }
} else {
    echo 'No grades available for this lab.';
}
echo $OUTPUT->footer();

<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Ensure this path is correct.

$id = optional_param('id', 0, PARAM_INT); // Course module ID

if ($id) {
    $cm = get_coursemodule_from_id('cloudlab', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cloudlab = $DB->get_record('cloudlab', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('invalidaccessparameter');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('gradereport/grader:view', $context);

$PAGE->set_url('/mod/cloudlab/all_labs.php', array('id' => $cm->id));
$PAGE->set_title(format_string($cloudlab->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Display the Platform Lab activity.
echo $OUTPUT->heading($cloudlab->name);

if (trim(strip_tags($cloudlab->intro))) {
    echo $OUTPUT->box(format_module_intro('cloudlab', $cloudlab, $cm->id), 'generalbox mod_introbox', 'cloudlabintro');
}

// Fetch and display all student labs for the current cloudlabid.
echo $OUTPUT->heading('All Running Labs');

$studentlabs = mod_cloudlab_get_all_student_labs($cloudlab->id);

if ($studentlabs) {
    $table = new html_table();
    $table->head = array('Student Name', 'Status', 'Start Time', 'Duration', 'Lab URL');
    $table->align = array('left', 'left', 'left', 'left', 'left');

    foreach ($studentlabs as $studentlab) {
        $studentname = fullname($DB->get_record('user', array('id' => $studentlab->user)));
        $table->data[] = array(
            $studentname,
            $studentlab->status,
            userdate($studentlab->start_time),
            $studentlab->duration . ' minutes',
            $studentlab->url ? html_writer::link($studentlab->url, $studentlab->url) : 'N/A'
        );
    }

    echo html_writer::table($table);
} else {
    echo $OUTPUT->box(get_string('nostudentlabsfound', 'cloudlab'), 'generalbox', 'nostudentlabsfound');
}

echo $OUTPUT->footer();
<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Ensure this path is correct.

$id = optional_param('id', 0, PARAM_INT); // Course module ID

if ($id) {
    $cm = get_coursemodule_from_id('tekouinlab', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tekouinlab = $DB->get_record('tekouinlab', array('id' => $cm->instance), '*', MUST_EXIST);
} else {
    print_error('invalidaccessparameter');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('gradereport/grader:view', $context);

$PAGE->set_url('/mod/tekouinlab/all_labs.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tekouinlab->name));
$PAGE->set_heading(format_string($course->fullname));

echo $OUTPUT->header();

// Display the Tekouin Lab activity.
echo $OUTPUT->heading($tekouinlab->name);

if (trim(strip_tags($tekouinlab->intro))) {
    echo $OUTPUT->box(format_module_intro('tekouinlab', $tekouinlab, $cm->id), 'generalbox mod_introbox', 'tekouinlabintro');
}

// Fetch and display all student labs for the current tekouinlabid.
echo $OUTPUT->heading('All Running Labs');

$studentlabs = mod_tekouinlab_get_all_student_labs($tekouinlab->id);

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
    echo $OUTPUT->box(get_string('nostudentlabsfound', 'tekouinlab'), 'generalbox', 'nostudentlabsfound');
}

echo $OUTPUT->footer();
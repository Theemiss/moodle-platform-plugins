<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Ensure this path is correct.

$id = optional_param('id', 0, PARAM_INT); // Course module ID
$t  = optional_param('t', 0, PARAM_INT);  // Tekouin VM instance ID

if ($id) {
    $cm = get_coursemodule_from_id('tekouinvm', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tekouinvm = $DB->get_record('tekouinvm', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $tekouinvm = $DB->get_record('tekouinvm', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $tekouinvm->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('tekouinvm', $tekouinvm->id, $course->id, false, MUST_EXIST);
} else {
    print_error('invalidaccessparameter');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/tekouinvm:view', $context);

echo $OUTPUT->heading($tekouinvm->name);

$template = $tekouinvm->template;
$userid = $USER->id;

$PAGE->set_url('/mod/tekouinvm/view.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'mod_tekouinvm'));
$PAGE->set_heading(get_string('pluginname', 'mod_tekouinvm'));

echo $OUTPUT->header();

// Check if the user has a running VM.
$vm_status = check_student_vm($userid);

if ($vm_status && isset($vm_status['status']) && $vm_status['status'] === 'running') {
    echo html_writer::tag('h3', get_string('vm_details', 'mod_tekouinvm'));
    echo html_writer::tag('p', get_string('vm_status', 'mod_tekouinvm') . ': ' . $vm_status['status']);
    $orgid = get_config('local_tekouin', 'apikey');

    // VM URL with org_id and user_id as query parameters
    $vm_url = new moodle_url($vm_status['url'], [
        'token' => $orgid, // Use course ID as org_id
    ]);

    // Button to open the VM in a new tab
    echo html_writer::tag('p', 
        html_writer::link(
            $vm_url, 
            get_string('open_vm', 'mod_tekouinvm'),
            array('class' => 'btn btn-success', 'target' => '_blank')
        )
    );

    // Destroy button
    $destroy_url = new moodle_url('/mod/tekouinvm/destroy.php', ['userid' => $userid, 'id' => $id, 'courseid' => $course->id]);
    echo html_writer::tag('p', 
        html_writer::link(
            $destroy_url, 
            get_string('destroy_vm', 'mod_tekouinvm'),
            array('class' => 'btn btn-danger')
        )
    );
} else {
    echo html_writer::tag('p', get_string('no_vm_found', 'mod_tekouinvm'));

    $spawn_url = new moodle_url('/mod/tekouinvm/spawn.php', ['id' => $cm->id]);
    echo html_writer::tag('p', 
        html_writer::link(
            $spawn_url, 
            get_string('spawn_vm', 'mod_tekouinvm'),
            array('class' => 'btn btn-primary')
        )
    );
}

echo $OUTPUT->footer();
?>
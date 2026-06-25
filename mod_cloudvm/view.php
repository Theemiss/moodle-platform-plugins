<?php
// This file is part of Moodle - http://moodle.org/

require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Ensure this path is correct.

$id = optional_param('id', 0, PARAM_INT); // Course module ID
$t  = optional_param('t', 0, PARAM_INT);  // Cloud VM instance ID

if ($id) {
    $cm = get_coursemodule_from_id('cloudvm', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $cloudvm = $DB->get_record('cloudvm', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $cloudvm = $DB->get_record('cloudvm', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cloudvm->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('cloudvm', $cloudvm->id, $course->id, false, MUST_EXIST);
} else {
    print_error('invalidaccessparameter');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/cloudvm:view', $context);

echo $OUTPUT->heading($cloudvm->name);

$template = $cloudvm->template;
$userid = $USER->id;

$PAGE->set_url('/mod/cloudvm/view.php', array('id' => $id));
$PAGE->set_title(get_string('pluginname', 'mod_cloudvm'));
$PAGE->set_heading(get_string('pluginname', 'mod_cloudvm'));

echo $OUTPUT->header();

// Check if the user has a running VM.
$vm_status = check_student_vm($userid);

if ($vm_status && isset($vm_status['status']) && $vm_status['status'] === 'running') {
    echo html_writer::tag('h3', get_string('vm_details', 'mod_cloudvm'));
    echo html_writer::tag('p', get_string('vm_status', 'mod_cloudvm') . ': ' . $vm_status['status']);
    $orgid = get_config('local_platformbridge', 'apikey');

    // VM URL with org_id and user_id as query parameters
    $vm_url = new moodle_url($vm_status['url'], [
        'token' => $orgid, // Use course ID as org_id
    ]);

    // Button to open the VM in a new tab
    echo html_writer::tag('p', 
        html_writer::link(
            $vm_url, 
            get_string('open_vm', 'mod_cloudvm'),
            array('class' => 'btn btn-success', 'target' => '_blank')
        )
    );

    // Destroy button
    $destroy_url = new moodle_url('/mod/cloudvm/destroy.php', ['userid' => $userid, 'id' => $id, 'courseid' => $course->id]);
    echo html_writer::tag('p', 
        html_writer::link(
            $destroy_url, 
            get_string('destroy_vm', 'mod_cloudvm'),
            array('class' => 'btn btn-danger')
        )
    );
} else {
    echo html_writer::tag('p', get_string('no_vm_found', 'mod_cloudvm'));

    $spawn_url = new moodle_url('/mod/cloudvm/spawn.php', ['id' => $cm->id]);
    echo html_writer::tag('p', 
        html_writer::link(
            $spawn_url, 
            get_string('spawn_vm', 'mod_cloudvm'),
            array('class' => 'btn btn-primary')
        )
    );
}

echo $OUTPUT->footer();
?>
<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/cloudlab/locallib.php');

$id = required_param('id', PARAM_INT); // Course ID.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/cloudlab/index.php', array('id' => $id));
$PAGE->set_title($course->shortname . ': ' . get_string('modulenameplural', 'cloudlab'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'cloudlab'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'cloudlab'));

if (!$cloudlabs = get_all_instances_in_course('cloudlab', $course)) {
    notice(get_string('nolabsfound', 'cloudlab'), new moodle_url('/course/view.php', array('id' => $course->id)));
    exit;
}

$table = new html_table();
$table->head  = array(get_string('name'), get_string('description'));
$table->align = array('left', 'left');

foreach ($cloudlabs as $cloudlab) {
    $url = new moodle_url('/mod/cloudlab/view.php', array('id' => $cloudlab->coursemodule));
    $name = html_writer::link($url, $cloudlab->name);
    $description = format_module_intro('cloudlab', $cloudlab, $cloudlab->coursemodule);
    $table->data[] = array($name, $description);
}

echo html_writer::table($table);

echo $OUTPUT->footer();
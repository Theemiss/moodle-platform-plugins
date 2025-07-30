<?php
require_once('../../config.php');
require_once($CFG->dirroot . '/mod/tekouinlab/locallib.php');

$id = required_param('id', PARAM_INT); // Course ID.

if (!$course = $DB->get_record('course', array('id' => $id))) {
    print_error('invalidcourseid');
}

require_course_login($course);
$PAGE->set_pagelayout('incourse');
$PAGE->set_url('/mod/tekouinlab/index.php', array('id' => $id));
$PAGE->set_title($course->shortname . ': ' . get_string('modulenameplural', 'tekouinlab'));
$PAGE->set_heading($course->fullname);
$PAGE->navbar->add(get_string('modulenameplural', 'tekouinlab'));

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('modulenameplural', 'tekouinlab'));

if (!$tekouinlabs = get_all_instances_in_course('tekouinlab', $course)) {
    notice(get_string('nolabsfound', 'tekouinlab'), new moodle_url('/course/view.php', array('id' => $course->id)));
    exit;
}

$table = new html_table();
$table->head  = array(get_string('name'), get_string('description'));
$table->align = array('left', 'left');

foreach ($tekouinlabs as $tekouinlab) {
    $url = new moodle_url('/mod/tekouinlab/view.php', array('id' => $tekouinlab->coursemodule));
    $name = html_writer::link($url, $tekouinlab->name);
    $description = format_module_intro('tekouinlab', $tekouinlab, $tekouinlab->coursemodule);
    $table->data[] = array($name, $description);
}

echo html_writer::table($table);

echo $OUTPUT->footer();
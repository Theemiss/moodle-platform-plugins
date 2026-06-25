<?php
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Ensure this path is correct.

$id = optional_param('id', 0, PARAM_INT); // Course module ID
$t  = optional_param('t', 0, PARAM_INT);  // Tekouin Lab instance ID

if ($id) {
    $cm = get_coursemodule_from_id('tekouinlab', $id, 0, false, MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
    $tekouinlab = $DB->get_record('tekouinlab', array('id' => $cm->instance), '*', MUST_EXIST);
} else if ($t) {
    $tekouinlab = $DB->get_record('tekouinlab', array('id' => $t), '*', MUST_EXIST);
    $course = $DB->get_record('course', array('id' => $tekouinlab->course), '*', MUST_EXIST);
    $cm = get_coursemodule_from_instance('tekouinlab', $tekouinlab->id, $course->id, false, MUST_EXIST);
} else {
    print_error('invalidaccessparameter');
}

require_login($course, true, $cm);

$context = context_module::instance($cm->id);
require_capability('mod/tekouinlab:view', $context);

$params = array(
    'context' => $context,
    'objectid' => $tekouinlab->id
);

$PAGE->set_url('/mod/tekouinlab/view.php', array('id' => $cm->id));
$PAGE->set_title(format_string($tekouinlab->name));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->requires->css(new moodle_url('/mod/tekouinlab/styles.css'));

$all_labs_url = new moodle_url('/mod/tekouinlab/all_labs.php', array('id' => $cm->id));

echo $OUTPUT->header();
echo $OUTPUT->heading($tekouinlab->name);

$labid = $tekouinlab->tekouinlabid;
$userid = $USER->id;

if (!check_student_lab($labid, $userid, $course->id)) {
    echo $OUTPUT->notification(get_string('accessdenied', 'mod_tekouinlab'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
} else {
    $studentlab = mod_tekouinlab_get_lab_details($labid, $userid);

    if ($studentlab) {
        echo $OUTPUT->box_start('generalbox studentlabdetails p-4 shadow-sm bg-light');
        echo html_writer::tag('h3', get_string('yourlabsession', 'mod_tekouinlab'), ['class' => 'fw-bold mb-3 text-primary']);
        echo html_writer::tag('p', '<strong>' . get_string('description', 'mod_tekouinlab') . ':</strong> ' . $studentlab->description, ['class' => 'mb-2']);

        echo html_writer::tag('p', '<strong>' . get_string('status', 'mod_tekouinlab') . ':</strong> ' . $studentlab->status, ['class' => 'mb-2']);
        echo html_writer::tag('p', '<strong>' . get_string('starttime', 'mod_tekouinlab') . ':</strong> ' . userdate($studentlab->start_time), ['class' => 'mb-2']);
        echo html_writer::tag('p', '<strong>' . get_string('duration', 'mod_tekouinlab') . ':</strong> ' . $studentlab->duration . ' minutes', ['class' => 'mb-3']);
        
        if ($studentlab->url) {
            echo html_writer::tag('p', '<strong>' . get_string('laburl', 'mod_tekouinlab') . ':</strong> ' . 
                html_writer::link($studentlab->url, $studentlab->url, ['class' => 'text-decoration-none text-info']), ['class' => 'mb-3']);
        }
        
        $destroyurl = new moodle_url('/mod/tekouinlab/destroy.php', ['labid' => $labid, 'userid' => $userid, 'courseid' => $course->id, 'id' => $id]);
        echo html_writer::tag('div', 
            html_writer::link($destroyurl, get_string('destroylab', 'mod_tekouinlab'), [
                'class' => 'btn btn-danger',
                'onclick' => 'return confirm("' . get_string('confirmdestroy', 'mod_tekouinlab') . '")'
            ]), 
            ['class' => 'text-end']
        );
        
        echo $OUTPUT->box_end();

        // Only show the Submit Flag form if the lab exists
        $flagform = new moodle_url('/mod/tekouinlab/submit_flag.php');
        echo html_writer::start_tag('form', ['method' => 'get', 'action' => $flagform, 'class' => 'mt-4']);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'labid', 'value' => $labid]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'userid', 'value' => $userid]);
        echo html_writer::empty_tag('input', ['type' => 'hidden', 'name' => 'id', 'value' => $cm->id]);
        
        echo html_writer::start_tag('div', ['class' => 'mb-3']);
        echo html_writer::tag('h4', get_string('flag', 'mod_tekouinlab'), ['class' => 'form-label fw-bold']);
        echo html_writer::start_tag('div', ['id' => 'flag_inputs', 'class' => 'mb-2']);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('div');

        echo html_writer::start_tag('div', ['class' => 'd-flex gap-2']);
        echo html_writer::tag('button', get_string('addflag', 'mod_tekouinlab'), [
            'type' => 'button',
            'id' => 'add_flag_button',
            'class' => 'btn btn-outline-primary'
        ]);
        echo html_writer::empty_tag('input', [
            'type' => 'submit',
            'value' => get_string('submitflag', 'mod_tekouinlab'),
            'class' => 'btn btn-primary'
        ]);
        echo html_writer::end_tag('div');
        echo html_writer::end_tag('form');
    } else {
        echo $OUTPUT->box_start('generalbox text-center p-4 shadow-sm bg-light');
        echo html_writer::tag('p', get_string('nostudentlabfound', 'mod_tekouinlab'), ['class' => 'alert alert-warning']);
        
        $spawnurl = new moodle_url('/mod/tekouinlab/spawn.php', ['id' => $cm->id]);
        echo html_writer::tag('div', 
            html_writer::link($spawnurl, get_string('spawnlab', 'mod_tekouinlab'), [
                'class' => 'btn btn-success'
            ]), 
            ['class' => 'text-center']
        );
        echo $OUTPUT->box_end();
    }
}
?>

<script type="text/javascript">
    let flagCounter = 1;
    document.getElementById('add_flag_button').addEventListener('click', function() {
        const flagContainer = document.getElementById('flag_inputs');
        const newInput = document.createElement('input');
        newInput.type = 'text';
        newInput.name = `flag_${flagCounter}`;
        newInput.placeholder = `Flag ${flagCounter}`;
        newInput.classList.add('form-control', 'mb-2'); // Bootstrap classes
        flagContainer.appendChild(newInput);
        flagCounter++;
    });
</script>

<?php
echo $OUTPUT->footer();
?>
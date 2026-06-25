<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

require_once('../../config.php');
require_once($CFG->dirroot . '/mod/tekouinvm/locallib.php');
$id = required_param('id', PARAM_INT); // Course Module ID
$userid = $USER->id;
$cm = get_coursemodule_from_id('tekouinvm', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$tekouinvm = $DB->get_record('tekouinvm', array('id' => $cm->instance), '*', MUST_EXIST);


require_login($course, true, $cm);
$context = context_module::instance($cm->id);
$context = context_module::instance($cm->id);
require_capability('mod/tekouinvm:view', $context);

$PAGE->set_url('/mod/tekouinvm/spawn.php', array('id' => $cm->id));
$PAGE->set_context($context);

$PAGE->set_title(get_string('spawn_vm', 'mod_tekouinvm'));
$PAGE->set_heading(get_string('spawn_vm', 'mod_tekouinvm'));


// Spawn a new VM session.
$result = spawn_vm_session($userid);

if ($result['status'] == 'success') {
    redirect(new moodle_url('/mod/tekouinvm/view.php', array('id' => $id)), $result['message'], null, \core\output\notification::NOTIFY_SUCCESS);

} else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification($result['message'], 'vm_spawn_error');
    echo $OUTPUT->footer();
}





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
require_once($CFG->dirroot . '/mod/cloudvm/locallib.php');
$userid = required_param('userid', PARAM_INT); // The user ID
$courseid = required_param('courseid', PARAM_INT); // The course ID
$id = required_param('id', PARAM_INT); // The activity ID
// Check if the current user has the required permissions to destroy the lab session
require_login($courseid);

$PAGE->set_url('/mod/cloudvm/destroy.php', array('id' => $id, 'userid' => $userid, 'courseid' => $courseid));
$context = context_course::instance($courseid);
$can_manage = has_capability('mod/cloudlab:view', $context);

$PAGE->set_title(get_string('destroy_vm', 'mod_cloudvm'));
$PAGE->set_heading(get_string('destroy_vm', 'mod_cloudvm'));

if (!$can_manage) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('permissiondenied', 'mod_cloudlab'), 'notifyproblem');
    echo $OUTPUT->footer();
    exit;
}




// Destroy the VM session.
$response = destroy_vm_session( $userid);


if ($response['status'] === 'success') {
    redirect(new moodle_url('/mod/cloudvm/view.php', array('id' => $id)), $response['message'], null, \core\output\notification::NOTIFY_SUCCESS);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->notification($response['message'], 'notifyproblem');
    echo $OUTPUT->footer();
}
?>

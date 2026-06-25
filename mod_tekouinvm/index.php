<?php

require_once('../../config.php');

$id = required_param('id', PARAM_INT);
$userid = $USER->id;

if (!$cm = get_coursemodule_from_id('tekouinvm', $id)) {
    throw new moodle_exception('invalidcoursemodule');
}

if (!$course = $DB->get_record('course', ['id' => $cm->course])) {
    throw new moodle_exception('coursemisconf');
}

if (!$tekouinvm = $DB->get_record('tekouinvm', ['id' => $cm->instance])) {
    throw new moodle_exception('invalidid', 'tekouinvm');
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/tekouinvm/view.php', ['id' => $id]);
$PAGE->set_title($tekouinvm->name);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($tekouinvm->name));

echo format_text($tekouinvm->intro, FORMAT_HTML);

// Check if a VM exists for the user in this course
$vm_data = tekouinvm_get_or_provision_vm($userid, $course->id, $tekouinvm->template);

if ($vm_data && isset($vm_data['status']) && isset($vm_data['vm_url'])) {
    echo html_writer::tag('p', 'VM Status: ' . format_string($vm_data['status']));
    echo html_writer::link($vm_data['vm_url'], get_string('accessvm', 'tekouinvm'), ['target' => '_blank', 'class' => 'btn btn-primary']);
} else {
    echo $OUTPUT->notification(get_string('vmprovisionfail', 'tekouinvm'), 'error');
}

echo $OUTPUT->footer();

function tekouinvm_get_or_provision_vm($userid, $courseid, $template) {
    global $CFG;

    $cache = cache::make('mod_tekouinvm', 'vmstatus');
    $cache_key = "vmstatus_{$userid}_{$courseid}";
    $vm_data = $cache->get($cache_key);

    if (!$vm_data) {
        $vm_data = tekouinvm_call_api('provision_vm', [
            'user_id' => $userid,
            'course_id' => $courseid,
            'template' => $template,
        ]);

        if ($vm_data) {
            $cache->set($cache_key, $vm_data, 300);
        }
    }

    return $vm_data;
}

function tekouinvm_call_api($endpoint, $data) {
    global $CFG;

    $api_url = get_config('local_tekouin', 'apiurl') . "/api/$endpoint/";
    $api_key = get_config('local_tekouin', 'apikey');
    $org_id = get_config('local_tekouin', 'orgid');

    $data['org_id'] = $org_id;

    $options = array(
        'http' => array(
            'header'  => "Content-type: application/json\r\nAuthorization: Bearer $api_key\r\n",
            'method'  => 'POST',
            'content' => json_encode($data),
        ),
    );

    $context = stream_context_create($options);
    $result = @file_get_contents($api_url, false, $context);

    if ($result === FALSE) {
        debugging("API call to $endpoint failed: " . error_get_last()['message'], DEBUG_DEVELOPER);
        return null;
    }

    return json_decode($result, true);
}

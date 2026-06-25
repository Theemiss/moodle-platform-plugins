<?php
// Include necessary Moodle files
require_once(__DIR__ . '/../../config.php');
require_once(__DIR__ . '/locallib.php'); // Make sure to include any necessary helper functions

// Function to fetch API configurations
function cloudlab_get_api_config() {
    global $CFG;

    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    // Check if the necessary configurations are available
    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        return null;
    }

    return [
        'url' => $apiurl,
        'orgid' => $orgid,
        'apikey' => $apikey,
    ];
}

// Get the course module and the lab instance
$id = required_param('id', PARAM_INT);  // Course module ID
$cm = get_coursemodule_from_id('cloudlab', $id, 0, false, MUST_EXIST);
$course = $DB->get_record('course', array('id' => $cm->course), '*', MUST_EXIST);
$cloudlab = $DB->get_record('cloudlab', array('id' => $cm->instance), '*', MUST_EXIST);

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/cloudlab:view', $context);

// Check if the user is submitting flags
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get all the submitted flags
    $flags = [];
    foreach ($_POST as $key => $value) {
        // Check if the key matches the format 'flag_X'
        if (preg_match('/^flag_(\d+)$/', $key, $matches)) {
            // Add flag value to the array
            $flags[] = trim($value);
        }
    }

    // Handle the flag submission
    if (!empty($flags)) {
        // Get API configuration
        $apiConfig = cloudlab_get_api_config();

        if ($apiConfig) {
            $url = $apiConfig['url'] . "/submit_flags/";
            $data = [
                'user_id' => $USER->id,
                'flags' => $flags,
                'lab_id' => $cloudlab->id,
                'org_id' => $apiConfig['orgid'],
                'api_key' => $apiConfig['apikey'],
                'timestamp' => time(), // Add timestamp if needed
            ];

            // Use Moodle's cURL wrapper to send the data to the Django backend
            $curl = new curl();
            $response = $curl->post($url, $data);

            // Check for errors in the request
            if ($curl->get_errno()) {
                echo $OUTPUT->notification(get_string('submitflagfailed', 'mod_cloudlab'), 'error');
            } else {
                // Handle the response (optional)
                $response_data = json_decode($response);
                if (isset($response_data->status) && $response_data->status === 'success') {
                    echo $OUTPUT->notification(get_string('submitflagsuccess', 'mod_cloudlab'), 'success');
                } else {
                    echo $OUTPUT->notification(get_string('submitflagfailed', 'mod_cloudlab'), 'error');
                }
            }
        } else {
            echo $OUTPUT->notification(get_string('api_configuration_error', 'mod_cloudlab'), 'error');
        }
    } else {
        echo $OUTPUT->notification(get_string('noflagsubmitted', 'mod_cloudlab'), 'error');
    }
} else {
    // If the form wasn't submitted via POST, just redirect back to the lab view page
    redirect(new moodle_url('/mod/cloudlab/view.php', array('id' => $cm->id)));
}

// Display the footer
echo $OUTPUT->footer();
?>

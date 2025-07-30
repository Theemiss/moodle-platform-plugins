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

defined('MOODLE_INTERNAL') || die();

/**
 * Send student lab status request to the Django API.
 *
 * @param int $labid The lab ID.
 * @param int $userid The user ID.
 * @param int $courseid The course ID.
 * @return mixed The response from the Django API, or false if an error occurs.
 */
function check_student_vm( $userid) {
    $apiurl = get_config('local_tekouin', 'apiurl'); // Django API URL
    $apikey = get_config('local_tekouin', 'apikey');
    $orgid = get_config('local_tekouin', 'orgid');

    if (empty($apiurl) || empty($apikey)) {
        return false;
    }

    // Construct the API URL to check the student's VM status.
    $url = "{$apiurl}/vms/{$userid}/?api_key={$apikey}&org_id={$orgid}";

    // Use Moodle's curl wrapper to make the API request.
    $curl = new curl();
    $response = $curl->get($url);

    if ($curl->get_errno()) {
        return false;
    }

    // Decode the response from the Django API.
    $data = json_decode($response, true);
    if (isset($data['status'])) {
        return $data; // Return the lab status details if present
    }

    return false;
}

/**
 * Spawn a new VM session for a student.
 *
 * @param int $labid The lab ID.
 * @param int $userid The user ID.
 * @param int $courseid The course ID.
 * @return array The result of the operation.
 */
function spawn_vm_session($userid) {
    $apiurl = get_config('local_tekouin', 'apiurl'); // Django API URL
    $apikey = get_config('local_tekouin', 'apikey');
    $org_id = get_config('local_tekouin', 'orgid');

    if (empty($apiurl) || empty($apikey)) {
        return [
            'status' => 'error',
            'message' => get_string('api_config_error', 'mod_tekouinvm'),
        ];
    }

    // Construct the API URL to spawn a VM session.
    $url = "{$apiurl}/vms/spawn/";

    // Initialize cURL session for POST request.
    $curl = new curl();
    $data = [
        'user_id' => $userid,
        'org_id' => $org_id,
        'api_key' => $apikey,
        'name' => 'VS Code Session',
    ];

    // Prepare the POST data and send the request.
    $response = $curl->post($url, $data);

    if ($curl->get_errno()) {
        return [
            'status' => 'error',
            'message' => get_string('lab_spawn_error', 'mod_tekouinvm'),
        ];
    }

    // Decode the response from the Django API.
    $response_data = json_decode($response, true);

    // Check if the lab was successfully spawned.
    if (isset($response_data['status']) && $response_data['status'] === 'running') {
        return [
            'status' => 'success',
            'message' => get_string('labspawned', 'mod_tekouinvm'),
            'lab_url' => $response_data['url'], // The URL for the spawned lab
        ];
    }

    // If the response doesn't contain 'success', handle the error.
    return [
        'status' => 'error',
        'message' => get_string('lab_spawn_error', 'mod_tekouinvm'),
    ];
}

/**
 * Destroy a VM session.
 *
 * @param int $labid The lab ID.
 * @param int $userid The user ID.
 * @param int $courseid The course ID.
 * @return array The result of the operation.
 */
function destroy_vm_session($userid) {
    $apiurl = get_config('local_tekouin', 'apiurl'); // Django API URL
    $apikey = get_config('local_tekouin', 'apikey');
    $org_id = get_config('local_tekouin', 'orgid');

    if (empty($apiurl) || empty($apikey)) {
        return [
            'status' => 'error',
            'message' => get_string('api_config_error', 'mod_tekouinvm'),
        ];
    }

    // Construct the API URL to destroy the VM session.
    $url = "{$apiurl}/vms/{$userid}/destroy/?api_key={$apikey}&org_id={$org_id}";

    // Use Moodle's curl wrapper to make the API request.
    $curl = new curl();
    $response = $curl->delete($url);

    if ($curl->get_errno()) {
        return [
            'status' => 'error',
            'message' => get_string('lab_destroy_error', 'mod_tekouinvm'),
        ];
    }

    // Decode the response from the Django API.
    $response_data = json_decode($response, true);

    // Check if the lab session was destroyed.
    if (isset($response_data['status']) && $response_data['status'] === 'success') {
        return [
            'status' => 'success',
            'message' => get_string('labdestroyed', 'mod_tekouinvm'),
        ];
    }

    // If the response doesn't contain 'success', handle the error.
    return [
        'status' => 'error',
        'message' => get_string('lab_destroy_error', 'mod_tekouinvm'),
    ];
}
?>

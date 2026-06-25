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

/**
 * Local library for the Platform Lab module.
 *
 * @package    mod_cloudlab
 * @copyright  2024 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/** 
 * 
 * 
 * 
 * 
 */
function check_student_lab($labid, $userid, $courseid) {
    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        return false;
    }

    // Construct the API URL correctly
    $url = "{$apiurl}/labs/{$labid}/student/{$userid}/{$courseid}?org_id={$orgid}&api_key={$apikey}";

    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('Accept: application/json'));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code !== 200) {
        return false;
    }

    // Decode the response
    $data = json_decode($response, true);
    if (isset($data['status'])) {
        return $data; // Return the lab details if status is set
    }

    return false;
}

/**
 * Fetch lab details from the external API.
 *
 * @param int $labid The ID of the lab to fetch.
 * @return stdClass|null The lab details, or null if not found.
 */
function mod_cloudlab_get_lab_details($labid, $userid) {
    global $CFG;

    // Example: Fetch lab details from an external API.
    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        return null;
    }

    $url = $apiurl . "/labs/{$orgid}/{$labid}?&api_key={$apikey}&user_id={$userid}";

    // Debugging: Log the URL being requested.

    // Use Moodle's curl wrapper to make the API request.
    $curl = new curl();
    $response = $curl->get($url);

    if ($curl->get_errno()) {
        return null;
    }

    if ($response) {
        $labdetails = json_decode($response);
        if ($labdetails && !empty($labdetails->id)) {
            return $labdetails;
        } else {
        }
    } else {
    }

    return null;
}
/**
 * Fetch the list of labs from the external API.
 *
 * @return array|null Array of lab objects, or null if no labs are found.
 */
function mod_cloudlab_get_labs() {
    global $CFG;

    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        return null;
    }

    $url = $apiurl . "/labs/?org_id={$orgid}&api_key={$apikey}";

    // Debugging: Log the URL being requested.

    // Use Moodle's curl wrapper to make the API request.
    $curl = new curl();
    $response = $curl->get($url);

    if ($curl->get_errno()) {
        return null;
    }

    if ($response) {
        $labs = json_decode($response);
        if ($labs && is_array($labs)) {
            return $labs;
        } else {
        }
    } else {
    }

    return null;
}
/**
 * Fetch all student labs for a given lab ID.
 *
 * @param int $labid The ID of the lab.
 * @return array|null Array of student lab objects, or null if no labs are found.
 */
function mod_cloudlab_get_all_student_labs($labid) {
    global $CFG, $DB;

    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        debugging('API URL, Org ID, or API Key is missing.', DEBUG_DEVELOPER);
        return null;
    }

    $url = $apiurl . "/student-labs?lab_id={$labid}&org_id={$orgid}&api_key={$apikey}";

    // Debugging: Log the URL being requested.

    // Use Moodle's curl wrapper to make the API request.
    $curl = new curl();
    $response = $curl->get($url);

    if ($curl->get_errno()) {
        return null;
    }

    if ($response) {
        $studentlabs = json_decode($response);
        if ($studentlabs && is_array($studentlabs)) {
            return $studentlabs;
        } else {
        }
    } else {
    }

    return null;
}




/**
 * Spawns a new lab session for a student
 *
 * @param object $cloudlab The Platformlab instance
 * @param int $userid The ID of the user
 * @param int $courseid The ID of the course
 * @return array The result of the operation
 */
function cloudlab_spawn_session($cloudlab, $userid, $courseid) {
    global $USER;

    // Check if the lab status is fetched correctly
    $lab_status = check_student_lab($cloudlab->cloudlabid, $userid, $courseid);

    // Ensure that lab_status is not false
    if ($lab_status === false) {
        return [
            'status' => 'error',
            'message' => get_string('no_access_to_lab', 'mod_cloudlab'),
        ];
    }

    // Check the lab status and return respective error messages using get_string
    if ($lab_status['status'] === 'Active') {
        return [
            'status' => 'error',
            'message' => get_string('lab_already_active', 'mod_cloudlab'),
        ];
    }
    if ($lab_status['status'] === 'Completed') {
        return [
            'status' => 'error',
            'message' => get_string('lab_completed', 'mod_cloudlab'),
        ];
    }
    if ($lab_status['status'] === 'Not Started') {
        return [
            'status' => 'error',
            'message' => get_string('lab_not_started', 'mod_cloudlab'),
        ];
    }

    // API URL and necessary parameters
    $apiurl = get_config('local_platformbridge', 'apiurl');
    $orgid = get_config('local_platformbridge', 'orgid');
    $apikey = get_config('local_platformbridge', 'apikey');

    if (empty($apiurl) || empty($orgid) || empty($apikey)) {
        return [
            'status' => 'error',
            'message' => get_string('api_config_error', 'mod_cloudlab'),
        ];
    }

    // Construct the API URL for spawning a lab session
    $url = "{$apiurl}/labs/{$cloudlab->cloudlabid}/student/{$userid}/{$courseid}?org_id={$orgid}&api_key={$apikey}";


    // Prepare POST data
    $data = [
        'userid' => $userid,
        'courseid' => $courseid,
        'org_id' => $orgid,
        'api_key' => $apikey
    ];

    // Initialize cURL session for POST request
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Accept: application/json',
        'Content-Type: application/x-www-form-urlencoded'
    ]);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));

    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Check if the request was successful
    if ($http_code !== 200) {
        return [
            'status' => 'error',
            'message' => get_string('lab_spawn_error', 'mod_cloudlab')
        ];
    }

    // Decode the response
    $data = json_decode($response, true);

    // Check if the lab was successfully spawned
    if (isset($data['status']) && $data['status'] === 'success') {
        return [
            'status' => 'success',
            'message' => get_string('labspawned', 'mod_cloudlab'),
            'lab_url' => $data['lab_url'], // The URL for the spawned lab
        ];
    }

    // If the response doesn't contain 'success', handle the error
    return [
        'status' => 'error',
        'message' => get_string('lab_spawn_error', 'mod_cloudlab')
    ];
}


/**
 * Destroy a lab session.
 *
 * @param int $labid The lab ID.
 * @param int $userid The user ID.
 * @param int $courseid The course ID.
 * @return array An array containing the status and message.
 */
function destroy_lab_session($labid, $userid, $courseid) {
    global $DB, $USER;

    // Check if the lab session exists for the user and course
    if (!$labid) {
        return array(
            'status' => 'error',
            'message' => get_string('nosessionfound', 'mod_cloudlab')
        );
    }

    // Retrieve API configuration
    $api_url = get_config('local_platformbridge', 'apiurl');
    $org_id = get_config('local_platformbridge', 'orgid');
    $api_key = get_config('local_platformbridge', 'apikey');

    if (empty($api_url) || empty($org_id) || empty($api_key)) {
        return array(
            'status' => 'error',
            'message' => get_string('invalidconfig', 'mod_cloudlab')
        );
    }

    // Construct the API URL
    $url = "{$api_url}/labs/{$labid}/student/{$userid}/{$courseid}?org_id={$org_id}&api_key={$api_key}";

    // Initialize cURL session
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE"); // Set DELETE method
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Accept: application/json'
    ));

    // Execute the cURL request
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    // Handle API response
    if ($http_code !== 200) {
        return array(
            'status' => 'error',
            'message' => get_string('apierror', 'mod_cloudlab')
        );
    }

    return array(
        'status' => 'success',
        'message' => get_string('labdestroyed', 'mod_cloudlab')
    );
}

<?php
defined('MOODLE_INTERNAL') || die();

class local_tekouin_api_client {
    private $apiurl;
    private $apikey;
    private $orgid;

    public function __construct() {
        $this->apiurl = get_config('local_tekouin', 'apiurl');
        $this->apikey = get_config('local_tekouin', 'apikey');
        $this->orgid = get_config('local_tekouin', 'orgid');
    }

    /**
     * Check the status of the API.
     *
     * @return array
     */
    public function check_api_status() {
        return $this->make_request('/integration/status?org_id=' . $this->orgid);
    }

    /**
     * Get a list of available VMs.
     *
     * @return array
     */
    public function get_available_vms() {
        return $this->make_request('/integration/vms/status?org_id=' . $this->orgid);
    }

    /**
     * Get a list of available labs.
     *
     * @return array
     */
    public function get_available_labs() {
        return $this->make_request('/integration/labs/status?org_id=' . $this->orgid);
    }

    /**
     * Make an API request.
     *
     * @param string $endpoint
     * @return array
     */
    private function make_request($endpoint) {
        $url = $this->apiurl . $endpoint;
        $headers = [
            'Authorization: Bearer ' . $this->apikey,
            'Content-Type: application/json',
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        $response = curl_exec($ch);
        curl_close($ch);

        return json_decode($response, true);
    }
}
<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Get the API URL from the plugin configuration.
 *
 * @return string
 */
function local_platformbridge_get_apiurl() {
    return get_config('local_platformbridge', 'apiurl');
}

/**
 * Get the API key from the plugin configuration.
 *
 * @return string
 */
function local_platformbridge_get_apikey() {
    return get_config('local_platformbridge', 'apikey');
}

/**
 * Get the organization ID from the plugin configuration.
 *
 * @return string
 */
function local_platformbridge_get_orgid() {
    return get_config('local_platformbridge', 'orgid');
}
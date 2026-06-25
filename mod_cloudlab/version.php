<?php
defined('MOODLE_INTERNAL') || die();

$plugin->version   = 202502018200; // YYYYMMDDXX (Year, Month, Day, Version)
$plugin->requires  = 2020061500; // Moodle 3.9 or higher
$plugin->component = 'mod_cloudlab'; // Plugin type and name
$plugin->maturity  = MATURITY_ALPHA; // Plugin maturity level
$plugin->release   = 'v2.0.0'; // Plugin release version
$plugin->dependencies = array(
    'local_platformbridge' => 2025021800, // The required plugin and its version.
);


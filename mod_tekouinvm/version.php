<?php

defined('MOODLE_INTERNAL') || die();

$plugin->component = 'mod_tekouinvm'; // The name of the plugin.
$plugin->version = 2025021820; // The current plugin version (YYYYMMDDXX).
$plugin->release = '2.0.0'; // Plugin release version.
$plugin->maturity = MATURITY_STABLE; // The maturity level of the plugin.
$plugin->requires = 2022041900; // Moodle version required (YYYYMMDDXX).
$plugin->dependencies = array(
    'local_tekouin' => 2025021800, // The required plugin and its version.
);

// Add other plugin details as necessary.

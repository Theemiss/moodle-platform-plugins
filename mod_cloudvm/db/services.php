<?php

defined( 'MOODLE_INTERNAL' ) || die();

$functions = [
        'mod_cloudvm_grade' => [
                'classname' => 'mod_cloudvm_webservice',
                'methodname' => 'submit_grade',
                'classpath' => 'mod/cloudvm/externallib.php',
                'description' => 'Grade Students in a Moodle Grade Book Item ',
                'requiredcapability' => 'mod/vpl:submit_grade',
                'type' => 'write',
        ],

];

$services = [
        'cloudvm web service' => [
                'functions' => [
                        'mod_cloudvm_grade',
                ],
                'shortname' => 'mod_cloudvm_grader',
                'restrictedusers' => 0,
                'enabled' => 0,
        ],
];
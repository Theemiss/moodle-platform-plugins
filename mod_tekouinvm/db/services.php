<?php

defined( 'MOODLE_INTERNAL' ) || die();

$functions = [
        'mod_tekouinvm_grade' => [
                'classname' => 'mod_tekouinvm_webservice',
                'methodname' => 'submit_grade',
                'classpath' => 'mod/tekouinvm/externallib.php',
                'description' => 'Grade Students in a Moodle Grade Book Item ',
                'requiredcapability' => 'mod/vpl:submit_grade',
                'type' => 'write',
        ],

];

$services = [
        'tekouinvm web service' => [
                'functions' => [
                        'mod_tekouinvm_grade',
                ],
                'shortname' => 'mod_tekouinvm_grader',
                'restrictedusers' => 0,
                'enabled' => 0,
        ],
];
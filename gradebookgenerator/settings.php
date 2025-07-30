<?php
defined('MOODLE_INTERNAL') || die();


$ADMIN->add('root', new admin_category('tool_gradebookgenerator',  get_string('pluginname', 'tool_gradebookgenerator')));

$ADMIN->add('tool_gradebookgenerator', new admin_externalpage('tool_gradebookgenerator_link', get_string('pluginname', 'tool_gradebookgenerator'),
        $CFG->wwwroot."/admin/tool/gradebookgenerator/index.php",
        'moodle/site:config'));

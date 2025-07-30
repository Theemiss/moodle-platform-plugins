<?php

require_once("$CFG->dirroot/course/moodleform_mod.php");

class mod_tekouinvm_mod_form extends moodleform_mod {
    public function definition() {
        $mform = $this->_form;

        // Add name field
        $mform->addElement('text', 'name', get_string('name', 'mod_tekouinvm'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        // Add intro field
        $this->standard_intro_elements();

        // Add template selection
        $mform->addElement('select', 'template', get_string('template', 'mod_tekouinvm'), [
            'vscode' => 'VS Code',
            'jupyter' => 'Jupyter Notebook'
        ]);
        $mform->setType('template', PARAM_ALPHANUMEXT);

        // Add standard course module elements
        $this->standard_coursemodule_elements();

        // Add action buttons
        $this->add_action_buttons();
    }
}
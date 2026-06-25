<?php
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php'); // Include the locallib.php file.

class mod_tekouinlab_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;

        // Name and description.
        $mform->addElement('text', 'name', get_string('tekouinlabname', 'tekouinlab'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description', 'tekouinlab'));

        // Placeholder for labs dropdown.
        $mform->addElement('select', 'tekouinlabid', get_string('selectlab', 'tekouinlab'), []);
        $mform->setType('tekouinlabid', PARAM_INT);
        $mform->addRule('tekouinlabid', null, 'required', null, 'client');

        // Fetch labs using locallib.php and populate the dropdown.
        $labs = mod_tekouinlab_get_labs(); // Use a new function in locallib.php to fetch labs.
        if ($labs) {
            foreach ($labs as $lab) {
                $mform->getElement('tekouinlabid')->addOption($lab->name, $lab->id);
            }
        } else {
            $mform->getElement('tekouinlabid')->addOption(get_string('nolabsfound', 'tekouinlab'), '');
        }

        // Standard Moodle fields.
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
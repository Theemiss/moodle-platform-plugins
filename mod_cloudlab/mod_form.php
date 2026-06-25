<?php
require_once($CFG->dirroot . '/course/moodleform_mod.php');
require_once(__DIR__ . '/locallib.php'); // Include the locallib.php file.

class mod_cloudlab_mod_form extends moodleform_mod {
    public function definition() {
        global $CFG, $PAGE;

        $mform = $this->_form;

        // Name and description.
        $mform->addElement('text', 'name', get_string('cloudlabname', 'cloudlab'), ['size' => '64']);
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('description', 'cloudlab'));

        // Placeholder for labs dropdown.
        $mform->addElement('select', 'cloudlabid', get_string('selectlab', 'cloudlab'), []);
        $mform->setType('cloudlabid', PARAM_INT);
        $mform->addRule('cloudlabid', null, 'required', null, 'client');

        // Fetch labs using locallib.php and populate the dropdown.
        $labs = mod_cloudlab_get_labs(); // Use a new function in locallib.php to fetch labs.
        if ($labs) {
            foreach ($labs as $lab) {
                $mform->getElement('cloudlabid')->addOption($lab->name, $lab->id);
            }
        } else {
            $mform->getElement('cloudlabid')->addOption(get_string('nolabsfound', 'cloudlab'), '');
        }

        // Standard Moodle fields.
        $this->standard_grading_coursemodule_elements();
        $this->standard_coursemodule_elements();
        $this->add_action_buttons();
    }
}
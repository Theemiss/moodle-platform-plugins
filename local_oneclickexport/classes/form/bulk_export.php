<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class local_oneclickexport_bulk_export_form extends moodleform {
    public function definition() {
        $mform = $this->_form;
        
        // Course selection
        $options = [
            'multiple' => true,
            'limittoenrolled' => true,
            'includefrontpage' => false
        ];
        $mform->addElement('course', 'courses', get_string('selectcourses', 'local_oneclickexport'), $options);
        $mform->addRule('courses', null, 'required');
        
        // Export options
        $mform->addElement('header', 'optionsheader', get_string('exportoptions', 'backup'));
        $mform->setExpanded('optionsheader', false);
        
        $mform->addElement('checkbox', 'includeusers', get_string('includeusers', 'backup'));
        $mform->addElement('checkbox', 'includecomments', get_string('includecomments', 'backup'));
        $mform->addElement('checkbox', 'includelogs', get_string('includelogs', 'backup'));
        
        // Submit buttons
        $this->add_action_buttons(true, get_string('startexport', 'local_oneclickexport'));
    }
}
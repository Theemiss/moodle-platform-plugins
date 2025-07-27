<?php
defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class local_oneclickexport_bulk_export_form extends moodleform {
    
    public function definition() {
        global $DB;
        
        $mform = $this->_form;
        
        // Course selection - only include visible courses
        $courses = $DB->get_records('course', ['visible' => 1], 'fullname', 'id,fullname,shortname');
        
        if (empty($courses)) {
            throw new moodle_exception('nocoursesavailable', 'local_oneclickexport');
        }
        
        $options = [];
        foreach ($courses as $course) {
            $options[$course->id] = format_string($course->fullname) . ' (' . $course->shortname . ')';
        }
        
        $select = $mform->addElement('select', 'courses', 
            get_string('selectcourses', 'local_oneclickexport'), 
            $options
        );
        $select->setMultiple(true);
        $mform->addRule('courses', get_string('required'), 'required', null, 'client');
        
        // Export options
        $mform->addElement('header', 'optionsheader', get_string('exportoptions', 'local_oneclickexport'));
        
        $mform->addElement('advcheckbox', 'includeusers', 
            '', 
            get_string('includeusers', 'local_oneclickexport')
        );
        $mform->setDefault('includeusers', 0);
        
        $mform->addElement('advcheckbox', 'includecomments', 
            '', 
            get_string('includecomments', 'local_oneclickexport')
        );
        $mform->setDefault('includecomments', 0);
        
        $mform->addElement('advcheckbox', 'includelogs', 
            '', 
            get_string('includelogs', 'local_oneclickexport')
        );
        $mform->setDefault('includelogs', 0);
        
        // Action buttons
        $this->add_action_buttons(true, get_string('exportcourses', 'local_oneclickexport'));
    }
    
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        
        if (empty($data['courses'])) {
            $errors['courses'] = get_string('selectatleastonecourse', 'local_oneclickexport');
        }
        
        return $errors;
    }
}
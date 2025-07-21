<?php
defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_task_generate_mbz extends \core\task\adhoc_task {
    public function execute() {
        global $CFG, $DB;
        
        require_once($CFG->dirroot.'/backup/util/includes/backup_includes.php');
        
        $data = $this->get_custom_data();
        $userid = $data->userid;
        $courseid = $data->courseid;
        
        $course = $DB->get_record('course', ['id' => $courseid], '*', MUST_EXIST);
        $user = $DB->get_record('user', ['id' => $userid], '*', MUST_EXIST);
        
        // Same backup logic as export.php but stores result for notification
        $bc = new backup_controller(
            backup::TYPE_1COURSE,
            $course->id,
            backup::FORMAT_MOODLE,
            backup::INTERACTIVE_NO,
            backup::MODE_GENERAL,
            $user->id
        );
        
        // Apply default settings
        $settings = $this->get_default_settings();
        foreach ($settings as $name => $value) {
            $bc->get_plan()->get_setting($name)->set_value($value);
        }
        
        $backupid = $bc->get_backupid();
        check_dir_exists($CFG->tempdir.'/backup/'.$backupid);
        
        $bc->execute_plan();
        $bc->destroy();
        
        // Store file info for notification
        $fs = get_file_storage();
        $files = $fs->get_area_files(
            context_system::instance()->id,
            'backup',
            'course',
            $backupid,
            'filename',
            false
        );
        
        if (!empty($files)) {
            $file = reset($files);
            $this->send_notification($user, $course, $file);
        }
        
        // Cleanup
        $fs->delete_area_files(context_system::instance()->id, 'backup', 'course', $backupid);
    }
    
    protected function get_default_settings() {
        return [
            'users' => 0,
            'anonymize' => 0,
            'role_assignments' => 0,
            'activities' => 1,
            'blocks' => 1,
            'filters' => 1,
            'comments' => 0,
            'completion_information' => 0,
            'logs' => 0,
            'histories' => 0
        ];
    }
    
    protected function send_notification($user, $course, $file) {
        $message = new \core\message\message();
        $message->component = 'local_oneclickexport';
        $message->name = 'exportcomplete';
        $message->userfrom = \core_user::get_noreply_user();
        $message->userto = $user;
        $message->subject = get_string('exportcomplete', 'local_oneclickexport', $course->fullname);
        $message->fullmessage = get_string('exportready', 'local_oneclickexport');
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = '<p>'.get_string('exportready', 'local_oneclickexport').'</p>';
        $message->smallmessage = get_string('exportsmall', 'local_oneclickexport');
        $message->contexturl = moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_itemid(),
            $file->get_filepath(),
            $file->get_filename(),
            false
        );
        $message->contexturlname = $file->get_filename();
        
        message_send($message);
    }
}
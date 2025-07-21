<?php
defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_dashboard_button implements renderable {
    public $courseid;
    public $context;
    
    public function __construct($courseid, $context) {
        $this->courseid = $courseid;
        $this->context = $context;
    }
    
    public function export_for_template(renderer_base $output) {
        if (!get_config('local_oneclickexport', 'showondashboard') ||
            !has_capability('local/oneclickexport:export', $this->context)) {
            return [];
        }
        
        return [
            'courseid' => $this->courseid,
            'exporturl' => (new moodle_url('/local/oneclickexport/export.php', ['id' => $this->courseid]))->out(false),
            'btntext' => get_string('exportcourse', 'local_oneclickexport'),
            'btnicon' => $output->pix_icon('i/export', '')
        ];
    }
}
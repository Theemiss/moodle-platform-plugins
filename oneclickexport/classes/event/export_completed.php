<?php
defined('MOODLE_INTERNAL') || die();

class local_oneclickexport_event_export_completed extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course';
    }
    
    public static function get_name() {
        return get_string('eventexportcompleted', 'local_oneclickexport');
    }
    
    public function get_description() {
        return "The one-click export for course with id {$this->objectid} was completed";
    }
}
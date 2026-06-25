<?php
defined('MOODLE_INTERNAL') || die();

/*  
*  TODO: Async event for export started
*  This event is triggered when a one-click export for a course is started.
*/
class local_oneclickexport_event_export_started extends \core\event\base
{
    protected function init()
    {
        $this->data['crud'] = 'r';
        $this->data['edulevel'] = self::LEVEL_TEACHING;
        $this->data['objecttable'] = 'course';
    }

    public static function get_name()
    {
        return get_string('eventexportstarted', 'local_oneclickexport');
    }

    public function get_description()
    {
        return "The user with id {$this->userid} started a one-click export for course with id {$this->objectid}";
    }
}

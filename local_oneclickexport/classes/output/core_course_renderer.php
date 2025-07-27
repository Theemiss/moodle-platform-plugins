<?php
namespace local_oneclickexport\output;

defined('MOODLE_INTERNAL') || die();

require_once($GLOBALS['CFG']->dirroot . '/course/renderer.php');

class core_course_renderer extends \core_course_renderer {
    public function course_header($course) {
        global $USER;

        $output = parent::course_header($course);

        if (!is_siteadmin($USER)) {
            return $output;
        }

        $url = new \moodle_url('/local/oneclickexport/export.php', ['id' => $course->id]);
        $button = \html_writer::link($url, '📦 Export Course', ['class' => 'btn btn-primary ml-3']);

        return $output . \html_writer::div($button, 'my-3');
    }
}

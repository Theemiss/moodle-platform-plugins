<?php
defined('MOODLE_INTERNAL') || die();

function local_oneclickexport_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('local/oneclickexport:export', $context)) {
        $url = new moodle_url('/local/oneclickexport/export.php', ['id' => $course->id]);
        $navigation->add(
            get_string('exportcourse', 'local_oneclickexport'),
            $url,
            navigation_node::TYPE_SETTING,
            null,
            'oneclickexport',
            new pix_icon('i/export', '')
        );
    }
}
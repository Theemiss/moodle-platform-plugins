<?php
defined('MOODLE_INTERNAL') || die();


function local_oneclickexport_extend_navigation_course($navigation, $course, $context) {
    if (get_config('local_oneclickexport', 'showinnavigation') &&
        has_capability('local/oneclickexport:export', $context)) {
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

function local_oneclickexport_course_card_actions(course_in_list $course) {
    global $PAGE;
    
    $context = context_course::instance($course->id);
    $button = new local_oneclickexport_dashboard_button($course->id, $context);
    
    $renderer = $PAGE->get_renderer('local_oneclickexport');
    return $renderer->render($button);
}
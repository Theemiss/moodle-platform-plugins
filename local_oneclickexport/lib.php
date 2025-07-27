<?php
defined('MOODLE_INTERNAL') || die();



function local_oneclickexport_get_renderer_override() {
    return [
        'core_course' => 'local_oneclickexport\\output\\core_course_renderer'
    ];
}

function local_oneclickexport_extend_navigation_course($navigation, $course, $context) {
    if (get_config('local_oneclickexport', 'showinnavigation') &&
        has_capability('local/oneclickexport:export', $context)) {
        global $PAGE, $OUTPUT;

        $url = new moodle_url('/local/oneclickexport/export.php', ['id' => $course->id]);
        if ($PAGE->pagetype == 'course-index') {
            $button = $OUTPUT->single_button($url, get_string('exportcourse', 'local_oneclickexport'));
            $PAGE->set_button($button . $PAGE->button);

        }

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

function local_oneclickexport_course_card_actions(course_in_list $course): ?renderable {
    global $PAGE;

    $context = context_course::instance($course->id);

    // Optional: capability check or siteadmin check
    if (!has_capability('local/oneclickexport:export', $context)) {
        return null;
    }

    // Renderer instance
    $renderer = $PAGE->get_renderer('local_oneclickexport');
    $url = new moodle_url('/local/oneclickexport/export.php', ['id' => $course->id]);

    // Render a styled button or link
    $button = html_writer::link($url, get_string('exportcourse', 'local_oneclickexport'), [
        'class' => 'btn btn-sm btn-outline-primary'
    ]);

    return new \core\output\notification($button, \core\output\notification::NOTIFY_INFO);
}

function local_oneclickexport_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($filearea === 'public_backups') {
        $itemid = array_shift($args);
        $filename = array_pop($args);
        $filepath = $args ? '/'.implode('/', $args).'/' : '/';
        
        $fs = get_file_storage();
        $file = $fs->get_file($context->id, 'local_oneclickexport', 'public_backups', $itemid, $filepath, $filename);
        
        if ($file) {
            send_stored_file($file, 0, 0, $forcedownload, $options);
        }
    }
    send_file_not_found();
}

function local_oneclickexport_before_footer() {
    global $PAGE, $COURSE, $OUTPUT;

    if ($PAGE->pagelayout === 'course' && !empty($COURSE) && $COURSE->id != SITEID) {
        $context = context_course::instance($COURSE->id);
        if (has_capability('local/oneclickexport:export', $context)) {
            $url = new moodle_url('/local/oneclickexport/export.php', ['id' => $COURSE->id]);

            echo html_writer::div(
                html_writer::link($url, get_string('exportcourse', 'local_oneclickexport'), [
                    'class' => 'btn btn-primary'
                ]),
                'text-center mt-3'
            );
        }
    }
}

function local_oneclickexport_get_course_export_url($courseid) {
    return new moodle_url('/local/oneclickexport/export.php', ['id' => $courseid]);
}
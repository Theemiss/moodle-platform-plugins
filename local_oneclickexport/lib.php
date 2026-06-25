<?php
defined('MOODLE_INTERNAL') || die();



/**
 * Extend the course navigation to include the OneClickExport button.
 *
 * This function adds an export button to the course navigation if the user has
 * the capability to export and the feature is enabled in the plugin settings.
 *
 * @param navigation_node $navigation The course navigation node.
 * @param stdClass $course The course object.
 * @param context $context The context of the course.
 */


function local_oneclickexport_extend_navigation_course($navigation, $course, $context)
{
    if (
        get_config('local_oneclickexport', 'showinnavigation') &&
        has_capability('local/oneclickexport:export', $context)
    ) {
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
            'oneclickexport'
        );
    }
}

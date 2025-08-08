<?php
namespace local_oneclickexport;

use core\hook\navigation\secondary_extend;


/**
 * Hook handlers for the local_oneclickexport plugin.
 * This class handles the course navigation hook to add export links.
 *
 * @package    local_oneclickexport
 * @category   hook_handlers
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
class hook_handlers
{
    public static function course_navigation(secondary_extend $hook): void
    {
        global $PAGE;
        if (!get_config('local_oneclickexport', 'showinnavigation')) {
            return;
        }
        $secondary = $hook->get_secondaryview();

        $course = $PAGE->course;
        $context = \context_course::instance($course->id);


        if (!has_capability('local/oneclickexport:export', $context)) {
            return;
        }

        $url = new \moodle_url('/local/oneclickexport/export.php', ['id' => $course->id]);
        $icon = new \pix_icon('i/export', '');
        $enabled = get_config('local_oneclickexport', 'showinnavigation');
        if (!empty($enabled)) {
            $secondary->add(
                get_string('exportcourse', 'local_oneclickexport'),
                $url,
                \navigation_node::TYPE_SETTING,
                null,
                'oneclickexport',
                $icon
            );
        }
    }
}

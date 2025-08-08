<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Hook definitions for the local_oneclickexport plugin.
 * This file defines hooks that can be used to extend Moodle's functionality.
 *
 * @package    local_oneclickexport
 * @category   hooks
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

$callbacks = [
    [
        'hook' => \core\hook\navigation\secondary_extend::class,    
        'callback' => \local_oneclickexport\hook_handlers::class . '::course_navigation',
        'priority' => 1000,
        'context' => 'Extend the course navigation to include the OneClickExport button'
    ],
];
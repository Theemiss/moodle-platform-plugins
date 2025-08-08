<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Scheduled task for cleaning up old export logs and files.
 * This task runs periodically to remove export records older than a specified retention period.
 *
 * @package    local_oneclickexport
 * @category   task
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

$tasks = [
    [
        'classname' => 'local_oneclickexport\task\cleanup_task',
        'blocking' => 0,
        'minute' => 'R',
        'hour' => '2',
        'day' => '*',
        'month' => '*',
        'dayofweek' => '*',
    ]
];

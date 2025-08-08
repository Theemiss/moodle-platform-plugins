<?php

namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');


/**
 * External API for exporting statistics of export operations. This class retrieves
 * various statistics related to export operations performed by the user.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class get_export_statistics extends \external_api
{
    public static function execute_parameters()
    {
        return new \external_function_parameters([
            'days' => new \external_value(PARAM_INT, 'Number of days to include', VALUE_DEFAULT, 30)
        ]);
    }

    public static function execute($days = 30)
    {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), [
            'days' => $days
        ]);

        $since = time() - ($params['days'] * 24 * 60 * 60);

        $stats = [
            'total' => 0,
            'completed' => 0,
            'failed' => 0,
            'processing' => 0,
            'bulk' => 0,
            'single' => 0,
            'totalsize' => 0,
            'courses' => []
        ];

        $sql = "SELECT status, exporttype, COUNT(*) as count, SUM(filesize) as size
                FROM {local_oneclickexport_log}
                WHERE userid = :userid AND timecreated > :since
                GROUP BY status, exporttype";

        $records = $DB->get_records_sql($sql, ['userid' => $USER->id, 'since' => $since]);

        foreach ($records as $record) {
            $stats['total'] += $record->count;
            $stats['totalsize'] += $record->size ?? 0;

            if ($record->status == 'completed') {
                $stats['completed'] += $record->count;
            } else if ($record->status == 'failed') {
                $stats['failed'] += $record->count;
            } else if ($record->status == 'processing') {
                $stats['processing'] += $record->count;
            }

            if ($record->exporttype == 'bulk') {
                $stats['bulk'] += $record->count;
            } else {
                $stats['single'] += $record->count;
            }
        }

        $sql = "SELECT l.courseid, c.shortname, COUNT(*) as count
                FROM {local_oneclickexport_log} l
                LEFT JOIN {course} c ON c.id = l.courseid
                WHERE l.userid = :userid AND l.timecreated > :since AND l.courseid > 0
                GROUP BY l.courseid, c.shortname
                ORDER BY count DESC
                LIMIT 5";

        $courses = $DB->get_records_sql($sql, ['userid' => $USER->id, 'since' => $since]);
        foreach ($courses as $course) {
            $stats['courses'][] = [
                'courseid' => $course->courseid,
                'shortname' => $course->shortname,
                'count' => $course->count
            ];
        }

        return $stats;
    }

    public static function execute_returns()
    {
        return new \external_single_structure([
            'total' => new \external_value(PARAM_INT, 'Total exports'),
            'completed' => new \external_value(PARAM_INT, 'Completed exports'),
            'failed' => new \external_value(PARAM_INT, 'Failed exports'),
            'processing' => new \external_value(PARAM_INT, 'Processing exports'),
            'bulk' => new \external_value(PARAM_INT, 'Bulk exports'),
            'single' => new \external_value(PARAM_INT, 'Single exports'),
            'totalsize' => new \external_value(PARAM_INT, 'Total size of all exports in bytes'),
            'courses' => new \external_multiple_structure(
                new \external_single_structure([
                    'courseid' => new \external_value(PARAM_INT, 'Course ID'),
                    'shortname' => new \external_value(PARAM_TEXT, 'Course short name'),
                    'count' => new \external_value(PARAM_INT, 'Number of exports')
                ]),
                'Most exported courses'
            )
        ]);
    }
}

<?php
namespace local_oneclickexport\external;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/externallib.php');
require_once($CFG->dirroot . '/local/oneclickexport/backup_service.php');
require_once($CFG->dirroot . '/local/oneclickexport/classes/logging.php');

/**
 * External API for exporting downloads of export operations. This class retrieves
 * the download URLs for completed exports, allowing users to download their exported files.
 *
 * @package    local_oneclickexport
 * @category   external
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

class get_export_downloads extends \external_api 
{
    public static function execute_parameters() {
        return new \external_function_parameters([
            'logid' => new \external_value(PARAM_INT, 'Export log ID')
        ]);
    }

    public static function execute($logid) {
        global $DB, $USER;

        $params = self::validate_parameters(self::execute_parameters(), ['logid' => $logid]);

        $export = $DB->get_record('local_oneclickexport_log', [
            'id' => $params['logid'],
            'userid' => $USER->id
        ]);
        
        if (!$export) {
            throw new \moodle_exception('invalidexportlog', 'local_oneclickexport');
        }

        $result = [];
        $fs = get_file_storage();
        
        if ($export->status == 'completed' && $export->fileid) {
            $file = $fs->get_file_by_id($export->fileid);
            if ($file && $file->get_filesize() > 0) {
                $result[] = [
                    'courseid' => $export->courseid, // Will be 0 for bulk exports
                    'downloadurl' => \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        $file->get_itemid(),
                        $file->get_filepath(),
                        $file->get_filename(),
                        true
                    )->out(false),
                    'filesize' => $file->get_filesize(),
                    'filename' => $file->get_filename(),
                    'exporttype' => $export->exporttype // 'single' or 'bulk'
                ];
            }
        }

        return $result;
    }

    public static function execute_returns() {
        return new \external_multiple_structure(
            new \external_single_structure([
                'courseid' => new \external_value(PARAM_INT, 'Course ID (0 for bulk exports)'),
                'downloadurl' => new \external_value(PARAM_URL, 'Download URL'),
                'filesize' => new \external_value(PARAM_INT, 'File size in bytes'),
                'filename' => new \external_value(PARAM_TEXT, 'File name'),
                'exporttype' => new \external_value(PARAM_TEXT, 'Type of export (single or bulk)')
            ])
        );
    }
}
<?php
defined('MOODLE_INTERNAL') || die();

/**
 * Plugin file serving for the OneClickExport plugin.
 * This file handles the serving of backup and bulk export files.
 *
 * @package    local_oneclickexport
 * @category   file
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */

function local_oneclickexport_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    if (!in_array($filearea, ['backup', 'bulk'])) {
        return false;
    }

    require_capability('local/oneclickexport:export', $context);

    $fs = get_file_storage();
    
    try {
        if (count($args) < 2) {
            throw new file_serving_exception('Invalid arguments');
        }

        $itemid = (int)array_shift($args);
        $filename = array_pop($args);
        $filepath = count($args) ? '/' . implode('/', $args) . '/' : '/';

        $file = $fs->get_file(
            $context->id,
            'local_oneclickexport',
            $filearea,
            $itemid,
            $filepath,
            $filename
        );

        if (!$file) {
            return false;
        }

      
        if ($filearea === 'bulk') {
            global $DB, $USER;
            
            $export = $DB->get_record('local_oneclickexport_log', [
                'id' => $itemid,
                'userid' => $USER->id
            ]);
            
            if (!$export) {
                return false;
            }
        }

        send_stored_file($file, 0, 0, $forcedownload, $options);
        
    } catch (Exception $e) {
        debugging("Error serving file: " . $e->getMessage(), DEBUG_DEVELOPER);
        return false;
    }
}
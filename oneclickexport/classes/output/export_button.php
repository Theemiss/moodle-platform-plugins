<?php
defined('MOODLE_INTERNAL') || die();

class export_button extends \core\output\inplace_editable {

    public function __construct($courseid) {
        $url = new moodle_url('/local/oneclickexport/export.php', ['id' => $courseid]);
        parent::__construct(
            'local_oneclickexport',
            'exportbutton',
            $courseid,
            true,
            '<a href="'.$url.'" class="btn btn-primary">'.get_string('exportcourse', 'local_oneclickexport').'</a>',
            ''
        );
    }
}
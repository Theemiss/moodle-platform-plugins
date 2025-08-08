<?php
/**
 * Bulk export form for the OneClickExport plugin with pagination
 * 
 * @package    local_oneclickexport
 * @copyright  2025 Ahmed Belhaj <ahmed.belhaj@campusna.com>
 */
require_once($CFG->libdir . '/formslib.php');
const DEFAULT_COURSES_PER_PAGE = 10;
class local_oneclickexport_bulk_export_form extends moodleform
{

    protected $courses_per_page;

    protected $currentpage = 0;

    public function __construct($action = null, $customdata = null, $method = 'post', $target = '', $attributes = null, $editable = true)
    {
        $this->currentpage = optional_param('page', 0, PARAM_INT);
        $this->courses_per_page = get_config('local_oneclickexport', 'courses_per_page') ?: self::DEFAULT_COURSES_PER_PAGE;

        parent::__construct($action, $customdata, $method, $target, $attributes, $editable);
    }

    public function definition()
    {
        global $DB;

        $mform = $this->_form;

        $mform->addElement('hidden', 'page', $this->currentpage);
        $mform->setType('page', PARAM_INT);

        $mform->addElement('header', 'coursesheader', get_string('selectcourses', 'local_oneclickexport'));

        list($courses, $totalcount) = $this->get_paginated_courses();

        if (empty($courses)) {
            $mform->addElement('html', '<div class="alert alert-info">' .
                get_string('nocoursesfound', 'local_oneclickexport') . '</div>');
        } else {
            $options = [];
            foreach ($courses as $course) {
                $options[$course->id] = format_string($course->fullname) . ' (' . $course->shortname . ')';
            }

            $select = $mform->addElement(
                'select',
                'courses',
                get_string('courses', 'local_oneclickexport'),
                $options
            );
            $select->setMultiple(true);

            $this->add_pagination_controls($totalcount);
        }

        $mform->addElement('header', 'optionsheader', get_string('exportoptions', 'local_oneclickexport'));

        $mform->addElement(
            'advcheckbox',
            'includeusers',
            '',
            get_string('includeusers', 'local_oneclickexport')
        );
        $mform->setDefault('includeusers', get_config('local_oneclickexport', 'includeusers'));

        $mform->addElement(
            'advcheckbox',
            'includecomments',
            '',
            get_string('includecomments', 'local_oneclickexport')
        );
        $mform->setDefault('includecomments', get_config('local_oneclickexport', 'includecomments'));

        $mform->addElement(
            'advcheckbox',
            'includelogs',
            '',
            get_string('includelogs', 'local_oneclickexport')
        );
        $mform->setDefault('includelogs', get_config('local_oneclickexport', 'includelogs'));

        $mform->addElement(
            'advcheckbox',
            'includecalendarevents',
            '',
            get_string('includecalendarevents', 'local_oneclickexport')
        );
        $mform->setDefault('includecalendarevents', get_config('local_oneclickexport', 'includecalendarevents'));

        $mform->addElement(
            'advcheckbox',
            'includeuserscompletion',
            '',
            get_string('includeuserscompletion', 'local_oneclickexport')
        );
        $mform->setDefault('includeuserscompletion', get_config('local_oneclickexport', 'includeuserscompletion'));

        $mform->addElement(
            'advcheckbox',
            'includeroleassignments',
            '',
            get_string('includeroleassignments', 'local_oneclickexport')
        );
        $mform->setDefault('includeroleassignments', get_config('local_oneclickexport', 'includeroleassignments'));

        $this->add_action_buttons(true, get_string('exportcourses', 'local_oneclickexport'));
    }

    protected function get_paginated_courses()
    {
        global $DB;

        $params = ['visible' => 1];
        $where = 'visible = :visible';

        $totalcount = $DB->count_records_select('course', $where, $params);

        $courses = $DB->get_records_select(
            'course',
            $where,
            $params,
            'fullname',
            'id,fullname,shortname',
            $this->currentpage * $this->courses_per_page,
            $this->courses_per_page,
        );

        return [$courses, $totalcount];
    }

    protected function add_pagination_controls($totalcount)
    {
        $mform = $this->_form;

        $totalpages = ceil($totalcount / $this->courses_per_page);

        if ($totalpages > 1) {
            $paginationhtml = '<div class="pagination my-3">';

            if ($this->currentpage > 0) {
                $paginationhtml .= '<button type="submit" name="page" value="' . ($this->currentpage - 1) . '" class="btn btn-secondary mr-2">&laquo; ' . get_string('previous', 'local_oneclickexport') . '</button>';
            }

            $paginationhtml .= '<span class="mx-2 align-middle">' .
                get_string('page') . ' ' . ($this->currentpage + 1) . ' / ' . $totalpages .
                ' (' . $totalcount . ' ' . get_string('courses', 'local_oneclickexport') . ')' .
                '</span>';

            if ($this->currentpage < $totalpages - 1) {
                $paginationhtml .= '<button type="submit" name="page" value="' . ($this->currentpage + 1) . '" class="btn btn-secondary ml-2">' . get_string('next', 'local_oneclickexport') . ' &raquo;</button>';
            }

            $paginationhtml .= '</div>';

            $mform->addElement('html', $paginationhtml);
        }
    }

    public function validation($data, $files)
    {
        $errors = parent::validation($data, $files);

        if (empty($data['courses'])) {
            $errors['courses'] = get_string('selectatleastonecourse', 'local_oneclickexport');
        }

        return $errors;
    }
}
<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Add a new instance of the activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @param mod_tekouinlab_mod_form $mform The form object.
 * @return int The ID of the newly created instance.
 */
function tekouinlab_add_instance($data, $mform = null) {
    global $DB;

    // Add any necessary data processing here.
    $data->timecreated = time();
    $data->timemodified = time();

    // Ensure 'id' field is not included if it's auto-increment.
    unset($data->id);

    // Ensure tekouinlabid has a value from the form.
    if (empty($data->tekouinlabid)) {
        throw new moodle_exception('nolatekouinlabid', 'tekouinlab', '', null, 'No lab ID was provided.');
    }

    // Insert the new record into the database.
    $id = $DB->insert_record('tekouinlab', $data);

    // Create a grade item for this activity.
    tekouinlab_grade_item_update($data);

    if (!empty($data->completionexpected)) {
        $cmid = $data->coursemodule;
        $completionexpected = $data->completionexpected;
        \core_completion\api::update_completion_date_event($cmid, 'tekouinlab', $data, $completionexpected);
    }

    return $id;
}

function tekouinlab_extend_navigation(navigation_node $navigation, $course, $module, $cm) {
    if (!$navigation) {
        return;
    }

    // URL for the TekouinLab plugin
    $url = new moodle_url('/mod/tekouinlab/view.php', ['id' => $cm->id]);
    $navigation->add(
        get_string('pluginname', 'mod_tekouinlab'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'tekouinlab'
    );

    // Add a link to view grades, visible only to teachers and administrators
    if (has_capability('gradereport/grader:view', context_module::instance($cm->id))) {
        $gradesurl = new moodle_url('/mod/tekouinlab/grades.php', ['id' => $cm->id]);
        $navigation->add(
            get_string('viewgrades', 'mod_tekouinlab'),
            $gradesurl,
            navigation_node::TYPE_CUSTOM,
            null,
            'tekouinlabgrades'
        );
    }
}

function tekouinlab_extend_settings_navigation(settings_navigation $settings, navigation_node $tekouinlabnode) {
    global $PAGE;

    if ($tekouinlabnode && $PAGE->cm && $PAGE->cm->modname === 'tekouinlab') {
        // URL for all labs
        $labsettingsurl = new moodle_url('/mod/tekouinlab/all_labs.php', ['id' => $PAGE->cm->id]);
        $tekouinlabnode->add(
            get_string('viewalllabs', 'mod_tekouinlab'),
            $labsettingsurl,
            navigation_node::TYPE_SETTING,
            null,
            'tekouinlabsettings'
        );

        // Add a link to the grades view, restricted to teachers and administrators
        if (has_capability('gradereport/grader:view', context_module::instance($PAGE->cm->id))) {
            $gradesurl = new moodle_url('/mod/tekouinlab/grades.php', ['id' => $PAGE->cm->id]);
            $tekouinlabnode->add(
                get_string('viewgrades', 'mod_tekouinlab'),
                $gradesurl,
                navigation_node::TYPE_SETTING,
                null,
                'tekouinlabgrades'
            );
        }
    }
}


/**
 * Delete an instance of the activity.
 *
 * @param int $id The ID of the instance to delete.
 * @return bool True if successful, false otherwise.
 */
function tekouinlab_delete_instance($id) {
    global $DB;

    // Ensure the instance exists.
    if (!$tekouinlab = $DB->get_record('tekouinlab', ['id' => $id])) {
        return false;
    }

    // Delete the record from the database.
    $DB->delete_records('tekouinlab', ['id' => $tekouinlab->id]);

    // Delete associated grade item.
    tekouinlab_grade_item_delete($tekouinlab);

    return true;
}

/**
 * Create or update the grade item for this activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @param array $grades Optional array of grades.
 * @return int Returns GRADE_UPDATE_OK, GRADE_UPDATE_FAILED, or GRADE_UPDATE_MULTIPLE.
 */
function tekouinlab_grade_item_update($data, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $data->name,
        'idnumber' => $data->id,
        'itemtype' => 'mod',
        'itemmodule' => 'tekouinlab',
    ];

    if ($data->grade == 0) {
        $params['gradetype'] = GRADE_TYPE_NONE;
        $params['deleted'] = true;
    } else {
        if ($data->grade > 0) {
            $params['gradetype'] = GRADE_TYPE_VALUE;
            $params['grademax'] = $data->grade;
            $params['grademin'] = 0;
        } else {
            $params['gradetype'] = GRADE_TYPE_SCALE;
            $params['scaleid'] = -$data->grade;
        }
    }

    // Update or create the grade item.
    return grade_update('mod/tekouinlab', $data->course, 'mod', 'tekouinlab', $data->id, 0, $grades, $params);
}

/**
 * Delete the grade item for this activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @return bool True if successful, false otherwise.
 */
function tekouinlab_grade_item_delete($data) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/tekouinlab', $data->course, 'mod', 'tekouinlab', $data->id, 0, null, ['deleted' => true]);
}

/**
 * Get the user's grade for this activity.
 *
 * @param int $tekouinlabid The ID of the activity instance.
 * @param int $userid The ID of the user.
 * @return float|bool The user's grade, or false if no grade is available.
 */
function tekouinlab_get_user_grade($tekouinlabid, $userid) {
    global $DB;

    $grade = $DB->get_record('grade_grades', [
        'itemid' => $tekouinlabid,
        'userid' => $userid,
    ]);

    return $grade ? $grade->rawgrade : false;
}

/**
 * Check which features the plugin supports.
 *
 * @param string $feature The feature to check.
 * @return bool|null True if supported, false if not, null if unknown.
 */
function tekouinlab_supports($feature) {
    switch ($feature) {
        case FEATURE_GROUPS:
            return true;
        case FEATURE_GROUPINGS:
            return true;
        case FEATURE_MOD_INTRO:
            return true;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return false;
        case FEATURE_GRADE_HAS_GRADE:
            return true;
        case FEATURE_GRADE_OUTCOMES:
            return true;
        case FEATURE_BACKUP_MOODLE2:
            return true;
        case FEATURE_SHOW_DESCRIPTION:
            return true;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_CONTROLS_GRADE_VISIBILITY:
            return true;
        default:
            if (defined('FEATURE_MOD_PURPOSE')) {
                if ($feature == FEATURE_MOD_PURPOSE) {
                    return MOD_PURPOSE_ASSESSMENT;
                }
            }
            return null;
    }
}

/**
 * Fetch the grades for the lab activity.
 *
 * @param int $instanceid The ID of the lab activity instance.
 * @return array An array of user grades for the lab activity.
 */
function fetch_lab_grades($instanceid) {
    global $DB;

    // Get the grade item for the 'tekouinlab' module instance
    $grade_item = $DB->get_record('grade_items', [
        'iteminstance' => $instanceid,
        'itemmodule' => 'tekouinlab',
    ]);

    if (!$grade_item) {
        // Handle the case where the grade item is not found
        return [];
    }

    // Fetch all grades for this grade item
    $grades = $DB->get_records('grade_grades', ['itemid' => $grade_item->id]);

    return $grades;
}

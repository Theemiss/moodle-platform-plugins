<?php
defined('MOODLE_INTERNAL') || die();
/**
 * Add a new instance of the activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @param mod_cloudlab_mod_form $mform The form object.
 * @return int The ID of the newly created instance.
 */
function cloudlab_add_instance($data, $mform = null) {
    global $DB;

    // Add any necessary data processing here.
    $data->timecreated = time();
    $data->timemodified = time();

    // Ensure 'id' field is not included if it's auto-increment.
    unset($data->id);

    // Ensure cloudlabid has a value from the form.
    if (empty($data->cloudlabid)) {
        throw new moodle_exception('nolacloudlabid', 'cloudlab', '', null, 'No lab ID was provided.');
    }

    // Insert the new record into the database.
    $id = $DB->insert_record('cloudlab', $data);

    // Create a grade item for this activity.
    cloudlab_grade_item_update($data);

    if (!empty($data->completionexpected)) {
        $cmid = $data->coursemodule;
        $completionexpected = $data->completionexpected;
        \core_completion\api::update_completion_date_event($cmid, 'cloudlab', $data, $completionexpected);
    }

    return $id;
}

function cloudlab_extend_navigation(navigation_node $navigation, $course, $module, $cm) {
    if (!$navigation) {
        return;
    }

    // URL for the Cloud Lab plugin
    $url = new moodle_url('/mod/cloudlab/view.php', ['id' => $cm->id]);
    $navigation->add(
        get_string('pluginname', 'mod_cloudlab'),
        $url,
        navigation_node::TYPE_CUSTOM,
        null,
        'cloudlab'
    );

    // Add a link to view grades, visible only to teachers and administrators
    if (has_capability('gradereport/grader:view', context_module::instance($cm->id))) {
        $gradesurl = new moodle_url('/mod/cloudlab/grades.php', ['id' => $cm->id]);
        $navigation->add(
            get_string('viewgrades', 'mod_cloudlab'),
            $gradesurl,
            navigation_node::TYPE_CUSTOM,
            null,
            'cloudlabgrades'
        );
    }
}

function cloudlab_extend_settings_navigation(settings_navigation $settings, navigation_node $cloudlabnode) {
    global $PAGE;

    if ($cloudlabnode && $PAGE->cm && $PAGE->cm->modname === 'cloudlab') {
        // URL for all labs
        $labsettingsurl = new moodle_url('/mod/cloudlab/all_labs.php', ['id' => $PAGE->cm->id]);
        $cloudlabnode->add(
            get_string('viewalllabs', 'mod_cloudlab'),
            $labsettingsurl,
            navigation_node::TYPE_SETTING,
            null,
            'cloudlabsettings'
        );

        // Add a link to the grades view, restricted to teachers and administrators
        if (has_capability('gradereport/grader:view', context_module::instance($PAGE->cm->id))) {
            $gradesurl = new moodle_url('/mod/cloudlab/grades.php', ['id' => $PAGE->cm->id]);
            $cloudlabnode->add(
                get_string('viewgrades', 'mod_cloudlab'),
                $gradesurl,
                navigation_node::TYPE_SETTING,
                null,
                'cloudlabgrades'
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
function cloudlab_delete_instance($id) {
    global $DB;

    // Ensure the instance exists.
    if (!$cloudlab = $DB->get_record('cloudlab', ['id' => $id])) {
        return false;
    }

    // Delete the record from the database.
    $DB->delete_records('cloudlab', ['id' => $cloudlab->id]);

    // Delete associated grade item.
    cloudlab_grade_item_delete($cloudlab);

    return true;
}

/**
 * Create or update the grade item for this activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @param array $grades Optional array of grades.
 * @return int Returns GRADE_UPDATE_OK, GRADE_UPDATE_FAILED, or GRADE_UPDATE_MULTIPLE.
 */
function cloudlab_grade_item_update($data, $grades = null) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    $params = [
        'itemname' => $data->name,
        'idnumber' => $data->id,
        'itemtype' => 'mod',
        'itemmodule' => 'cloudlab',
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
    return grade_update('mod/cloudlab', $data->course, 'mod', 'cloudlab', $data->id, 0, $grades, $params);
}

/**
 * Delete the grade item for this activity.
 *
 * @param stdClass $data The data from the form (submitted by mod_form.php).
 * @return bool True if successful, false otherwise.
 */
function cloudlab_grade_item_delete($data) {
    global $CFG;
    require_once($CFG->libdir . '/gradelib.php');

    return grade_update('mod/cloudlab', $data->course, 'mod', 'cloudlab', $data->id, 0, null, ['deleted' => true]);
}

/**
 * Get the user's grade for this activity.
 *
 * @param int $cloudlabid The ID of the activity instance.
 * @param int $userid The ID of the user.
 * @return float|bool The user's grade, or false if no grade is available.
 */
function cloudlab_get_user_grade($cloudlabid, $userid) {
    global $DB;

    $grade = $DB->get_record('grade_grades', [
        'itemid' => $cloudlabid,
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
function cloudlab_supports($feature) {
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

    // Get the grade item for the 'cloudlab' module instance
    $grade_item = $DB->get_record('grade_items', [
        'iteminstance' => $instanceid,
        'itemmodule' => 'cloudlab',
    ]);

    if (!$grade_item) {
        // Handle the case where the grade item is not found
        return [];
    }

    // Fetch all grades for this grade item
    $grades = $DB->get_records('grade_grades', ['itemid' => $grade_item->id]);

    return $grades;
}

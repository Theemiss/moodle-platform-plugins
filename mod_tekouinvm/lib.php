<?php

defined('MOODLE_INTERNAL') || die();

function tekouinvm_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    return $DB->insert_record('tekouinvm', $data);
}

function tekouinvm_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('tekouinvm', $data);
}

function tekouinvm_delete_instance($id) {
    global $DB;

    if (!$vm = $DB->get_record('tekouinvm', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('tekouinvm', ['id' => $id]);
    return true;
}
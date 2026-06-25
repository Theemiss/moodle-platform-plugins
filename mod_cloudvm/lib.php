<?php

defined('MOODLE_INTERNAL') || die();

function cloudvm_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = time();

    return $DB->insert_record('cloudvm', $data);
}

function cloudvm_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    return $DB->update_record('cloudvm', $data);
}

function cloudvm_delete_instance($id) {
    global $DB;

    if (!$vm = $DB->get_record('cloudvm', ['id' => $id])) {
        return false;
    }

    $DB->delete_records('cloudvm', ['id' => $id]);
    return true;
}
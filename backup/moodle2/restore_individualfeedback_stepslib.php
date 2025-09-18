<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @package    mod_individualfeedback
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the restore steps that will be used by the restore_individualfeedback_activity_task
 */

/**
 * Structure step to restore one individualfeedback activity
 */
class restore_individualfeedback_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('individualfeedback', '/activity/individualfeedback');
        $paths[] = new restore_path_element('individualfeedback_item', '/activity/individualfeedback/items/item');
        if ($userinfo) {
            $paths[] = new restore_path_element('individualfeedback_completed', '/activity/individualfeedback/completeds/completed');
            $paths[] = new restore_path_element('individualfeedback_value', '/activity/individualfeedback/completeds/completed/values/value');
        }

        // Return the paths wrapped into standard activity structure
        return $this->prepare_activity_structure($paths);
    }

    protected function process_individualfeedback($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        // Any changes to the list of dates that needs to be rolled should be same during course restore and course reset.
        // See MDL-9367.
        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        //$data->timemodified = $this->apply_date_offset($data->timemodified);
        // insert the individualfeedback record
        $newitemid = $DB->insert_record('individualfeedback', $data);
        // immediately after inserting "activity" record, call this
        $this->apply_activity_instance($newitemid);
    }

    protected function process_individualfeedback_item($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->feedback = $this->get_new_parentid('individualfeedback');
        $data->typ = clean_param($data->typ, PARAM_ALPHA);
        //$data->dependitem = $this->get_mappingid('individualfeedback_item', $data->dependitem);
        $newitemid = $DB->insert_record('individualfeedback_item', $data);
        $this->set_mapping('individualfeedback_item', $oldid, $newitemid, true); // Can have files
    }

    protected function process_individualfeedback_completed($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->feedback = $this->get_new_parentid('individualfeedback');
        $data->userid = $this->get_mappingid('user', $data->userid);
        if ($this->task->is_samesite() && !empty($data->courseid)) {
            $data->courseid = $data->courseid;
        } else if ($this->get_courseid() == SITEID) {
            $data->courseid = SITEID;
        } else {
            $data->courseid = 0;
        }

        $newitemid = $DB->insert_record('individualfeedback_completed', $data);
        $this->set_mapping('individualfeedback_completed', $oldid, $newitemid);
    }

    protected function process_individualfeedback_value($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->completed = $this->get_new_parentid('individualfeedback_completed');
        $data->item = $this->get_mappingid('individualfeedback_item', $data->item);
        if ($this->task->is_samesite() && !empty($data->course_id)) {
            $data->course_id = $data->course_id;
        } else if ($this->get_courseid() == SITEID) {
            $data->course_id = SITEID;
        } else {
            $data->course_id = 0;
        }

        $newitemid = $DB->insert_record('individualfeedback_value', $data);
        $this->set_mapping('individualfeedback_value', $oldid, $newitemid);
    }

    protected function after_execute() {
        global $DB;
        // Add feedback related files, no need to match by itemname (just internally handled context)
        $this->add_related_files('mod_individualfeedback', 'intro', null);
        $this->add_related_files('mod_individualfeedback', 'page_after_submit', null);
        $this->add_related_files('mod_individualfeedback', 'item', 'individualfeedback_item');

        // Once all items are restored we can set their dependency.
        if ($records = $DB->get_records('individualfeedback_item', array('feedback' => $this->task->get_activityid()))) {
            foreach ($records as $record) {
                // Get new id for dependitem if present. This will also reset dependitem if not found.
                $record->dependitem = $this->get_mappingid('individualfeedback_item', $record->dependitem);
                $DB->update_record('individualfeedback_item', $record);
            }
        }
    }
}

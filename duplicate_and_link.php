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
 * the first page to view the individualfeedback
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

$id = required_param('id', PARAM_INT);
$sectionreturn = optional_param('sectionreturn', false, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
$individualfeedback = $PAGE->activityrecord;

$context = context_module::instance($cm->id);

$PAGE->set_url('/mod/individualfeedback/duplicate_and_link.php', array('id' => $cm->id, 'sectionreturn' => $sectionreturn));

if (!$newcm = duplicate_module($course, $cm)) {
    throw new \moodle_exception('error_duplicating', 'individualfeedback');
}

$newcm = get_fast_modinfo($course)->get_cm($newcm->id);

// Create the linked record(s).
individualfeedback_create_linked_record($cm->instance, $newcm->instance);

redirect(course_get_url($course, $cm->sectionnum, array('sr' => $sectionreturn)),
            get_string('individualfeedback_cloned_and_linked', 'individualfeedback'));

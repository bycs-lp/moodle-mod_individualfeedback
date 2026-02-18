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
 * print the form to map courses for global feedbacks
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once(__DIR__ . "/../../config.php");
require_once($CFG->dirroot . "/mod/individualfeedback/lib.php");
require_once("$CFG->libdir/tablelib.php");

$id = required_param('id', PARAM_INT); // Course Module ID.

$url = new moodle_url('/mod/individualfeedback/mapcourse.php', array('id'=>$id));
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_login($course, true, $cm);
$individualfeedback = $PAGE->activityrecord;

$context = context_module::instance($cm->id);
require_capability('mod/individualfeedback:mapcourse', $context);

$coursemap = array_keys(individualfeedback_get_courses_from_sitecourse_map($individualfeedback->id));
$form = new mod_individualfeedback_course_map_form();
$form->set_data(array('id' => $cm->id, 'mappedcourses' => $coursemap));
$mainurl = new moodle_url('/mod/individualfeedback/view.php', ['id' => $id]);
if ($form->is_cancelled()) {
    redirect($mainurl);
} else if ($data = $form->get_data()) {
    individualfeedback_update_sitecourse_map($individualfeedback, $data->mappedcourses);
    redirect($mainurl, get_string('mappingchanged', 'individualfeedback'), null, \core\output\notification::NOTIFY_SUCCESS);
}

// Print the page header.
$strfeedbacks = get_string("modulenameplural", "individualfeedback");
$strfeedback  = get_string("modulename", "individualfeedback");

$PAGE->set_heading($course->fullname);
$PAGE->set_title($individualfeedback->name);
echo $OUTPUT->header();

echo $OUTPUT->box(get_string('mapcourseinfo', 'individualfeedback'));

$form->display();

echo $OUTPUT->footer();

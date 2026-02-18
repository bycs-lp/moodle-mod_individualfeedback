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
 * prints the form to edit the individualfeedback items such moving, deleting and so on
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once('../../config.php');
require_once('lib.php');

individualfeedback_init_individualfeedback_session();

$id = required_param('id', PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    throw new \moodle_exception('invalidsesskey');
}

$switchitemrequired = optional_param('switchitemrequired', false, PARAM_INT);
$deleteitem = optional_param('deleteitem', false, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');

$context = context_module::instance($cm->id);
require_login($course, false, $cm);
require_capability('mod/individualfeedback:edititems', $context);
$individualfeedback = $PAGE->activityrecord;
$individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);
$url = new moodle_url('/mod/individualfeedback/edit.php', ['id' => $cm->id]);

if ($switchitemrequired) {
    require_sesskey();
    $items = $individualfeedbackstructure->get_items();
    if (isset($items[$switchitemrequired])) {
        individualfeedback_switch_item_required($items[$switchitemrequired]);
    }
    redirect($url);
}

if ($deleteitem) {
    require_sesskey();
    $items = $individualfeedbackstructure->get_items();
    if (isset($items[$deleteitem])) {
        individualfeedback_delete_item($deleteitem);
    }
    redirect($url);
}

//Get the feedbackitems
$lastposition = 0;
$individualfeedbackitems = $DB->get_records('individualfeedback_item', ['individualfeedback' => $individualfeedback->id], 'position');
if (is_array($individualfeedbackitems)) {
    $individualfeedbackitems = array_values($individualfeedbackitems);
    if (count($individualfeedbackitems) > 0) {
        $lastitem = $individualfeedbackitems[count($individualfeedbackitems)-1];
        $lastposition = $lastitem->position;
    } else {
        $lastposition = 0;
    }
}
$lastposition++;

$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);

/** @var \mod_individualfeedback\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
        [format_string($individualfeedback->name), format_string($course->fullname)],
        get_string('questions', 'individualfeedback')
);

$actionbar = new \mod_individualfeedback\output\edit_action_bar($cm->id, $url, $lastposition);
$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => ''
]);
$PAGE->add_body_class('limitedwidth');
$PAGE->requires->js_call_amd('mod_individualfeedback/edit', 'init', [$cm->id]);

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('edit_items', 'mod_individualfeedback'), $PAGE->activityheader->get_heading_level());
echo $renderer->main_action_bar($actionbar);
$form = new mod_individualfeedback_complete_form(mod_individualfeedback_complete_form::MODE_EDIT,
        $individualfeedbackstructure, 'individualfeedback_edit_form');
$form->display();

echo $OUTPUT->footer();

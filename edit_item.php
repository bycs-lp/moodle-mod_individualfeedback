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
 * prints the form to edit a dedicated item
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */

require_once("../../config.php");
require_once("lib.php");

individualfeedback_init_individualfeedback_session();

$itemid = optional_param('id', false, PARAM_INT);
if (!$itemid) {
    $cmid = required_param('cmid', PARAM_INT);
    $typ = required_param('typ', PARAM_ALPHA);
}

if ($itemid) {
    $item = $DB->get_record('individualfeedback_item', array('id' => $itemid), '*', MUST_EXIST);
    list($course, $cm) = get_course_and_cm_from_instance($item->feedback, 'individualfeedback');
    $url = new moodle_url('/mod/individualfeedback/edit_item.php', array('id' => $itemid));
    $typ = $item->typ;
} else {
    $item = null;
    list($course, $cm) = get_course_and_cm_from_cmid($cmid, 'individualfeedback');
    $url = new moodle_url('/mod/individualfeedback/edit_item.php', array('cmid' => $cm->id, 'typ' => $typ));
    $item = (object)['id' => null, 'position' => -1, 'typ' => $typ, 'options' => ''];
}

require_login($course, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/individualfeedback:edititems', $context);
$feedback = $PAGE->activityrecord;

$editurl = new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id));

$PAGE->set_url($url);

// If the typ is pagebreak so the item will be saved directly.
if (!$item->id && $typ === 'pagebreak') {
    require_sesskey();

    $redirectmessage = '';
    if (!individualfeedback_create_pagebreak($feedback->id)) {
        $redirectmessage = get_string('cannotcreatepagebreak', 'mod_feedback');
    }

    redirect($editurl, $redirectmessage, null, \core\output\notification::NOTIFY_WARNING);
}

// +++ NEW CODE
// get the existing item or create it   
// $formdata->itemid = isset($formdata->itemid) ? $formdata->itemid : NULL;
if (!$typ || !file_exists($CFG->dirroot.'/mod/individualfeedback/item/'.$typ.'/lib.php')) {
    throw new \moodle_exception('typemissing', 'individualfeedback', $editurl->out(false));
}
require_once($CFG->dirroot.'/mod/individualfeedback/item/'.$typ.'/lib.php');
// --- NEW CODE

$itemobj = individualfeedback_get_item_class($typ);
$itemobj->build_editform($item, $feedback, $cm);

if ($itemobj->is_cancelled()) {
    redirect($editurl);
    exit;
}
if ($itemobj->get_data()) {
    if ($item = $itemobj->save_item()) {
        individualfeedback_move_item($item, $item->position);
        redirect($editurl);
    }
}

////////////////////////////////////////////////////////////////////////////////////
/// Print the page header
$strfeedbacks = get_string("modulenameplural", "individualfeedback");
$strfeedback  = get_string("modulename", "individualfeedback");

navigation_node::override_active_url(new moodle_url('/mod/individualfeedback/edit.php',
        array('id' => $cm->id, 'do_show' => 'edit')));
if ($item->id) {
    $PAGE->navbar->add(get_string('edit_item', 'individualfeedback'));
} else {
    $PAGE->navbar->add(get_string('add_item', 'individualfeedback'));
}
$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('mod_individualfeedback');
$pagetitle = ($itemid) ? get_string('edit_item', 'individualfeedback') : get_string('add_item', 'individualfeedback');
$renderer->set_title(
    [format_string($feedback->name), format_string($course->fullname)],
    $pagetitle
);

$PAGE->activityheader->set_attrs([
    "hidecompletion" => true,
    "description" => ''
]);
$PAGE->add_body_class('limitedwidth');
echo $OUTPUT->header();

//print errormsg
if (isset($error)) {
    echo $error;
}
$itemobj->show_editform();

/// Finish the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

echo $OUTPUT->footer();

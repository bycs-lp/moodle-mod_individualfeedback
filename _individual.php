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
require_once('edit_form.php');
global $CFG, $PAGE, $OUTPUT;

individualfeedback_init_individualfeedback_session();

$id = required_param('id', PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    throw new \moodle_exception('invalidsesskey');
}

$do_show = optional_param('do_show', 'edit', PARAM_ALPHA);
$switchitemrequired = optional_param('switchitemrequired', false, PARAM_INT);
$deleteitem = optional_param('deleteitem', false, PARAM_INT);

$current_tab = $do_show;

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');

$context = context_module::instance($cm->id);
require_login($course, false, $cm);
require_capability('mod/individualfeedback:edititems', $context);
$individualfeedback = $PAGE->activityrecord;
$individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);
$url = new moodle_url('/mod/individualfeedback/edit.php', array('id'=>$id, 'do_show'=>$do_show));

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

// Process the create template form.
$cancreatetemplates = has_capability('mod/individualfeedback:createprivatetemplate', $context) ||
            has_capability('mod/individualfeedback:createpublictemplate', $context);
$create_template_form = new individualfeedback_edit_create_template_form(null, array('id' => $id));
if ($data = $create_template_form->get_data()) {
    // Check the capabilities to create templates.
    if (!$cancreatetemplates) {
        throw new \moodle_exception('cannotsavetempl', 'individualfeedback', $url);
    }
    $ispublic = !empty($data->ispublic) ? 1 : 0;
    if (!individualfeedback_save_as_template($individualfeedback, $data->templatename, $ispublic)) {
        redirect($url, get_string('saving_failed', 'individualfeedback'), null, \core\output\notification::NOTIFY_ERROR);
    } else {
        redirect($url, get_string('template_saved', 'individualfeedback'), null, \core\output\notification::NOTIFY_SUCCESS);
    }
}

//Get the individualfeedbackitems
$lastposition = 0;
$individualfeedbackitems = $DB->get_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id), 'position');
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


//The use_template-form
$use_template_form = new individualfeedback_edit_use_template_form('use_templ.php', array('course' => $course, 'id' => $id));

//Print the page header.
$strindividualfeedbacks = get_string('modulenameplural', 'individualfeedback');
$strindividualfeedback  = get_string('modulename', 'individualfeedback');

$PAGE->set_url($url);
$PAGE->set_heading($course->fullname);
$PAGE->set_title($individualfeedback->name);
$actionbar = new \mod_individualfeedback\output\edit_action_bar($cm->id, $url, $lastposition);
$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => ''
]);
$PAGE->add_body_class('limitedwidth'); //Adding the limitedwidth class for the limited width.

//Adding the javascript module for the items dragdrop.
if (count($individualfeedbackitems) > 1) {
    if ($do_show == 'edit') {
        // SFSUBM-21 - move the question out of the questiongroup div if it's outside the actual question group.
        $PAGE->requires->jquery();
        // $PAGE->requires->js('/mod/individualfeedback/edit_movequestion.js');

        $PAGE->requires->strings_for_js(array(
               'pluginname',
               'move_item',
               'position',
               'move_questiongroup',
            ), 'individualfeedback');
        $PAGE->requires->yui_module('moodle-mod_individualfeedback-dragdrop', 'M.mod_individualfeedback.init_dragdrop',
                array(array('cmid' => $cm->id)));
        $PAGE->requires->js_call_amd('mod_individualfeedback/movequestiongroup', 'init', array('cmid' => $cm->id));
    }
}

echo $OUTPUT->header();
echo $OUTPUT->heading(format_string($individualfeedback->name));

/// print the tabs
require('tabs.php');

// Print the main part of the page.

if ($do_show == 'templates') {
    // Print the template-section.
    $use_template_form->display();

    if ($cancreatetemplates) {
        $deleteurl = new moodle_url('/mod/individualfeedback/delete_template.php', array('id' => $id));
        $create_template_form->display();
        echo '<p><a href="'.$deleteurl->out().'">'.
             get_string('delete_templates', 'individualfeedback').
             '</a></p>';
    } else {
        echo '&nbsp;';
    }

    if (has_capability('mod/individualfeedback:edititems', $context)) {
        $urlparams = array('action'=>'exportfile', 'id'=>$id);
        $exporturl = new moodle_url('/mod/individualfeedback/export.php', $urlparams);
        $importurl = new moodle_url('/mod/individualfeedback/import.php', array('id'=>$id));
        echo '<p>
            <a href="'.$exporturl->out().'">'.get_string('export_questions', 'individualfeedback').'</a>/
            <a href="'.$importurl->out().'">'.get_string('import_questions', 'individualfeedback').'</a>
        </p>';
    }
}

if ($do_show == 'edit') {
    // Check if it is a linked individual feedback activity.
    if ($linkedid = individualfeedback_get_linkedid($individualfeedback->id)) {
        echo html_writer::tag('p', get_string('individualfeedback_is_linked', 'individualfeedback'), array('class' => 'error'));

        $form = new mod_individualfeedback_complete_form(mod_individualfeedback_complete_form::MODE_PRINT,
                $individualfeedbackstructure, 'individualfeedback_edit_form');
        $form->display();
    } else {
        // Print the Item-Edit-section.
        $select = new single_select(new moodle_url('/mod/individualfeedback/edit_item.php',
                array('cmid' => $id, 'position' => $lastposition, 'sesskey' => sesskey())),
            'typ', individualfeedback_load_individualfeedback_items_options());
        $select->label = get_string('add_item', 'mod_individualfeedback');
        echo $OUTPUT->render($select);

        $form = new mod_individualfeedback_complete_form(mod_individualfeedback_complete_form::MODE_EDIT,
                $individualfeedbackstructure, 'individualfeedback_edit_form');
        echo '<div id="individualfeedback_dragarea">'; // The container for the dragging area.
        $form->display();
        echo '</div>';
    }
}

echo $OUTPUT->footer();

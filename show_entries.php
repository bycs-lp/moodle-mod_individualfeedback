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
 * print the single entries
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

use mod_individualfeedback\manager;

require_once("../../config.php");
require_once("lib.php");

////////////////////////////////////////////////////////
//get the params
////////////////////////////////////////////////////////
$id = required_param('id', PARAM_INT);
$userid = optional_param('userid', false, PARAM_INT);
$showcompleted = optional_param('showcompleted', false, PARAM_INT);
$deleteid = optional_param('delete', null, PARAM_INT);
$courseid = optional_param('courseid', null, PARAM_INT);

////////////////////////////////////////////////////////
//get the objects
////////////////////////////////////////////////////////

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');

$baseurl = new moodle_url('/mod/individualfeedback/show_entries.php', array('id' => $cm->id));
$PAGE->set_url(new moodle_url($baseurl, array('userid' => $userid, 'showcompleted' => $showcompleted,
        'delete' => $deleteid)));
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
$individualfeedback = $PAGE->activityrecord;

require_capability('mod/individualfeedback:viewreports', $context);

$actionbar = new \mod_individualfeedback\output\responses_action_bar($cm->id, $baseurl);

if ($deleteid) {
    // This is a request to delete a reponse.
    require_capability('mod/individualfeedback:deletesubmissions', $context);
    require_sesskey();
    $individualfeedbackstructure = new mod_individualfeedback_completion($individualfeedback, $cm, 0, true, $deleteid);
    individualfeedback_delete_completed($individualfeedbackstructure->get_completed(), $individualfeedback, $cm);
    redirect($baseurl);
} else if ($showcompleted || $userid) {
    // Viewing individual response.
    $individualfeedbackstructure = new mod_individualfeedback_completion($individualfeedback, $cm, 0, true, $showcompleted, $userid);
} else {
    // Viewing list of reponses.
    $individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm, $courseid);
}

$responsestable = new mod_individualfeedback_responses_table($individualfeedbackstructure);
$anonresponsestable = new mod_individualfeedback_responses_anon_table($individualfeedbackstructure);

if ($responsestable->is_downloading()) {
    $responsestable->download();
}
if ($anonresponsestable->is_downloading()) {
    $anonresponsestable->download();
}

// Process course select form.
$courseselectform = new mod_individualfeedback_course_select_form($baseurl, $individualfeedbackstructure, $individualfeedback->course == SITEID);
if ($data = $courseselectform->get_data()) {
    redirect(new moodle_url($baseurl, ['courseid' => $data->courseid]));
}
// Print the page header.
navigation_node::override_active_url($baseurl);
$PAGE->set_heading($course->fullname);

/** @var \mod_individualfeedback\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
        [format_string($individualfeedback->name), format_string($course->fullname)],
        get_string('responses', 'individualfeedback')
);

$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => ''
]);

echo $OUTPUT->header();
echo $renderer->main_action_bar($actionbar);
echo $OUTPUT->heading(get_string('show_entries', 'mod_individualfeedback'), 3);

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

if ($userid || $showcompleted) {
    // Print the response of the given user.
    $completedrecord = $individualfeedbackstructure->get_completed();

    if ($userid) {
        $usr = $DB->get_record('user', array('id' => $userid), '*', MUST_EXIST);
        $responsetitle = userdate($completedrecord->timemodified) . ' (' . fullname($usr) . ')';
    } else {
        $responsetitle = get_string('response_nr', 'individualfeedback') . ': ' .
                $completedrecord->random_response . ' (' . get_string('anonymous', 'individualfeedback') . ')';
    }

    echo $OUTPUT->heading($responsetitle, 4);

    $form = new mod_individualfeedback_complete_form(mod_individualfeedback_complete_form::MODE_VIEW_RESPONSE,
            $individualfeedbackstructure, 'individualfeedback_viewresponse_form');
    $form->display();

    list($prevresponseurl, $returnurl, $nextresponseurl) = $userid ?
            $responsestable->get_reponse_navigation_links($completedrecord) :
            $anonresponsestable->get_reponse_navigation_links($completedrecord);

    echo html_writer::start_div('response_navigation');

    $responsenavigation = [
        'col1content' => '',
        'col2content' => html_writer::link($returnurl, get_string('back'), ['class' => 'back_to_list']),
        'col3content' => '',
    ];

    if ($prevresponseurl) {
        $responsenavigation['col1content'] = html_writer::link($prevresponseurl, get_string('prev'), ['class' => 'prev_response']);
    }

    if ($nextresponseurl) {
        $responsenavigation['col3content'] = html_writer::link($nextresponseurl, get_string('next'), ['class' => 'next_response']);
    }

    echo $OUTPUT->render_from_template('core/columns-1to1to1', $responsenavigation);
    echo html_writer::end_div();

} else {
    // Print the list of responses.
    $courseselectform->display();

    if (!manager::can_see_others_in_groups($individualfeedbackstructure->get_cm())) {
        echo $OUTPUT->notification(get_string('notingroup'));
        echo $OUTPUT->footer();
        exit();
    }
    // Show non-anonymous responses (always retrieve them even if current individualfeedback is anonymous).
    $totalrows = $responsestable->get_total_responses_count();
    if (!$individualfeedbackstructure->is_anonymous() || $totalrows) {
        echo $OUTPUT->heading(get_string('non_anonymous_entries', 'individualfeedback', $totalrows), 4);
        $responsestable->display();
    }

    // Show anonymous responses (always retrieve them even if current individualfeedback is not anonymous).
    $individualfeedbackstructure->shuffle_anonym_responses();
    $totalrows = $anonresponsestable->get_total_responses_count();
    if ($individualfeedbackstructure->is_anonymous() || $totalrows) {
        echo $OUTPUT->heading(get_string('anonymous_entries', 'individualfeedback', $totalrows), 4);
        $anonresponsestable->display();
    }

}

// Finish the page.
echo $OUTPUT->footer();

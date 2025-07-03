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
 * the first page to view the feedback
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

$id = required_param('id', PARAM_INT);
$courseid = optional_param('courseid', false, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
$feedback = $PAGE->activityrecord;

$feedbackcompletion = new mod_individualfeedback_completion($feedback, $cm, $courseid);

$context = context_module::instance($cm->id);

if ($course->id == SITEID) {
    $PAGE->set_pagelayout('incourse');
}
$PAGE->set_url('/mod/individualfeedback/view.php', array('id' => $cm->id));

/** @var \mod_individualfeedback\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
    [format_string($feedback->name), format_string($course->fullname)]
);

$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');

// Check whether the feedback is mapped to the given courseid.
if (!has_capability('mod/individualfeedback:edititems', $context) &&
        !$feedbackcompletion->check_course_is_mapped()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('cannotaccess', 'mod_individualfeedback'));
    echo $OUTPUT->footer();
    exit;
}

$viewcompletion = $feedbackcompletion->is_open() && $feedbackcompletion->can_complete() && $feedbackcompletion->can_submit();
$actionbar = new \mod_individualfeedback\output\standard_action_bar(
    $cm->id,
    $viewcompletion,
    $feedbackcompletion->get_resume_page(),
    $courseid
);

// Trigger module viewed event.
$feedbackcompletion->trigger_module_viewed();

/// Print the main part of the page
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////
///////////////////////////////////////////////////////////////////////////

$previewimg = $OUTPUT->pix_icon('t/preview', get_string('preview'));
$previewlnk = new moodle_url('/mod/individualfeedback/print.php', array('id' => $id));
if ($courseid) {
    $previewlnk->param('courseid', $courseid);
}
$preview = html_writer::link($previewlnk, $previewimg);

$PAGE->activityheader->set_description("");

// Print the page header.
echo $OUTPUT->header();

// Show description.
echo $OUTPUT->box_start('generalbox individualfeedback_description');
$options = (object)array('noclean' => true);
echo format_module_intro('feedback', $feedback, $cm->id);
echo $renderer->main_action_bar($actionbar);
echo $OUTPUT->box_end();

//show some infos to the feedback
if (has_capability('mod/individualfeedback:edititems', $context)) {

    echo $OUTPUT->heading(get_string('overview', 'mod_individualfeedback'), 3);

    //get the groupid
    $groupselect = groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/individualfeedback/view.php?id='.$cm->id, true);
    $mygroupid = groups_get_activity_group($cm);

    echo $groupselect.'<div class="clearer">&nbsp;</div>';
    $summary = new mod_individualfeedback\output\summary($feedbackcompletion, $mygroupid);
    echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

    if ($pageaftersubmit = $feedbackcompletion->page_after_submit()) {
        echo $OUTPUT->heading(get_string("page_after_submit", "feedback"), 3);
        echo $OUTPUT->box($pageaftersubmit, 'generalbox individualfeedback_after_submit');
    }
}

if (!$PAGE->has_secondary_navigation()) {
    if (!has_capability('mod/individualfeedback:viewreports', $context) &&
        $feedbackcompletion->can_view_analysis()) {
        $analysisurl = new moodle_url('/mod/individualfeedback/analysis.php', array('id' => $id));
        echo '<div class="mdl-align"><a href="' . $analysisurl->out() . '">';
        echo get_string('completed_feedbacks', 'mod_individualfeedback') . '</a>';
        echo '</div>';
    }

    if (has_capability('mod/individualfeedback:mapcourse', $context) && $feedback->course == SITEID) {
        echo $OUTPUT->box_start('generalbox individualfeedback_mapped_courses');
        echo $OUTPUT->heading(get_string("mappedcourses", "feedback"), 3);
        echo '<p>' . get_string('mapcourse_help', 'mod_individualfeedback') . '</p>';
        $mapurl = new moodle_url('/mod/individualfeedback/mapcourse.php', array('id' => $id));
        echo '<p class="mdl-align">' . html_writer::link($mapurl, get_string('mapcourses', 'mod_individualfeedback')) . '</p>';
        echo $OUTPUT->box_end();
    }
}

if ($feedbackcompletion->can_complete()) {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    if (!$feedbackcompletion->is_open()) {
        // Feedback is not yet open or is already closed.
        echo $OUTPUT->notification(get_string('individualfeedback_is_not_open', 'mod_individualfeedback'));
        echo $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    } else if (!$feedbackcompletion->can_submit()) {
        // Feedback was already submitted.
        echo $OUTPUT->notification(get_string('this_individualfeedback_is_already_submitted', 'mod_individualfeedback'));
        $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    }
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();

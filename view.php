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
$courseid = optional_param('courseid', false, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
$individualfeedback = $PAGE->activityrecord;

$individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $courseid);

$context = context_module::instance($cm->id);

if ($course->id == SITEID) {
    $PAGE->set_pagelayout('incourse');
}
$PAGE->set_url('/mod/individualfeedback/view.php', array('id' => $cm->id));

/** @var \mod_individualfeedback\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
    [format_string($individualfeedback->name), format_string($course->fullname)]
);

$PAGE->set_heading($course->fullname);
$PAGE->add_body_class('limitedwidth');

// Check whether the individualfeedback is mapped to the given courseid.
if (!has_capability('mod/individualfeedback:edititems', $context) &&
        !$individualfeedbackcompletion->check_course_is_mapped()) {
    echo $OUTPUT->header();
    echo $OUTPUT->notification(get_string('cannotaccess', 'mod_individualfeedback'));
    echo $OUTPUT->footer();
    exit;
}

$viewcompletion = $individualfeedbackcompletion->is_open() && $individualfeedbackcompletion->can_complete() && $individualfeedbackcompletion->can_submit();
$actionbar = new \mod_individualfeedback\output\standard_action_bar(
    $cm->id,
    $viewcompletion,
    $individualfeedbackcompletion->get_resume_page(),
    $courseid
);

// Trigger module viewed event.
$individualfeedbackcompletion->trigger_module_viewed();

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

// Print the page header.
echo $OUTPUT->header();

echo $renderer->main_action_bar($actionbar);

//show some infos to the individualfeedback
if (has_capability('mod/individualfeedback:edititems', $context)) {

    echo $OUTPUT->heading(get_string('overview', 'individualfeedback'), 3);

    //get the groupid
    $groupselect = groups_print_activity_menu($cm, $CFG->wwwroot.'/mod/individualfeedback/view.php?id='.$cm->id, true);
    $mygroupid = groups_get_activity_group($cm);

    echo $groupselect.'<div class="clearer">&nbsp;</div>';
    $summary = new mod_individualfeedback\output\summary($individualfeedbackcompletion, $mygroupid);
    echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

    if ($pageaftersubmit = $individualfeedbackcompletion->page_after_submit()) {
        echo $OUTPUT->heading(get_string("page_after_submit", "individualfeedback"), 3);
        echo $OUTPUT->box($pageaftersubmit, 'generalbox individualfeedback_after_submit');
    }
}

if (!$PAGE->has_secondary_navigation()) {
    if (!has_capability('mod/individualfeedback:viewreports', $context) &&
        $individualfeedbackcompletion->can_view_analysis()) {
        $analysisurl = new moodle_url('/mod/individualfeedback/analysis.php', array('id' => $id));
        echo '<div class="mdl-align"><a href="' . $analysisurl->out() . '">';
        echo get_string('completed_individualfeedbacks', 'individualfeedback') . '</a>';
        echo '</div>';
    }

    if (has_capability('mod/individualfeedback:mapcourse', $context) && $individualfeedback->course == SITEID) {
        echo $OUTPUT->box_start('generalbox individualfeedback_mapped_courses');
        echo $OUTPUT->heading(get_string("mappedcourses", "individualfeedback"), 3);
        echo '<p>' . get_string('mapcourse_help', 'individualfeedback') . '</p>';
        $mapurl = new moodle_url('/mod/individualfeedback/mapcourse.php', array('id' => $id));
        echo '<p class="mdl-align">' . html_writer::link($mapurl, get_string('mapcourses', 'individualfeedback')) . '</p>';
        echo $OUTPUT->box_end();
    }
}

if ($individualfeedbackcompletion->can_complete()) {
    echo $OUTPUT->box_start('generalbox boxaligncenter');
    if (!$individualfeedbackcompletion->is_open()) {
        // Individualfeedback is not yet open or is already closed.
        echo $OUTPUT->notification(get_string('individualfeedback_is_not_open', 'individualfeedback'));
        echo $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    } else if (!$individualfeedbackcompletion->can_submit()) {
        // Individualfeedback was already submitted.
        echo $OUTPUT->notification(
            get_string('this_individualfeedback_is_already_submitted', 'individualfeedback'),
            \core\output\notification::NOTIFY_INFO,
            closebutton: false,
        );
        $OUTPUT->continue_button(course_get_url($courseid ?: $course->id));
    }
    echo $OUTPUT->box_end();
}

echo $OUTPUT->footer();

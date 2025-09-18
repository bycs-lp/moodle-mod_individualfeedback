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
 * shows an analysed view of feedback
 *
 * @copyright Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);  // Course module id.
// +++ NEW CODE
$currentsubtab = optional_param('subtab', 'detail_questions', PARAM_TEXT);
$url = new moodle_url('/mod/individualfeedback/analysis.php', array('id'=>$id, 'subtab' => $currentsubtab));
// --- NEW CODE
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);

$feedback = $PAGE->activityrecord;
$feedbackstructure = new mod_individualfeedback_structure($feedback, $cm);

$context = context_module::instance($cm->id);

if (!$feedbackstructure->can_view_analysis()) {
    throw new \moodle_exception('error');
}

// +++ NEW CODE
$actionbar = new \mod_individualfeedback\output\compare_action_bar($cm->id, $url);
// --- NEW CODE

/// Print the page header.

$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
    [format_string($feedback->name), format_string($course->fullname)],
    get_string('analysis', 'individualfeedback')
);

$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => ''
]);
$PAGE->add_body_class('limitedwidth');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('analysis', 'individualfeedback'), 3);
// +++ NEW CODE 
echo $renderer->main_action_bar($actionbar);
// -- NEW CODE

//get the groupid
$mygroupid = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $url);

// Button "Export to excel".
if (has_capability('mod/individualfeedback:viewreports', $context) && $feedbackstructure->get_items()) {
    echo $OUTPUT->container_start('form-buttons');
    $aurl = new moodle_url('/mod/individualfeedback/analysis_to_excel.php', ['sesskey' => sesskey(), 'id' => $id]);
    echo $OUTPUT->single_button($aurl, get_string('export_to_excel', 'individualfeedback'));
    echo $OUTPUT->container_end();
}

// Show the summary.
$summary = new mod_individualfeedback\output\summary($feedbackstructure, $mygroupid);
echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

// Get the items of the feedback.
$items = $feedbackstructure->get_items(true);

$check_anonymously = true;
if ($mygroupid > 0 AND $feedback->anonymous == FEEDBACK_ANONYMOUS_YES) {
    $completedcount = $feedbackstructure->count_completed_responses($mygroupid);
    if ($completedcount < FEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP) {
        $check_anonymously = false;
    }
}

// If other reports will be implemented, delete next code section.
echo '<div>';

if ($check_anonymously) {
    // Print the items in an analysed form.
    foreach ($items as $item) {
        $itemobj = individualfeedback_get_item_class($item->typ);
        $printnr = ($feedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_analysed($item, $printnr, $mygroupid);
    
        // +++ NEW CODE
        if(str_contains($item->options, 'n')) {
            echo "* " . get_string('negative_formulated', 'individualfeedback');
        }
        // --- NEW CODE
    }
} else {
    echo $OUTPUT->heading_with_help(get_string('insufficient_responses_for_this_group', 'individualfeedback'),
        'insufficient_responses',
        'individualfeedback', '', '', 3);
}



echo '</div>';
// End of section.

echo $OUTPUT->footer();

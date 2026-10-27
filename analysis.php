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
 * shows an analysed view of individualfeedback
 *
 * @copyright Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);  // Course module id.

$url = new moodle_url('/mod/individualfeedback/analysis.php', array('id'=>$id));
// +++ MBS-Hack (nersesov) : report mode of the analysis page (H25).
$mode = \mod_individualfeedback\hack\lib::get_analysis_mode();
$url->param('mode', $mode);
// --- MBS-Hack
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);

$individualfeedback = $PAGE->activityrecord;
$individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);

$context = context_module::instance($cm->id);

if (!$individualfeedbackstructure->can_view_analysis()) {
    throw new \moodle_exception('error');
}

/// Print the page header

$PAGE->set_heading($course->fullname);
if (!has_capability('mod/individualfeedback:complete', $context, $USER, false)) {
    // If the user cannot complete the individualfeedback (potentially because is a teacher), hide the navigation footer.
    $PAGE->set_show_navigation_footer(false);
}

$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
    [format_string($individualfeedback->name), format_string($course->fullname)],
    get_string('analysis', 'individualfeedback')
);

$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => ''
]);
$PAGE->add_body_class('limitedwidth');
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('analysis', 'mod_individualfeedback'), 3);

// +++ MBS-Hack (nersesov) : render compare action bar (linked feedbacks comparison).
echo \mod_individualfeedback\hack\lib::render_compare_action_bar($cm->id, $url, $renderer, $mode);
// --- MBS-Hack

//get the groupid
$mygroupid = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $url);

// Button "Export to excel".
if (has_capability('mod/individualfeedback:viewreports', $context) && $individualfeedbackstructure->get_items()) {
    echo $OUTPUT->container_start('form-buttons');
    $aurl = new moodle_url('/mod/individualfeedback/analysis_to_excel.php', ['sesskey' => sesskey(), 'id' => $id]);
    // +++ MBS-Hack (nersesov) : export of the selected report mode (H25).
    $aurl = \mod_individualfeedback\hack\lib::get_analysis_export_url($mode, $aurl, $cm->id);
    // --- MBS-Hack
    echo $OUTPUT->single_button($aurl, get_string('export_to_excel', 'individualfeedback'));
    echo $OUTPUT->container_end();
}

// Show the summary.
$summary = new mod_individualfeedback\output\summary($individualfeedbackstructure, $mygroupid);
echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

// Get the items of the individualfeedback.
$items = $individualfeedbackstructure->get_items(true);

$check_anonymously = true;
if ($mygroupid > 0 AND $individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_YES) {
    $completedcount = $individualfeedbackstructure->count_completed_responses($mygroupid);
    if ($completedcount < INDIVIDUALFEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP) {
        $check_anonymously = false;
    }
}

// +++ MBS-Hack (nersesov) : report modes detail/overview/comparison × questions/groups (H25).
if ($fragment = \mod_individualfeedback\hack\lib::get_analysis_report_fragment($mode)) {
    require($fragment);
} else {
// --- MBS-Hack
echo '<div>';
if ($check_anonymously) {
    // Print the items in an analysed form.
    foreach ($items as $item) {
        $itemobj = individualfeedback_get_item_class($item->typ);
        $printnr = ($individualfeedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_analysed($item, $printnr, $mygroupid);
    }
} else {
    echo $OUTPUT->heading_with_help(get_string('insufficient_responses_for_this_group', 'individualfeedback'),
                                    'insufficient_responses',
                                    'individualfeedback', '', '', 3);
}
echo '</div>';
// +++ MBS-Hack (nersesov) : end of the report-mode switch (H25).
}
// --- MBS-Hack

echo $OUTPUT->footer();

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

// +++ MBS-Hack (nersesov) add compare action bar
$actionbar = new \mod_individualfeedback\output\compare_action_bar($cm->id, $url);
echo $renderer->main_action_bar($actionbar);
// --- MBS-Hack

//get the groupid
$mygroupid = groups_get_activity_group($cm, true);
groups_print_activity_menu($cm, $url);

// Button "Export to excel".
if (has_capability('mod/individualfeedback:viewreports', $context) && $individualfeedbackstructure->get_items()) {
    echo $OUTPUT->container_start('form-buttons');
    $aurl = new moodle_url('/mod/individualfeedback/analysis_to_excel.php', ['sesskey' => sesskey(), 'id' => $id]);
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
                                    'mod_individualfeedback', '', '', 3);
}
echo '</div>';

echo $OUTPUT->footer();

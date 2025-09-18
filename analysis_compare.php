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
$id_compare = required_param('id_compare', PARAM_INT);  // Course module id_comare.
$url = new moodle_url('/mod/individualfeedback/analysis_compare.php', array('id'=>$id));
$PAGE->set_url($url);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
list($course_compare, $cm_compare) = get_course_and_cm_from_cmid($id_compare, 'individualfeedback');
$activityrecord = $DB->get_record('individualfeedback', ['id' => $cm_compare->instance]);

$feedback = $PAGE->activityrecord;

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

echo '<TABLE BORDER="2" CELLPADDING="10">';
echo '<TR>';
echo '<TD>';
analysis_compare($PAGE->activityrecord,$cm, $id, $url);
echo '</TD>';
echo '<TD>';
analysis_compare($activityrecord,$cm_compare, $id_compare, $url);
echo '</TD>';
echo '</TR>';
echo '</TABLE>';


echo $OUTPUT->footer();

function analysis_compare($feedback, $cm, $id, $url) {
    global $OUTPUT;
    echo $feedback->name;
    $feedbackstructure = new mod_individualfeedback_structure($feedback, $cm);

    $context = context_module::instance($cm->id);

    if (!$feedbackstructure->can_view_analysis()) {
        throw new \moodle_exception('error');
    }

    //get the groupid
    $mygroupid = groups_get_activity_group($cm, true);
    groups_print_activity_menu($cm, $url);

    // Show the summary.
    $summary = new mod_individualfeedback\output\summary($feedbackstructure, $mygroupid);
    echo $OUTPUT->render_from_template('mod_individualfeedback/summary', $summary->export_for_template($OUTPUT));

    // Get the items of the feedback.
    $items = $feedbackstructure->get_items(true);

    $check_anonymously = true;
    if ($mygroupid > 0 and $feedback->anonymous == FEEDBACK_ANONYMOUS_YES) {
        $completedcount = $feedbackstructure->count_completed_responses($mygroupid);
        if ($completedcount < FEEDBACK_MIN_ANONYMOUS_COUNT_IN_GROUP) {
            $check_anonymously = false;
        }
    }

    if ($check_anonymously) {
        // Print the items in an analysed form.
        foreach ($items as $item) {
            $itemobj = individualfeedback_get_item_class($item->typ);
            $printnr = ($feedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
            $itemobj->print_analysed($item, $printnr, $mygroupid);
        }
    } else {
        echo $OUTPUT->heading_with_help(get_string('insufficient_responses_for_this_group', 'individualfeedback'),
            'insufficient_responses',
            'feedback', '', '', 3);
    }
}


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
 * shows two analysed view of feedback side by side
 *
 * @copyright   2025 Marcelo A. R. Schmitt <marcelo.rauh@gmail.com> lern.link
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$compareclass = \mod_individualfeedback\local\analysis\compare::class;

$id = required_param('id', PARAM_INT);  // Course module id.
$idcompare = required_param('id_compare', PARAM_INT);  // Course module id_comare.
$url = new moodle_url('/mod/individualfeedback/analysis_compare.php', ['id' => $id]);
$PAGE->set_url($url);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
[$coursecompare, $cmcompare] = get_course_and_cm_from_cmid($idcompare, 'individualfeedback');

$context = \core\context\module::instance($cm->id);
require_capability('mod/individualfeedback:viewreports', $context);

$activityrecord = $DB->get_record('individualfeedback', ['id' => $cmcompare->instance]);

$individualfeedback = $PAGE->activityrecord;

/// Print the page header.

$PAGE->set_heading($course->fullname);

$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
    [format_string($individualfeedback->name), format_string($course->fullname)],
    get_string('analysis', 'individualfeedback')
);

$PAGE->activityheader->set_attrs([
    'hidecompletion' => true,
    'description' => '',
]);
$PAGE->add_body_class('limitedwidth');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('analysis', 'individualfeedback'), 3);

echo html_writer::start_div('individualfeedback-comparison individualfeedback-comparison--compact');
echo html_writer::start_div('individualfeedback-comparison__column');
$compareclass::render($PAGE->activityrecord, $cm, $id, $url, true);
echo html_writer::end_div();
echo html_writer::start_div('individualfeedback-comparison__column');
$compareclass::render($activityrecord, $cmcompare, $idcompare, $url, true);
echo html_writer::end_div();
echo html_writer::end_div();


echo $OUTPUT->footer();

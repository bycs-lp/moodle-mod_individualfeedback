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
 * Duplicates an individualfeedback activity and links the copy with the original.
 *
 * Linked activities (individualfeedback_linked) are the basis of the comparison reports
 * (modes comparison_questions / comparison_groups of analysis.php).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

$id = required_param('id', PARAM_INT);
$sectionreturn = optional_param('sectionreturn', null, PARAM_INT);

[$course, $cm] = get_course_and_cm_from_cmid($id, 'individualfeedback');
require_course_login($course, true, $cm);
require_sesskey();

$context = \core\context\module::instance($cm->id);
require_capability('mod/individualfeedback:edititems', $context);
require_capability('moodle/course:manageactivities', \core\context\course::instance($course->id));

$PAGE->set_url('/mod/individualfeedback/duplicate_and_link.php', ['id' => $cm->id]);

$newcm = \mod_individualfeedback\hack\lib::duplicate_and_link($course, $cm);

$returnurl = new moodle_url('/mod/individualfeedback/analysis.php', ['id' => $newcm->id, 'mode' => 'comparison_questions']);
redirect(
    $returnurl,
    get_string('individualfeedback_cloned_and_linked', 'individualfeedback'),
    null,
    \core\output\notification::NOTIFY_SUCCESS
);

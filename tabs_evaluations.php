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
 * prints the evaluations tabbed bar
 *
 * @author Andreas Grabs
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
defined('MOODLE_INTERNAL') OR die('not allowed');

$tabs = array();
$row  = array();
$inactive = array();
$activated = array();

//some pages deliver the cmid instead the id
if (isset($cmid) AND intval($cmid) AND $cmid > 0) {
    $usedid = $cmid;
} else {
    $usedid = $id;
}

$context = context_module::instance($usedid);

$courseid = optional_param('courseid', false, PARAM_INT);

if (!isset($currentsubtab)) {
    $currentsubtab = '';
}

$urlparams = ['id' => $usedid];
if ($individualfeedback->course == SITEID && $courseid) {
    $urlparams['courseid'] = $courseid;
}

if (has_capability('mod/individualfeedback:viewreports', $context)) {
    $subtabs = array('detail_questions', 'detail_groups', 'overview_questions', 'overview_groups');

    // Check if the individual feedback activity is linked to other activities.
    if (individualfeedback_get_linkedid($individualfeedback->id)) {
        $subtabs[] = 'comparison_questions';
        $subtabs[] = 'comparison_groups';
    }

    foreach ($subtabs as $subtab) {
        $additionalparams = array('subtab' => $subtab);
        $linkurl = new moodle_url('/mod/individualfeedback/analysis.php', array_merge($urlparams, $additionalparams));
        $row[] = new tabobject($subtab, $linkurl->out(), get_string($subtab, 'individualfeedback'));
    }
}

if (count($row) > 1) {
    $tabs[] = $row;

    print_tabs($tabs, $currentsubtab, $inactive, $activated);
}


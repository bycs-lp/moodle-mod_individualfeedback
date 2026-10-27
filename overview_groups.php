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
 * Report fragment "Overview (Groups)": average per question group against the self-assessment.
 *
 * Included by analysis.php for mode=overview_groups (H25); runs in the page scope of analysis.php.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$items = $individualfeedbackstructure->get_groups_and_items();
$groups = [];
foreach ($items as $item) {
    if ($item->typ == 'questiongroup') {
        $groups[$item->id] = $item->name;
    }
}

if (count($groups)) {
    $PAGE->requires->js_call_amd('mod_individualfeedback/filterquestiongroup', 'init');
    echo html_writer::start_tag('form');
    echo get_string('filter_questiongroups', 'individualfeedback');
    echo html_writer::start_tag('select', ['name' => 'questiongroup_select', 'class' => 'questiongroup_select']);
    echo html_writer::tag('option', get_string('all_results', 'individualfeedback'), ['value' => 0]);
    foreach ($groups as $groupid => $groupname) {
        echo html_writer::tag('option', format_string($groupname), ['value' => $groupid]);
    }
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('form');
}

echo html_writer::start_tag('div', ['class' => 'clear']);
foreach ($items as $item) {
    $itemobj = individualfeedback_get_item_class($item->typ);
    if (method_exists($itemobj, 'print_overview_groups')) {
        $printnr = ($individualfeedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_overview_groups($item, $printnr, $mygroupid);
    }
}
echo html_writer::end_tag('div');

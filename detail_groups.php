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
 * shows an analysed view of individualfeedback based on subtab
 *
 * @copyright Martijn Spruijt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

defined('MOODLE_INTERNAL') || die();

// Button "Export to excel".
if (has_capability('mod/individualfeedback:viewreports', $context) && $individualfeedbackstructure->get_items()) {
    echo $OUTPUT->container_start('form-buttons');
    $aurl = new moodle_url('/mod/individualfeedback/detail_groups_to_excel.php', ['sesskey' => sesskey(), 'id' => $id]);
    echo $OUTPUT->single_button($aurl, get_string('export_to_excel', 'individualfeedback'));
    echo $OUTPUT->container_end();
}

// Get the items of the individualfeedback.
$items = $individualfeedbackstructure->get_groups_and_items();

$groups = array();
foreach ($items as $item) {
    if ($item->typ == 'questiongroup') {
        $groups[$item->id] = $item->name;
    }
}

if (count($groups)) {
    $PAGE->requires->js_call_amd('mod_individualfeedback/filterquestiongroup', 'init');

    echo html_writer::start_tag('form');
    echo get_string('filter_questiongroups', 'individualfeedback');
    echo html_writer::start_tag('select', array('name' => 'questiongroup_select', 'class' => 'questiongroup_select'));
    echo html_writer::tag('option', get_string('all_results', 'individualfeedback'), array('value' => 0));
    foreach ($groups as $groupid => $groupname) {
        echo html_writer::tag('option', $groupname, array('value' => $groupid));
    }
    echo html_writer::end_tag('select');
    echo html_writer::end_tag('form');
}

echo html_writer::start_tag('div', array('class' => 'clear'));
// Print the items in an analysed form.
foreach ($items as $item) {
    $itemobj = individualfeedback_get_item_class($item->typ);
    if (method_exists($itemobj, 'print_detail_groups')) {
        $printnr = ($individualfeedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_detail_groups($item, $printnr);
    }
}
echo html_writer::end_tag('div');

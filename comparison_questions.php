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

global $CFG, $OUTPUT, $PAGE;

// Check if it's actually linked.
if (!individualfeedback_get_linkedid($individualfeedback->id)) {
    throw new \moodle_exception('individualfeedback_not_linked', 'individualfeedback');
}

// Check if the questions are equal.
if (!individualfeedback_check_linked_questions($individualfeedback->id)) {
    throw new \moodle_exception('individualfeedback_questions_not_equal', 'individualfeedback');
}

// Button "Export to excel".
if (has_capability('mod/individualfeedback:viewreports', $context) && $individualfeedbackstructure->get_items()) {
    echo $OUTPUT->container_start('form-buttons');
    $aurl = new moodle_url('/mod/individualfeedback/comparison_questions_to_excel.php', ['sesskey' => sesskey(), 'id' => $id]);
    echo $OUTPUT->single_button($aurl, get_string('export_to_excel', 'individualfeedback'));
    echo $OUTPUT->container_end();
}

// Get the items of the individualfeedback.
$items = $individualfeedbackstructure->get_items();
$allindividualfeedbacks = individualfeedback_get_linked_individualfeedbacks($individualfeedback->id);

echo html_writer::start_tag('div', array('class' => 'clear'));
// Print the items in an analysed form.
foreach ($items as $item) {
    $itemobj = individualfeedback_get_item_class($item->typ);
    if (method_exists($itemobj, 'print_comparison_questions')) {
        $printnr = ($individualfeedback->autonumbering && $item->itemnr) ? ($item->itemnr . '.') : '';
        $itemobj->print_comparison_questions($item, $allindividualfeedbacks, $printnr);
    }
}
echo html_writer::end_tag('div');

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

namespace mod_individualfeedback\local;

/**
 * Scale statistics interface for the core multichoice item type.
 *
 * Injected into individualfeedback_item_multichoice with a single marked use-line (D9 pattern).
 * The three fork scale types implement get_answer_data() themselves; multichoice inherits the
 * core get_analysed() only, so the group, overview and comparison reports could not aggregate it
 * (client review 26.10.2026, finding #4).
 *
 * Decision for the checkbox subtype ('c'): a response may carry several answer positions, so an
 * average over the positions is not defined. Checkbox items are excluded from the scale statistics
 * (see individualfeedback_is_statistic_item()); for robustness this method still returns an empty
 * data set for them instead of failing.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait multichoice_statistics_trait {
    /**
     * Collected data for the scale statistics (answer positions 1..n and their counts).
     *
     * @param \stdClass $item The individualfeedback_item record.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @return array answers, values[position => count], totalvalues
     */
    public function get_answer_data($item, $groupid = false, $courseid = false) {
        $info = $this->get_info($item);
        if ($info->subtype === 'c') {
            return ['answers' => 0, 'values' => [], 'totalvalues' => 0];
        }
        return $this->get_item_answer_data($item, INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $groupid, $courseid);
    }
}

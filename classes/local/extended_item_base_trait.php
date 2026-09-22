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
 * Trait with extended item methods used by the analysis features (Hack H18).
 *
 * Injected into individualfeedback_item_base via a single `use` statement
 * (trait injection pattern explicitly approved by A. Wagner, 03.07.2026 —
 * see D9 in review/PLAN.md and review/email_h18_thread.txt). Because the
 * trait lives in the abstract base class, the methods are available on every
 * item type — including the core item types — without touching their files.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\local;

defined('MOODLE_INTERNAL') || die();

/**
 * Extended item methods for overview/comparison analysis and self-assessment.
 *
 * All methods are additive. DB access goes through
 * individualfeedback_get_group_values(), whose hack layer is guarded by
 * is_running_core_test() internally; the guards here only skip work that
 * relies on fork-specific DB fields.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
trait extended_item_base_trait {
    /**
     * Get the self-assessment value for the current user if the capability allows.
     *
     * @param \stdClass $item The individualfeedback item.
     * @return \stdClass|false The first self-assessment value record or false.
     */
    public function check_and_get_self_assessment_data($item) {
        global $PAGE;

        $data = [];
        if (!has_capability('mod/individualfeedback:selfassessment', $PAGE->context)) {
            return false;
        }
        if (!\mod_individualfeedback\hack\lib::is_running_core_test()) {
            $data = individualfeedback_get_group_values($item, false, false, false, true);
        }
        return $data ? reset($data) : false;
    }

    /**
     * Helper collecting per-answer counts for the detailed analysis.
     *
     * @param \stdClass $item The db record from individualfeedback_item.
     * @param string $seperator The answer separator used by the item class.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @return array Analysed data: answers, values[], totalvalues.
     */
    public function get_item_answer_data($item, $seperator, $groupid = false, $courseid = false) {
        $info = $this->get_info($item);

        $analyseditem = [];

        // Get the possible answers.
        $answers = explode($seperator, $info->presentation);
        if (!is_array($answers)) {
            $analyseditem['answers'] = 0;
            return $analyseditem;
        }

        $sizeofanswers = count($answers);
        $analyseditem['answers'] = $sizeofanswers;
        $analyseditem['values'] = [];

        // Get the values.
        $values = null;
        if (!\mod_individualfeedback\hack\lib::is_running_core_test()) {
            $values = individualfeedback_get_group_values($item, $groupid, $courseid, $this->ignoreempty($item));
        }
        if (!$values) {
            $analyseditem['totalvalues'] = 0;
            return $analyseditem;
        }

        // Answer is not required, so check if an answer is given.
        $totalvalues = 0;
        foreach ($values as $value) {
            if ($value->value != null) {
                $totalvalues++;
            }
        }
        $analyseditem['totalvalues'] = $totalvalues;

        // Get the answer count for each answer.
        for ($i = 1; $i <= $sizeofanswers; $i++) {
            $answercount = 0;
            foreach ($values as $value) {
                if ($value->value == $i) {
                    $answercount++;
                }
            }
            $analyseditem['values'][$i] = $answercount;
        }

        return $analyseditem;
    }

    /**
     * Prints the overview questions chart (average vs self-assessment).
     *
     * @param \stdClass $item The item (question) information.
     * @param string $itemnr Item number prefix.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @return void
     */
    public function print_overview_questions($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return;
        }

        $overviewdata = ['average' => 0];
        if ($data = $this->get_answer_data($item, $groupid, $courseid)) {
            if ($data['totalvalues']) {
                $totalvalue = 0;
                foreach ($data['values'] as $key => $value) {
                    $totalvalue += ($key * $value);
                }
                $overviewdata['average'] = round($totalvalue / $data['totalvalues'], 2);
            }
        }

        $overviewdata['selfassessment'] = 0;
        if ($selfassessment = $this->check_and_get_self_assessment_data($item)) {
            $overviewdata['selfassessment'] = $selfassessment->value;
        }

        if (!$overviewdata['average'] && !$overviewdata['selfassessment']) {
            return;
        }

        echo "<table class=\"analysis itemtype_{$item->typ}\">";
        echo '<tr><th colspan="2" align="left">';
        echo $itemnr . ' ';
        if (strval($item->label) !== '') {
            echo '(' . format_string($item->label) . ') ';
        }
        echo format_string($item->name);
        echo '</th></tr>';
        echo "</table>";

        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        if ($overviewdata['average']) {
            $series = new \core\chart_series(
                format_string(get_string('average', 'individualfeedback')),
                [$overviewdata['average']]
            );
            $series->set_labels([$overviewdata['average']]);
            $chart->add_series($series);
        }
        if ($overviewdata['selfassessment']) {
            $series = new \core\chart_series(
                format_string(get_string('selfassessment', 'individualfeedback')),
                [$overviewdata['selfassessment']]
            );
            $series->set_labels([$overviewdata['selfassessment']]);
            $chart->add_series($series);
        }

        $answers = [0 => ''];
        for ($i = 1; $i <= $data['answers']; $i++) {
            $answers[] = get_string('answer') . " " . $i;
        }

        $xaxis = $chart->get_xaxis(0, true);
        $xaxis->set_stepsize(1);
        $xaxis->set_min(0);
        $xaxis->set_max($i);
        $xaxis->set_labels($answers);
        $chart->set_xaxis($xaxis);

        echo $OUTPUT->render($chart);
    }

    /**
     * Prints the comparison questions chart across linked individualfeedbacks.
     *
     * @param \stdClass $item The item (question) information.
     * @param array $allindividualfeedbacks All linked individualfeedback records.
     * @param string $itemnr Item number prefix.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @return void
     */
    public function print_comparison_questions(
        $item,
        $allindividualfeedbacks,
        $itemnr = '',
        $groupid = false,
        $courseid = false
    ) {
        global $OUTPUT, $DB;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return;
        }

        $individualfeedbackids = [];
        foreach ($allindividualfeedbacks as $oneindividualfeedback) {
            $individualfeedbackids[] = $oneindividualfeedback->id;
        }

        $allitems = [];
        foreach ($individualfeedbackids as $id) {
            if ($id != $item->individualfeedback) {
                $params = ['individualfeedback' => $id, 'position' => $item->position];
                $otheritem = $DB->get_record('individualfeedback_item', $params);
            } else {
                $otheritem = $item;
            }
            if ($otheritem) {
                $allitems[$id] = $otheritem;
            }
        }

        $overviewdata = [];
        $data = null;
        foreach ($allitems as $currentitem) {
            if ($data = $this->get_answer_data($currentitem, $groupid, $courseid)) {
                if (!$data['totalvalues']) {
                    $overviewdata[$currentitem->individualfeedback] = 0;
                } else {
                    $totalvalue = 0;
                    foreach ($data['values'] as $key => $value) {
                        $totalvalue += ($key * $value);
                    }
                    $overviewdata[$currentitem->individualfeedback] = round($totalvalue / $data['totalvalues'], 2);
                }
            }
        }

        if (!array_filter($overviewdata)) {
            return;
        }

        echo "<table class=\"analysis itemtype_{$item->typ}\">";
        echo '<tr><th colspan="2" align="left">';
        echo $itemnr . ' ';
        if (strval($item->label) !== '') {
            echo '(' . format_string($item->label) . ') ';
        }
        echo format_string($item->name);
        echo '</th></tr>';
        echo "</table>";

        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        foreach ($overviewdata as $key => $value) {
            $individualfeedbackname = format_string($allindividualfeedbacks[$key]->name);
            $series = new \core\chart_series($individualfeedbackname, [$value]);
            $series->set_labels([$value]);
            $chart->add_series($series);
        }

        $answers = [0 => ''];
        for ($i = 1; $i <= $data['answers']; $i++) {
            $answers[] = get_string('answer') . " " . $i;
        }

        $xaxis = $chart->get_xaxis(0, true);
        $xaxis->set_stepsize(1);
        $xaxis->set_min(0);
        $xaxis->set_max($i);
        $xaxis->set_labels($answers);
        $chart->set_xaxis($xaxis);

        echo $OUTPUT->render($chart);
    }

    /**
     * Excel export for overview questions (average and selfassessment columns).
     *
     * @param object $worksheet Reference to the spreadsheet worksheet.
     * @param int $rowoffset Current row offset.
     * @param object $xlsformats Prepared cell formats.
     * @param \stdClass $item The item (question) information.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @return int The new row offset.
     */
    public function excelprint_overview_questions(
        &$worksheet,
        $rowoffset,
        $xlsformats,
        $item,
        $groupid,
        $courseid = false
    ) {
        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return $rowoffset;
        }

        $overviewdata = ['average' => 0];
        if ($data = $this->get_answer_data($item, $groupid, $courseid)) {
            if ($data['totalvalues']) {
                $totalvalue = 0;
                foreach ($data['values'] as $key => $value) {
                    $totalvalue += ($key * $value);
                }
                $overviewdata['average'] = round($totalvalue / $data['totalvalues'], 2);
            }
        }

        $overviewdata['selfassessment'] = 0;
        if ($selfassessment = $this->check_and_get_self_assessment_data($item)) {
            $overviewdata['selfassessment'] = $selfassessment->value;
        }

        if (!$overviewdata['average'] && !$overviewdata['selfassessment']) {
            return $rowoffset;
        }

        $worksheet->write_string($rowoffset, 0, $item->label, $xlsformats->head2);
        $worksheet->write_string($rowoffset, 1, format_string($item->name), $xlsformats->head2);
        $worksheet->write_string($rowoffset, 2, get_string('average', 'individualfeedback'), $xlsformats->head2);
        $worksheet->write_number($rowoffset + 1, 2, $overviewdata['average'], $xlsformats->default);
        $worksheet->write_string($rowoffset, 3, get_string('selfassessment', 'individualfeedback'), $xlsformats->head2);
        $worksheet->write_number($rowoffset + 1, 3, $overviewdata['selfassessment'], $xlsformats->default);

        return $rowoffset + 2;
    }

    /**
     * Excel export for comparison questions across linked individualfeedbacks.
     *
     * @param object $worksheet Reference to the spreadsheet worksheet.
     * @param int $rowoffset Current row offset.
     * @param object $xlsformats Prepared cell formats.
     * @param \stdClass $item The item (question) information.
     * @param int|false $groupid Group to filter by.
     * @param int|false $courseid Course to filter by.
     * @param array $allindividualfeedbacks All linked individualfeedback records.
     * @return int The new row offset.
     */
    public function excelprint_comparison_questions(
        &$worksheet,
        $rowoffset,
        $xlsformats,
        $item,
        $groupid,
        $courseid = false,
        $allindividualfeedbacks = []
    ) {
        global $DB;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return $rowoffset;
        }

        $allitems = [$item->individualfeedback => $item];
        foreach ($allindividualfeedbacks as $oneindividualfeedback) {
            if ($oneindividualfeedback->id != $item->individualfeedback) {
                $params = ['individualfeedback' => $oneindividualfeedback->id, 'position' => $item->position];
                if ($otheritem = $DB->get_record('individualfeedback_item', $params)) {
                    $allitems[$oneindividualfeedback->id] = $otheritem;
                }
            }
        }

        $overviewdata = [];
        foreach ($allitems as $currentitem) {
            if ($data = $this->get_answer_data($currentitem, $groupid, $courseid)) {
                if (!$data['totalvalues']) {
                    $overviewdata[$currentitem->individualfeedback] = 0;
                } else {
                    $totalvalue = 0;
                    foreach ($data['values'] as $key => $value) {
                        $totalvalue += ($key * $value);
                    }
                    $overviewdata[$currentitem->individualfeedback] = round($totalvalue / $data['totalvalues'], 2);
                }
            }
        }

        if (!array_filter($overviewdata)) {
            return $rowoffset;
        }

        $worksheet->write_string($rowoffset, 0, $item->label, $xlsformats->head2);
        $worksheet->write_string($rowoffset, 1, format_string($item->name), $xlsformats->head2);

        $column = 2;
        foreach ($overviewdata as $key => $value) {
            $individualfeedbackname = format_string($allindividualfeedbacks[$key]->name);
            $worksheet->write_string($rowoffset, $column, $individualfeedbackname, $xlsformats->head2);
            $worksheet->write_number($rowoffset + 1, $column, $value, $xlsformats->default);
            $column++;
        }

        return $rowoffset + 2;
    }
}

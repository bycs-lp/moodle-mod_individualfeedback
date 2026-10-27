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
 * Scale statistics across item types (client review 26.10.2026, findings #4, #5 and #14).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/item/questiongroup/lib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/item/fourlevelapproval/lib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/tests/fixtures/recording_worksheet.php');

use mod_individualfeedback\hack\core_hack_testcase;
use mod_individualfeedback\fixtures\recording_worksheet;

/**
 * Multichoice in the scale statistics, group scope inside question groups, export without answers.
 *
 * @covers \mod_individualfeedback\local\multichoice_statistics_trait
 * @covers \individualfeedback_item_questiongroup
 * @covers \individualfeedback_item_fourlevelapproval
 */
final class scale_statistics_test extends core_hack_testcase {
    /**
     * Course with a feedback containing a question group around one four-level item, two groups of students.
     *
     * @return array [$course, $cm, $individualfeedback, $groupitem, $scaleitem, $group1, $group2]
     */
    private function setup_grouped_feedback(): array {
        global $DB;
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module('individualfeedback', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);

        $base = ['individualfeedback' => $individualfeedback->id, 'template' => 0, 'label' => '', 'presentation' => '',
            'hasvalue' => 0, 'required' => 0, 'dependitem' => 0, 'dependvalue' => '', 'options' => ''];
        $groupid = $DB->insert_record('individualfeedback_item', (object) array_merge($base, ['name' => 'Group A', 'typ' => 'questiongroup', 'position' => 1]));
        $scaleid = $DB->insert_record('individualfeedback_item', (object) array_merge($base, [
            'name' => 'Scale Q', 'typ' => 'fourlevelapproval', 'position' => 2, 'hasvalue' => 1,
            'presentation' => 'r' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_TYPE_SEP . 'a' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_LINE_SEP
                . 'b' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_LINE_SEP . 'c' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_LINE_SEP . 'd',
        ]));
        $DB->insert_record('individualfeedback_item', (object) array_merge($base, ['name' => '', 'typ' => 'questiongroupend', 'position' => 3, 'dependitem' => $groupid]));

        $group1 = $generator->create_group(['courseid' => $course->id]);
        $group2 = $generator->create_group(['courseid' => $course->id]);
        // Group 1: two users answering position 1 and 2; group 2: one user answering position 4.
        foreach ([[$group1, 1], [$group1, 2], [$group2, 4]] as [$group, $answer]) {
            $user = $generator->create_user();
            $generator->enrol_user($user->id, $course->id, 'student');
            $generator->create_group_member(['groupid' => $group->id, 'userid' => $user->id]);
            $completedid = $DB->insert_record('individualfeedback_completed', (object) [
                'individualfeedback' => $individualfeedback->id, 'userid' => $user->id, 'timemodified' => time(),
                'random_response' => 0, 'anonymous_response' => INDIVIDUALFEEDBACK_ANONYMOUS_NO, 'selfassessment' => 0,
            ]);
            $DB->insert_record('individualfeedback_value', (object) [
                'course_id' => $course->id, 'item' => $scaleid, 'completed' => $completedid, 'value' => (string) $answer, 'tmplid' => 0,
            ]);
        }
        return [
            $course, $cm, $individualfeedback,
            $DB->get_record('individualfeedback_item', ['id' => $groupid]),
            $DB->get_record('individualfeedback_item', ['id' => $scaleid]),
            $group1, $group2,
        ];
    }

    /**
     * Finding #4: single-answer multichoice takes part in the statistics, the checkbox subtype does not.
     */
    public function test_multichoice_statistics_interface(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module('individualfeedback', ['course' => $course->id]);
        $plugingenerator = $generator->get_plugin_generator('mod_individualfeedback');
        $radio = $plugingenerator->create_item_multichoice($individualfeedback, ['name' => 'Radio', 'subtype' => 'r', 'values' => "a\nb\nc"]);
        $check = $plugingenerator->create_item_multichoice($individualfeedback, ['name' => 'Check', 'subtype' => 'c', 'values' => "a\nb\nc"]);

        $this->assertTrue(individualfeedback_is_statistic_item($radio));
        $this->assertFalse(individualfeedback_is_statistic_item($check), 'Checkbox multichoice is excluded from scale statistics.');

        $itemobj = individualfeedback_get_item_class('multichoice');
        $this->assertTrue(method_exists($itemobj, 'get_answer_data'), 'The use-line injection in item/multichoice/lib.php is lost (finding #4).');

        $user = $generator->create_user();
        $generator->enrol_user($user->id, $course->id, 'student');
        $plugingenerator->create_response(['cmid' => $individualfeedback->cmid, 'userid' => $user->id, 'Radio' => 'b']);

        $data = $itemobj->get_answer_data($radio, false, false);
        $this->assertSame(3, $data['answers']);
        $this->assertSame(1, $data['totalvalues']);
        $this->assertSame(1, $data['values'][2]);

        $data = $itemobj->get_answer_data($check, false, false);
        $this->assertSame(0, $data['totalvalues']);

        // The overview rendering must not fail for either item.
        ob_start();
        try {
            $itemobj->print_overview_questions($radio);
            $itemobj->print_overview_questions($check);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Finding #5: the group export inside a question group respects the selected group.
     */
    public function test_question_group_export_respects_group_scope(): void {
        $this->resetAfterTest();
        $this->setAdminUser();
        [$course, $cm, $individualfeedback, $groupitem, $scaleitem, $group1, $group2] = $this->setup_grouped_feedback();
        $groupobj = individualfeedback_get_item_class('questiongroup');
        $formats = (object) ['head2' => null, 'default' => null, 'procent' => null, 'value_bold' => null, 'head1' => null];

        $this->assertCount(1, $groupobj->get_question_in_group($groupitem));

        $all = new recording_worksheet();
        $groupobj->excelprint_detail_groups($all, 0, $formats, $groupitem, false, false);
        $g1 = new recording_worksheet();
        $groupobj->excelprint_detail_groups($g1, 0, $formats, $groupitem, $group1->id, false);
        $g2 = new recording_worksheet();
        $groupobj->excelprint_detail_groups($g2, 0, $formats, $groupitem, $group2->id, false);

        // Row 2 holds the answer counts per position (row 1 the answer texts, row 3 the quotients):
        // all = [1, 1, 0, 1], group 1 = [1, 1, 0, 0], group 2 = [0, 0, 0, 1].
        $counts = fn(recording_worksheet $ws) => array_values(array_map(
            fn($cell) => (int) $cell[2],
            array_filter($ws->cells, fn($cell) => $cell[0] === 2 && is_numeric($cell[2]) && $cell[1] >= 2)
        ));
        $this->assertSame([1, 1, 0, 1], $counts($all));
        $this->assertSame([1, 1, 0, 0], $counts($g1), 'Group 1 export must only count group 1 members.');
        $this->assertSame([0, 0, 0, 1], $counts($g2), 'Group 2 export must only count group 2 members.');
    }

    /**
     * Finding #14: exporting a scale item without answers returns the row offset instead of failing.
     */
    public function test_scale_export_without_answers(): void {
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module('individualfeedback', ['course' => $course->id]);
        $formats = (object) ['head2' => null, 'default' => null, 'procent' => null, 'value_bold' => null, 'head1' => null];

        foreach (['fourlevelapproval', 'fivelevelapproval', 'fourlevelfrequency'] as $type) {
            $item = (object) [
                'id' => 0, 'individualfeedback' => $individualfeedback->id, 'template' => 0, 'name' => $type, 'label' => 'l',
                'presentation' => 'r' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_TYPE_SEP . 'a' . INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_LINE_SEP . 'b',
                'typ' => $type, 'hasvalue' => 1, 'position' => 1, 'required' => 0, 'dependitem' => 0, 'dependvalue' => '', 'options' => '',
            ];
            global $DB;
            $item->id = $DB->insert_record('individualfeedback_item', $item);
            $itemobj = individualfeedback_get_item_class($type);
            $ws = new recording_worksheet();
            $this->assertSame(7, $itemobj->excelprint_item($ws, 7, $formats, $item, false, false), "{$type}: no answers → unchanged offset");
            $this->assertEmpty($ws->cells);
        }
    }
}

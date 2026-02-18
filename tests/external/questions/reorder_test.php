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

namespace mod_individualfeedback\external\questions;

use core_external\external_api;

/**
 * Unit tests of external class for re-ordering feedback question items
 *
 * @package    mod_individualfeedback
 * @covers     \mod_individualfeedback\external\questions\reorder
 * @copyright  2024 Mikel Martín <mikel@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
final class reorder_test extends \advanced_testcase {

    /**
     * Text execute method
     */
    public function test_execute(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        // Create a course with a feedback activity and some questions.
        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);

        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item1 = $individualfeedbackgenerator->create_item_label($individualfeedback);
        $item2 = $individualfeedbackgenerator->create_item_info($individualfeedback);
        $item3 = $individualfeedbackgenerator->create_item_numeric($individualfeedback);

        // Check initial items order.
        $this->assertEquals([$item1->id, $item2->id, $item3->id], $this->get_individualfeedback_item_order($individualfeedback));

        // Call the execute method to invert the items order.
        $result = reorder::execute($cm->id, "$item3->id,$item2->id,$item1->id");
        $result = external_api::clean_returnvalue(reorder::execute_returns(), $result);
        $this->assertTrue($result);

        // Check items order is inverted.
        $this->assertEquals([$item3->id, $item2->id, $item1->id], $this->get_individualfeedback_item_order($individualfeedback));
    }

    /**
     * Get the order of the feedback items.
     *
     * @param object $individualfeedback The individualfeedback activity.
     * @return array
     */
    private function get_individualfeedback_item_order($individualfeedback) {
        global $DB;
        return $DB->get_fieldset_select(
            'individualfeedback_item',
            'id',
            'individualfeedback = :individualfeedbackid ORDER BY position ASC',
            ['individualfeedbackid' => $individualfeedback->id]
        );
    }
}

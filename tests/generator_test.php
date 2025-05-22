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
 * Genarator tests.
 *
 * @package    mod_individualfeedback
 * @copyright  2013 Ankit Agarwal
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @group      mod_individualfeedback
 * @group      mebis
 */

global $CFG;

class mod_individualfeedback_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('individualfeedback', array('course' => $course->id)));
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course->id));
        $this->assertEquals(1, $DB->count_records('individualfeedback', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('individualfeedback', array('course' => $course->id)));
        $this->assertTrue($DB->record_exists('individualfeedback', array('id' => $individualfeedback->id)));

        $params = array('course' => $course->id, 'name' => 'One more individualfeedback');
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', $params);
        $this->assertEquals(2, $DB->count_records('individualfeedback', array('course' => $course->id)));
        $this->assertEquals('One more individualfeedback', $DB->get_field_select('individualfeedback', 'name', 'id = :id',
                array('id' => $individualfeedback->id)));
    }

    public function test_create_item_label() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        $item1 = $individualfeedbackgenerator->create_item_label($individualfeedback);

        $editor = array(
            'text' => "Custom editor",
            'format' => FORMAT_HTML,
            'itemid' => 0
        );
        $item2 = $individualfeedbackgenerator->create_item_label($individualfeedback, array('presentation_editor' => $editor));
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($item1->id, $records[$item1->id]->id);
        $this->assertEquals($item2->id, $records[$item2->id]->id);
        $this->assertEquals($editor['text'], $records[$item2->id]->presentation);
        $this->assertEquals('label', $records[$item1->id]->typ);
    }

    public function test_create_item_multichoice() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        $item1 = $individualfeedbackgenerator->create_item_multichoice($individualfeedback);
        $item2 = $individualfeedbackgenerator->create_item_multichoice($individualfeedback, array('values' => "1\n2\n3\n4\n5", 'horizontal' => 1));
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($item1->id, $records[$item1->id]->id);
        $this->assertEquals($item2->id, $records[$item2->id]->id);
        $this->assertEquals('r>>>>>a|b|c|d|e', $records[$item1->id]->presentation);
        $this->assertEquals('r>>>>>1|2|3|4|5<<<<<1', $records[$item2->id]->presentation);
        $this->assertEquals('multichoice', $records[$item1->id]->typ);
    }

    public function test_create_item_numeric() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        $item1 = $individualfeedbackgenerator->create_item_numeric($individualfeedback);
        $item2 = $individualfeedbackgenerator->create_item_numeric($individualfeedback, array('rangefrom' => '0', 'rangeto' => '10'));
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($item1->id, $records[$item1->id]->id);
        $this->assertEquals($item2->id, $records[$item2->id]->id);
        $this->assertEquals('-|-', $records[$item1->id]->presentation);
        $this->assertEquals('0|10', $records[$item2->id]->presentation);
        $this->assertEquals('numeric', $records[$item1->id]->typ);
    }

    public function test_create_item_textarea() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        $item1 = $individualfeedbackgenerator->create_item_textarea($individualfeedback);
        $item2 = $individualfeedbackgenerator->create_item_textarea($individualfeedback, array('itemwidth' => '20', 'itemheight' => '10'));
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($item1->id, $records[$item1->id]->id);
        $this->assertEquals($item2->id, $records[$item2->id]->id);
        $this->assertEquals('40|20', $records[$item1->id]->presentation);
        $this->assertEquals('20|10', $records[$item2->id]->presentation);
        $this->assertEquals('textarea', $records[$item1->id]->typ);
    }

    public function test_create_item_textfield() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        $item1 = $individualfeedbackgenerator->create_item_textfield($individualfeedback);
        $item2 = $individualfeedbackgenerator->create_item_textfield($individualfeedback, array('itemsize' => '20', 'itemmaxlength' => '10'));
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($item1->id, $records[$item1->id]->id);
        $this->assertEquals($item2->id, $records[$item2->id]->id);
        $this->assertEquals('20|30', $records[$item1->id]->presentation);
        $this->assertEquals('20|10', $records[$item2->id]->presentation);
        $this->assertEquals('textfield', $records[$item1->id]->typ);
    }

    public function test_create_item_pagebreak() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', array('course' => $course));
        $individualfeedbackgenerator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');

        // Try to create a page break in an empty individualfeedback (no items).
        $individualfeedbackgenerator->create_item_pagebreak($individualfeedback);
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(0, $records);

        // Create at least one item before the pagebreak.
        $individualfeedbackgenerator->create_item_textfield($individualfeedback);

        // Now, create one pagebreak.
        $item1 = $individualfeedbackgenerator->create_item_pagebreak($individualfeedback);
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);

        // This pagebreak won't be created (there is already one in the last position).
        $item2 = $individualfeedbackgenerator->create_item_pagebreak($individualfeedback);
        $this->assertFalse($item2);
        $records = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'id');
        $this->assertCount(2, $records);
    }
}


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
 * Unit tests for mod_individualfeedback\hack\lib.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

/**
 * Tests for the hack\lib class which provides template and item helpers
 * that override or extend core mod_feedback behaviour.
 *
 * @covers \mod_individualfeedback\hack\lib
 */
final class hack_lib_test extends core_hack_testcase {
    // -------------------------------------------------------------------------
    // individualfeedback_create_template
    // -------------------------------------------------------------------------

    /**
     * Creating a private template stores a record with the correct course owner.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_create_template
     */
    public function test_create_private_template(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $template = individualfeedback_create_template($course->id, 'My Private Template', 0);

        $this->assertNotEmpty($template->id, 'Template id must be set after creation.');
        $this->assertEquals('My Private Template', $template->name);
        $this->assertEquals(0, $template->ispublic);
        $this->assertEquals(
            $course->id,
            $template->course,
            'Private template must be owned by the given course.'
        );
        $this->assertTrue(
            $DB->record_exists('individualfeedback_template', ['id' => $template->id]),
            'Template must be persisted in the database.'
        );
    }

    /**
     * A public template is stored with course = 0, per MBS requirements only admins
     * may create public (system-level) templates.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_create_template
     */
    public function test_create_public_template_sets_course_zero(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $template = individualfeedback_create_template($course->id, 'System Template', 1);

        $this->assertEquals(1, $template->ispublic);
        $this->assertEquals(
            0,
            $template->course,
            'Public template course must be 0 (system scope.'
        );
    }

    /**
     * Template name is stored exactly as provided.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_create_template
     */
    public function test_create_template_preserves_name(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $name = 'Ünïcödé Témplate <>&"';
        $template = individualfeedback_create_template($course->id, $name, 0);

        $this->assertSame($name, $template->name);
    }

    // -------------------------------------------------------------------------
    // individualfeedback_delete_item
    // -------------------------------------------------------------------------

    /**
     * Deleting an existing item removes it from the database.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_delete_item
     */
    public function test_delete_item_removes_record(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id]
        );
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_textfield($individualfeedback);

        $this->assertTrue(
            $DB->record_exists('individualfeedback_item', ['id' => $item->id]),
            'Item must exist before deletion.'
        );

        individualfeedback_delete_item($item->id);

        $this->assertFalse(
            $DB->record_exists('individualfeedback_item', ['id' => $item->id]),
            'Item must be removed from the database after deletion.'
        );
    }

    /**
     * Deleting a non-existent item must not throw an exception.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_delete_item
     */
    public function test_delete_nonexistent_item_is_silent(): void {
        $this->resetAfterTest();
        // Expect no exception for a missing item id.
        individualfeedback_delete_item(PHP_INT_MAX, false);
        $this->assertTrue(true, 'No exception should be thrown for a non-existent item.');
    }

    /**
     * Deleting an item also removes its associated tmp and saved values.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_delete_item
     */
    public function test_delete_item_removes_values(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id]
        );
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_textfield($individualfeedback);

        // Insert a fake saved value for this item.
        $completedid = $DB->insert_record('individualfeedback_completed', (object)[
            'individualfeedback' => $individualfeedback->id,
            'userid'             => 0,
            'timemodified'       => time(),
            'random_response'    => 0,
            'anonymous_response' => 1,
            'selfassessment'     => 0,
        ]);
        $DB->insert_record('individualfeedback_value', (object)[
            'course_id' => $course->id,
            'item'      => $item->id,
            'completed' => $completedid,
            'value'     => 'test',
            'tmplid'    => 0,
        ]);

        individualfeedback_delete_item($item->id);

        $this->assertEquals(
            0,
            $DB->count_records('individualfeedback_value', ['item' => $item->id]),
            'All saved values for the deleted item must be removed.'
        );
    }

    // -------------------------------------------------------------------------
    // individualfeedback_load_individualfeedback_items_options
    // -------------------------------------------------------------------------

    /**
     * The options list must include the custom MBS question types introduced in commit 3.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_load_individualfeedback_items_options
     */
    public function test_options_include_custom_question_types(): void {
        $this->resetAfterTest();

        $options = individualfeedback_load_individualfeedback_items_options();

        $this->assertIsArray($options, 'Options must be an array.');
        $this->assertArrayHasKey(
            'questiongroup',
            $options,
            'Custom type "questiongroup" must be present in item options.'
        );
    }

    /**
     * The options list must also retain the pagebreak entry (inherited from core).
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_load_individualfeedback_items_options
     */
    public function test_options_include_pagebreak(): void {
        $this->resetAfterTest();

        $options = individualfeedback_load_individualfeedback_items_options();

        $this->assertArrayHasKey(
            'pagebreak',
            $options,
            'Inherited "pagebreak" type must remain in options.'
        );
    }

    // -------------------------------------------------------------------------
    // individualfeedback_get_group_values
    // -------------------------------------------------------------------------

    /**
     * get_group_values returns an array (possibly empty) for an item with no responses.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_get_group_values
     */
    public function test_get_group_values_returns_empty_array_for_no_responses(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $params = [
            'course'    => $course->id,
            'anonymous' => INDIVIDUALFEEDBACK_ANONYMOUS_NO,
        ];
        $individualfeedback = $this->getDataGenerator()->create_module('individualfeedback', $params);
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_textfield($individualfeedback);

        $values = individualfeedback_get_group_values($item, false, $course->id);

        $this->assertIsArray($values, 'get_group_values must always return an array.');
        $this->assertCount(0, $values, 'No values expected when no responses have been submitted.');
    }

    /**
     * Self-assessment flag filters values correctly — selfassessment=1 returns only
     * records with ic.selfassessment=1.
     *
     * @covers \mod_individualfeedback\hack\lib::individualfeedback_get_group_values
     */
    public function test_get_group_values_selfassessment_filter(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id, 'anonymous' => INDIVIDUALFEEDBACK_ANONYMOUS_NO]
        );
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_textfield($individualfeedback);

        // Insert one normal response and one self-assessment response.
        $normalid = $DB->insert_record('individualfeedback_completed', (object)[
            'individualfeedback' => $individualfeedback->id,
            'userid'             => 0,
            'timemodified'       => time(),
            'random_response'    => 0,
            'anonymous_response' => 0,
            'selfassessment'     => 0,
        ]);
        $DB->insert_record('individualfeedback_value', (object)[
            'course_id' => $course->id,
            'item'      => $item->id,
            'completed' => $normalid,
            'value'     => 'normal',
            'tmplid'    => 0,
        ]);

        $selfid = $DB->insert_record('individualfeedback_completed', (object)[
            'individualfeedback' => $individualfeedback->id,
            'userid'             => 2,
            'timemodified'       => time(),
            'random_response'    => 0,
            'anonymous_response' => 0,
            'selfassessment'     => 1,
        ]);
        $DB->insert_record('individualfeedback_value', (object)[
            'course_id' => $course->id,
            'item'      => $item->id,
            'completed' => $selfid,
            'value'     => 'selfassess',
            'tmplid'    => 0,
        ]);

        // Requesting selfassessment=false must NOT return the self-assessment record.
        $normal = individualfeedback_get_group_values($item, false, $course->id, false, false, false);
        $this->assertCount(1, $normal);

        // Requesting selfassessment=true must return only the self-assessment record.
        $this->setAdminUser(); // setUser so $USER->id matches inserted userid.
        $self = individualfeedback_get_group_values($item, false, $course->id, false, false, true);
        $this->assertCount(1, $self);
    }
}

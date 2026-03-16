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
 * Unit tests for the questiongroup question type.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/item/questiongroup/lib.php');

/**
 * Tests for individualfeedback_item_questiongroup.
 *
 * questiongroup is a structural container type that:
 * - groups related questions visually and analytically
 * - has no store value of its own (get_hasvalue() returns 0)
 * - always creates an empty string as its stored value
 * - pairs with a "questiongroupend" sentinel item in the DB
 *
 * @covers \individualfeedback_item_questiongroup
 */
final class questiongroup_test extends \advanced_testcase {

    /** @var \individualfeedback_item_questiongroup */
    private \individualfeedback_item_questiongroup $item;

    protected function setUp(): void {
        parent::setUp();
        $this->item = new \individualfeedback_item_questiongroup();
    }

    // -----------------------------------------------------------------------
    // Type identity
    // -----------------------------------------------------------------------

    /**
     * The internal type string must be 'questiongroup'.
     *
     * @covers \individualfeedback_item_questiongroup
     */
    public function test_type_is_questiongroup(): void {
        $reflection = new \ReflectionClass($this->item);
        $prop = $reflection->getProperty('type');
        $prop->setAccessible(true);
        $this->assertEquals('questiongroup', $prop->getValue($this->item));
    }

    // -----------------------------------------------------------------------
    // get_hasvalue()
    // -----------------------------------------------------------------------

    /**
     * questiongroup is a structural container; it carries no response value.
     *
     * @covers \individualfeedback_item_questiongroup::get_hasvalue
     */
    public function test_get_hasvalue_returns_zero(): void {
        $result = $this->item->get_hasvalue();
        $this->assertEquals(0, $result,
            'questiongroup must return 0 from get_hasvalue() — it stores no response data.');
    }

    // -----------------------------------------------------------------------
    // create_value()
    // -----------------------------------------------------------------------

    /**
     * create_value() must always produce an empty string since questiongroup
     * stores no user input.
     *
     * @covers \individualfeedback_item_questiongroup::create_value
     */
    public function test_create_value_returns_empty_string(): void {
        $result = $this->item->create_value([]);
        $this->assertSame('', $result);
    }

    /**
     * create_value() ignores any passed data and always returns ''.
     *
     * @covers \individualfeedback_item_questiongroup::create_value
     */
    public function test_create_value_ignores_input(): void {
        $result = $this->item->create_value(['anything', 123, true]);
        $this->assertSame('', $result);
    }

    // -----------------------------------------------------------------------
    // get_printval()
    // -----------------------------------------------------------------------

    /**
     * get_printval() returns an empty string — group headers are not printable values.
     *
     * @covers \individualfeedback_item_questiongroup::get_printval
     */
    public function test_get_printval_returns_empty(): void {
        $record = $this->make_item_record();
        $value  = (object)['value' => 'anything'];
        $result = $this->item->get_printval($record, $value);
        $this->assertSame('', $result);
    }

    // -----------------------------------------------------------------------
    // save_item() — creates questiongroupend sentinel
    // -----------------------------------------------------------------------

    /**
     * Saving a new questiongroup item also creates the paired questiongroupend
     * sentinel record in the database.
     *
     * @covers \individualfeedback_item_questiongroup::save_item
     */
    public function test_save_item_creates_questiongroupend(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback', ['course' => $course->id]
        );

        // Insert a minimal questiongroup item record directly (bypassing the form).
        $itemdata = (object)[
            'individualfeedback' => $individualfeedback->id,
            'typ'                => 'questiongroup',
            'name'               => 'My Group',
            'label'              => 'grp1',
            'presentation'       => '',
            'options'            => '',
            'position'           => 1,
            'required'           => 0,
            'dependitem'         => 0,
            'dependvalue'        => '',
            'hasvalue'           => 0,
        ];
        $itemid = $DB->insert_record('individualfeedback_item', $itemdata);

        // Manually call save_item using a partial mock of the form data.
        // We use reflection to inject item data and call save_item().
        $reflection   = new \ReflectionClass($this->item);
        $itemProperty = $reflection->getProperty('item');
        $itemProperty->setAccessible(true);

        $itemdata->id         = $itemid;
        $itemdata->clone_item = false;
        $itemProperty->setValue($this->item, $itemdata);

        $this->item->save_item();

        // After save_item(), there must be a questiongroupend record linked back to $itemid.
        $end = $DB->get_record('individualfeedback_item', [
            'individualfeedback' => $individualfeedback->id,
            'typ'                => 'questiongroupend',
            'dependitem'         => $itemid,
        ]);
        $this->assertNotFalse($end,
            'save_item() must create a paired questiongroupend item referencing the group id.');
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function make_item_record(): \stdClass {
        $record = new \stdClass();
        $record->presentation = '';
        $record->options      = '';
        $record->typ          = 'questiongroup';
        $record->required     = 0;
        $record->label        = '';
        $record->name         = 'Group header';
        return $record;
    }
}

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
 * Unit tests for the fourlevelapproval question type.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/item/fourlevelapproval/lib.php');

/**
 * Tests for individualfeedback_item_fourlevelapproval.
 *
 * The fourlevelapproval type is a custom MBS rating scale with 4 approval levels.
 * Presentation format: "{subtype}>>>>>{options}<<<<<{horizontal}"
 * where options are pipe-separated ("Option 1|Option 2|Option 3|Option 4").
 *
 * @covers \individualfeedback_item_fourlevelapproval
 */
final class fourlevelapproval_test extends \advanced_testcase {

    /** @var \individualfeedback_item_fourlevelapproval */
    private \individualfeedback_item_fourlevelapproval $item;

    protected function setUp(): void {
        parent::setUp();
        $this->item = new \individualfeedback_item_fourlevelapproval();
    }

    // -----------------------------------------------------------------------
    // get_info() – presentation string parsing
    // -----------------------------------------------------------------------

    /**
     * Radio subtype is parsed correctly from a standard presentation string.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_info
     */
    public function test_get_info_radio_subtype(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');
        $info = $this->item->get_info($record);

        $this->assertEquals('r', $info->subtype);
        $this->assertEquals('A|B|C|D', $info->presentation);
        $this->assertFalse($info->horizontal);
    }

    /**
     * Checkbox subtype 'c' is parsed correctly.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_info
     */
    public function test_get_info_checkbox_subtype(): void {
        $record = $this->make_item_record('c>>>>>A|B|C|D');
        $info = $this->item->get_info($record);

        $this->assertEquals('c', $info->subtype);
    }

    /**
     * Dropdown subtype 'd' is parsed correctly.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_info
     */
    public function test_get_info_dropdown_subtype(): void {
        $record = $this->make_item_record('d>>>>>A|B|C|D');
        $info = $this->item->get_info($record);

        $this->assertEquals('d', $info->subtype);
    }

    /**
     * Horizontal flag is parsed correctly when set to 1.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_info
     */
    public function test_get_info_horizontal_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D<<<<<1');
        $info = $this->item->get_info($record);

        $this->assertTrue($info->horizontal);
    }

    /**
     * Vertical layout: horizontal flag is false when omitted.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_info
     */
    public function test_get_info_vertical_by_default(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D<<<<<0');
        $info = $this->item->get_info($record);

        $this->assertFalse($info->horizontal);
    }

    // -----------------------------------------------------------------------
    // ignoreempty / negativeformulated / hidenoselect flag helpers
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelapproval::set_ignoreempty
     * @covers \individualfeedback_item_fourlevelapproval::ignoreempty
     */
    public function test_ignoreempty_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');

        $this->item->set_ignoreempty($record, true);
        $this->assertTrue($this->item->ignoreempty($record));

        $this->item->set_ignoreempty($record, false);
        $this->assertFalse($this->item->ignoreempty($record));
    }

    /**
     * Setting the flag twice does not duplicate it in the options string.
     *
     * @covers \individualfeedback_item_fourlevelapproval::set_ignoreempty
     */
    public function test_ignoreempty_no_duplicate(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');

        $this->item->set_ignoreempty($record, true);
        $this->item->set_ignoreempty($record, true);

        $count = substr_count($record->options, INDIVIDUALFEEDBACK_FOURLEVELAPPROVAL_IGNOREEMPTY);
        $this->assertEquals(1, $count, 'Flag must not be duplicated in options string.');
    }

    /**
     * @covers \individualfeedback_item_fourlevelapproval::set_negativeformulated
     * @covers \individualfeedback_item_fourlevelapproval::negativeformulated
     */
    public function test_negativeformulated_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');

        $this->item->set_negativeformulated($record, true);
        $this->assertTrue($this->item->negativeformulated($record));

        $this->item->set_negativeformulated($record, false);
        $this->assertFalse($this->item->negativeformulated($record));
    }

    /**
     * @covers \individualfeedback_item_fourlevelapproval::set_hidenoselect
     * @covers \individualfeedback_item_fourlevelapproval::hidenoselect
     */
    public function test_hidenoselect_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');

        $this->item->set_hidenoselect($record, true);
        $this->assertTrue($this->item->hidenoselect($record));

        $this->item->set_hidenoselect($record, false);
        $this->assertFalse($this->item->hidenoselect($record));
    }

    // -----------------------------------------------------------------------
    // create_value()
    // -----------------------------------------------------------------------

    /**
     * create_value() joins non-empty, unique array values with the pipe separator.
     *
     * @covers \individualfeedback_item_fourlevelapproval::create_value
     */
    public function test_create_value_joins_with_separator(): void {
        $result = $this->item->create_value([1 => 1, 2 => 2, 3 => 3]);
        $this->assertEquals('1|2|3', $result);
    }

    /**
     * create_value() filters out empty/zero values.
     *
     * @covers \individualfeedback_item_fourlevelapproval::create_value
     */
    public function test_create_value_filters_empty(): void {
        $result = $this->item->create_value([0 => 0, 1 => 2, 2 => 0]);
        $this->assertEquals('2', $result);
    }

    /**
     * create_value() deduplicates repeated entries.
     *
     * @covers \individualfeedback_item_fourlevelapproval::create_value
     */
    public function test_create_value_deduplicates(): void {
        $result = $this->item->create_value([1 => 3, 2 => 3]);
        $this->assertEquals('3', $result);
    }

    // -----------------------------------------------------------------------
    // compare_value()
    // -----------------------------------------------------------------------

    /**
     * compare_value() returns true when the stored value matches the label text.
     *
     * @covers \individualfeedback_item_fourlevelapproval::compare_value
     */
    public function test_compare_value_match(): void {
        $record = $this->make_item_record('r>>>>>Strongly agree|Agree|Disagree|Strongly disagree');
        $result = $this->item->compare_value($record, '2', 'Agree');
        $this->assertTrue($result);
    }

    /**
     * compare_value() returns false when there is no match.
     *
     * @covers \individualfeedback_item_fourlevelapproval::compare_value
     */
    public function test_compare_value_no_match(): void {
        $record = $this->make_item_record('r>>>>>Strongly agree|Agree|Disagree|Strongly disagree');
        $result = $this->item->compare_value($record, '1', 'Agree');
        $this->assertFalse($result);
    }

    /**
     * compare_value() handles array dbvalue (checkbox subtype).
     *
     * @covers \individualfeedback_item_fourlevelapproval::compare_value
     */
    public function test_compare_value_array_dbvalue(): void {
        $record = $this->make_item_record('c>>>>>A|B|C|D');
        // Index 3 corresponds to 'C'.
        $result = $this->item->compare_value($record, [1 => 1, 2 => 3], 'C');
        $this->assertTrue($result);
    }

    // -----------------------------------------------------------------------
    // get_printval()
    // -----------------------------------------------------------------------

    /**
     * get_printval() returns the human-readable label for a stored radio value.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_printval
     */
    public function test_get_printval_radio(): void {
        $record = $this->make_item_record('r>>>>>Strongly agree|Agree|Disagree|Strongly disagree');
        $value = (object)['value' => '3'];
        $printval = $this->item->get_printval($record, $value);
        $this->assertEquals('Disagree', $printval);
    }

    /**
     * get_printval() returns empty string when value is not set.
     *
     * @covers \individualfeedback_item_fourlevelapproval::get_printval
     */
    public function test_get_printval_missing_value(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D');
        $value = new \stdClass(); // no 'value' property
        $printval = $this->item->get_printval($record, $value);
        $this->assertEquals('', $printval);
    }

    // -----------------------------------------------------------------------
    // get_analysed() – with hack layer active
    // -----------------------------------------------------------------------

    /**
     * get_analysed() returns null when there are no responses, even with hack active.
     *
     * @covers \individualfeedback_item_fourlevelapproval
     */
    public function test_get_analysed_returns_null_with_no_values(): void {
        $this->resetAfterTest();
        \mod_individualfeedback\hack\mbs_hack::start_mbs_test();

        $course = $this->getDataGenerator()->create_course();
        $fb = $this->getDataGenerator()->create_module(
            'individualfeedback', ['course' => $course->id]
        );
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_multichoice($fb, [
            'presentation' => 'r>>>>>Strongly agree|Agree|Disagree|Strongly disagree',
        ]);
        $item->typ = 'fourlevelapproval';

        $reflection = new \ReflectionClass($this->item);
        $method = $reflection->getMethod('get_analysed');
        $method->setAccessible(true);

        $result = $method->invoke($this->item, $item, false, $course->id);
        $this->assertNull($result, 'get_analysed must return null when no values are present.');

        \mod_individualfeedback\hack\mbs_hack::stop_mbs_test();
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    /**
     * Build a minimal item record with the given presentation string.
     */
    private function make_item_record(string $presentation): \stdClass {
        $record = new \stdClass();
        $record->presentation = $presentation;
        $record->options      = '';
        $record->typ          = 'fourlevelapproval';
        $record->required     = 0;
        $record->label        = '';
        $record->name         = 'Test question';
        return $record;
    }
}

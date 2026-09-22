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
 * Unit tests for the fourlevelfrequency question type.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/item/fourlevelfrequency/lib.php');

/**
 * Tests for individualfeedback_item_fourlevelfrequency.
 *
 * fourlevelfrequency is a 4-level frequency scale (e.g. Always / Often / Rarely / Never).
 * Its presentation format and flag helpers are identical in structure to fourlevelapproval.
 *
 * @covers \individualfeedback_item_fourlevelfrequency
 */
final class fourlevelfrequency_test extends \advanced_testcase {
    /** @var \individualfeedback_item_fourlevelfrequency */
    private \individualfeedback_item_fourlevelfrequency $item;

    protected function setUp(): void {
        parent::setUp();
        $this->item = new \individualfeedback_item_fourlevelfrequency();
    }

    // -----------------------------------------------------------------------
    // get_info()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::get_info
     */
    public function test_get_info_radio_subtype(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $info = $this->item->get_info($record);

        $this->assertEquals('r', $info->subtype);
        $this->assertEquals('Always|Often|Rarely|Never', $info->presentation);
        $this->assertFalse($info->horizontal);
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::get_info
     */
    public function test_get_info_checkbox_subtype(): void {
        $record = $this->make_item_record('c>>>>>Always|Often|Rarely|Never');
        $info = $this->item->get_info($record);
        $this->assertEquals('c', $info->subtype);
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::get_info
     */
    public function test_get_info_horizontal_flag(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never<<<<<1');
        $info = $this->item->get_info($record);
        $this->assertTrue($info->horizontal);
    }

    // -----------------------------------------------------------------------
    // Flag helpers
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::set_ignoreempty
     * @covers \individualfeedback_item_fourlevelfrequency::ignoreempty
     */
    public function test_ignoreempty_flag(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');

        $this->item->set_ignoreempty($record, true);
        $this->assertTrue($this->item->ignoreempty($record));

        $this->item->set_ignoreempty($record, false);
        $this->assertFalse($this->item->ignoreempty($record));
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::set_negativeformulated
     * @covers \individualfeedback_item_fourlevelfrequency::negativeformulated
     */
    public function test_negativeformulated_flag(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');

        $this->item->set_negativeformulated($record, true);
        $this->assertTrue($this->item->negativeformulated($record));

        $this->item->set_negativeformulated($record, false);
        $this->assertFalse($this->item->negativeformulated($record));
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::set_hidenoselect
     * @covers \individualfeedback_item_fourlevelfrequency::hidenoselect
     */
    public function test_hidenoselect_flag(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');

        $this->item->set_hidenoselect($record, true);
        $this->assertTrue($this->item->hidenoselect($record));

        $this->item->set_hidenoselect($record, false);
        $this->assertFalse($this->item->hidenoselect($record));
    }

    /**
     * Setting a flag twice must not duplicate the flag character in the options string.
     *
     * @covers \individualfeedback_item_fourlevelfrequency::set_ignoreempty
     */
    public function test_flag_no_duplicate(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');

        $this->item->set_ignoreempty($record, true);
        $this->item->set_ignoreempty($record, true);

        $count = substr_count($record->options, INDIVIDUALFEEDBACK_FOURLEVELFREQUENCY_IGNOREEMPTY);
        $this->assertEquals(1, $count, 'Flag must appear only once in options string.');
    }

    // -----------------------------------------------------------------------
    // create_value()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::create_value
     */
    public function test_create_value_joins_with_separator(): void {
        $result = $this->item->create_value([1 => 1, 2 => 2, 3 => 4]);
        $this->assertEquals('1|2|4', $result);
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::create_value
     */
    public function test_create_value_filters_empty(): void {
        $result = $this->item->create_value([0, 3, 0]);
        $this->assertEquals('3', $result);
    }

    // -----------------------------------------------------------------------
    // compare_value()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::compare_value
     */
    public function test_compare_value_match(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $this->assertTrue($this->item->compare_value($record, '1', 'Always'));
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::compare_value
     */
    public function test_compare_value_no_match(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $this->assertFalse($this->item->compare_value($record, '2', 'Always'));
    }

    /**
     * Last option (index 4 = 'Never') should match correctly.
     *
     * @covers \individualfeedback_item_fourlevelfrequency::compare_value
     */
    public function test_compare_value_last_option(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $this->assertTrue($this->item->compare_value($record, '4', 'Never'));
    }

    // -----------------------------------------------------------------------
    // get_printval()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::get_printval
     */
    public function test_get_printval_returns_label(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $value  = (object)['value' => '2'];
        $this->assertEquals('Often', $this->item->get_printval($record, $value));
    }

    /**
     * @covers \individualfeedback_item_fourlevelfrequency::get_printval
     */
    public function test_get_printval_missing_value_returns_empty(): void {
        $record = $this->make_item_record('r>>>>>Always|Often|Rarely|Never');
        $value  = new \stdClass(); // no 'value' property
        $this->assertEquals('', $this->item->get_printval($record, $value));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function make_item_record(string $presentation): \stdClass {
        $record = new \stdClass();
        $record->presentation = $presentation;
        $record->options      = '';
        $record->typ          = 'fourlevelfrequency';
        $record->required     = 0;
        $record->label        = '';
        $record->name         = 'Test question';
        return $record;
    }
}

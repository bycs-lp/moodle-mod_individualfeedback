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
 * Unit tests for the fivelevelapproval question type.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/item/fivelevelapproval/lib.php');

/**
 * Tests for individualfeedback_item_fivelevelapproval.
 *
 * fivelevelapproval is a 5-level approval scale extending the 4-level variant
 * with a neutral mid-point option. Its format and helpers mirror fourlevelapproval.
 *
 * @covers \individualfeedback_item_fivelevelapproval
 */
final class fivelevelapproval_test extends \advanced_testcase {
    /** @var \individualfeedback_item_fivelevelapproval */
    private \individualfeedback_item_fivelevelapproval $item;

    protected function setUp(): void {
        parent::setUp();
        $this->item = new \individualfeedback_item_fivelevelapproval();
    }

    // -----------------------------------------------------------------------
    // get_info()
    // -----------------------------------------------------------------------

    /**
     * Parsing a 5-option radio presentation produces the correct subtype.
     *
     * @covers \individualfeedback_item_fivelevelapproval::get_info
     */
    public function test_get_info_radio_five_options(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $info = $this->item->get_info($record);

        $this->assertEquals('r', $info->subtype);
        $this->assertEquals(
            'Fully agree|Agree|Neutral|Disagree|Fully disagree',
            $info->presentation
        );
        $this->assertFalse($info->horizontal);
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::get_info
     */
    public function test_get_info_checkbox_subtype(): void {
        $record = $this->make_item_record('c>>>>>A|B|C|D|E');
        $info = $this->item->get_info($record);
        $this->assertEquals('c', $info->subtype);
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::get_info
     */
    public function test_get_info_horizontal_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E<<<<<1');
        $info = $this->item->get_info($record);
        $this->assertTrue($info->horizontal);
    }

    // -----------------------------------------------------------------------
    // Flag helpers
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fivelevelapproval::set_ignoreempty
     * @covers \individualfeedback_item_fivelevelapproval::ignoreempty
     */
    public function test_ignoreempty_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E');

        $this->item->set_ignoreempty($record, true);
        $this->assertTrue($this->item->ignoreempty($record));

        $this->item->set_ignoreempty($record, false);
        $this->assertFalse($this->item->ignoreempty($record));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::set_negativeformulated
     * @covers \individualfeedback_item_fivelevelapproval::negativeformulated
     */
    public function test_negativeformulated_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E');

        $this->item->set_negativeformulated($record, true);
        $this->assertTrue($this->item->negativeformulated($record));

        $this->item->set_negativeformulated($record, false);
        $this->assertFalse($this->item->negativeformulated($record));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::set_hidenoselect
     * @covers \individualfeedback_item_fivelevelapproval::hidenoselect
     */
    public function test_hidenoselect_flag(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E');

        $this->item->set_hidenoselect($record, true);
        $this->assertTrue($this->item->hidenoselect($record));

        $this->item->set_hidenoselect($record, false);
        $this->assertFalse($this->item->hidenoselect($record));
    }

    /**
     * Setting the same flag twice must not duplicate the flag character.
     *
     * @covers \individualfeedback_item_fivelevelapproval::set_negativeformulated
     */
    public function test_flag_no_duplicate(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E');

        $this->item->set_negativeformulated($record, true);
        $this->item->set_negativeformulated($record, true);

        $count = substr_count($record->options, INDIVIDUALFEEDBACK_FIVELEVELAPPROVAL_NEGATIVEFORMULATED);
        $this->assertEquals(1, $count, 'Flag must appear only once in the options string.');
    }

    // -----------------------------------------------------------------------
    // create_value()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fivelevelapproval::create_value
     */
    public function test_create_value_joins_five_levels(): void {
        $result = $this->item->create_value([1 => 1, 2 => 3, 3 => 5]);
        $this->assertEquals('1|3|5', $result);
    }

    /**
     * Neutral mid-point (index 3) is handled correctly.
     *
     * @covers \individualfeedback_item_fivelevelapproval::create_value
     */
    public function test_create_value_neutral_midpoint(): void {
        $result = $this->item->create_value([1 => 3]);
        $this->assertEquals('3', $result);
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::create_value
     */
    public function test_create_value_filters_empty(): void {
        $result = $this->item->create_value([0, 2, 0, 4]);
        // Only non-zero unique values remain.
        $parts = explode('|', $result);
        $this->assertNotContains('0', $parts);
    }

    // -----------------------------------------------------------------------
    // compare_value()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fivelevelapproval::compare_value
     */
    public function test_compare_value_match_neutral(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $this->assertTrue($this->item->compare_value($record, '3', 'Neutral'));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::compare_value
     */
    public function test_compare_value_match_first(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $this->assertTrue($this->item->compare_value($record, '1', 'Fully agree'));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::compare_value
     */
    public function test_compare_value_no_match(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $this->assertFalse($this->item->compare_value($record, '1', 'Neutral'));
    }

    // -----------------------------------------------------------------------
    // get_printval()
    // -----------------------------------------------------------------------

    /**
     * @covers \individualfeedback_item_fivelevelapproval::get_printval
     */
    public function test_get_printval_neutral(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $value  = (object)['value' => '3'];
        $this->assertEquals('Neutral', $this->item->get_printval($record, $value));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::get_printval
     */
    public function test_get_printval_last_level(): void {
        $record = $this->make_item_record('r>>>>>Fully agree|Agree|Neutral|Disagree|Fully disagree');
        $value  = (object)['value' => '5'];
        $this->assertEquals('Fully disagree', $this->item->get_printval($record, $value));
    }

    /**
     * @covers \individualfeedback_item_fivelevelapproval::get_printval
     */
    public function test_get_printval_missing_value(): void {
        $record = $this->make_item_record('r>>>>>A|B|C|D|E');
        $value  = new \stdClass();
        $this->assertEquals('', $this->item->get_printval($record, $value));
    }

    // -----------------------------------------------------------------------
    // Helpers
    // -----------------------------------------------------------------------

    private function make_item_record(string $presentation): \stdClass {
        $record = new \stdClass();
        $record->presentation = $presentation;
        $record->options      = '';
        $record->typ          = 'fivelevelapproval';
        $record->required     = 0;
        $record->label        = '';
        $record->name         = 'Test question';
        return $record;
    }
}

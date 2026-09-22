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
 * Unit tests for mod_individualfeedback\hack\mbs_hack.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

/**
 * Tests for the mbs_hack class which controls test-mode switching in the hack layer.
 *
 * The hack layer uses a static flag to distinguish between:
 *   - Core PHPUnit tests (where the hack must step aside to avoid breaking inherited tests)
 *   - MBS-specific PHPUnit tests (where the hack executes its custom logic)
 *
 * @covers \mod_individualfeedback\hack\mbs_hack
 */
final class mbs_hack_test extends \advanced_testcase {
    protected function setUp(): void {
        parent::setUp();
        // Ensure clean state before each test.
        mbs_hack::stop_mbs_test();
    }

    protected function tearDown(): void {
        // Always reset after each test to avoid polluting other test suites.
        mbs_hack::stop_mbs_test();
        parent::tearDown();
    }

    /**
     * By default (mbstestrunning = false), running inside PHPUnit means
     * is_running_core_test() returns true — the hack layer stays passive.
     *
     * @covers \mod_individualfeedback\hack\mbs_hack::is_running_core_test
     */
    public function test_default_state_is_core_test(): void {
        $this->assertTrue(
            mbs_hack::is_running_core_test(),
            'Without start_mbs_test(), is_running_core_test() must return true inside PHPUnit.'
        );
    }

    /**
     * After start_mbs_test(), is_running_core_test() returns false,
     * allowing the hack layer to execute MBS-specific logic.
     *
     * @covers \mod_individualfeedback\hack\mbs_hack::start_mbs_test
     * @covers \mod_individualfeedback\hack\mbs_hack::is_running_core_test
     */
    public function test_start_mbs_test_activates_hack_layer(): void {
        mbs_hack::start_mbs_test();

        $this->assertFalse(
            mbs_hack::is_running_core_test(),
            'After start_mbs_test(), is_running_core_test() must return false.'
        );
    }

    /**
     * After stop_mbs_test(), is_running_core_test() returns true again,
     * restoring passive mode for core test compatibility.
     *
     * @covers \mod_individualfeedback\hack\mbs_hack::stop_mbs_test
     * @covers \mod_individualfeedback\hack\mbs_hack::is_running_core_test
     */
    public function test_stop_mbs_test_restores_core_test_mode(): void {
        mbs_hack::start_mbs_test();
        mbs_hack::stop_mbs_test();

        $this->assertTrue(
            mbs_hack::is_running_core_test(),
            'After stop_mbs_test(), is_running_core_test() must return true again.'
        );
    }

    /**
     * Multiple start/stop cycles must all toggle the flag correctly.
     *
     * @covers \mod_individualfeedback\hack\mbs_hack::start_mbs_test
     * @covers \mod_individualfeedback\hack\mbs_hack::stop_mbs_test
     * @covers \mod_individualfeedback\hack\mbs_hack::is_running_core_test
     */
    public function test_multiple_start_stop_cycles(): void {
        for ($i = 0; $i < 3; $i++) {
            mbs_hack::start_mbs_test();
            $this->assertFalse(
                mbs_hack::is_running_core_test(),
                "Cycle {$i}: start_mbs_test() should deactivate core-test detection."
            );

            mbs_hack::stop_mbs_test();
            $this->assertTrue(
                mbs_hack::is_running_core_test(),
                "Cycle {$i}: stop_mbs_test() should restore core-test detection."
            );
        }
    }

    /**
     * Calling start_mbs_test() twice must not corrupt the flag —
     * a subsequent stop_mbs_test() must still restore core-test mode.
     *
     * @covers \mod_individualfeedback\hack\mbs_hack::start_mbs_test
     * @covers \mod_individualfeedback\hack\mbs_hack::stop_mbs_test
     */
    public function test_double_start_is_safe(): void {
        mbs_hack::start_mbs_test();
        mbs_hack::start_mbs_test(); // Second call must be idempotent.
        $this->assertFalse(mbs_hack::is_running_core_test());

        mbs_hack::stop_mbs_test();
        $this->assertTrue(mbs_hack::is_running_core_test());
    }
}

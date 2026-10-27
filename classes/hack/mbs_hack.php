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
 * Hack helpers for Moodle core test detection.
 *
 * @package     mod_individualfeedback
 * @copyright   2026 ISB Bayern / mebis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

/**
 * Legacy helper controlling test-mode switching in the hack layer.
 *
 * Superseded by core_hack; kept for backwards compatibility with existing
 * callers and tests.
 *
 * @package     mod_individualfeedback
 * @copyright   2026 ISB Bayern / mebis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mbs_hack extends core_hack {
    /**
     * Enable hack behaviour for the running test (same switch as core_hack::start_hack_test()).
     *
     * Review 26.10.2026, finding #11: the two switches used to be independent, so a test
     * activating mbs_hack still ran the lib hacks in core-test mode.
     *
     * @return void
     */
    public static function start_mbs_test() {
        core_hack::start_hack_test();
    }

    /**
     * Restore core-test behaviour (same switch as core_hack::stop_hack_test()).
     *
     * @return void
     */
    public static function stop_mbs_test() {
        core_hack::stop_hack_test();
    }
}

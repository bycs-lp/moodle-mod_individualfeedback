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
class mbs_hack {
    /** @var bool indicates that an MBS hack test is running */
    private static $mbstestrunning = false;

    /**
     * Flag that an MBS hack unit test is starting.
     */
    public static function start_mbs_test() {
        self::$mbstestrunning = true;
    }

    /**
     * Flag that an MBS hack unit test has finished.
     */
    public static function stop_mbs_test() {
        self::$mbstestrunning = false;
    }

    /**
     * Check whether a Moodle core test is running (hack should be inactive).
     *
     * @return bool
     */
    public static function is_running_core_test() {
        return (PHPUNIT_TEST && empty(self::$mbstestrunning));
    }
}

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
 * Base class of a hack implementation
 *
 * CORE-HACKs should be derived from this class, to:
 *
 * 1. indicate, that this class implements a core hack.
 * 2. skip the hack when a core unit test is running.
 *
 * Quick coding guidelines for hacks:
 *
 * 1. Try to call hack code in one line and leave appropriate comment, for example:
 *
 * // +++ MBS-HACK (Andreas Wagner) - Need to fix this and that for a better moodle (Ticketnumber)
 * \local_hacktemplate\hack\core_message::dohack();
 * // --- MBS-HACK
 *
 * 2. Create a class in local/hacktemplate/hack/[pluginname] that extends \local_hacktemplate\hack\base_hack to implement the hack functions:
 *
 * /**
 *  * Hack called from /path/to/file/calling the hack. *
 *  *
 *  * Do the hack to fix this and that.
 *  *
 *  public static function doHack() {
 *
 *      if (self::is_running_core_test()) {
 *          return;
 *      }
 *
 *      execute hack code;
 * }
 *
 * @package   local_hacktemplate
 * @copyright 2025 Andreas Wagner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

/**
 * Modified bas class for use in a unit test that checks core hacks.
 *
 * @package   local_hacktemplate
 * @copyright 2025 Andreas Wagner
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_hack {

    /** @var boolean indicate that mbs test is running */
    private static $hacktestrunning = false;

    /**
     * Set the flag to indicate that a mbs unit test is running to true.
     */
    public static function start_hack_test() {
        self::$hacktestrunning = true;
    }

    /**
     * Set the flag to indicate that a mbs unit test is running to false.
     */
    public static function stop_hack_test() {
        self::$hacktestrunning = false;
    }

    /**
     * Check if a moodle core test is running (so a core hack might be inactive).
     *
     * @return boolean
     */
    public static function is_running_core_test() {
        return (PHPUNIT_TEST && empty(self::$hacktestrunning));
    }
}
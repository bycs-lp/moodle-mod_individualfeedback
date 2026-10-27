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
 * Test fixture: worksheet stand-in for Excel export tests.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\fixtures;

/**
 * Worksheet stand-in recording every written cell.
 */
class recording_worksheet {
    /** @var array[] [row, col, value] */
    public array $cells = [];

    /**
     * Record a string cell.
     *
     * @param int $row Row.
     * @param int $col Column.
     * @param string $value Value.
     * @param mixed $format Ignored.
     */
    public function write_string($row, $col, $value, $format = null): void {
        $this->cells[] = [$row, $col, $value];
    }

    /**
     * Record a number cell.
     *
     * @param int $row Row.
     * @param int $col Column.
     * @param float $value Value.
     * @param mixed $format Ignored.
     */
    public function write_number($row, $col, $value, $format = null): void {
        $this->cells[] = [$row, $col, $value];
    }
}

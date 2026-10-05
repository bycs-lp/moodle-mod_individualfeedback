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
 * Post-install hook for mod_individualfeedback.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Applies the fork-specific schema and capability additions after install.
 *
 * install.xml stays identical to the renamed core file (Wagner D3); all
 * fork additions are applied programmatically via the hack layer.
 */
function xmldb_individualfeedback_install() {
    global $DB;

    // +++ MBS-Hack (nersesov) : apply fork-specific schema additions (install.xml stays identical to core).
    \mod_individualfeedback\hack\db_schema::apply_fork_schema();
    // --- MBS-Hack
}

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
 * individualfeedback version information
 *
 * @package mod_individualfeedback
 * @author     Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2025033001;       // The current module version (Baseversion Date: 2020070900)
$plugin->requires  = 2017050500;    // Requires this Moodle version
$plugin->component = 'mod_individualfeedback';   // Full name of the plugin (used for diagnostics)
$plugin->maturity = MATURITY_ALPHA;
$plugin->release  = '4.6';      // Release version (Baseversion Date: 2020070900)
$plugin->cron      = 0;

$individualfeedback_version_intern = 1; //this version is used for restore older backups

// Hinweise zum Refactoring Prozess. Die hier referenzierte Moodle Version v3.0 (2020070900). muss auf 4.4 / 4.5 angehoben werden.
// General Notes on the depreaching process: https://moodle.org/mod/forum/discuss.php?d=457946&lang=de
// A list with all depreached functions since 2.4 https://phpdoc.moodledev.io/main/da/d58/deprecated.html

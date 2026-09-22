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
 * Individualfeedback version information
 *
 * @package mod_individualfeedback
 * @author     Andreas Grabs
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

// +++ MBS-Hack (nersesov) : fork base pin. This plugin is a rename of Moodle core mod_feedback taken from
// Moodle main at commit e68a1418bea512dd5992c29eb9e36570a3844e94 (tag v5.3.0-beta, 2026-09-16). It will be
// re-pinned to the v5.3.0 release tag / MOODLE_503_STABLE once published. Future core updates are applied by
// rebasing the fork commits onto the filtered public/mod/feedback history from that branch.
// --- MBS-Hack
$plugin->version   = 2026042002;       // The current module version (Date: YYYYMMDDXX).
$plugin->requires  = 2026041000;    // Requires this Moodle version.
$plugin->component = 'mod_individualfeedback';   // Full name of the plugin (used for diagnostics)
$plugin->cron      = 0;

$individualfeedback_version_intern = 1; //this version is used for restore older backups

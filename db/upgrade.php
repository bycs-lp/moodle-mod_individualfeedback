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
 * This file keeps track of upgrades to the feedback module.
 *
 * Sometimes, changes between versions involve
 * alterations to database structures and other
 * major things that may break installations.
 *
 * The upgrade function in this file will attempt
 * to perform all the necessary actions to upgrade
 * your older installation to the current version.
 *
 * If there's something it cannot do itself, it
 * will tell you what you need to do.
 *
 * The commands in here will all be database-neutral,
 * using the methods of database_manager class
 *
 * Please do not forget to use upgrade_set_timeout()
 * before any action that may take longer time to finish.
 *
 * @package   mod_individualfeedback
 * @copyright Andreas Grabs
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function xmldb_individualfeedback_upgrade($oldversion) {
    global $DB;

    $dbman = $DB->get_manager();

    // Handle case where tables might already exist from previous installation attempts.
    if ($oldversion < 2024100703) {
        // If tables exist but version wasn't properly registered, just mark as upgraded.
        // This handles the case where installation was attempted but not completed properly.
        if ($dbman->table_exists('individualfeedback')) {
            // Tables exist, so we can proceed with normal upgrade logic.
            // No schema changes needed at this point.
        }
        
        upgrade_mod_savepoint(true, 2024100703, 'individualfeedback');
    }

    // Automatically generated Moodle v4.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.2.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.3.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.4.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v4.5.0 release upgrade line.
    // Put any upgrade step following this.

    // Rename column from 'feedback' to 'individualfeedback' in all tables.
    if ($oldversion < 2024100708) {
        // Rename columns in individualfeedback_item table
        $table = new xmldb_table('individualfeedback_item');
        $field = new xmldb_field('feedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'individualfeedback');
        }

        // Rename columns in individualfeedback_completed table
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('feedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'individualfeedback');
        }

        // Rename columns in individualfeedback_completedtmp table
        $table = new xmldb_table('individualfeedback_completedtmp');
        $field = new xmldb_field('feedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'individualfeedback');
        }

        // Rename columns in individualfeedback_sitecourse_map table
        $table = new xmldb_table('individualfeedback_sitecourse_map');
        $field = new xmldb_field('feedbackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        if ($dbman->field_exists($table, $field)) {
            $dbman->rename_field($table, $field, 'individualfeedbackid');
        }

        upgrade_mod_savepoint(true, 2024100708, 'individualfeedback');
    }

    return true;
}

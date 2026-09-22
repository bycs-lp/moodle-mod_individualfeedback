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
 * Fork-specific database schema additions for mod_individualfeedback.
 *
 * The Wagner-approved approach keeps db/install.xml identical to the renamed
 * core file. All fork-specific schema additions are applied programmatically
 * from xmldb_individualfeedback_install() and xmldb_individualfeedback_upgrade()
 * via this class. Every operation is idempotent (guarded by field/table
 * existence checks), so install and upgrade can share the same code path.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

/**
 * Applies the fork-specific schema additions.
 *
 * @package mod_individualfeedback
 * @copyright 2026 ISB Bayern / mebis
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class db_schema {
    /**
     * Apply all fork-specific schema additions (idempotent).
     *
     * Additions on top of the core schema:
     * - individualfeedback_template.userid (+ index): owner of private user templates.
     * - individualfeedback_completed.selfassessment: flag for self-assessment responses.
     * - individualfeedback_completedtmp.selfassessment: same flag on the tmp table.
     * - individualfeedback_linked: n:m linking of feedbacks for comparison reports.
     *
     * @return void
     */
    public static function apply_fork_schema(): void {
        global $DB;

        $dbman = $DB->get_manager();

        // Add userid to individualfeedback_template for private (user) templates.
        $table = new \xmldb_table('individualfeedback_template');
        $field = new \xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'name');
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
        $index = new \xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, ['userid']);
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Add selfassessment to individualfeedback_completed and individualfeedback_completedtmp.
        foreach (['individualfeedback_completed', 'individualfeedback_completedtmp'] as $tablename) {
            $table = new \xmldb_table($tablename);
            $field = new \xmldb_field(
                'selfassessment',
                XMLDB_TYPE_INTEGER,
                '1',
                null,
                XMLDB_NOTNULL,
                null,
                '0',
                'courseid'
            );
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Create individualfeedback_linked table for comparison reports.
        $table = new \xmldb_table('individualfeedback_linked');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('linkedid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('individualfeedbackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_key('primary', XMLDB_KEY_PRIMARY, ['id']);
        $table->add_key(
            'individualfeedbackid',
            XMLDB_KEY_FOREIGN,
            ['individualfeedbackid'],
            'individualfeedback',
            ['id']
        );
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // NOTE: the mod/individualfeedback:selfassessment capability is declared in
        // db/access.php (marked MBS-Hack block) and NOT here: update_capabilities()
        // runs after every plugin upgrade and deletes capabilities missing from
        // db/access.php, so a programmatic insert would not survive the next upgrade.
    }
}

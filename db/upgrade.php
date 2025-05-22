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

// This file keeps track of upgrades to
// the individualfeedback module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installation to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the methods of database_manager class
//
// Please do not forget to use upgrade_set_timeout()
// before any action that may take longer time to finish.

defined('MOODLE_INTERNAL') || die();

function xmldb_individualfeedback_upgrade($oldversion) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/individualfeedback/db/upgradelib.php');

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    // Moodle v2.8.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v2.9.0 release upgrade line.
    // Put any upgrade step following this.

    // Moodle v3.0.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2016031600) {
        // Remove labels from all 'captcha' and 'label' items.
        $DB->execute('UPDATE {individualfeedback_item} SET label = ? WHERE typ = ? OR typ = ?',
                array('', 'captcha', 'label'));

        // Data savepoint reached.
        upgrade_mod_savepoint(true, 2016031600, 'individualfeedback');
    }

    if ($oldversion < 2016040100) {

        // In order to keep the previous "Analysis" results unchanged,
        // set all multiple-answer multiplechoice questions as "Do not analyse empty submits"="Yes"
        // because prior to this date this setting did not work.

        $sql = "UPDATE {individualfeedback_item} SET options = " . $DB->sql_concat('?', 'options') .
                " WHERE typ = ? AND presentation LIKE ? AND options NOT LIKE ?";
        $params = array('i', 'multichoice', 'c%', '%i%');
        $DB->execute($sql, $params);

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016040100, 'individualfeedback');
    }

    if ($oldversion < 2016051103) {

        // Define index completed_item (unique) to be added to individualfeedback_value.
        $table = new xmldb_table('individualfeedback_value');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

        // Conditionally launch add index completed_item.
        if (!$dbman->index_exists($table, $index)) {
            mod_individualfeedback_upgrade_delete_duplicate_values();
            $dbman->add_index($table, $index);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051103, 'individualfeedback');
    }

    if ($oldversion < 2016051104) {

        // Define index completed_item (unique) to be added to individualfeedback_valuetmp.
        $table = new xmldb_table('individualfeedback_valuetmp');
        $index = new xmldb_index('completed_item', XMLDB_INDEX_UNIQUE, array('completed', 'item', 'course_id'));

        // Conditionally launch add index completed_item.
        if (!$dbman->index_exists($table, $index)) {
            mod_individualfeedback_upgrade_delete_duplicate_values(true);
            $dbman->add_index($table, $index);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051104, 'individualfeedback');
    }

    if ($oldversion < 2016051105) {

        // Define field courseid to be added to individualfeedback_completed.
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Run upgrade script to fill the new field courseid with the data from individualfeedback_value table.
            mod_individualfeedback_upgrade_courseid(false);
        }

        // Define field courseid to be added to indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('courseid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'anonymous_response');

        // Conditionally launch add field courseid.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            // Run upgrade script to fill the new field courseid with the data from individualfeedback_valuetmp table.
            mod_individualfeedback_upgrade_courseid(true);
        }

        // Define table individualfeedback_tracking to be dropped.
        $table = new xmldb_table('individualfeedback_tracking');

        // Conditionally launch drop table for individualfeedback_tracking.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2016051105, 'individualfeedback');
    }

    // Moodle v3.1.0 release upgrade line.
    // Put any upgrade step following this.

    // Automatically generated Moodle v3.2.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2017032800) {

        // Delete duplicated records in individualfeedback_completed. We just keep the last record of completion.
        // Related values in individualfeedback_value won't be deleted (they won't be used and can be kept there as a backup).
        $sql = "SELECT MAX(id) as maxid, userid, individualfeedback, courseid
                  FROM {individualfeedback_completed}
                 WHERE userid <> 0
              GROUP BY userid, individualfeedback, courseid
                HAVING COUNT(id) > 1";

        $duplicatedrows = $DB->get_recordset_sql($sql);
        foreach ($duplicatedrows as $row) {
            $DB->delete_records_select('individualfeedback_completed', 'userid = ? AND individualfeedback = ? AND courseid = ? AND id <> ?', array(
                $row->userid,
                $row->individualfeedback,
                $row->courseid,
                $row->maxid,
            ));
        }
        $duplicatedrows->close();

        // individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017032800, 'individualfeedback');
    }

    // Automatically generated Moodle v3.3.0 release upgrade line.
    // Put any upgrade step following this.

    if ($oldversion < 2017111000) {

        // Define index userid (not unique) to be dropped form individualfeedback_completed.
        $table = new xmldb_table('individualfeedback_completed');
        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch drop index userid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Changing type of field userid on table individualfeedback_completed to char.
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('userid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'individualfeedback');

        // Launch change of type for field userid.
        $dbman->change_field_type($table, $field);

        // Define index userid (not unique) to be dropped form indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));

        // Conditionally launch drop index userid.
        if ($dbman->index_exists($table, $index)) {
            $dbman->drop_index($table, $index);
        }

        // Changing type of field userid on table indfeedback_completedtmp to char.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('userid', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, null, '0', 'individualfeedback');

        // Launch change of type for field userid.
        $dbman->change_field_type($table, $field);

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017111000, 'individualfeedback');
    }

    if ($oldversion < 2017111300) {

        // Define field selfassessment to be added to individualfeedback_completed.
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('selfassessment', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'courseid');

        // Conditionally launch add field selfassessment.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Define field selfassessment to be added to indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('selfassessment', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', 'courseid');

        // Conditionally launch add field selfassessment.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017111300, 'individualfeedback');
    }

    if ($oldversion < 2017112000) {

        // Define table individualfeedback_linked to be created.
        $table = new xmldb_table('individualfeedback_linked');

        // Adding fields to table individualfeedback_linked.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('linkedid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('individualfeedbackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);

        // Adding keys to table individualfeedback_linked.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('individualfeedbackid', XMLDB_KEY_FOREIGN, array('individualfeedbackid'), 'individualfeedback', array('id'));

        // Conditionally launch create table for individualfeedback_linked.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017112000, 'individualfeedback');
    }

    if ($oldversion < 2017120102) {
        
        // SFSUBM-25 - add additional capabilities.
        list($where, $params) = $DB->get_in_or_equal(array('editingteacher', 'teacher'));
        if ($roles = $DB->get_records_select('role', 'shortname ' . $where, $params)) {
            foreach ($roles as $role) {
                // Assign capabilities in roles Teacher and Editing Teacher.
                assign_capability('mod/individualfeedback:complete', CAP_ALLOW, $role->id, context_system::instance()->id);
            }
        }

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2017120102, 'individualfeedback');
    }

    if ($oldversion < 2025022003) {

        // Define field userid to be added to individualfeedback_template.
        $table = new xmldb_table('individualfeedback_template');
        $field = new xmldb_field('userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, null);

        // Conditionally launch add field id.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2025022003, 'individualfeedback');
    }

    if ($oldversion < 2025022502) {

        // Define key individualfeedback (foreign) to be dropped form individualfeedback_item.
        $table = new xmldb_table('individualfeedback_item');
        $key = new xmldb_key('individualfeedback', XMLDB_KEY_FOREIGN, ['individualfeedback'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table individualfeedback_item to NEWNAMEGOESHERE.
        $table = new xmldb_table('individualfeedback_item');
        $field = new xmldb_field('individualfeedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedback');
        // Define key feedback (foreign) to be added to individualfeedback_item.
        $table = new xmldb_table('individualfeedback_item');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedback'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Define key individualfeedback (foreign) to be dropped form individualfeedback_completed.
        $table = new xmldb_table('individualfeedback_completed');
        $key = new xmldb_key('individualfeedback', XMLDB_KEY_FOREIGN, ['individualfeedback'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table individualfeedback_item to NEWNAMEGOESHERE.
        $table = new xmldb_table('individualfeedback_completed');
        $field = new xmldb_field('individualfeedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedback');
        // Define key feedback (foreign) to be added to individualfeedback_item.
        $table = new xmldb_table('individualfeedback_completed');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedback'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Define key individualfeedback (foreign) to be dropped form indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $key = new xmldb_key('individualfeedback', XMLDB_KEY_FOREIGN, ['individualfeedback'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table indfeedback_completedtmp to NEWNAMEGOESHERE.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('individualfeedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedback');
        // Define key feedback (foreign) to be added to indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedback'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Define key individualfeedback (foreign) to be dropped form indfeedback_sitecourse_map.
        $table = new xmldb_table('indfeedback_sitecourse_map');
        $key = new xmldb_key('individualfeedbackid', XMLDB_KEY_FOREIGN, ['individualfeedbackid'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table indfeedback_sitecourse_map to NEWNAMEGOESHERE.
        $table = new xmldb_table('indfeedback_sitecourse_map');
        $field = new xmldb_field('individualfeedbackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedbackid');
        // Define key feedback (foreign) to be added to indfeedback_sitecourse_map.
        $table = new xmldb_table('indfeedback_sitecourse_map');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedbackid'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Define key individualfeedback (foreign) to be dropped form individualfeedback_linked.
        $table = new xmldb_table('individualfeedback_linked');
        $key = new xmldb_key('individualfeedbackid', XMLDB_KEY_FOREIGN, ['individualfeedbackid'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table indfeedback_sitecourse_map to NEWNAMEGOESHERE.
        $table = new xmldb_table('individualfeedback_linked');
        $field = new xmldb_field('individualfeedbackid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedbackid');
        // Define key feedback (foreign) to be added to indfeedback_sitecourse_map.
        $table = new xmldb_table('individualfeedback_linked');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedbackid'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2025022502, 'individualfeedback');
    }

    if ($oldversion < 2025033001) {

        // Define key individualfeedback (foreign) to be dropped form indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $key = new xmldb_key('individualfeedback', XMLDB_KEY_FOREIGN, ['individualfeedback'], 'individualfeedback', ['id']);
        // Launch drop key feedback.
        $dbman->drop_key($table, $key);
        // Rename field feedback on table indfeedback_completedtmp to NEWNAMEGOESHERE.
        $table = new xmldb_table('indfeedback_completedtmp');
        $field = new xmldb_field('individualfeedback', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'id');
        // Launch rename field feedback.
        $dbman->rename_field($table, $field, 'feedback');
        // Define key feedback (foreign) to be added to indfeedback_completedtmp.
        $table = new xmldb_table('indfeedback_completedtmp');
        $key = new xmldb_key('feedback', XMLDB_KEY_FOREIGN, ['feedback'], 'individualfeedback', ['id']);
        // Launch add key feedback.
        $dbman->add_key($table, $key);

        // Individualfeedback savepoint reached.
        upgrade_mod_savepoint(true, 2025033001, 'individualfeedback');
    }


    return true;
}

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
 * Hack lib for mod_individualfeedback (template and item logic).
 *
 * @package mod_individualfeedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

/**
 * Hack lib for mod_individualfeedback (template and item logic).
 *
 * @package mod_individualfeedback
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lib extends core_hack {

    /**
     * Hack function to add private templates to the template list.
     *
     * @param array $templates The existing templates computed by the original function.
     * @param string $onlyownorpublic Filter for templates: 'private' to include only user's
     *     private templates, 'public' for public templates, or '' for all.
     * @return array The modified list of templates including private ones if applicable.
     */
    public static function add_private_template_list(array $templates, string $onlyownorpublic): array {
        global $DB, $USER;

        if (self::is_running_core_test()) {
            return $templates;
        }

        if ($onlyownorpublic === 'private') {
            return $DB->get_records(
                'individualfeedback_template',
                ['ispublic' => 2, 'userid' => $USER->id],
                'name'
            );
        }

        return $templates;
    }

    /**
     * Hack function to store the creator's user ID on a new template record.
     *
     * @param stdClass $templ The template object about to be inserted.
     * @return stdClass The template object with userid set.
     */
    public static function set_template_userid(\stdClass $templ): \stdClass {
        global $USER;

        if (self::is_running_core_test()) {
            return $templ;
        }

        $templ->userid = $USER->id;
        return $templ;
    }

    /**
     * Hack function that returns true when the item record is missing (already deleted).
     *
     * This is needed because group item deletion can cause recursive calls to
     * individualfeedback_delete_item(); without this guard the outer loop would
     * try to delete an already-removed record.
     *
     * @param mixed $item The result of get_record() — a stdClass or false/null.
     * @return bool True if the item no longer exists and deletion should be skipped.
     */
    public static function is_item_already_deleted($item): bool {
        if (self::is_running_core_test()) {
            return false;
        }

        return !$item;
    }

    /**
     * Hack function to delete all child items when a question group item is removed.
     *
     * @param stdClass $item The item being deleted.
     * @return void
     */
    public static function delete_group_items_for_item(\stdClass $item): void {
        if (self::is_running_core_test()) {
            return;
        }

        if ($item->typ === 'questiongroup') {
            individualfeedback_delete_group_items($item);
        }
    }

    /**
     * Hack function to add the 'questiongroup' type to the available item options.
     *
     * @param array $options The options array built by the original function.
     * @return array The options array with 'questiongroup' added.
     */
    public static function add_questiongroup_option(array $options): array {
        if (self::is_running_core_test()) {
            return $options;
        }

        $options['questiongroup'] = get_string('questiongroup', 'individualfeedback');
        return $options;
    }

    /**
     * Hack function to return group values filtered by the selfassessment flag.
     *
     * Returns null during core tests so the original function body is used instead.
     *
     * @param stdClass $item The feedback item.
     * @param int|false $groupid Group ID or false for all groups.
     * @param int|false $courseid Course ID or false for all courses.
     * @param bool $ignore_empty Skip empty/zero values when true.
     * @param bool $selfassessment True to return self-assessment values; false for peer values.
     * @return array|null Filtered value records, or null to fall through to original code.
     */
    public static function get_values_by_selfassessment(
        \stdClass $item,
        $groupid,
        $courseid,
        bool $ignore_empty,
        bool $selfassessment
    ): ?array {
        global $DB, $USER;

        if (self::is_running_core_test()) {
            return null;
        }

        $params = ['selfassessment' => (int) $selfassessment];
        if ($ignore_empty) {
            $value = $DB->sql_compare_text('value');
            $ignore_empty_select = "AND $value != :emptyvalue AND $value != :zerovalue";
            $params += ['emptyvalue' => '', 'zerovalue' => '0'];
        } else {
            $ignore_empty_select = '';
        }

        if ($courseid) {
            $select = "item = :itemid AND course_id = :courseid " . $ignore_empty_select;
            $params += ['itemid' => $item->id, 'courseid' => $courseid];
        } else {
            $select = "item = :itemid " . $ignore_empty_select;
            $params += ['itemid' => $item->id];
        }

        $sql = "SELECT iv.*
        FROM {individualfeedback_value} iv
        JOIN {individualfeedback_completed} ic ON iv.completed = ic.id
        WHERE {$select}
        AND ic.selfassessment = :selfassessment";

        if ($selfassessment) {
            $sql .= " AND ic.userid = :userid";
            $params['userid'] = individualfeedback_hash_userid($USER->id);
        }

        $values = $DB->get_records_sql($sql, $params);

        $params2 = ['id' => $item->individualfeedback];
        if ($DB->get_field('individualfeedback', 'anonymous', $params2) == INDIVIDUALFEEDBACK_ANONYMOUS_YES) {
            if (is_array($values)) {
                shuffle($values);
            }
        }

        return $values;
    }
}


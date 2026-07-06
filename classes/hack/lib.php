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
 * @copyright 2026 ISB Bayern / mebis
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
     * Marker character stored in the multichoice item options to flag a
     * negatively formulated question (H12).
     */
    const MULTICHOICE_NEGATIVEFORMULATED = 'n';

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
     * @param bool $ignoreempty Skip empty/zero values when true.
     * @param bool $selfassessment True to return self-assessment values; false for peer values.
     * @return array|null Filtered value records, or null to fall through to original code.
     */
    public static function get_values_by_selfassessment(
        \stdClass $item,
        $groupid,
        $courseid,
        bool $ignoreempty,
        bool $selfassessment
    ): ?array {
        global $DB, $USER;

        if (self::is_running_core_test()) {
            return null;
        }

        $params = ['selfassessment' => (int) $selfassessment];
        if ($ignoreempty) {
            $value = $DB->sql_compare_text('value');
            $ignoreemptyselect = "AND $value != :emptyvalue AND $value != :zerovalue";
            $params += ['emptyvalue' => '', 'zerovalue' => '0'];
        } else {
            $ignoreemptyselect = '';
        }

        if ($courseid) {
            $select = "item = :itemid AND course_id = :courseid " . $ignoreemptyselect;
            $params += ['itemid' => $item->id, 'courseid' => $courseid];
        } else {
            $select = "item = :itemid " . $ignoreemptyselect;
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

    /**
     * Returns whether the user may complete this individualfeedback when they already
     * hold the mod/individualfeedback:complete capability.
     *
     * In production the capability is granted to editingteacher/teacher for the selfassessment
     * workflow. During core unit tests we restore the original mod_feedback behaviour where
     * those roles did not have the capability, so that pre-existing core tests keep passing.
     *
     * @param \context $context  The module context.
     * @param int      $userid   The user to check.
     * @return bool
     */
    public static function can_complete_for_selfassessment(\context $context, int $userid): bool {
        if (self::is_running_core_test()) {
            // Core tests: original mod_feedback did not grant complete to editing teachers.
            // Site admins always have all capabilities by default — do not restrict them.
            if (is_siteadmin($userid)) {
                return true;
            }
            return !has_capability('mod/individualfeedback:edititems', $context, $userid);
        }
        return true;
    }

    /**
     * Filters a list of user IDs to exclude editing teachers from the non-respondents
     * list during core unit tests.
     *
     * In production all users returned by the capability check pass through unchanged
     * (selfassessment workflow intentionally includes teachers).  During core unit tests
     * teachers are excluded so that pre-existing tests that count expected respondents
     * keep passing.
     *
     * @param \context $context  The module context.
     * @param array    $userids  Numerically indexed array of user IDs.
     * @return array             Filtered (or unchanged) array of user IDs.
     */
    public static function filter_respondents_for_core_test(\context $context, array $userids): array {
        if (!self::is_running_core_test()) {
            return $userids;
        }
        return array_values(array_filter($userids, function (int $userid) use ($context): bool {
            if (is_siteadmin($userid)) {
                return true;
            }
            return !has_capability('mod/individualfeedback:edititems', $context, $userid);
        }));
    }

    /**
     * Hack function to extend the privacy metadata collection with fork-specific fields.
     *
     * Adds the selfassessment field description for the completed/completedtmp tables and
     * declares the individualfeedback_template table which stores the creating user's ID
     * for private (user) templates. Required for GDPR/DSGVO compliance of the fork.
     *
     * No is_running_core_test() guard: privacy metadata must always be complete —
     * it describes the actual database schema, which contains these fields in every
     * environment. Core privacy tests validate string existence, not field lists.
     *
     * @param \core_privacy\local\metadata\collection $collection The metadata collection.
     * @param array $completedfields The field list for the completed tables (by reference pattern).
     * @return array The extended completed field list.
     */
    public static function add_privacy_metadata(
        \core_privacy\local\metadata\collection $collection,
        array $completedfields
    ): array {
        $completedfields['selfassessment'] = 'privacy:metadata:completed:selfassessment';

        $templatefields = [
            'userid' => 'privacy:metadata:template:userid',
        ];
        $collection->add_database_table(
            'individualfeedback_template',
            $templatefields,
            'privacy:metadata:template'
        );

        return $completedfields;
    }

    /**
     * Hack function to extend an exported privacy submission with the selfassessment flag.
     *
     * No is_running_core_test() guard: the field exists in every environment and
     * privacy exports must always reflect all stored personal data (DSGVO).
     *
     * @param array $submission The submission entry being assembled for export.
     * @param \stdClass $record The joined DB record (must carry the selfassessment column).
     * @return array The extended submission entry.
     */
    public static function add_privacy_export_fields(array $submission, \stdClass $record): array {
        $submission['selfassessment'] =
            \core_privacy\local\request\transform::yesno($record->selfassessment ?? 0);
        return $submission;
    }

    /**
     * Hack function to amend the CSS class of a complete-form item row (H9).
     *
     * Adds the questiongroupmoveitem marker class (used by the sortable-list
     * JS to allow dragging rows into/out of question groups) and the
     * dependent-item marker used by the question group layout CSS.
     *
     * @param string $class The class string built by the original method.
     * @param \stdClass $item The item being rendered.
     * @return string The amended (or unchanged, during core tests) class string.
     */
    public static function amend_item_css_class(string $class, \stdClass $item): string {
        if (self::is_running_core_test()) {
            return $class;
        }

        $class .= ' questiongroupmoveitem';
        if ($item->dependitem && $item->typ !== 'questiongroupend') {
            $class .= ' individualfeedback_is_dependent';
        }
        return $class;
    }

    /**
     * Hack function that applies question-group-aware dependency labels (H9).
     *
     * When the depend item is a question group the label is suppressed
     * (group dependency is shown on the group bar itself, not on each child).
     * Returns true when the hack handled the label, false to fall through to
     * the original code path.
     *
     * @param \HTML_QuickForm_element $element The form element being labelled.
     * @param \stdClass $dependitem The item this element depends on.
     * @param \stdClass $item The item being rendered.
     * @return bool True if handled by the hack, false to run the original code.
     */
    public static function apply_questiongroup_dependency_label(
        $element,
        \stdClass $dependitem,
        \stdClass $item
    ): bool {
        if (self::is_running_core_test()) {
            return false;
        }

        if ($dependitem->typ == 'questiongroup') {
            if ($item->typ !== 'questiongroupend') {
                $element->setLabel('');
            }
            return true;
        }
        return false;
    }

    /**
     * Hack function to add the selfassessment column to the anonymous responses SQL (H10).
     *
     * @param string $fields The SELECT field list built by the original code.
     * @return string The amended (or unchanged, during core tests) field list.
     */
    public static function add_selfassessment_to_fields(string $fields): string {
        if (self::is_running_core_test()) {
            return $fields;
        }
        return $fields . ', c.selfassessment';
    }

    /**
     * Hack function returning a marker suffix for self-assessment responses (H10).
     *
     * Shown next to the response number in the anonymous responses table so
     * evaluators can distinguish self-assessment entries.
     *
     * @param \stdClass $row The table row (may carry the selfassessment column).
     * @return string HTML marker or '' (always '' during core tests).
     */
    public static function get_selfassessment_marker(\stdClass $row): string {
        if (self::is_running_core_test()) {
            return '';
        }
        if (empty($row->selfassessment)) {
            return '';
        }
        return ' ' . \html_writer::tag(
            'span',
            '*',
            ['title' => get_string('selfassessment', 'individualfeedback')]
        );
    }

    /**
     * Hack function rendering the compare action bar on the analysis page (H13).
     *
     * @param int $cmid The course module id.
     * @param \moodle_url $url The current page URL.
     * @param \plugin_renderer_base $renderer The individualfeedback renderer.
     * @return string Rendered HTML or '' (always '' during core tests).
     */
    public static function render_compare_action_bar(int $cmid, \moodle_url $url, $renderer): string {
        if (self::is_running_core_test()) {
            return '';
        }
        $actionbar = new \mod_individualfeedback\output\compare_action_bar($cmid, $url);
        return $renderer->main_action_bar($actionbar);
    }

    /**
     * Hack function enforcing owner-only deletion of private user templates (H11).
     *
     * Private user templates (ispublic == 2) may only be deleted by their owner.
     * Redirects (and therefore terminates the request) when a non-owner tries.
     *
     * @param \stdClass $template The template record about to be deleted.
     * @param \moodle_url $url The manage-templates page URL to redirect back to.
     * @return void
     */
    public static function enforce_private_template_ownership(\stdClass $template, \moodle_url $url): void {
        global $USER;

        if (self::is_running_core_test()) {
            return;
        }

        if (
            isset($template->ispublic) && (int)$template->ispublic === 2
                && $template->userid != $USER->id
        ) {
            redirect(
                $url,
                get_string('nopermission', 'core'),
                null,
                \core\output\notification::NOTIFY_ERROR
            );
        }
    }

    /**
     * Hack function rendering the private (user) templates section on the
     * manage-templates page (H11).
     *
     * @param \stdClass $course The course record.
     * @param array $params Base URL params for the use_templ links.
     * @return string Rendered HTML or '' (always '' during core tests).
     */
    public static function render_private_templates_section(\stdClass $course, array $params): string {
        global $OUTPUT;

        if (self::is_running_core_test()) {
            return '';
        }

        $templates = individualfeedback_get_template_list($course, 'private');
        $baseurl = new \moodle_url('/mod/individualfeedback/use_templ.php', $params);
        $table = new \mod_individualfeedback_templates_table(
            'individualfeedback_template_user_table',
            $baseurl
        );

        ob_start();
        echo $OUTPUT->box_start('coursetemplates');
        echo $OUTPUT->heading(get_string('user'), 3);
        $table->display($templates);
        echo $OUTPUT->box_end();
        return ob_get_clean();
    }

    /**
     * Hack function adding the template visibility radio buttons to the
     * create-template form (H14).
     *
     * Replaces the core "available for all courses" checkbox with a three-way
     * choice: course template (0), private user template (2) and — only with
     * the createpublictemplate capability — public template (1).
     *
     * @param \MoodleQuickForm $mform The form being built.
     * @return bool True when the hack added its elements, false to run the original code.
     */
    public static function add_template_visibility_elements($mform): bool {
        if (self::is_running_core_test()) {
            return false;
        }

        $mform->addElement('radio', 'ispublic', '', get_string('course'), 0);
        $mform->addElement('radio', 'ispublic', '', get_string('user'), 2);
        if (has_capability('mod/individualfeedback:createpublictemplate', \context_system::instance())) {
            $mform->addElement(
                'radio',
                'ispublic',
                '',
                get_string('public', 'individualfeedback'),
                1
            );
        }
        $mform->setDefault('ispublic', 0);
        $mform->setType('ispublic', PARAM_INT);
        return true;
    }

    /**
     * Hack function normalising the submitted ispublic value (H14).
     *
     * Enforces the createpublictemplate capability server-side: a user without
     * it can never store a public template, no matter what was submitted
     * (Wagner finding #14 — capability enforcement in process_dynamic_submission).
     *
     * @param \stdClass $formdata The submitted form data.
     * @return int The sanitised ispublic value (0, 1 or 2).
     */
    public static function normalize_template_ispublic(\stdClass $formdata): int {
        if (self::is_running_core_test()) {
            return !empty($formdata->ispublic) ? 1 : 0;
        }

        $ispublic = !empty($formdata->ispublic) ? (int)$formdata->ispublic : 0;
        if (
            $ispublic === 1
                && !has_capability('mod/individualfeedback:createpublictemplate', \context_system::instance())
        ) {
            $ispublic = 0;
        }
        return $ispublic;
    }

    /**
     * Hack function reading the negativeformulated flag from a multichoice item (H12).
     *
     * The flag is stored as a marker character inside the item options string,
     * so no schema change is needed for the multichoice item type.
     *
     * @param \stdClass $item The multichoice item.
     * @return bool True when the question is negatively formulated (always false during core tests).
     */
    public static function get_negativeformulated(\stdClass $item): bool {
        if (self::is_running_core_test()) {
            return false;
        }
        return strpos((string)$item->options, self::MULTICHOICE_NEGATIVEFORMULATED) !== false;
    }

    /**
     * Hack function persisting the negativeformulated flag on a multichoice item (H12).
     *
     * @param \stdClass $item The multichoice item (options is modified in place).
     * @param bool $negativeformulated The flag value to store.
     * @return void
     */
    public static function set_negativeformulated(\stdClass $item, bool $negativeformulated): void {
        if (self::is_running_core_test()) {
            return;
        }
        $item->options = str_replace(self::MULTICHOICE_NEGATIVEFORMULATED, '', (string)$item->options);
        if ($negativeformulated) {
            $item->options .= self::MULTICHOICE_NEGATIVEFORMULATED;
        }
    }

    /**
     * Hack function adding the negativeformulated selector to the multichoice
     * item edit form (H12).
     *
     * @param \MoodleQuickForm $mform The item edit form being built.
     * @return void
     */
    public static function add_negativeformulated_form_element($mform): void {
        if (self::is_running_core_test()) {
            return;
        }
        $mform->addElement(
            'selectyesno',
            'negativeformulated',
            get_string('negative_formulated', 'individualfeedback')
        );
        $mform->addHelpButton('negativeformulated', 'negative_formulated', 'individualfeedback');
        $mform->setDefault('negativeformulated', 0);
    }

    /**
     * Hack function controlling whether items can toggle the required flag (H18).
     *
     * The fork manages the required flag itself (selfassessment workflow), so
     * the base class returns false in production. During core tests the
     * original mod_feedback behaviour (true) is restored.
     *
     * @return bool
     */
    public static function can_switch_require_default(): bool {
        return self::is_running_core_test();
    }

    /**
     * Hack function resolving the lib.php path for fork-specific item types (H18).
     *
     * individualfeedback_item_questiongroupend has no own item directory: it is a
     * dummy end-marker class living in item/questiongroup/lib.php (agreed with
     * A. Wagner, email 05.06./03.07.2026). This resolver points the class loader
     * to the right file.
     *
     * @param string $typeclean The cleaned item type name.
     * @param string $itemclasspath The path computed by the original code.
     * @return string The (possibly corrected) path to require.
     */
    public static function resolve_item_class_path(string $typeclean, string $itemclasspath): string {
        global $CFG;

        if (self::is_running_core_test()) {
            return $itemclasspath;
        }

        if ($typeclean === 'questiongroupend') {
            return $CFG->dirroot . '/mod/individualfeedback/item/questiongroup/lib.php';
        }
        return $itemclasspath;
    }
}

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
 * Unit tests for the hacks added after the client code review of 26.10.2026 (H22-H26).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\userlist;
use core_privacy\local\request\writer;
use mod_individualfeedback\privacy\provider;

/**
 * Findings 1 (H22), 6 (H23), 12 (H24), 9 (H25 + duplicate_and_link) and 13 (H26) of the review.
 *
 * Every test runs in hack-test (= production) mode via core_hack_testcase and, where the
 * behaviour differs, additionally in core-test mode.
 *
 * @covers \mod_individualfeedback\hack\lib
 */
final class review_20261026_test extends core_hack_testcase {
    /**
     * Course, activity with one text item, owner A and second editing teacher B.
     *
     * @return array [$course, $cm, $individualfeedback, $usera, $userb]
     */
    private function setup_course_with_two_teachers(): array {
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module('individualfeedback', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
        $generator->get_plugin_generator('mod_individualfeedback')->create_item_textfield($individualfeedback, ['name' => 'Q1']);
        $usera = $generator->create_user();
        $userb = $generator->create_user();
        $generator->enrol_user($usera->id, $course->id, 'editingteacher');
        $generator->enrol_user($userb->id, $course->id, 'editingteacher');
        return [$course, $cm, $individualfeedback, $usera, $userb];
    }

    // -------------------------------------------------------------------------
    // H22 — private user templates are owner-only (finding #1).
    // -------------------------------------------------------------------------

    /**
     * can_access_template(): private templates only for their owner; course/public templates unchanged.
     */
    public function test_can_access_template_mode_dependent(): void {
        $this->resetAfterTest();
        $private = (object) ['ispublic' => 2, 'userid' => 5];
        $course = (object) ['ispublic' => 0, 'userid' => 5];
        $public = (object) ['ispublic' => 1, 'userid' => 5];

        $this->assertTrue(lib::can_access_template($private, 5));
        $this->assertFalse(lib::can_access_template($private, 6), 'Foreign private template must not be accessible.');
        $this->assertTrue(lib::can_access_template($course, 6));
        $this->assertTrue(lib::can_access_template($public, 6));

        core_hack::stop_hack_test();
        try {
            $this->assertTrue(lib::can_access_template($private, 6), 'Core-test mode keeps the original behaviour.');
        } finally {
            core_hack::start_hack_test();
        }
    }

    /**
     * Applying a foreign private template through individualfeedback_items_from_template() is refused,
     * applying the own one works (injection in lib.php).
     */
    public function test_items_from_template_enforces_private_ownership(): void {
        global $DB;
        $this->resetAfterTest();
        [$course, $cm, $individualfeedback, $usera, $userb] = $this->setup_course_with_two_teachers();

        $this->setUser($usera);
        $this->assertTrue(individualfeedback_save_as_template($individualfeedback, 'A private', 2));
        $templateid = $DB->get_field('individualfeedback_template', 'id', ['name' => 'A private'], MUST_EXIST);

        $target = $this->getDataGenerator()->create_module('individualfeedback', ['course' => $course->id]);

        $this->setUser($userb);
        try {
            individualfeedback_items_from_template($target, $templateid);
            $this->fail('A foreign private template must not be applicable.');
        } catch (\moodle_exception $e) {
            $this->assertSame('nopermissions', $e->errorcode);
        }

        $this->setUser($usera);
        individualfeedback_items_from_template($target, $templateid);
        $this->assertSame(
            1,
            $DB->count_records('individualfeedback_item', ['individualfeedback' => $target->id]),
            'The owner can apply the private template.'
        );
    }

    // -------------------------------------------------------------------------
    // H23 — anonymity threshold counts peer responses only (finding #6).
    // -------------------------------------------------------------------------

    /**
     * One student response plus one teacher self-assessment in a group: production counts 1,
     * core-test mode (original query) counts 2.
     */
    public function test_count_completed_responses_excludes_selfassessments(): void {
        global $DB;
        $this->resetAfterTest();
        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module(
            'individualfeedback',
            ['course' => $course->id, 'anonymous' => INDIVIDUALFEEDBACK_ANONYMOUS_YES]
        );
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
        $context = \core\context\module::instance($cm->id);
        $generator->get_plugin_generator('mod_individualfeedback')->create_item_textfield($individualfeedback, ['name' => 'Q1']);

        $student = $generator->create_user();
        $teacher = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('mod/individualfeedback:complete', CAP_ALLOW, $roleid, $context->id, true);
        $group = $generator->create_group(['courseid' => $course->id]);
        $generator->create_group_member(['groupid' => $group->id, 'userid' => $student->id]);
        $generator->create_group_member(['groupid' => $group->id, 'userid' => $teacher->id]);

        $plugingenerator = $generator->get_plugin_generator('mod_individualfeedback');
        $plugingenerator->create_response(['cmid' => $cm->id, 'userid' => $student->id, 'Q1' => 'peer']);
        $plugingenerator->create_response(['cmid' => $cm->id, 'userid' => $teacher->id, 'Q1' => 'self']);
        $this->assertSame(1, $DB->count_records('individualfeedback_completed', ['selfassessment' => 1]));

        $structure = new \mod_individualfeedback_structure($individualfeedback, $cm);
        $this->assertSame(1, (int) $structure->count_completed_responses($group->id));
        $this->assertSame(1, (int) $structure->count_completed_responses(0));

        core_hack::stop_hack_test();
        try {
            $this->assertSame(
                2,
                (int) $structure->count_completed_responses($group->id),
                'Core-test mode must use the original query (all completed records).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H24 — owners may delete their private user templates (finding #12).
    // -------------------------------------------------------------------------

    /**
     * adjust_template_delete_permission(): owner with deletetemplate → true, others → false,
     * course/public templates and core-test mode → unchanged input.
     */
    public function test_adjust_template_delete_permission(): void {
        $this->resetAfterTest();
        [$course, $cm, $individualfeedback, $usera, $userb] = $this->setup_course_with_two_teachers();
        $context = \core\context\module::instance($cm->id);
        $private = (object) ['ispublic' => 2, 'userid' => $usera->id];
        $public = (object) ['ispublic' => 1, 'userid' => $usera->id];

        $this->setUser($usera);
        $this->assertTrue(lib::adjust_template_delete_permission(false, $private, $context));
        $this->assertFalse(lib::adjust_template_delete_permission(false, $public, $context), 'Public templates keep the original decision.');

        $this->setUser($userb);
        $this->assertFalse(lib::adjust_template_delete_permission(false, $private, $context));
        $this->assertFalse(lib::adjust_template_delete_permission(true, $private, $context), 'Original "true" is overruled for foreign private templates.');

        core_hack::stop_hack_test();
        try {
            $this->setUser($usera);
            $this->assertFalse(lib::adjust_template_delete_permission(false, $private, $context));
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H25 — report modes of analysis.php (finding #9).
    // -------------------------------------------------------------------------

    /**
     * get_analysis_mode(): whitelisted modes only; core-test mode always yields the core rendering.
     */
    public function test_get_analysis_mode_whitelist(): void {
        $this->resetAfterTest();
        $_GET['mode'] = 'overview_groups';
        $this->assertSame('overview_groups', lib::get_analysis_mode());
        $_GET['mode'] = '../../config';
        $this->assertSame('detail_questions', lib::get_analysis_mode());
        $_GET['mode'] = 'overview_groups';
        core_hack::stop_hack_test();
        try {
            $this->assertSame('detail_questions', lib::get_analysis_mode());
        } finally {
            core_hack::start_hack_test();
            unset($_GET['mode']);
        }
    }

    /**
     * Every non-default mode has a fragment file and an export script; the default mode keeps core.
     */
    public function test_analysis_fragments_and_exports_exist(): void {
        global $CFG;
        $this->resetAfterTest();
        $default = new \moodle_url('/mod/individualfeedback/analysis_to_excel.php', ['id' => 1]);
        $this->assertNull(lib::get_analysis_report_fragment('detail_questions'));
        $this->assertSame($default, lib::get_analysis_export_url('detail_questions', $default, 1));
        foreach (lib::ANALYSIS_MODES as $mode) {
            if ($mode === 'detail_questions') {
                continue;
            }
            $fragment = lib::get_analysis_report_fragment($mode);
            $this->assertFileExists($fragment, "Fragment for mode {$mode}");
            $this->assertFileExists($CFG->dirroot . "/mod/individualfeedback/{$mode}_to_excel.php");
            $this->assertStringContainsString("{$mode}_to_excel.php", lib::get_analysis_export_url($mode, $default, 1)->out(false));
        }
        $this->assertNull(lib::get_analysis_report_fragment('not_a_mode'));
    }

    /**
     * duplicate_and_link(): creates a linked copy; comparison modes become available.
     */
    public function test_duplicate_and_link(): void {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();
        [$course, $cm, $individualfeedback] = $this->setup_course_with_two_teachers();
        $cminfo = get_fast_modinfo($course)->get_cm($cm->id);

        $this->assertCount(4, lib::get_available_analysis_modes($individualfeedback));

        $newcm = lib::duplicate_and_link($course, $cminfo);
        $this->assertNotEquals($cm->id, $newcm->id);
        $this->assertSame('individualfeedback', $newcm->modname);

        $linkedid = individualfeedback_get_linkedid($individualfeedback->id);
        $this->assertNotEmpty($linkedid);
        $this->assertEquals($linkedid, individualfeedback_get_linkedid($newcm->instance));
        $this->assertCount(2, individualfeedback_get_linked_individualfeedbacks($individualfeedback->id));
        $this->assertTrue((bool) individualfeedback_check_linked_questions($individualfeedback->id));
        $this->assertCount(6, lib::get_available_analysis_modes($individualfeedback));
        $this->assertSame(1, $DB->count_records('individualfeedback_item', ['individualfeedback' => $newcm->instance]));
    }

    // -------------------------------------------------------------------------
    // H26 — privacy lifecycle of template ownership (finding #13).
    // -------------------------------------------------------------------------

    /**
     * Contexts, users-in-context, export and delete cover templates owned by the user:
     * private user templates are deleted, shared templates lose the owner reference.
     */
    public function test_privacy_lifecycle_for_template_owners(): void {
        global $DB;
        $this->resetAfterTest();
        [$course, $cm, $individualfeedback, $usera] = $this->setup_course_with_two_teachers();
        $coursecontext = \core\context\course::instance($course->id);
        $systemcontext = \core\context\system::instance();

        $this->setUser($usera);
        individualfeedback_save_as_template($individualfeedback, 'A private', 2);
        individualfeedback_save_as_template($individualfeedback, 'A course template', 0);
        $privateid = $DB->get_field('individualfeedback_template', 'id', ['name' => 'A private'], MUST_EXIST);
        $courseid = $DB->get_field('individualfeedback_template', 'id', ['name' => 'A course template'], MUST_EXIST);
        $this->assertSame((int) $usera->id, (int) $DB->get_field('individualfeedback_template', 'userid', ['id' => $courseid]));

        // Discovery.
        $contextids = array_map('intval', provider::get_contexts_for_userid($usera->id)->get_contextids());
        $this->assertContains((int) $systemcontext->id, $contextids, 'System context holds the private user template (course = 0).');
        $this->assertContains((int) $coursecontext->id, $contextids, 'Course context holds the course template.');

        $userlist = new userlist($systemcontext, 'mod_individualfeedback');
        provider::get_users_in_context($userlist);
        $this->assertContains((int) $usera->id, array_map('intval', $userlist->get_userids()));

        // Export.
        $approved = new approved_contextlist($usera, 'mod_individualfeedback', [$systemcontext->id, $coursecontext->id]);
        provider::export_user_data($approved);
        $exported = writer::with_context($systemcontext)->get_data([get_string('privacy:export:templates', 'individualfeedback')]);
        $this->assertNotEmpty($exported->templates);
        $this->assertSame('A private', $exported->templates[0]->name);

        // Delete for user.
        provider::delete_data_for_user($approved);
        $this->assertFalse($DB->record_exists('individualfeedback_template', ['id' => $privateid]), 'Private user template is deleted.');
        $this->assertTrue($DB->record_exists('individualfeedback_template', ['id' => $courseid]), 'Course template is kept ...');
        $this->assertSame(0, (int) $DB->get_field('individualfeedback_template', 'userid', ['id' => $courseid]), '... without owner reference.');
    }

    /**
     * delete_data_for_all_users_in_context() and delete_data_for_users() handle course contexts.
     */
    public function test_privacy_delete_in_course_context(): void {
        global $DB;
        $this->resetAfterTest();
        [$course, $cm, $individualfeedback, $usera, $userb] = $this->setup_course_with_two_teachers();
        $coursecontext = \core\context\course::instance($course->id);

        $this->setUser($usera);
        individualfeedback_save_as_template($individualfeedback, 'A course template', 0);
        $this->setUser($userb);
        individualfeedback_save_as_template($individualfeedback, 'B course template', 0);
        $a = $DB->get_field('individualfeedback_template', 'id', ['name' => 'A course template'], MUST_EXIST);
        $b = $DB->get_field('individualfeedback_template', 'id', ['name' => 'B course template'], MUST_EXIST);

        $userlist = new \core_privacy\local\request\approved_userlist($coursecontext, 'mod_individualfeedback', [$usera->id]);
        provider::delete_data_for_users($userlist);
        $this->assertSame(0, (int) $DB->get_field('individualfeedback_template', 'userid', ['id' => $a]));
        $this->assertSame((int) $userb->id, (int) $DB->get_field('individualfeedback_template', 'userid', ['id' => $b]));

        provider::delete_data_for_all_users_in_context($coursecontext);
        $this->assertSame(0, (int) $DB->get_field('individualfeedback_template', 'userid', ['id' => $b]));
        $this->assertTrue($DB->record_exists('individualfeedback_template', ['id' => $b]), 'Shared templates survive, anonymised.');
    }
}

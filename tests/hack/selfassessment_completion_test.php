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
 * Unit tests for the selfassessment completion hacks (H6, H7).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

/**
 * Tests for hack\lib::can_complete_for_selfassessment (H7) and
 * hack\lib::filter_respondents_for_core_test (H6).
 *
 * Both hacks change behaviour depending on the hack-test flag:
 * - production / hack-test mode: teachers with mod/individualfeedback:complete
 *   may complete (selfassessment workflow) and appear among non-respondents.
 * - core-test mode: original mod_feedback behaviour is restored so that
 *   unmodified core unit tests keep passing.
 *
 * The tests below exercise BOTH modes, so they fail if either the injection
 * line in classes/completion.php / lib.php or the guard inside the hack
 * method is lost.
 *
 * @covers \mod_individualfeedback\hack\lib
 */
final class selfassessment_completion_test extends core_hack_testcase {

    /**
     * The selfassessment capability must exist after install/upgrade.
     *
     * Declared in db/access.php (MBS-Hack block). A programmatic insert is NOT
     * sufficient: update_capabilities() runs after every plugin upgrade and
     * deletes capabilities missing from db/access.php — this test guards
     * against regressing to that approach (found in browser smoke-test).
     */
    public function test_selfassessment_capability_exists(): void {
        global $DB;

        $this->assertTrue(
            $DB->record_exists('capabilities', ['name' => 'mod/individualfeedback:selfassessment']),
            'mod/individualfeedback:selfassessment missing — it must be declared in db/access.php, '
            . 'a programmatic insert does not survive update_capabilities() on the next upgrade.'
        );
    }
    /**
     * Create a course with an individualfeedback instance, one student and one
     * editing teacher who has been granted mod/individualfeedback:complete.
     *
     * @return array [$course, $cm, $context, $student, $teacher]
     */
    private function setup_feedback_with_teacher(): array {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id]
        );
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
        $context = \context_module::instance($cm->id);

        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');

        $teacher = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($teacher->id, $course->id, 'editingteacher');

        // Grant complete to editingteacher (selfassessment workflow, see D.4).
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('mod/individualfeedback:complete', CAP_ALLOW, $roleid, $context->id, true);

        return [$course, $cm, $context, $student, $teacher];
    }

    // -------------------------------------------------------------------------
    // H7 — completion::can_complete() / can_complete_for_selfassessment().
    // -------------------------------------------------------------------------

    /**
     * In production (hack-test mode) an editing teacher holding the complete
     * capability may complete the individualfeedback (selfassessment).
     */
    public function test_teacher_can_complete_in_production_mode(): void {
        $this->resetAfterTest();
        [, $cm, $context, $student, $teacher] = $this->setup_feedback_with_teacher();

        // Hack-test mode is active via core_hack_testcase::setUp().
        $this->assertTrue(
            lib::can_complete_for_selfassessment($context, $teacher->id),
            'Editing teacher must be able to complete (selfassessment) in production mode.'
        );
        $this->assertTrue(
            lib::can_complete_for_selfassessment($context, $student->id),
            'Student must be able to complete in production mode.'
        );
    }

    /**
     * In core-test mode the original mod_feedback behaviour is restored:
     * editing teachers may NOT complete, students still may.
     */
    public function test_teacher_cannot_complete_in_core_test_mode(): void {
        $this->resetAfterTest();
        [, $cm, $context, $student, $teacher] = $this->setup_feedback_with_teacher();

        core_hack::stop_hack_test();
        try {
            $this->assertFalse(
                lib::can_complete_for_selfassessment($context, $teacher->id),
                'Editing teacher must NOT complete in core-test mode (original behaviour).'
            );
            $this->assertTrue(
                lib::can_complete_for_selfassessment($context, $student->id),
                'Student completion must be unaffected in core-test mode.'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    /**
     * End-to-end through completion::can_complete(): the injection line in
     * classes/completion.php delegates to the hack. Without the injection the
     * teacher would always get TRUE in core-test mode — this asserts FALSE.
     */
    public function test_can_complete_injection_effective_end_to_end(): void {
        global $DB;
        $this->resetAfterTest();
        [$course, $cm, $context, $student, $teacher] = $this->setup_feedback_with_teacher();

        $individualfeedback = $DB->get_record(
            'individualfeedback',
            ['id' => $cm->instance],
            '*',
            MUST_EXIST
        );
        $cminfo = \cm_info::create($cm);

        // Production mode: teacher may complete.
        $completion = new \mod_individualfeedback_completion(
            $individualfeedback,
            $cminfo,
            $course->id,
            false,
            null,
            null,
            $teacher->id
        );
        $this->assertTrue(
            $completion->can_complete(),
            'Production mode: teacher with complete capability may complete.'
        );

        // Core-test mode: injection must restore original behaviour.
        core_hack::stop_hack_test();
        try {
            $completion = new \mod_individualfeedback_completion(
                $individualfeedback,
                $cminfo,
                $course->id,
                false,
                null,
                null,
                $teacher->id
            );
            $this->assertFalse(
                $completion->can_complete(),
                'Core-test mode: teacher must not complete — fails if the injection '
                . 'line in classes/completion.php is lost.'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H6 — individualfeedback_get_incomplete_users() / filter_respondents_for_core_test().
    // -------------------------------------------------------------------------

    /**
     * In production mode teachers with the complete capability appear in the
     * non-respondents list (selfassessment includes teachers).
     */
    public function test_incomplete_users_includes_teacher_in_production_mode(): void {
        $this->resetAfterTest();
        [, $cm, $context, $student, $teacher] = $this->setup_feedback_with_teacher();

        $cminfo = \cm_info::create($cm);
        $incomplete = individualfeedback_get_incomplete_users($cminfo);

        $this->assertContains((int)$student->id, array_map('intval', $incomplete));
        $this->assertContains(
            (int)$teacher->id,
            array_map('intval', $incomplete),
            'Production mode: teacher must be listed among non-respondents.'
        );
    }

    /**
     * In core-test mode teachers are filtered out of the non-respondents list
     * so unmodified core tests that count respondents keep passing.
     */
    public function test_incomplete_users_excludes_teacher_in_core_test_mode(): void {
        $this->resetAfterTest();
        [, $cm, $context, $student, $teacher] = $this->setup_feedback_with_teacher();

        $cminfo = \cm_info::create($cm);

        core_hack::stop_hack_test();
        try {
            $incomplete = individualfeedback_get_incomplete_users($cminfo);

            $this->assertContains(
                (int)$student->id,
                array_map('intval', $incomplete),
                'Student must remain in the non-respondents list in core-test mode.'
            );
            $this->assertNotContains(
                (int)$teacher->id,
                array_map('intval', $incomplete),
                'Core-test mode: teacher must be filtered out — fails if the injection '
                . 'line in lib.php individualfeedback_get_incomplete_users() is lost.'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }
}

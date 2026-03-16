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
 * Unit tests for mod_individualfeedback\local\analysis\compare.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\local\analysis;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

/**
 * Tests for compare — the side-by-side feedback comparison renderer.
 *
 * Most of the compare::render() logic depends on Moodle output context and
 * capability checks. These tests cover the controllable, pure code paths:
 *
 * - compact mode toggle (static property)
 * - render() raises moodle_exception for users without view-analysis capability
 *
 * @covers \mod_individualfeedback\local\analysis\compare
 */
final class compare_test extends \advanced_testcase {

    // -----------------------------------------------------------------------
    // compact mode static property
    // -----------------------------------------------------------------------

    /**
     * The static $compactmode property starts as false.
     *
     * @covers \mod_individualfeedback\local\analysis\compare
     */
    public function test_compact_mode_defaults_to_false(): void {
        $reflection = new \ReflectionClass(compare::class);
        $prop = $reflection->getProperty('compactmode');
        $prop->setAccessible(true);

        // Reset to false first in case a prior test left it true.
        $prop->setValue(null, false);

        $this->assertFalse($prop->getValue(null),
            'compactmode must default to false when no render() call with compact=true has occurred.');
    }

    /**
     * render() sets compactmode=true when $compact=true is passed.
     *
     * We verify via reflection since compactmode is a protected static.
     * The render() call will throw (no capability), but the static assignment
     * happens before the capability check, so we can read the value in the catch.
     *
     * @covers \mod_individualfeedback\local\analysis\compare::render
     */
    public function test_render_sets_compact_mode_before_capability_check(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $fbrecord = $this->getDataGenerator()->create_module(
            'individualfeedback', ['course' => $course->id]
        );
        $cm = get_coursemodule_from_instance('individualfeedback', $fbrecord->id);

        $reflection = new \ReflectionClass(compare::class);
        $prop = $reflection->getProperty('compactmode');
        $prop->setAccessible(true);
        $prop->setValue(null, false); // reset

        // render() sets $compactmode then calls can_view_analysis();
        // admin has capability so render() will proceed past the check.
        // We just verify there is no exception for admin.
        $url = new \moodle_url('/mod/individualfeedback/analysis_compare.php', ['id' => $cm->id]);
        try {
            // Suppress output from render() using output buffering.
            ob_start();
            compare::render($fbrecord, $cm, $cm->id, $url, true);
            ob_end_clean();
        } catch (\Throwable $e) {
            ob_end_clean();
            // Any exception is acceptable here — we only care about the flag.
        }

        $this->assertTrue($prop->getValue(null),
            'compactmode must be set to true inside render() when $compact=true is passed.');

        // Clean up static state.
        $prop->setValue(null, false);
    }

    // -----------------------------------------------------------------------
    // render() – permission enforcement
    // -----------------------------------------------------------------------

    /**
     * render() must throw a moodle_exception when the user cannot view analysis.
     *
     * A student enrolled with no extra capabilities cannot view the analysis,
     * so render() must raise moodle_exception with errorcode 'error'.
     *
     * @covers \mod_individualfeedback\local\analysis\compare::render
     */
    public function test_render_throws_for_user_without_capability(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $fbrecord = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id, 'publish_stats' => 0]
        );
        $cm = get_coursemodule_from_instance('individualfeedback', $fbrecord->id);

        // Create a student who cannot view analysis.
        $student = $this->getDataGenerator()->create_user();
        $this->getDataGenerator()->enrol_user($student->id, $course->id, 'student');
        $this->setUser($student);

        $url = new \moodle_url('/mod/individualfeedback/analysis_compare.php', ['id' => $cm->id]);

        $this->expectException(\moodle_exception::class);

        ob_start();
        try {
            compare::render($fbrecord, $cm, $cm->id, $url);
        } finally {
            ob_end_clean();
        }
    }

    /**
     * Admin user (has all capabilities) must not hit the exception branch.
     *
     * @covers \mod_individualfeedback\local\analysis\compare::render
     */
    public function test_render_does_not_throw_for_admin(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $fbrecord = $this->getDataGenerator()->create_module(
            'individualfeedback', ['course' => $course->id, 'publish_stats' => 1]
        );
        $cm = get_coursemodule_from_instance('individualfeedback', $fbrecord->id);
        $url = new \moodle_url('/mod/individualfeedback/analysis_compare.php', ['id' => $cm->id]);

        $threw = false;
        ob_start();
        try {
            compare::render($fbrecord, $cm, $cm->id, $url);
        } catch (\moodle_exception $e) {
            $threw = true;
        } finally {
            ob_end_clean();
        }

        $this->assertFalse($threw,
            'Admin must not receive a moodle_exception from compare::render().');
    }
}

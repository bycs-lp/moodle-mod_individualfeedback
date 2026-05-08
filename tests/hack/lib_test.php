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

namespace mod_individualfeedback\hack;

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

/**
 * Unit tests for the mod_individualfeedback hack lib class.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @covers     \mod_individualfeedback\hack\lib
 */
final class lib_test extends core_hack_testcase {

    /**
     * Insert a template record into the individualfeedback_template table.
     *
     * @param string $name The template name.
     * @param int $ispublic The ispublic flag (0 = course, 1 = public, 2 = private).
     * @param int $userid The owning user id.
     * @param int $courseid The course id (defaults to 0).
     * @return int The new record id.
     */
    private function create_template(string $name, int $ispublic, int $userid, int $courseid = 0): int {
        global $DB;

        $record = (object) [
            'course' => $courseid,
            'ispublic' => $ispublic,
            'name' => $name,
            'userid' => $userid,
        ];

        return $DB->insert_record('individualfeedback_template', $record);
    }

    /**
     * Test that calling individualfeedback_get_template_list() with the
     * 'private' filter returns the current user's private templates via the
     * hack integration.
     */
    public function test_individualfeedback_get_template_list_returns_private_templates(): void {
        global $USER;

        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user1 = $this->getDataGenerator()->create_user();
        $user2 = $this->getDataGenerator()->create_user();
        $this->setUser($user1);

        // Private templates of the current user - must be returned.
        $this->create_template('private-user1-a', 2, $user1->id, $course->id);
        $this->create_template('private-user1-b', 2, $user1->id, $course->id);

        // Private template of another user - must NOT be returned.
        $this->create_template('private-user2', 2, $user2->id, $course->id);

        // Public template - must NOT be returned for the 'private' filter.
        $this->create_template('public', 1, $user1->id, $course->id);

        $templates = individualfeedback_get_template_list($course, 'private');

        $this->assertIsArray($templates);
        $this->assertCount(2, $templates);

        $names = array_map(fn($t) => $t->name, $templates);
        $this->assertEqualsCanonicalizing(['private-user1-a', 'private-user1-b'], $names);

        foreach ($templates as $template) {
            $this->assertEquals($USER->id, $template->userid);
            $this->assertEquals(2, $template->ispublic);
        }
    }

    /**
     * Test that calling individualfeedback_get_template_list() with a
     * non-private filter is not augmented by the private template hack.
     *
     * The hack returns an empty array for non-private filters, which the
     * wrapper assigns back to the result. This test documents that behaviour
     * by ensuring no private templates leak into a non-private listing.
     */
    public function test_individualfeedback_get_template_list_non_private_excludes_private(): void {
        $this->resetAfterTest();

        $course = $this->getDataGenerator()->create_course();
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->create_template('private', 2, $user->id, $course->id);
        $this->create_template('public', 1, $user->id, $course->id);

        $templates = individualfeedback_get_template_list($course, 'public');

        $this->assertIsArray($templates);
        foreach ($templates as $template) {
            $this->assertEquals(1, $template->ispublic,
                'Private templates must not be included in non-private listings.');
        }
    }
}
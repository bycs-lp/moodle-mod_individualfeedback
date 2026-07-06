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
 * Unit tests for the privacy provider hacks (Hack H8).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

use core_privacy\local\metadata\collection;

/**
 * Tests that the fork-specific privacy metadata and export fields are present (DSGVO).
 *
 * The privacy hacks deliberately have NO is_running_core_test() guard:
 * metadata must always describe the real DB schema. These tests verify the
 * injections in classes/privacy/provider.php are effective.
 *
 * @covers \mod_individualfeedback\hack\lib::add_privacy_metadata
 * @covers \mod_individualfeedback\hack\lib::add_privacy_export_fields
 */
final class privacy_hack_test extends core_hack_testcase {
    /**
     * get_metadata() must declare the selfassessment field on the completed table.
     */
    public function test_metadata_contains_selfassessment(): void {
        $collection = new collection('mod_individualfeedback');
        $collection = \mod_individualfeedback\privacy\provider::get_metadata($collection);

        $found = false;
        foreach ($collection->get_collection() as $type) {
            if ($type->get_name() === 'individualfeedback_completed') {
                $fields = $type->get_privacy_fields();
                $this->assertArrayHasKey(
                    'selfassessment',
                    $fields,
                    'selfassessment missing from individualfeedback_completed privacy metadata (H8, finding #6).'
                );
                $found = true;
            }
        }
        $this->assertTrue($found, 'individualfeedback_completed table missing from privacy metadata.');
    }

    /**
     * get_metadata() must declare the individualfeedback_template table with its
     * userid field (private user templates store the owner).
     */
    public function test_metadata_contains_template_table(): void {
        $collection = new collection('mod_individualfeedback');
        $collection = \mod_individualfeedback\privacy\provider::get_metadata($collection);

        $found = false;
        foreach ($collection->get_collection() as $type) {
            if ($type->get_name() === 'individualfeedback_template') {
                $fields = $type->get_privacy_fields();
                $this->assertArrayHasKey(
                    'userid',
                    $fields,
                    'userid missing from individualfeedback_template privacy metadata (H8).'
                );
                $found = true;
            }
        }
        $this->assertTrue(
            $found,
            'individualfeedback_template table missing from privacy metadata — injection in get_metadata() lost (H8).'
        );
    }

    /**
     * All privacy metadata language strings must exist (moodlecheck also
     * requires them; a missing string breaks the privacy registry UI).
     */
    public function test_privacy_lang_strings_exist(): void {
        foreach (
            ['privacy:metadata:completed:selfassessment',
                'privacy:metadata:template',
                'privacy:metadata:template:userid'] as $identifier
        ) {
            $manager = get_string_manager();
            $this->assertTrue(
                $manager->string_exists($identifier, 'mod_individualfeedback'),
                "Language string {$identifier} missing (H8/D5)."
            );
        }
    }

    /**
     * add_privacy_export_fields() must append the transformed selfassessment flag.
     */
    public function test_export_fields_include_selfassessment(): void {
        $submission = ['inprogress' => 'No', 'answers' => []];

        $record = (object) ['selfassessment' => 1];
        $result = lib::add_privacy_export_fields($submission, $record);
        $this->assertArrayHasKey('selfassessment', $result);

        $record = (object) ['selfassessment' => 0];
        $result = lib::add_privacy_export_fields($submission, $record);
        $this->assertArrayHasKey('selfassessment', $result);

        // Records without the column (defensive) must not raise notices.
        $result = lib::add_privacy_export_fields($submission, new \stdClass());
        $this->assertArrayHasKey('selfassessment', $result);
    }
}

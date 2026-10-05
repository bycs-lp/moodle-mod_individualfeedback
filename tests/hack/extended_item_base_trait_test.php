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
 * Unit tests for the extended_item_base_trait injection (Hack H18).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/item/individualfeedback_item_class.php');

/**
 * Tests that the trait injection into individualfeedback_item_base is effective.
 *
 * The trait was explicitly approved by A. Wagner (03.07.2026): a single
 * `use` line inside the abstract base class makes the extended analysis
 * methods available on EVERY item type, including core item types, without
 * touching their files. These tests fail if the `use` line is lost.
 *
 * @covers \mod_individualfeedback\local\extended_item_base_trait
 */
final class extended_item_base_trait_test extends core_hack_testcase {
    /**
     * The trait must be attached to the abstract base class.
     */
    public function test_base_class_uses_trait(): void {
        $traits = class_uses(\individualfeedback_item_base::class);
        $this->assertArrayHasKey(
            \mod_individualfeedback\local\extended_item_base_trait::class,
            $traits,
            'The use-line injection in item/individualfeedback_item_class.php is lost (H18).'
        );
    }

    /**
     * Extended methods must be available on CORE item types (textfield,
     * multichoice) via inheritance from the base class — this is the whole
     * point of the trait approach vs. a subclass.
     */
    public function test_extended_methods_available_on_core_item_types(): void {
        foreach (['textfield', 'multichoice', 'numeric', 'textarea'] as $typ) {
            $itemobj = individualfeedback_get_item_class($typ);
            foreach (
                ['check_and_get_self_assessment_data', 'get_item_answer_data',
                    'print_overview_questions', 'print_comparison_questions',
                    'excelprint_overview_questions', 'excelprint_comparison_questions'] as $method
            ) {
                $this->assertTrue(
                    method_exists($itemobj, $method),
                    "Method {$method} missing on core item type {$typ} — trait injection lost (H18)."
                );
            }
        }
    }

    /**
     * Extended methods must equally be available on the fork's own item types.
     */
    public function test_extended_methods_available_on_fork_item_types(): void {
        foreach (['fourlevelapproval', 'fivelevelapproval', 'fourlevelfrequency'] as $typ) {
            $itemobj = individualfeedback_get_item_class($typ);
            $this->assertTrue(
                method_exists($itemobj, 'get_item_answer_data'),
                "Trait methods missing on fork item type {$typ} (H18)."
            );
        }
    }

    /**
     * The questiongroupend dummy class must be loadable through
     * individualfeedback_get_item_class() although it has no own item
     * directory (resolver hack in lib.php).
     */
    public function test_questiongroupend_class_resolvable(): void {
        $itemobj = individualfeedback_get_item_class('questiongroupend');
        $this->assertInstanceOf(\individualfeedback_item_base::class, $itemobj);
        $this->assertEquals(
            0,
            $itemobj->get_hasvalue(),
            'questiongroupend is a dummy end marker and must not expect a value.'
        );
    }

    /**
     * can_switch_require(): fork behaviour (false) in production mode,
     * original mod_feedback behaviour (true) during core tests.
     */
    public function test_can_switch_require_mode_dependent(): void {
        $itemobj = individualfeedback_get_item_class('textfield');

        // Hack-test mode (= production behaviour).
        $this->assertFalse(
            $itemobj->can_switch_require(),
            'Production mode: required flag is managed by the fork.'
        );

        core_hack::stop_hack_test();
        try {
            $this->assertTrue(
                $itemobj->can_switch_require(),
                'Core-test mode: original behaviour must be restored — fails if the '
                . 'injection in can_switch_require() is lost.'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    /**
     * get_item_answer_data() must return a sane empty result during core-test
     * mode instead of raising undefined-variable notices (bug in the old c3
     * implementation — $values was undefined when the guard fired).
     */
    public function test_get_item_answer_data_core_test_safe(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $individualfeedback = $this->getDataGenerator()->create_module(
            'individualfeedback',
            ['course' => $course->id]
        );
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_individualfeedback');
        $item = $generator->create_item_multichoice($individualfeedback);

        $itemobj = individualfeedback_get_item_class('multichoice');

        core_hack::stop_hack_test();
        try {
            $result = $itemobj->get_item_answer_data($item, '|');
            $this->assertIsArray($result);
            $this->assertEquals(
                0,
                $result['totalvalues'],
                'Core-test mode: no values may be fetched, result must be a clean zero.'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }
}

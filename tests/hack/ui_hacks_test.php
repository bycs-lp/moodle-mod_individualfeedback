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
 * Unit tests for the UI and form hacks H9-H14.
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\hack;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/classes/complete_form.php');

/**
 * Tests for the H9-H14 hack methods, each exercised in both hack-test
 * (= production) and core-test mode where behaviour differs.
 *
 * @covers \mod_individualfeedback\hack\lib
 */
final class ui_hacks_test extends core_hack_testcase {
    // -------------------------------------------------------------------------
    // H9 — complete_form question group layout.
    // -------------------------------------------------------------------------

    /**
     * The group trait must be attached to the complete form (use-line injection).
     */
    public function test_complete_form_uses_group_trait(): void {
        $traits = class_uses(\mod_individualfeedback_complete_form::class);
        $this->assertArrayHasKey(
            \mod_individualfeedback\local\complete_form_group_trait::class,
            $traits,
            'The use-line injection in classes/complete_form.php is lost (H9).'
        );
    }

    /**
     * amend_item_css_class(): adds marker classes in production, unchanged in core-test mode.
     */
    public function test_amend_item_css_class_mode_dependent(): void {
        $item = (object) ['typ' => 'textfield', 'dependitem' => 3];

        // Production mode: marker classes added.
        $class = lib::amend_item_css_class('base', $item);
        $this->assertStringContainsString('questiongroupmoveitem', $class);
        $this->assertStringContainsString('individualfeedback_is_dependent', $class);

        // questiongroupend never gets the dependent marker.
        $enditem = (object) ['typ' => 'questiongroupend', 'dependitem' => 3];
        $class = lib::amend_item_css_class('base', $enditem);
        $this->assertStringNotContainsString('individualfeedback_is_dependent', $class);

        // Core-test mode: string must be returned unchanged.
        core_hack::stop_hack_test();
        try {
            $this->assertEquals(
                'base',
                lib::amend_item_css_class('base', $item),
                'Core-test mode: CSS class must be unchanged — guard inside hack method (H9).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H10 — responses_anon_table selfassessment.
    // -------------------------------------------------------------------------

    /**
     * add_selfassessment_to_fields(): appends the column in production only.
     */
    public function test_add_selfassessment_to_fields_mode_dependent(): void {
        $fields = 'c.id, c.random_response, c.courseid';

        $this->assertEquals(
            $fields . ', c.selfassessment',
            lib::add_selfassessment_to_fields($fields)
        );

        core_hack::stop_hack_test();
        try {
            $this->assertEquals(
                $fields,
                lib::add_selfassessment_to_fields($fields),
                'Core-test mode: SQL fields must be unchanged (H10).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    /**
     * get_selfassessment_marker(): marker only for flagged rows in production.
     */
    public function test_get_selfassessment_marker_mode_dependent(): void {
        $flagged = (object) ['selfassessment' => 1];
        $normal = (object) ['selfassessment' => 0];

        $this->assertStringContainsString('*', lib::get_selfassessment_marker($flagged));
        $this->assertSame('', lib::get_selfassessment_marker($normal));

        core_hack::stop_hack_test();
        try {
            $this->assertSame(
                '',
                lib::get_selfassessment_marker($flagged),
                'Core-test mode: no marker even for flagged rows (H10).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H11 — manage_templates private user templates.
    // -------------------------------------------------------------------------

    /**
     * enforce_private_template_ownership(): non-owner deletion of a private
     * template redirects (throws in PHPUnit); owner and non-private pass through.
     */
    public function test_enforce_private_template_ownership(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $url = new \moodle_url('/mod/individualfeedback/manage_templates.php', ['id' => 1]);

        // Owner: no redirect.
        $own = (object) ['ispublic' => 2, 'userid' => $user->id];
        lib::enforce_private_template_ownership($own, $url);

        // Non-private templates: no redirect regardless of owner.
        $public = (object) ['ispublic' => 1, 'userid' => $other->id];
        lib::enforce_private_template_ownership($public, $url);

        // Foreign private template: redirect() throws in PHPUnit.
        $foreign = (object) ['ispublic' => 2, 'userid' => $other->id];
        $this->expectException(\moodle_exception::class);
        lib::enforce_private_template_ownership($foreign, $url);
    }

    /**
     * enforce_private_template_ownership() must be inactive in core-test mode.
     */
    public function test_enforce_private_template_ownership_core_test_mode(): void {
        $this->resetAfterTest();
        $user = $this->getDataGenerator()->create_user();
        $other = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $url = new \moodle_url('/mod/individualfeedback/manage_templates.php', ['id' => 1]);
        $foreign = (object) ['ispublic' => 2, 'userid' => $other->id];

        core_hack::stop_hack_test();
        try {
            // Must NOT redirect/throw.
            lib::enforce_private_template_ownership($foreign, $url);
            $this->assertTrue(true);
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H12 — multichoice negativeformulated flag.
    // -------------------------------------------------------------------------

    /**
     * set/get negativeformulated round-trip via the item options string.
     */
    public function test_negativeformulated_roundtrip(): void {
        $item = (object) ['options' => 'i'];

        lib::set_negativeformulated($item, true);
        $this->assertTrue(lib::get_negativeformulated($item));
        $this->assertStringContainsString(
            'i',
            $item->options,
            'Other option flags must be preserved.'
        );

        lib::set_negativeformulated($item, false);
        $this->assertFalse(lib::get_negativeformulated($item));
        $this->assertEquals('i', $item->options);

        // Setting twice must not duplicate the marker.
        lib::set_negativeformulated($item, true);
        lib::set_negativeformulated($item, true);
        $this->assertEquals(1, substr_count($item->options, lib::MULTICHOICE_NEGATIVEFORMULATED));
    }

    /**
     * negativeformulated is inert in core-test mode (no read, no write).
     */
    public function test_negativeformulated_core_test_mode(): void {
        $item = (object) ['options' => 'in'];

        core_hack::stop_hack_test();
        try {
            $this->assertFalse(
                lib::get_negativeformulated($item),
                'Core-test mode: flag must never be reported (H12).'
            );
            lib::set_negativeformulated($item, true);
            $this->assertEquals(
                'in',
                $item->options,
                'Core-test mode: options must not be modified (H12).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H14 — create_template_form ispublic normalisation.
    // -------------------------------------------------------------------------

    /**
     * normalize_template_ispublic(): a user WITHOUT createpublictemplate can
     * never store a public template even when POSTing ispublic=1 (server-side
     * enforcement); private (2) and course (0) pass through.
     */
    public function test_normalize_template_ispublic_enforces_capability(): void {
        $this->resetAfterTest();

        // Plain user without the system capability.
        $user = $this->getDataGenerator()->create_user();
        $this->setUser($user);

        $this->assertSame(
            0,
            lib::normalize_template_ispublic((object) ['ispublic' => 1]),
            'ispublic=1 must be downgraded to 0 without createpublictemplate (H14).'
        );
        $this->assertSame(
            2,
            lib::normalize_template_ispublic((object) ['ispublic' => 2]),
            'Private user template does not need the public capability.'
        );
        $this->assertSame(0, lib::normalize_template_ispublic((object) ['ispublic' => 0]));
        $this->assertSame(0, lib::normalize_template_ispublic(new \stdClass()));

        // Admin holds the capability: public template allowed.
        $this->setAdminUser();
        $this->assertSame(1, lib::normalize_template_ispublic((object) ['ispublic' => 1]));
    }

    /**
     * normalize_template_ispublic(): original checkbox semantics in core-test mode.
     */
    public function test_normalize_template_ispublic_core_test_mode(): void {
        $this->resetAfterTest();
        $this->setAdminUser();

        core_hack::stop_hack_test();
        try {
            // Original code: !empty() ? 1 : 0 — value 2 collapses to 1.
            $this->assertSame(
                1,
                lib::normalize_template_ispublic((object) ['ispublic' => 2]),
                'Core-test mode: original checkbox semantics must apply (H14).'
            );
            $this->assertSame(0, lib::normalize_template_ispublic(new \stdClass()));
        } finally {
            core_hack::start_hack_test();
        }
    }

    // -------------------------------------------------------------------------
    // H13 — compare action bar (render guard only; rendering needs a renderer).
    // -------------------------------------------------------------------------

    /**
     * render_compare_action_bar() must return '' during core tests without
     * touching the renderer (guard check).
     */
    public function test_render_compare_action_bar_core_test_mode(): void {
        core_hack::stop_hack_test();
        try {
            $result = lib::render_compare_action_bar(
                1,
                new \moodle_url('/mod/individualfeedback/analysis.php'),
                null
            );
            $this->assertSame(
                '',
                $result,
                'Core-test mode: compare action bar must not render (H13).'
            );
        } finally {
            core_hack::start_hack_test();
        }
    }
}

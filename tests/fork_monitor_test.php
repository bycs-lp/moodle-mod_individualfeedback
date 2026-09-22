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
 * Fork-monitoring tests for mod_individualfeedback.
 *
 * PURPOSE
 * -------
 * mod_individualfeedback is a fork of Moodle core's mod_feedback.
 * Whenever Moodle upstream changes a file that this plugin copies or
 * closely mirrors, the plugin developer must review those changes and
 * decide whether to incorporate them.
 *
 * These tests detect upstream changes by comparing key mod_feedback files
 * against reference snapshots stored in tests/fixtures/fork_monitor/.
 *
 * HOW TO USE
 * ----------
 * 1. When the plugin is initially released, run:
 *      vendor/bin/phpunit mod_individualfeedback/tests/fork_monitor_test.php
 *    All tests will PASS because the snapshots match the installed core.
 *
 * 2. After a Moodle upgrade, run the same command.
 *    A FAILING test means an upstream file changed — review the diff and
 *    update the plugin accordingly, then regenerate the snapshot.
 *
 * REGENERATING SNAPSHOTS
 * ----------------------
 * After intentionally incorporating an upstream change, copy the updated
 * core file to the snapshot directory:
 *
 *   cp {$CFG->dirroot}/mod/feedback/item/feedback_item_class.php \
 *      mod/individualfeedback/tests/fixtures/fork_monitor/feedback_item_class.php
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback;

defined('MOODLE_INTERNAL') || die();

/**
 * Fork-monitoring test suite.
 *
 * Each test compares a specific core mod_feedback file with the snapshot
 * stored in tests/fixtures/fork_monitor/.
 *
 * @covers \mod_individualfeedback
 */
final class fork_monitor_test extends \advanced_testcase {
    /** Absolute path to the snapshot directory. */
    private string $snapshotdir;

    /** Absolute path to the mod_feedback directory in Moodle core. */
    private string $feedbackdir;

    protected function setUp(): void {
        parent::setUp();
        global $CFG;
        $this->snapshotdir = __DIR__ . '/fixtures/fork_monitor';
        $this->feedbackdir = $CFG->dirroot . '/mod/feedback';
    }

    // -----------------------------------------------------------------------
    // Helper
    // -----------------------------------------------------------------------

    /**
     * Skip the test if either the snapshot or the core file is absent.
     * Returns the two paths for the assertFileEquals() call.
     *
     * @param string $snapshotfilename Filename inside fixtures/fork_monitor/.
     * @param string $corerelpathFromFeedback Relative path inside mod/feedback/.
     * @return array{string, string} [$snapshotpath, $corepath]
     */
    private function resolve_paths(string $snapshotfilename, string $corerelpathFromFeedback): array {
        $snapshotpath = $this->snapshotdir . '/' . $snapshotfilename;
        $corepath     = $this->feedbackdir . '/' . $corerelpathFromFeedback;

        if (!is_dir($this->feedbackdir)) {
            $this->markTestSkipped(
                'mod_feedback is not installed; fork-monitor tests require it.'
            );
        }

        if (!file_exists($snapshotpath)) {
            $this->markTestSkipped(
                "Fork-monitor snapshot '{$snapshotfilename}' not yet created. " .
                "Copy the current core file to tests/fixtures/fork_monitor/ to initialise monitoring."
            );
        }

        if (!file_exists($corepath)) {
            $this->markTestSkipped("Core file not found: {$corepath}");
        }

        return [$snapshotpath, $corepath];
    }

    // -----------------------------------------------------------------------
    // Monitored files
    // -----------------------------------------------------------------------

    /**
     * Monitor: mod/feedback/item/feedback_item_class.php
     *
     * This is the base item class that individualfeedback_item_class.php was
     * derived from. Any upstream change here may require a parallel update in
     * the plugin's own base class.
     *
     * @covers \mod_individualfeedback
     */
    public function test_feedback_item_base_class_unchanged(): void {
        [$snapshot, $core] = $this->resolve_paths(
            'feedback_item_class.php',
            'item/feedback_item_class.php'
        );

        $this->assertFileEquals(
            $snapshot,
            $core,
            "UPSTREAM CHANGE DETECTED: mod/feedback/item/feedback_item_class.php " .
            "has changed since the last snapshot. " .
            "Review the diff against mod_individualfeedback/item/individualfeedback_item_class.php " .
            "and update the plugin or its snapshot accordingly."
        );
    }

    /**
     * Monitor: mod/feedback/lib.php
     *
     * The plugin's lib.php was derived from this file. Upstream changes may
     * introduce new API functions or modify existing ones.
     *
     * @covers \mod_individualfeedback
     */
    public function test_feedback_lib_unchanged(): void {
        [$snapshot, $core] = $this->resolve_paths(
            'feedback_lib.php',
            'lib.php'
        );

        $this->assertFileEquals(
            $snapshot,
            $core,
            "UPSTREAM CHANGE DETECTED: mod/feedback/lib.php has changed. " .
            "Review and update mod_individualfeedback/lib.php or its snapshot."
        );
    }

    /**
     * Monitor: mod/feedback/classes/structure.php
     *
     * mod_individualfeedback\structure extends/mirrors mod_feedback's structure
     * class. Upstream changes here affect the plugin's data access layer.
     *
     * @covers \mod_individualfeedback
     */
    public function test_feedback_structure_class_unchanged(): void {
        [$snapshot, $core] = $this->resolve_paths(
            'feedback_structure.php',
            'classes/structure.php'
        );

        $this->assertFileEquals(
            $snapshot,
            $core,
            "UPSTREAM CHANGE DETECTED: mod/feedback/classes/structure.php has changed. " .
            "Review and update mod_individualfeedback/classes/structure.php or its snapshot."
        );
    }

    /**
     * Monitor: mod/feedback/classes/external.php
     *
     * External API changes affect Web Service compatibility, which is critical
     * for Moodle mobile and REST clients.
     *
     * @covers \mod_individualfeedback
     */
    public function test_feedback_external_class_unchanged(): void {
        [$snapshot, $core] = $this->resolve_paths(
            'feedback_external.php',
            'classes/external.php'
        );

        $this->assertFileEquals(
            $snapshot,
            $core,
            "UPSTREAM CHANGE DETECTED: mod/feedback/classes/external.php has changed. " .
            "Review and update mod_individualfeedback/classes/external.php or its snapshot."
        );
    }

    /**
     * Monitor: mod/feedback/db/install.xml
     *
     * Schema changes in the upstream plugin may need to be reflected in this
     * plugin's own install.xml and upgrade.php.
     *
     * @covers \mod_individualfeedback
     */
    public function test_feedback_db_install_xml_unchanged(): void {
        [$snapshot, $core] = $this->resolve_paths(
            'feedback_install.xml',
            'db/install.xml'
        );

        $this->assertFileEquals(
            $snapshot,
            $core,
            "UPSTREAM CHANGE DETECTED: mod/feedback/db/install.xml has changed. " .
            "Review schema changes and update mod_individualfeedback/db/install.xml or upgrade.php."
        );
    }

    // -----------------------------------------------------------------------
    // Snapshot initialisation helper test
    // -----------------------------------------------------------------------

    /**
     * Self-check: the snapshot directory exists (or will be created by CI).
     *
     * This test always passes — it merely documents the expected snapshot
     * directory location for developers.
     *
     * @covers \mod_individualfeedback
     */
    public function test_snapshot_directory_documented(): void {
        // The snapshot directory may not exist yet if no snapshots have been
        // created. That is fine — the monitoring tests skip gracefully.
        $this->assertStringEndsWith(
            'tests/fixtures/fork_monitor',
            $this->snapshotdir
        );
    }
}

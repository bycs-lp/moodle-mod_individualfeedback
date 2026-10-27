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
 * Backup and restore tests for the fork specifics (client review 26.10.2026, findings #2 and #3).
 *
 * @package    mod_individualfeedback
 * @copyright  2026 ISB Bayern / mebis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_individualfeedback\backup;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/moodle2/restore_plan_builder.class.php');
require_once($CFG->dirroot . '/backup/moodle2/backup_stepslib.php');
require_once($CFG->dirroot . '/backup/moodle2/restore_stepslib.php');
require_once($CFG->dirroot . '/mod/individualfeedback/backup/moodle2/backup_individualfeedback_activity_task.class.php');
require_once($CFG->dirroot . '/mod/individualfeedback/backup/moodle2/restore_individualfeedback_activity_task.class.php');

use mod_individualfeedback\hack\core_hack;
use mod_individualfeedback\hack\core_hack_testcase;

/**
 * Link tokens must not collide with core mod_feedback; the selfassessment flag must survive a round trip.
 *
 * @covers \backup_individualfeedback_activity_task
 * @covers \restore_individualfeedback_activity_task
 * @covers \backup_individualfeedback_activity_structure_step
 */
final class fork_backup_test extends core_hack_testcase {
    /**
     * Finding #2: encoder tokens and decoder rule names are module specific and disjoint from mod_feedback.
     */
    public function test_link_tokens_do_not_collide_with_mod_feedback(): void {
        global $CFG;
        $this->resetAfterTest();

        $content = $CFG->wwwroot . '/mod/individualfeedback/view.php?id=5 and ' . $CFG->wwwroot . '/mod/feedback/view.php?id=7'
            . ' and ' . $CFG->wwwroot . '/mod/individualfeedback/analysis.php?id=5';
        $encoded = \backup_individualfeedback_activity_task::encode_content_links($content);
        $this->assertStringContainsString('$@INDIVIDUALFEEDBACKVIEWBYID*5@$', $encoded);
        $this->assertStringContainsString('$@INDIVIDUALFEEDBACKANALYSISBYID*5@$', $encoded);
        $this->assertStringNotContainsString('$@FEEDBACKVIEWBYID', $encoded, 'The fork must not emit core mod_feedback tokens.');
        $this->assertStringContainsString('/mod/feedback/view.php?id=7', $encoded, 'Core mod_feedback links are left to core.');

        $linkname = function ($rule): string {
            $property = new \ReflectionProperty($rule, 'linkname');
            $property->setAccessible(true);
            return (string) $property->getValue($rule);
        };
        $ournames = array_map($linkname, \restore_individualfeedback_activity_task::define_decode_rules());
        $this->assertNotEmpty($ournames);
        foreach ($ournames as $name) {
            $this->assertStringStartsWith('INDIVIDUALFEEDBACK', $name);
        }

        $corefile = $CFG->dirroot . '/mod/feedback/backup/moodle2/restore_feedback_activity_task.class.php';
        if (file_exists($corefile)) {
            require_once($corefile);
            $corenames = array_map($linkname, \restore_feedback_activity_task::define_decode_rules());
            $this->assertEmpty(array_intersect($ournames, $corenames), 'Decoder rule names must be disjoint from mod_feedback.');
            $coreencoded = \backup_feedback_activity_task::encode_content_links($content);
            $this->assertStringContainsString(
                '/mod/individualfeedback/view.php?id=5',
                $coreencoded,
                'Core mod_feedback must leave individualfeedback links untouched.'
            );
        }
    }

    /**
     * Finding #3: the selfassessment flag of completed responses is part of the backup structure.
     */
    public function test_selfassessment_flag_survives_backup_and_restore(): void {
        global $DB, $USER;
        $this->resetAfterTest();
        $this->setAdminUser();

        $generator = $this->getDataGenerator();
        $course = $generator->create_course();
        $individualfeedback = $generator->create_module('individualfeedback', ['course' => $course->id]);
        $cm = get_coursemodule_from_instance('individualfeedback', $individualfeedback->id);
        $context = \core\context\module::instance($cm->id);
        $generator->get_plugin_generator('mod_individualfeedback')->create_item_textfield($individualfeedback, ['name' => 'Q1']);

        $student = $generator->create_user();
        $teacher = $generator->create_user();
        $generator->enrol_user($student->id, $course->id, 'student');
        $generator->enrol_user($teacher->id, $course->id, 'editingteacher');
        $roleid = $DB->get_field('role', 'id', ['shortname' => 'editingteacher'], MUST_EXIST);
        assign_capability('mod/individualfeedback:complete', CAP_ALLOW, $roleid, $context->id, true);

        $plugingenerator = $generator->get_plugin_generator('mod_individualfeedback');
        $plugingenerator->create_response(['cmid' => $cm->id, 'userid' => $student->id, 'Q1' => 'peer']);
        $plugingenerator->create_response(['cmid' => $cm->id, 'userid' => $teacher->id, 'Q1' => 'self']);
        $this->assertSame(1, $DB->count_records(
            'individualfeedback_completed',
            ['individualfeedback' => $individualfeedback->id, 'selfassessment' => 1]
        ));

        $newcourseid = $this->backup_and_restore_with_users($course);

        $newinstance = $DB->get_record('individualfeedback', ['course' => $newcourseid], '*', MUST_EXIST);
        $completed = $DB->get_records('individualfeedback_completed', ['individualfeedback' => $newinstance->id], '', 'id, userid, selfassessment');
        $this->assertCount(2, $completed);
        $flags = [];
        foreach ($completed as $record) {
            $flags[(int) $record->userid] = (int) $record->selfassessment;
        }
        $this->assertSame(1, $flags[$teacher->id], 'The teacher self-assessment must be restored as self-assessment.');
        $this->assertSame(0, $flags[$student->id]);
    }

    /**
     * Back up a course including user data and restore it into a new course.
     *
     * @param \stdClass $course The course to copy.
     * @return int The new course id.
     */
    private function backup_and_restore_with_users(\stdClass $course): int {
        global $USER, $CFG;

        // Same recipe as core's restore_date_testcase::backup_and_restore().
        $CFG->backup_file_logger_level = \backup::LOG_NONE;
        set_config('backup_general_users', 1, 'backup');
        $bc = new \backup_controller(
            \backup::TYPE_1COURSE,
            $course->id,
            \backup::FORMAT_MOODLE,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id
        );
        $bc->execute_plan();
        $results = $bc->get_results();
        $file = $results['backup_destination'];
        $packer = get_file_packer('application/vnd.moodle.backup');
        $filepath = $CFG->dataroot . '/temp/backup/individualfeedback-fork-test';
        $file->extract_to_pathname($packer, $filepath);
        $bc->destroy();

        $newcourseid = \restore_dbops::create_new_course($course->fullname, $course->shortname . '_2', $course->category);
        $rc = new \restore_controller(
            'individualfeedback-fork-test',
            $newcourseid,
            \backup::INTERACTIVE_NO,
            \backup::MODE_GENERAL,
            $USER->id,
            \backup::TARGET_NEW_COURSE
        );
        $this->assertTrue($rc->execute_precheck());
        $rc->execute_plan();
        $rc->destroy();

        return $newcourseid;
    }
}

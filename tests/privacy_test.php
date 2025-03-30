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
 * Tests related to privacy.
 *
 * Answers were anonymously stored in database. So we us a data privacy null provider to indicate, that
 * no privacy data is stored, but we need to ensure that data is anonymized correctly here.
 *
 * @package    mod_individualfeedback
 * @author     2020 Peter Mayer <peter.mayer@isb.bayern.de>, Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @group      mod_individualfeedback
 * @group      mebis
 */
defined('MOODLE_INTERNAL') || die();
global $CFG;

use core_privacy\tests\provider_testcase;
use core_privacy\local\request\approved_contextlist;
use core_privacy\local\request\transform;
use core_privacy\local\request\writer;
use mod_individualfeedback\privacy\provider;

require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

/**
 * Privacy provider tests.
 *
 * @package    mod_individualfeedback
 * @author     2020 Peter Mayer <peter.mayer@isb.bayern.de>, Andreas Wagner
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 *
 * @group      mod_individualfeedback
 * @group      mebis
 */
class mod_individualfeedback_privacy_testcase extends provider_testcase {

    /**
     * Check, whether data is anonymously stored in database.
     */
    public function test_data_stored_anonymized() {
        global $DB;

        $this->resetAfterTest();

        $dg = $this->getDataGenerator();
        $fg = $dg->get_plugin_generator('mod_individualfeedback');

        $c1 = $dg->create_course();
        $individualfeedback = $dg->create_module('individualfeedback', ['course' => $c1, 'anonymous' => INDIVIDUALFEEDBACK_ANONYMOUS_NO]);

        $u1 = $dg->create_user();
        $u2 = $dg->create_user();

        // Create submission for user1.
        $this->setUser($u1);
        $i1 = $fg->create_item_numeric($individualfeedback);
        $i2 = $fg->create_item_multichoice($individualfeedback);
        $answers = ['numeric_' . $i1->id => '1', 'multichoice_' . $i2->id => [1]];

        $this->create_submission_with_answers($individualfeedback, $u1, $answers);

        // Should have one anonymized completed record.
        $completed = $DB->get_records('individualfeedback_completed');
        $this->assertCount(1, $completed);
        $completed = array_shift($completed);
        $this->assertEquals(individualfeedback_hash_userid($u1->id), $completed->userid);

        // Temporary completed should be deleted.
        $completed = $DB->get_records('indfeedback_completedtmp');
        $this->assertCount(0, $completed);

        // Unsaved submission for u2.
        $this->setUser($u2);
        $i1 = $fg->create_item_numeric($individualfeedback);
        $i2 = $fg->create_item_multichoice($individualfeedback);
        $answers = ['numeric_' . $i1->id => '1', 'multichoice_' . $i2->id => [1]];
        $this->create_tmp_submission_with_answers($individualfeedback, $u2, $answers);

         // Should have no additinal anonymized completed record.
        $completed = $DB->get_records('individualfeedback_completed');
        $this->assertCount(1, $completed);

         // Should have one anonymized completed tmp record.
        $completed = $DB->get_records('indfeedback_completedtmp');
        $this->assertCount(1, $completed);
        $completed = array_shift($completed);
        $this->assertEquals(individualfeedback_hash_userid($u2->id), $completed->userid);
    }

    /**
     * Create an submission with answers.
     *
     * @param object $individualfeedback The individualfeedback.
     * @param object $user the user.
     * @param array $answers Answers.
     * @param int $submissioncount The number of submissions expected after this entry.
     * @return void
     */
    protected function create_submission_with_answers($individualfeedback, $user, $answers, $submissioncount = 1) {
        global $DB;

        $modinfo = get_fast_modinfo($individualfeedback->course);
        $cm = $modinfo->get_cm($individualfeedback->cmid);

        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $individualfeedback->course, false, null, null, $user->id);
        $individualfeedbackcompletion->save_response_tmp((object) $answers);
        $individualfeedbackcompletion->save_response();
        // Check if there is a record with hashed userid.
        $this->assertEquals(1, $DB->count_records('individualfeedback_completed', ['individualfeedback' => $individualfeedback->id,
                    'userid' => individualfeedback_hash_userid($user->id)]));
        // Check if there is a record with real userid.
        $this->assertEquals(0, $DB->count_records('individualfeedback_completed', ['individualfeedback' => $individualfeedback->id,
                    'userid' => $user->id]));
        // Count answers.
        $this->assertEquals(count($answers), $DB->count_records('individualfeedback_value', [
                    'completed' => $individualfeedbackcompletion->get_completed()->id]));
    }

    /**
     * Create a temporary submission with answers.
     *
     * @param object $individualfeedback The individualfeedback.
     * @param object $user the user.
     * @param array $answers Answers.
     * @return void
     */
    protected function create_tmp_submission_with_answers($individualfeedback, $user, $answers) {
        global $DB;

        $modinfo = get_fast_modinfo($individualfeedback->course);
        $cm = $modinfo->get_cm($individualfeedback->cmid);

        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $individualfeedback->course, false, null, null, $user->id);
        $individualfeedbackcompletion->save_response_tmp((object) $answers);
        // Check if there is a record with hashed userid.
        $this->assertEquals(1, $DB->count_records('indfeedback_completedtmp', ['individualfeedback' => $individualfeedback->id,
            'userid' => individualfeedback_hash_userid($user->id)]));
        // Check if there is no record with userid.
        $this->assertEquals(0, $DB->count_records('indfeedback_completedtmp', ['individualfeedback' => $individualfeedback->id,
            'userid' => $user->id]));
        // Check if there is a record with userid = 0.
        $this->assertEquals(2, $DB->count_records('individualfeedback_valuetmp', [
                    'completed' => $individualfeedbackcompletion->get_current_completed_tmp()->id]));
    }
}

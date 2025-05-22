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
 * print the form to add or edit a feedback-instance
 *
 * @author Marcelo Augusto Rauh Schmitt
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_feedback
 */

//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once($CFG->dirroot.'/course/moodleform_mod.php');
require_once($CFG->dirroot.'/mod/feedback/mod_form.php');
require_once($CFG->dirroot.'/mod/feedback/lib.php');


class mod_individualfeedback_mod_form extends mod_feedback_mod_form {

    public function definition() {
        global $CFG, $DB;

        $editoroptions = feedback_get_editor_options();

        $mform    =& $this->_form;

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'feedback'), array('size'=>'64'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('description', 'feedback'));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'timinghdr', get_string('availability'));

        $mform->addElement('date_time_selector', 'timeopen', get_string('feedbackopen', 'feedback'),
            array('optional' => true));

        $mform->addElement('date_time_selector', 'timeclose', get_string('feedbackclose', 'feedback'),
            array('optional' => true));

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'feedbackhdr', get_string('questionandsubmission', 'feedback'));

        $options=array();
        $options[1]  = get_string('anonymous', 'feedback');
        $options[2]  = get_string('non_anonymous', 'feedback');
        $mform->addElement('select',
            'anonymous',
            get_string('anonymous_edit', 'feedback'),
            $options);

        // check if there is existing responses to this feedback
        if (is_numeric($this->_instance) AND
            $this->_instance AND
            $feedback = $DB->get_record("individualfeedback", array("id"=>$this->_instance))) {

            $completed_feedback_count = feedback_get_completeds_group_count($feedback);
        } else {
            $completed_feedback_count = false;
        }

        if ($completed_feedback_count) {
            $multiple_submit_value = $feedback->multiple_submit ? get_string('yes') : get_string('no');
            $mform->addElement('text',
                'multiple_submit_static',
                get_string('multiplesubmit', 'feedback'),
                array('size'=>'4',
                    'disabled'=>'disabled',
                    'value'=>$multiple_submit_value));
            $mform->setType('multiple_submit_static', PARAM_RAW);

            $mform->addElement('hidden', 'multiple_submit', '');
            $mform->setType('multiple_submit', PARAM_INT);
            $mform->addHelpButton('multiple_submit_static', 'multiplesubmit', 'feedback');
        } else {
            $mform->addElement('selectyesno',
                'multiple_submit',
                get_string('multiplesubmit', 'feedback'));

            $mform->addHelpButton('multiple_submit', 'multiplesubmit', 'feedback');
        }

        $mform->addElement('selectyesno', 'email_notification', get_string('email_notification', 'feedback'));
        $mform->addHelpButton('email_notification', 'email_notification', 'feedback');

        $mform->addElement('selectyesno', 'autonumbering', get_string('autonumbering', 'feedback'));
        $mform->addHelpButton('autonumbering', 'autonumbering', 'feedback');

        //-------------------------------------------------------------------------------
        $mform->addElement('header', 'aftersubmithdr', get_string('after_submit', 'feedback'));

        $mform->addElement('selectyesno', 'publish_stats', get_string('show_analysepage_after_submit', 'feedback'));

        $mform->addElement('editor',
            'page_after_submit_editor',
            get_string("page_after_submit", "feedback"),
            null,
            $editoroptions);

        $mform->setType('page_after_submit_editor', PARAM_RAW);

        $mform->addElement('text',
            'site_after_submit',
            get_string('url_for_continue', 'feedback'),
            array('size'=>'64', 'maxlength'=>'255'));

        $mform->setType('site_after_submit', PARAM_TEXT);
        $mform->addHelpButton('site_after_submit', 'url_for_continue', 'feedback');
        //-------------------------------------------------------------------------------
        $this->standard_coursemodule_elements();
        //-------------------------------------------------------------------------------
        // buttons
        $this->add_action_buttons();
    }
}

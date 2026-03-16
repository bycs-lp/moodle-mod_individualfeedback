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

namespace mod_individualfeedback\form;

use core_form\dynamic_form;
use moodle_url;
use context;
use context_module;
use context_system;

/**
 * Prints the create new template form
 *
 * @copyright 2021 Peter Dias
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
class create_template_form extends dynamic_form {
    /**
     * Define the form
     */
    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('text',
            'templatename',
            get_string('name', 'individualfeedback'),
            ['maxlength' => '200', 'size' => '50']);
        $mform->setType('templatename', PARAM_TEXT);

        // +++ MBS-Hack (nersesov) add ispublic radio buttons to template creation form
        // Add private template checkbox.
         $mform->addElement('radio', 'ispublic', '', get_string('course'),0);
         $mform->addElement('radio', 'ispublic', '', get_string('user'),2);
         if (has_capability('mod/individualfeedback:createpublictemplate', context_system::instance())) {
             $mform->addElement('radio', 'ispublic', '', get_string('public', 'individualfeedback'),1);
         }
         // --- MBS-Hack
    }

    /**
     * Returns context where this form is used
     *
     * @return context
     */
    protected function get_context_for_dynamic_submission(): context {
        $id = $this->optional_param('id', null, PARAM_INT);
        list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
        return context_module::instance($cm->id);
    }

    /**
     * Checks if current user has access to this form, otherwise throws exception
     *
     * @throws \moodle_exception User does not have capability to access the form
     */
    protected function check_access_for_dynamic_submission(): void {
        $context = $this->get_context_for_dynamic_submission();
        if (!has_capability('mod/individualfeedback:edititems', $context) ||
            !(has_capability('mod/individualfeedback:createprivatetemplate', $context) ||
            has_capability('mod/individualfeedback:createpublictemplate', $context))) {
            throw new \moodle_exception('nocapabilitytousethisservice');
        }
    }

    /**
     * Process the form submission, used if form was submitted via AJAX
     *
     * @return array Returns whether a new template was created.
     */
    public function process_dynamic_submission(): array {
        global $PAGE, $USER;
        $formdata = $this->get_data();
        $ispublic = !empty($formdata->ispublic) ? $formdata->ispublic : 0;
        $result = individualfeedback_save_as_template($PAGE->activityrecord, $formdata->templatename, $ispublic);
        return [
            'result' => $result,
        ];
    }

    /**
     * Load in existing data as form defaults
     */
    public function set_data_for_dynamic_submission(): void {
        $this->set_data((object)[
            'id' => $this->optional_param('id', null, PARAM_INT),
        ]);
    }

    /**
     * Returns url to set in $PAGE->set_url() when form is being rendered or submitted via AJAX
     *
     * @return moodle_url
     */
    protected function get_page_url_for_dynamic_submission(): moodle_url {
        $params = [
            'id' => $this->optional_param('id', null, PARAM_INT),
        ];
        return new moodle_url('/mod/individualfeedback/edit.php', $params);
    }
}

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
 * Contains class mod_individualfeedback_course_map_form
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();

/**
 * Form for mapping courses to the individualfeedback
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_individualfeedback_course_select_form extends moodleform {
    /** @var moodle_url */
    protected $action;
    /** @var mod_individualfeedback_structure $individualfeedbackstructure */
    protected $individualfeedbackstructure;

    /**
     * Constructor
     *
     * @param string|moodle_url $action the action attribute for the form
     * @param mod_individualfeedback_structure $individualfeedbackstructure
     * @param bool $editable
     */
    public function __construct($action, mod_individualfeedback_structure $individualfeedbackstructure, $editable = true) {
        $this->action = new moodle_url($action, ['courseid' => null]);
        $this->individualfeedbackstructure = $individualfeedbackstructure;
        parent::__construct($action, null, 'post', '', ['id' => 'individualfeedback_course_filter'], $editable);
    }

    /**
     * Definition of the form
     */
    public function definition() {
        $mform = $this->_form;
        $individualfeedbackstructure = $this->individualfeedbackstructure;

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (!$this->_form->_freezeAll && ($courses = $individualfeedbackstructure->get_completed_courses()) && count($courses) > 1) {
            $elements = [];
            $elements[] = $mform->createElement('autocomplete', 'courseid', get_string('filter_by_course', 'individualfeedback'),
                ['' => get_string('fulllistofcourses')] + $courses);
            $elements[] = $mform->createElement('submit', 'submitbutton', get_string('filter'));
            if ($individualfeedbackstructure->get_courseid()) {
                $elements[] = $mform->createElement('static', 'showall', '',
                    html_writer::link($this->action, get_string('show_all', 'individualfeedback')));
            }
            if (defined('BEHAT_SITE_RUNNING')) {
                // TODO MDL-53734 remove this - behat does not recognise autocomplete element inside a group.
                foreach ($elements as $element) {
                    $mform->addElement($element);
                }
            } else {
                $mform->addGroup($elements, 'coursefilter', get_string('filter_by_course', 'individualfeedback'), array(' '), false);
            }
        }

        $this->set_data(['courseid' => $individualfeedbackstructure->get_courseid(), 'id' => $individualfeedbackstructure->get_cm()->id]);
    }
}

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

require_once($CFG->dirroot.'/mod/individualfeedback/item/individualfeedback_item_form_class.php');

class individualfeedback_questiongroup_form extends individualfeedback_item_form {
    protected $type = "questiongroup";

    public function definition() {
        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string($this->type, 'individualfeedback'));
        //$mform->addElement('advcheckbox', 'required', get_string('required', 'individualfeedback'), '' , null , array(0, 1));
        $mform->addElement('hidden', 'required', 0);
        $mform->setType('required', PARAM_INT);

        $mform->addElement('text',
                            'name',
                            get_string('questiongroup_name', 'individualfeedback'),
                            array('size'=>INDIVIDUALFEEDBACK_ITEM_NAME_TEXTBOX_SIZE, 'maxlength'=>255));

        $mform->addElement('hidden', 'presentation', '');
        $mform->setType('presentation', PARAM_TEXT);

        $mform->addElement('hidden', 'label', '');
        $mform->setType('label', PARAM_TEXT);

        parent::definition();
        $this->set_data($item);
    }
}


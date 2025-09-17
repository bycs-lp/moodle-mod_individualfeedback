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

class individualfeedback_multichoicerated_form extends individualfeedback_item_form {
    protected $type = "multichoicerated";

    /** @var object Form element */
    protected $values;

    public function definition() {
        $item = $this->_customdata['item'];
        $common = $this->_customdata['common'];
        $positionlist = $this->_customdata['positionlist'];
        $position = $this->_customdata['position'];

        $mform =& $this->_form;

        $mform->addElement('header', 'general', get_string($this->type, 'feedback'));

        $mform->addElement('advcheckbox', 'required', get_string('required', 'mod_individualfeedback'), '' , null , array(0, 1));

        $mform->addElement('text',
                            'name',
                            get_string('item_name', 'mod_individualfeedback'),
                            array('size'=>INDIVIDUALFEEDBACK_ITEM_NAME_TEXTBOX_SIZE,
                                  'maxlength'=>255));

        $mform->addElement('text',
                            'label',
                            get_string('item_label', 'mod_individualfeedback'),
                            array('size'=>INDIVIDUALFEEDBACK_ITEM_LABEL_TEXTBOX_SIZE,
                                  'maxlength'=>255));

        $mform->addElement('select',
                            'subtype',
                            get_string('multichoicetype', 'mod_individualfeedback').'&nbsp;',
                            array('r'=>get_string('radio', 'mod_individualfeedback'),
                                  'd'=>get_string('dropdown', 'mod_individualfeedback')));

        $mform->addElement('select',
                            'horizontal',
                            get_string('adjustment', 'mod_individualfeedback').'&nbsp;',
                            array(0 => get_string('vertical', 'mod_individualfeedback'),
                                  1 => get_string('horizontal', 'mod_individualfeedback')));
        $mform->hideIf('horizontal', 'subtype', 'eq', 'd');

        $mform->addElement('selectyesno',
                           'hidenoselect',
                           get_string('hide_no_select_option', 'mod_individualfeedback'));
        $mform->hideIf('hidenoselect', 'subtype', 'eq', 'd');

        $mform->addElement('selectyesno',
                           'ignoreempty',
                           get_string('do_not_analyse_empty_submits', 'mod_individualfeedback'));
        $mform->disabledIf('ignoreempty', 'required', 'eq', '1');

        $this->values = $mform->addElement('textarea',
                            'values',
                            get_string('multichoice_values', 'mod_individualfeedback'),
                            'wrap="virtual" rows="10" cols="65"');

        $mform->addElement('static',
                            'hint',
                            '',
                            get_string('use_one_line_for_each_value', 'mod_individualfeedback'));

        parent::definition();
        $this->set_data($item);

    }

    public function set_data($item) {
        $info = $this->_customdata['info'];

        $item->horizontal = $info->horizontal;

        $item->subtype = $info->subtype;

        $item->values = $info->values;

        return parent::set_data($item);
    }

    public function get_data() {
        if (!$item = parent::get_data()) {
            return false;
        }

        $itemobj = new individualfeedback_item_multichoicerated();

        $presentation = $itemobj->prepare_presentation_values_save(trim($item->values),
                                                INDIVIDUALFEEDBACK_MULTICHOICERATED_VALUE_SEP2,
                                                INDIVIDUALFEEDBACK_MULTICHOICERATED_VALUE_SEP);
        if (!isset($item->subtype)) {
            $subtype = 'r';
        } else {
            $subtype = substr($item->subtype, 0, 1);
        }
        if (isset($item->horizontal) AND $item->horizontal == 1 AND $subtype != 'd') {
            $presentation .= INDIVIDUALFEEDBACK_MULTICHOICERATED_ADJUST_SEP.'1';
        }
        $item->presentation = $subtype.INDIVIDUALFEEDBACK_MULTICHOICERATED_TYPE_SEP.$presentation;
        if (!isset($item->hidenoselect)) {
            $item->hidenoselect = 1;
        }
        if (!isset($item->ignoreempty)) {
            $item->ignoreempty = 0;
        }
        return $item;
    }
}

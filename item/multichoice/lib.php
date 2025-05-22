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

defined('MOODLE_INTERNAL') OR die('not allowed');
require_once($CFG->dirroot.'/mod/individualfeedback/item/individualfeedback_item_class.php');

define('INDIVIDUALFEEDBACK_MULTICHOICE_TYPE_SEP', '>>>>>');
define('INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP', '|');
define('INDIVIDUALFEEDBACK_MULTICHOICE_ADJUST_SEP', '<<<<<');
define('INDIVIDUALFEEDBACK_MULTICHOICE_IGNOREEMPTY', 'i');
define('INDIVIDUALFEEDBACK_MULTICHOICE_NEGATIVEFORMULATED', 'n');
define('INDIVIDUALFEEDBACK_MULTICHOICE_HIDENOSELECT', 'h');

class individualfeedback_item_multichoice extends individualfeedback_item_base {
    protected $type = "multichoice";

    public function build_editform($item, $feedback, $cm) {
        global $DB, $CFG;
        require_once('multichoice_form.php');

        //get the lastposition number of the individualfeedback_items
        $position = $item->position;
        $lastposition = $DB->count_records('individualfeedback_item', array('feedback'=>$feedback->id));
        if ($position == -1) {
            $i_formselect_last = $lastposition + 1;
            $i_formselect_value = $lastposition + 1;
            $item->position = $lastposition + 1;
        } else {
            $i_formselect_last = $lastposition;
            $i_formselect_value = $item->position;
        }
        //the elements for position dropdownlist
        $positionlist = array_slice(range(0, $i_formselect_last), 1, $i_formselect_last, true);

        $item->presentation = empty($item->presentation) ? '' : $item->presentation;
        $info = $this->get_info($item);

        $item->negativeformulated = $this->negativeformulated($item);
        $item->ignoreempty = $this->ignoreempty($item);
        $item->hidenoselect = $this->hidenoselect($item);

        //all items for dependitem
        $feedbackitems = individualfeedback_get_depend_candidates_for_item($feedback, $item);
        $commonparams = array('cmid'=>$cm->id,
                             'id'=>isset($item->id) ? $item->id : null,
                             'typ'=>$item->typ,
                             'items'=>$feedbackitems,
                             'feedback'=>$feedback->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position,
                            'info' => $info);

        $this->item_form = new individualfeedback_multichoice_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$this->get_data()) {
            return false;
        }
        $item = $this->item;

        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = ''; //to clone this item
            $item->position++;
        }

        $this->set_ignoreempty($item, $item->ignoreempty);
        $this->set_negativeformulated($item, $item->negativeformulated);
        $this->set_hidenoselect($item, $item->hidenoselect);

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('individualfeedback_item', $item);
        } else {
            $DB->update_record('individualfeedback_item', $item);
        }

        return $DB->get_record('individualfeedback_item', array('id'=>$item->id));
    }

    /**
     * Helper function for collected data, for detailed analysis
     *
     * @param stdClass $item the db-object from individualfeedback_item
     * @param int $groupid
     * @param int $courseid
     * @return array
     */
    public function get_answer_data($item, $groupid = false, $courseid = false) {
        return $this->get_item_answer_data($item, INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $groupid, $courseid);
    }

    //gets an array with three values(typ, name, XXX)
    //XXX is an object with answertext, answercount and quotient

    /**
     * Helper function for collected data, both for analysis page and export to excel
     *
     * @param stdClass $item the db-object from individualfeedback_item
     * @param int $groupid
     * @param int $courseid
     * @return array
     */
    protected function get_analysed($item, $groupid = false, $courseid = false) {
        $info = $this->get_info($item);

        $analysed_item = array();
        $analysed_item[] = $item->typ;
        $analysed_item[] = format_string($item->name);

        //get the possible answers
        $answers = null;
        $answers = explode (INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        if (!is_array($answers)) {
            return null;
        }

        //get the values
        $values = individualfeedback_get_group_values($item, $groupid, $courseid, $this->negativeformulated($item), $this->ignoreempty($item));
        if (!$values) {
            return null;
        }

        //get answertext, answercount and quotient for each answer
        $analysed_answer = array();
        if ($info->subtype == 'c') {
            $sizeofanswers = count($answers);
            for ($i = 1; $i <= $sizeofanswers; $i++) {
                $ans = new stdClass();
                $ans->answertext = $answers[$i-1];
                $ans->answercount = 0;
                foreach ($values as $value) {
                    //ist die Antwort gleich dem index der Antworten + 1?
                    $vallist = explode(INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $value->value);
                    foreach ($vallist as $val) {
                        if ($val == $i) {
                            $ans->answercount++;
                        }
                    }
                }
                $ans->quotient = $ans->answercount / count($values);
                $analysed_answer[] = $ans;
            }
        } else {
            $sizeofanswers = count($answers);
            for ($i = 1; $i <= $sizeofanswers; $i++) {
                $ans = new stdClass();
                $ans->answertext = $answers[$i-1];
                $ans->answercount = 0;
                foreach ($values as $value) {
                    //ist die Antwort gleich dem index der Antworten + 1?
                    if ($value->value == $i) {
                        $ans->answercount++;
                    }
                }
                $ans->quotient = $ans->answercount / count($values);
                $analysed_answer[] = $ans;
            }
        }
        $analysed_item[] = $analysed_answer;
        return $analysed_item;
    }

    public function get_printval($item, $value) {
        $info = $this->get_info($item);

        $printval = '';

        if (!isset($value->value)) {
            return $printval;
        }

        $presentation = explode (INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);

        if ($info->subtype == 'c') {
            $vallist = array_values(explode (INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $value->value));
            $sizeofvallist = count($vallist);
            $sizeofpresentation = count($presentation);
            for ($i = 0; $i < $sizeofvallist; $i++) {
                for ($k = 0; $k < $sizeofpresentation; $k++) {
                    if ($vallist[$i] == ($k + 1)) {//Die Werte beginnen bei 1, das Array aber mit 0
                        $printval .= trim(format_string($presentation[$k])) . chr(10);
                        break;
                    }
                }
            }
        } else {
            $index = 1;
            foreach ($presentation as $pres) {
                if ($value->value == $index) {
                    $printval = format_string($pres);
                    break;
                }
                $index++;
            }
        }
        return $printval;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        if ($analysed_item) {
            $itemname = $analysed_item[1];
            echo "<table class=\"analysis itemtype_{$item->typ}\">";
            echo '<tr><th colspan="2" class="text-start">';
            echo $itemnr . ' ';
            if (strval($item->label) !== '') {
                echo '('. format_string($item->label).') ';
            }
            echo format_string($itemname);
            echo '</th></tr>';
            echo "</table>";
            $analysed_vals = $analysed_item[2];
            $count = 0;
            $data = [];
            foreach ($analysed_vals as $val) {
                $quotient = format_float($val->quotient * 100, 2);
                $strquotient = '';
                if ($val->quotient > 0) {
                    $strquotient = ' ('. $quotient . ' %)';
                }
                $answertext = format_text(trim($val->answertext), FORMAT_HTML,
                        array('noclean' => true, 'para' => false));

                $data['labels'][$count] = $answertext;
                $data['series'][$count] = $val->answercount;
                $data['series_labels'][$count] = $val->answercount . $strquotient;
                $count++;
            }
            $chart = new \core\chart_bar();
            $chart->set_horizontal(true);
            $series = new \core\chart_series(format_string(get_string("responses", "feedback")), $data['series']);
            $series->set_labels($data['series_labels']);
            $chart->add_series($series);
            $chart->set_labels($data['labels']);

            echo $OUTPUT->render($chart);
        }
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $analysed_item = $this->get_analysed($item, $groupid, $courseid);
        if (!$analysed_item) {
            return $row_offset;
        }

        $data = $analysed_item[2];

        //frage schreiben
        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, $analysed_item[1], $xls_formats->head2);
        if (is_array($data)) {
            $sizeofdata = count($data);
            for ($i = 0; $i < $sizeofdata; $i++) {
                $analysed_data = $data[$i];

                $worksheet->write_string($row_offset,
                                         $i + 2,
                                         trim($analysed_data->answertext),
                                         $xls_formats->head2);

                $worksheet->write_number($row_offset + 1,
                                         $i + 2,
                                         $analysed_data->answercount,
                                         $xls_formats->default);

                $worksheet->write_number($row_offset + 2,
                                         $i + 2,
                                         $analysed_data->quotient,
                                         $xls_formats->procent);
            }
        }
        $row_offset += 3;
        return $row_offset;
    }

    /**
     * Options for the multichoice element
     * @param stdClass $item
     * @return array
     */
    protected function get_options($item) {
        $info = $this->get_info($item);
        $presentation = explode (INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        $options = array();
        foreach ($presentation as $idx => $optiontext) {
            $options[$idx + 1] = format_text($optiontext, FORMAT_HTML, array('noclean' => true, 'para' => false));
        }
        if ($info->subtype === 'r' && !$this->hidenoselect($item)) {
            $options = array(0 => get_string('not_selected', 'feedback')) + $options;
        }

        return $options;
    }

        /**
     * Adds an input element to the complete form
     *
     * This element has many options - it can be displayed as group or radio elements,
     * group of checkboxes or a dropdown list.
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        $info = $this->get_info($item);
        $name = $this->get_display_name($item);
        $class = 'multichoice-' . $info->subtype;
        $inputname = $item->typ . '_' . $item->id;
        $options = $this->get_options($item);
        $separator = !empty($info->horizontal) ? ' ' : '<br>';
        // From here on lines backported from commit - MDL-62947 mod_feedback: c96c9601af41f9fe05b7a5259bc1590b8df17d9b.
        $tmpvalue = $form->get_item_value($item) ?? 0; // Used for element defaults, so must be a valid value (not null).

        // Subtypes:
        // r = radio
        // c = checkbox
        // d = dropdown.
        if ($info->subtype === 'd' || ($info->subtype === 'r' && $form->is_frozen())) {
            // Display as a dropdown in the complete form or a single value in the response view.
            $element = $form->add_form_element($item,
                    ['select', $inputname, $name, array(0 => '') + $options, array('class' => $class)],
                    false, false);
            $form->set_element_default($inputname, $tmpvalue);
            $form->set_element_type($inputname, PARAM_INT);
        } else if ($info->subtype === 'c' && $form->is_frozen()) {
            // Display list of checkbox values in the response view.
            $objs = [];
            foreach (explode(INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $form->get_item_value($item)) as $v) {
                $objs[] = ['static', $inputname."[$v]", '', isset($options[$v]) ? $options[$v] : ''];
            }
            $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
        } else {
            // Display group or radio or checkbox elements.
            $class .= ' multichoice-' . ($info->horizontal ? 'horizontal' : 'vertical');
            $objs = [];
            if ($info->subtype === 'c') {
                // Checkboxes.
                $objs[] = ['hidden', $inputname.'[0]', 0];
                $form->set_element_type($inputname.'[0]', PARAM_INT);
                foreach ($options as $idx => $label) {
                    $objs[] = ['advcheckbox', $inputname.'['.$idx.']', '', $label, null, array(0, $idx)];
                    $form->set_element_type($inputname.'['.$idx.']', PARAM_INT);
                }
                // Span to hold the element id. The id is used for drag and drop reordering.
                $objs[] = ['static', '', '', html_writer::span('', '', ['id' => 'feedback_item_' . $item->id])];
                $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
                if ($tmpvalue) {
                    foreach (explode(INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $tmpvalue) as $v) {
                        $form->set_element_default($inputname.'['.$v.']', $v);
                    }
                }
            } else {
                // Radio.
                if (!array_key_exists(0, $options)) {
                    // Always add a hidden element to the group to guarantee we get a value in the submit data.
                    $objs[] = ['hidden', $inputname, 0];
                }
                foreach ($options as $idx => $label) {
                    $objs[] = ['radio', $inputname, '', $label, $idx];
                }
                // Span to hold the element id. The id is used for drag and drop reordering.
                $objs[] = ['static', '', '', html_writer::span('', '', ['id' => 'feedback_item_' . $item->id])];
                $element = $form->add_form_group_element($item, 'group_'.$inputname, $name, $objs, $separator, $class);
                $form->set_element_default($inputname, $tmpvalue);
                $form->set_element_type($inputname, PARAM_INT);
            }
        }

        // Process 'required' rule.
        if ($item->required) {
            $elementname = $element->getName();
            $form->add_validation_rule(function($values) use ($elementname, $item) {
                $inputname = $item->typ . '_' . $item->id;
                return empty($values[$inputname]) || (is_array($values[$inputname]) && !array_filter($values[$inputname])) ?
                    array($elementname => get_string('required')) : true;
            });
        }
    }

    /**
     * Prepares value that user put in the form for storing in DB
     * @param array $value
     * @return string
     */
    public function create_value($value) {
        // Next line backported commit - MDL-62947 mod_feedback: c96c9601af41f9fe05b7a5259bc1590b8df17d9b.
        // Could be an array (multichoice checkbox) or single value (multichoice radio or dropdown).
        $value = is_array($value) ? $value : [$value];

        $value = array_unique(array_filter($value));
        return join(INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $value);
    }

    /**
     * Compares the dbvalue with the dependvalue
     *
     * @param stdClass $item
     * @param string $dbvalue is the value input by user in the format as it is stored in the db
     * @param string $dependvalue is the value that it needs to be compared against
     */
    public function compare_value($item, $dbvalue, $dependvalue) {

        if (is_array($dbvalue)) {
            $dbvalues = $dbvalue;
        } else {
            $dbvalues = explode(INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $dbvalue);
        }

        $info = $this->get_info($item);
        $presentation = explode (INDIVIDUALFEEDBACK_MULTICHOICE_LINE_SEP, $info->presentation);
        $index = 1;
        foreach ($presentation as $pres) {
            foreach ($dbvalues as $dbval) {
                if ($dbval == $index AND trim($pres) == $dependvalue) {
                    return true;
                }
            }
            $index++;
        }
        return false;
    }

    public function get_info($item) {
        $presentation = empty($item->presentation) ? '' : $item->presentation;

        $info = new stdClass();
        //check the subtype of the multichoice
        //it can be check(c), radio(r) or dropdown(d)
        $info->subtype = '';
        $info->presentation = '';
        $info->horizontal = false;

        $parts = explode(INDIVIDUALFEEDBACK_MULTICHOICE_TYPE_SEP, $item->presentation);

        // Here we happen to override the predefined  $info->presentation = ''; which can lead to a NULL value, messing up
        // in the next if-loop, when we try to explode(INDIVIDUALFEEDBACK_MULTICHOICE_ADJUST_SEP, $info->presentation);
        @list($info->subtype, $info->presentation) = $parts;
        /**
         * When $item is
         * object(stdClass)#210 (5) {
         * ["id"]=> NULL
         * ["position"]=> int(4)
         * ["typ"]=> string(11) "multichoice"
         * ["options"]=> string(0) ""
         * ["presentation"]=> string(0) "" }
         *
         * Than $parts is only a single cell array with an empty string. This will fail the next if-statement.
         * array(1) { [0]=> string(0) "" }
         */
        if (!isset($info->subtype)) {
            $info->subtype = 'r';
        }
        if (!isset($info->presentation)) {
            $info->presentation = '';
        }

        #echo("Item<br>");
        #var_dump($item);
        #echo("Parts<br>");
        #var_dump($parts);

        if ($info->subtype != 'd') {
            $parts = explode(INDIVIDUALFEEDBACK_MULTICHOICE_ADJUST_SEP, $info->presentation);
            @list($info->presentation, $info->horizontal) = $parts;
            if (isset($info->horizontal) AND $info->horizontal == 1) {
                $info->horizontal = true;
            } else {
                $info->horizontal = false;
            }
        }
        return $info;
    }

    public function set_ignoreempty($item, $ignoreempty=true) {
        $item->options = str_replace(INDIVIDUALFEEDBACK_MULTICHOICE_IGNOREEMPTY, '', $item->options);
        if ($ignoreempty) {
            $item->options .= INDIVIDUALFEEDBACK_MULTICHOICE_IGNOREEMPTY;
        }
    }

    public function ignoreempty($item) {
        if (strstr($item->options, INDIVIDUALFEEDBACK_MULTICHOICE_IGNOREEMPTY)) {
            return true;
        }
        return false;
    }

    public function set_negativeformulated($item, $negativeformulated=true) {
        $item->options = str_replace(INDIVIDUALFEEDBACK_MULTICHOICE_NEGATIVEFORMULATED, '', $item->options);
        if ($negativeformulated) {
            $item->options .= INDIVIDUALFEEDBACK_MULTICHOICE_NEGATIVEFORMULATED;
        }
    }

    public function negativeformulated($item) {
        if (strstr($item->options, INDIVIDUALFEEDBACK_MULTICHOICE_NEGATIVEFORMULATED)) {
            return true;
        }
        return false;
    }

    public function set_hidenoselect($item, $hidenoselect=true) {
        $item->options = str_replace(INDIVIDUALFEEDBACK_MULTICHOICE_HIDENOSELECT, '', $item->options);
        if ($hidenoselect) {
            $item->options .= INDIVIDUALFEEDBACK_MULTICHOICE_HIDENOSELECT;
        }
    }

    public function hidenoselect($item) {
        if (strstr($item->options, INDIVIDUALFEEDBACK_MULTICHOICE_HIDENOSELECT)) {
            return true;
        }
        return false;
    }

    /**
     * Return the analysis data ready for external functions.
     *
     * @param stdClass $item     the item (question) information
     * @param int      $groupid  the group id to filter data (optional)
     * @param int      $courseid the course id (optional)
     * @return array an array of data with non scalar types json encoded
     * @since  Moodle 3.3
     */
    public function get_analysed_for_external($item, $groupid = false, $courseid = false) {

        $externaldata = array();
        $data = $this->get_analysed($item, $groupid, $courseid);

        if (!empty($data[2]) && is_array($data[2])) {
            foreach ($data[2] as $d) {
                $externaldata[] = json_encode($d);
            }
        }
        return $externaldata;
    }
}

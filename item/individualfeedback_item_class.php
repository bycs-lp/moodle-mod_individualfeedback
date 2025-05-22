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

abstract class individualfeedback_item_base {

    /** @var string type of the element, should be overridden by each item type */
    protected $type;

    /** @var feedback_item_form */
    protected $item_form;

    /** @var stdClass */
    protected $item;

    /**
     * constructor
     */
    public function __construct() {
    }

    /**
     * Displays the form for editing an item
     *
     * this function only can used after the call of build_editform()
     */
    public function show_editform() {
        $this->item_form->display();
    }

    /**
     * Checks if the editing form was cancelled
     *
     * @return bool
     */
    public function is_cancelled() {
        return $this->item_form->is_cancelled();
    }

    /**
     * Gets submitted data from the edit form and saves it in $this->item
     *
     * @return bool
     */
    public function get_data() {
        if ($this->item !== null) {
            return true;
        }
        if ($this->item = $this->item_form->get_data()) {
            return true;
        }
        return false;
    }

    /**
     * Set the item data (to be used by data generators).
     *
     * @param stdClass $itemdata the item data to set
     * @since Moodle 3.3
     */
    public function set_data($itemdata) {
        $this->item = $itemdata;
    }

    /**
     * Creates and returns an instance of the form for editing the item
     *
     * @param stdClass $item
     * @param stdClass $individualfeedback
     * @param cm_info|stdClass $cm
     */
    abstract public function build_editform($item, $individualfeedback, $cm);

    /**
     * Saves the item after it has been edited (or created)
     */
    abstract public function save_item();

    /**
     * Converts the value from complete_form data to the string value that is stored in the db.
     * @param mixed $value element from mod_individualfeedback_complete_form::get_data() with the name $item->typ.'_'.$item->id
     * @return string
     */
    public function create_value($value) {
        return strval($value);
    }

    /**
     * Compares the dbvalue with the dependvalue
     *
     * @param stdClass $item
     * @param string $dbvalue is the value input by user in the format as it is stored in the db
     * @param string $dependvalue is the value that it needs to be compared against
     */
    public function compare_value($item, $dbvalue, $dependvalue) {
        return strval($dbvalue) === strval($dependvalue);
    }

    /**
     * Wether this item type has a value that is expected from the user and saved in the stored values.
     * @return int
     */
    public function get_hasvalue() {
        return 1;
    }

    /**
     * Wether this item can be set as both required and not
     * @return bool
     */
    public function can_switch_require() {
        return false;
    }

    /**
     * Adds summary information about an item to the Excel export file
     *
     * @param object $worksheet a reference to the pear_spreadsheet-object
     * @param integer $row_offset
     * @param stdClass $xls_formats see analysis_to_excel.php
     * @param object $item the db-object from individualfeedback_item
     * @param integer $groupid
     * @param integer $courseid
     * @return integer the new row_offset
     */
    abstract public function excelprint_item(&$worksheet, $row_offset,
                                      $xls_formats, $item,
                                      $groupid, $courseid = false);

    /**
     * Prints analysis for the current item
     *
     * @param $item the db-object from individualfeedback_item
     * @param string $itemnr
     * @param integer $groupid
     * @param integer $courseid
     * @return integer the new itemnr
     */
    abstract public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false);

    /**
     * Prepares the value for exporting to Excel
     *
     * @param object $item the db-object from individualfeedback_item
     * @param string $value a item-related value from individualfeedback_values
     * @return string
     */
    abstract public function get_printval($item, $value);

    /**
     * Returns the formatted name of the item for the complete form or response view
     *
     * @param stdClass $item
     * @param bool $withpostfix
     * @return string
     */
    public function get_display_name($item, $withpostfix = true) {
        return format_text($item->name, FORMAT_HTML, array('noclean' => true, 'para' => false)) .
                ($withpostfix ? $this->get_display_name_postfix($item) : '');
    }

    /**
     * Returns the postfix to be appended to the display name that is based on other settings
     *
     * @param stdClass $item
     * @return string
     */
    public function get_display_name_postfix($item) {
        return '';
    }

    /**
     * Adds an input element to the complete form
     *
     * This method is called:
     * - to display the form when user completes individualfeedback
     * - to display existing elements when teacher edits the individualfeedback items
     * - to display the individualfeedback preview (print.php)
     * - to display the completed response
     * - to preview a individualfeedback template
     *
     * If it is important which mode the form is in, use $form->get_mode()
     *
     * Each item type must add a single form element with the name $item->typ.'_'.$item->id
     * This element must always be present in form data even if nothing is selected (i.e. use advcheckbox and not checkbox).
     * To add an element use either:
     * $form->add_form_element() - adds a single element to the form
     * $form->add_form_group_element() - adds a group element to the form
     *
     * Other useful methods:
     * $form->get_item_value()
     * $form->set_element_default()
     * $form->add_validation_rule()
     * $form->set_element_type()
     *
     * The element must support freezing so it can be used for viewing the response as well.
     * If the desired form element does not support freezing, check $form->is_frozen()
     * and create a static element instead.
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    abstract public function complete_form_element($item, $form);

    /**
     * Returns the list of actions allowed on this item in the edit mode
     *
     * @param stdClass $item
     * @param stdClass $individualfeedback
     * @param cm_info $cm
     * @return action_menu_link[]
     */
    public function edit_actions($item, $individualfeedback, $cm) {
        $actions = array();

        $strupdate = get_string('edit_item', 'individualfeedback');
        $actions['update'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit_item.php', array('id' => $item->id)),
            new pix_icon('t/edit', $strupdate, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strupdate,
            array('class' => 'editing_update', 'data-action' => 'update')
        );

        if ($this->can_switch_require()) {
            if ($item->required == 1) {
                $buttontitle = get_string('switch_item_to_not_required', 'individualfeedback');
                $buttonimg = 'required';
            } else {
                $buttontitle = get_string('switch_item_to_required', 'individualfeedback');
                $buttonimg = 'notrequired';
            }
            $actions['required'] = new action_menu_link_secondary(
                new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id,
                    'switchitemrequired' => $item->id, 'sesskey' => sesskey())),
                new pix_icon($buttonimg, $buttontitle, 'individualfeedback', array('class' => 'iconsmall', 'title' => '')),
                $buttontitle,
                array('class' => 'editing_togglerequired', 'data-action' => 'togglerequired')
            );
        }

        $strdelete = get_string('delete_item', 'individualfeedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete', 'data-action' => 'delete')
        );

        return $actions;
    }

    /**
     * Return extra data for external functions.
     *
     * Some items may have additional configuration data or default values that should be returned for external functions:
     * - Info elements: The default value information (course or category name)
     * - Captcha: The recaptcha challenge hash key
     *
     * @param stdClass $item the item object
     * @return str the data, may be json_encoded for large structures
     */
    public function get_data_for_external($item) {
        return null;
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
    abstract public function get_analysed_for_external($item, $groupid = false, $courseid = false);

    /**
     * Prints the overview questions data
     *
     * @param stdClass $item     the item (question) information
     * @param int      $groupid  the group id to filter data (optional)
     * @param int      $courseid the course id (optional)
     */
    public function print_overview_questions($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return;
        }

        $overviewdata = array();
        if ($data = $this->get_answer_data($item, $groupid, $courseid)) {
            if (!$data['totalvalues']) {
                $overviewdata['average'] = 0;
            } else {
                $totalvalue = 0;
                foreach ($data['values'] as $key => $value) {
                    $totalvalue += ($key * $value);
                }
                $average = $totalvalue / $data['totalvalues'];
                $overviewdata['average'] = round($average, 2);
            }
        }

        $overviewdata['selfassessment'] = 0;
        if ($selfassessment = $this->check_and_get_self_assessment_data($item)) {
            $overviewdata['selfassessment'] = $selfassessment->value;
        }

        if (!$overviewdata['average'] && !$overviewdata['selfassessment']) {
            return '';
        }

        echo "<table class=\"analysis itemtype_{$item->typ}\">";
        echo '<tr><th colspan="2" align="left">';
        echo $itemnr . ' ';
        if (strval($item->label) !== '') {
            echo '('. format_string($item->label).') ';
        }
        echo format_string($item->name);
        echo '</th></tr>';
        echo "</table>";
        $graphdata = array();
        $graphdata['series_labels1'] = array($overviewdata['average']);
        $graphdata['series_labels2'] = array($overviewdata['selfassessment']);
        $graphdata['series1'] = array($overviewdata['average']);
        $graphdata['series2'] = array($overviewdata['selfassessment']);

        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        if ($overviewdata['average']) {
            $series = new \core\chart_series(format_string(get_string('average', 'individualfeedback')), $graphdata['series1']);
            $series->set_labels($graphdata['series_labels1']);
            $chart->add_series($series);
        }
        if ($overviewdata['selfassessment']) {
            $series = new \core\chart_series(format_string(get_string('selfassessment', 'individualfeedback')), $graphdata['series2']);
            $series->set_labels($graphdata['series_labels2']);
            $chart->add_series($series);
        }

        $answers = array(0 => '');
        for ($i = 1; $i <= $data['answers']; $i++) {
            $answers[] = get_string('answer') . " " . $i;
        }

        $xaxis = $chart->get_xaxis(0, true);
        $xaxis->set_stepsize(1);
        $xaxis->set_min(0);
        $xaxis->set_max(($i));
        $xaxis->set_labels($answers);
        $chart->set_xaxis($xaxis);

        echo $OUTPUT->render($chart);
    }

    /**
     * Prints the comparison questions data
     *
     * @param stdClass $item            the item (question) information
     * @param array    $allfeedbacks    Array with all the linked activities
     * @param int      $groupid         the group id to filter data (optional)
     * @param int      $courseid        the course id (optional)
     */
    public function print_comparison_questions($item, $allfeedbacks, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT, $DB;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return;
        }

        $feedbackids = array();
        if (count($allfeedbacks)) {
            foreach($allfeedbacks as $feedback) {
                $feedbackids[] = $feedback->id;
            }
        }

        $allitems = array();
        foreach ($feedbackids as $id) {
            if ($id != $item->individualfeedback) {
                $params = array('individualfeedback' => $id, 'position' => $item->position);
                $otheritem = $DB->get_record('individualfeedback_item', $params);
            } else {
                $otheritem = $item;
            }
            $allitems[$id] = $otheritem;
        }

        $overviewdata = array();
        foreach ($allitems as $currentitem) {
            if ($data = $this->get_answer_data($currentitem, $groupid, $courseid)) {
                if (!$data['totalvalues']) {
                    $overviewdata[$currentitem->individualfeedback] = 0;
                } else {
                    $totalvalue = 0;
                    foreach ($data['values'] as $key => $value) {
                        $totalvalue += ($key * $value);
                    }
                    $average = $totalvalue / $data['totalvalues'];
                    $overviewdata[$currentitem->individualfeedback] = round($average, 2);
                }
            }
        }

        $canprint = false;
        foreach ($overviewdata as $value) {
            if ($value) {
                $canprint = true;
                break;
            }
        }

        if (!$canprint) {
            return '';
        }

        echo "<table class=\"analysis itemtype_{$item->typ}\">";
        echo '<tr><th colspan="2" align="left">';
        echo $itemnr . ' ';
        if (strval($item->label) !== '') {
            echo '('. format_string($item->label).') ';
        }
        echo format_string($item->name);
        echo '</th></tr>';
        echo "</table>";

        $chart = new \core\chart_bar();
        $chart->set_horizontal(true);
        foreach ($overviewdata as $key => $value) {
            $feedbackname = format_string($allfeedbacks[$key]->name);
            $series = new \core\chart_series($feedbackname, array($value));
            $series->set_labels(array($value));
            $chart->add_series($series);
        }

        $answers = array(0 => '');
        for ($i = 1; $i <= $data['answers']; $i++) {
            $answers[] = get_string('answer') . " " . $i;
        }

        $xaxis = $chart->get_xaxis(0, true);
        $xaxis->set_stepsize(1);
        $xaxis->set_min(0);
        $xaxis->set_max(($i));
        $xaxis->set_labels($answers);
        $chart->set_xaxis($xaxis);

        echo $OUTPUT->render($chart);
    }

    public function excelprint_overview_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return $row_offset;
        }

        $overviewdata = array();
        if ($data = $this->get_answer_data($item, $groupid, $courseid)) {
            if (!$data['totalvalues']) {
                $overviewdata['average'] = 0;
            } else {
                $totalvalue = 0;
                foreach ($data['values'] as $key => $value) {
                    $totalvalue += ($key * $value);
                }
                $average = $totalvalue / $data['totalvalues'];
                $overviewdata['average'] = round($average, 2);
            }
        }

        $overviewdata['selfassessment'] = 0;
        if ($selfassessment = $this->check_and_get_self_assessment_data($item)) {
            $overviewdata['selfassessment'] = $selfassessment->value;
        }

        if (!$overviewdata['average'] && !$overviewdata['selfassessment']) {
            return $row_offset;
        }

        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, format_string($item->name), $xls_formats->head2);
        $worksheet->write_string($row_offset, 2, get_string('average', 'individualfeedback'), $xls_formats->head2);
        $worksheet->write_number($row_offset + 1, 2, $overviewdata['average'], $xls_formats->default);
        $worksheet->write_string($row_offset, 3, get_string('selfassessment', 'individualfeedback'), $xls_formats->head2);
        $worksheet->write_number($row_offset + 1, 3, $overviewdata['selfassessment'], $xls_formats->default);

        $row_offset += 2;

        return $row_offset;
    }

    public function excelprint_comparison_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false, $allfeedbacks = array()) {
        global $DB;

        $availableitems = individualfeedback_get_statistic_question_types();
        if (!in_array($item->typ, $availableitems)) {
            return $row_offset;
        }

        $feedbackids = array();
        if (count($allfeedbacks)) {
            foreach($allfeedbacks as $feedback) {
                if ($feedback->id != $item->individualfeedback) {
                    $feedbackids[] = $feedback->id;
                }
            }
        }

        $allitems = array($item->individualfeedback => $item);
        foreach ($feedbackids as $id) {
            $params = array('individualfeedback' => $id, 'position' => $item->position);
            $otheritem = $DB->get_record('individualfeedback_item', $params);
            $allitems[$id] = $otheritem;
        }

        $overviewdata = array();
        foreach ($allitems as $currentitem) {
            if ($data = $this->get_answer_data($currentitem, $groupid, $courseid)) {
                if (!$data['totalvalues']) {
                    $overviewdata[$currentitem->individualfeedback] = 0;
                } else {
                    $totalvalue = 0;
                    foreach ($data['values'] as $key => $value) {
                        $totalvalue += ($key * $value);
                    }
                    $average = $totalvalue / $data['totalvalues'];
                    $overviewdata[$currentitem->individualfeedback] = round($average, 2);
                }
            }
        }

        $canprint = false;
        foreach ($overviewdata as $value) {
            if ($value) {
                $canprint = true;
                break;
            }
        }

        if (!$canprint) {
            return $row_offset;
        }

        $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
        $worksheet->write_string($row_offset, 1, format_string($item->name), $xls_formats->head2);

        $column = 2;
        foreach ($overviewdata as $key => $value) {
            $feedbackname = format_string($allfeedbacks[$key]->name);
            $worksheet->write_string($row_offset, $column, $feedbackname, $xls_formats->head2);
            $worksheet->write_number($row_offset + 1, $column, $value, $xls_formats->default);
            $column++;
        }

        $row_offset += 2;

        return $row_offset;
    }

    public function check_and_get_self_assessment_data($item) {
        global $DB, $PAGE;

        $data = array();
        if (!has_capability('mod/individualfeedback:selfassessment', $PAGE->context)) {
            return $data;
        }
        $data = individualfeedback_get_group_values($item, false, false, false, false, true);
        return reset($data);
    }

    /**
     * Helper function for collected data, for detailed analysis
     *
     * @param stdClass $item the db-object from individualfeedback_item
     * @param var $seperator - based on the item class
     * @param int $groupid
     * @param int $courseid
     * @return array
     */
    public function get_item_answer_data($item, $seperator, $groupid = false, $courseid = false) {
        $info = $this->get_info($item);

        $analysed_item = array();

        //get the possible answers
        $answers = null;
        $answers = explode ($seperator, $info->presentation);
        if (!is_array($answers)) {
            $analysed_item['answers'] = 0;
            return $analysed_item;
        }

        $sizeofanswers = count($answers);
        $analysed_item['answers'] = $sizeofanswers;
        $analysed_item['values'] = array();

        //get the values
        $values = individualfeedback_get_group_values($item, $groupid, $courseid, $this->negativeformulated($item), $this->ignoreempty($item));
        if (!$values) {
            $analysed_item['totalvalues'] = 0;
            return $analysed_item;
        }

        // Answer is not required, so check if an answer is given.
        $totalvalues = 0;
        foreach ($values as $value) {
            if ($value->value != null) {
                $totalvalues++;
            }
        }
        $analysed_item['totalvalues'] = $totalvalues;

        //get answertext, answercount and quotient for each answer
        $analysed_answer = array();
        for ($i = 1; $i <= $sizeofanswers; $i++) {
            $answercount = 0;
            foreach ($values as $value) {
                //ist die Antwort gleich dem index der Antworten + 1?
                if ($value->value == $i) {
                    $answercount++;
                }
            }
            $analysed_item['values'][$i] = $answercount;
        }

        return $analysed_item;
    }

}

//a dummy class to realize pagebreaks
class individualfeedback_item_pagebreak extends individualfeedback_item_base {
    protected $type = "pagebreak";

    public function show_editform() {
    }

    /**
     * Checks if the editing form was cancelled
     * @return bool
     */
    public function is_cancelled() {
    }
    public function get_data() {
    }
    public function build_editform($item, $individualfeedback, $cm) {
    }
    public function save_item() {
    }
    public function create_value($data) {
    }
    public function get_hasvalue() {
        return 0;
    }
    public function excelprint_item(&$worksheet, $row_offset,
                            $xls_formats, $item,
                            $groupid, $courseid = false) {
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
    }
    public function get_printval($item, $value) {
    }
    public function can_switch_require() {
        return false;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        $form->add_form_element($item,
            ['static',
                $item->typ.'_'.$item->id,
                '',
                html_writer::empty_tag('hr', ['class' => 'individualfeedback_pagebreak', 'id' => 'feedback_item_' . $item->id])
            ]);
    }

    /**
     * Returns the list of actions allowed on this item in the edit mode
     *
     * @param stdClass $item
     * @param stdClass $individualfeedback
     * @param cm_info $cm
     * @return action_menu_link[]
     */
    public function edit_actions($item, $individualfeedback, $cm) {
        $actions = array();
        $strdelete = get_string('delete_pagebreak', 'individualfeedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete', 'data-action' => 'delete')
        );
        return $actions;
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
        return;
    }
}

//a dummy class to realize questiongroupsend
class individualfeedback_item_questiongroupend extends individualfeedback_item_base {
    protected $type = "questiongroupend";

    public function show_editform() {
    }

    /**
     * Checks if the editing form was cancelled
     * @return bool
     */
    public function is_cancelled() {
    }
    public function get_data() {
    }
    public function build_editform($item, $individualfeedback, $cm) {
    }
    public function save_item() {
    }
    public function create_value($data) {
    }
    public function get_hasvalue() {
        return 0;
    }
    public function excelprint_item(&$worksheet, $row_offset,
                            $xls_formats, $item,
                            $groupid, $courseid = false) {
        $worksheet->write_string($row_offset, 0, get_string('end_of_questiongroup', 'individualfeedback'), $xls_formats->head2);
        $row_offset++;
        $row_offset++;
        return $row_offset;
    }

    public function excelprint_detail_groups(&$worksheet, $row_offset,
                            $xls_formats, $item,
                            $groupid, $courseid = false) {
        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_overview_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_overview_groups(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_comparison_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false, $allfeedbacks = array()) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_comparison_groups(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false, $allfeedbacks = array()) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo html_writer::tag('div', get_string('end_of_questiongroup', 'individualfeedback'));
        echo html_writer::end_tag('div');
    }

    public function print_detail_groups($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function print_overview_questions($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function print_overview_groups($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function print_comparison_questions($item, $allfeedbacks, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function print_comparison_groups($item, $allfeedbacks, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function get_printval($item, $value) {
    }
    public function can_switch_require() {
        return false;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        $form->add_form_element($item,
            ['static',
                $item->typ.'_'.$item->id,
                '',
                html_writer::tag('span', get_string('end_of_questiongroup', 'individualfeedback'), ['class' => 'individualfeedback_questiongroupend', 'id' => 'feedback_item_' . $item->id])
            ]);
        $form->add_form_element($item, ['html', html_writer::end_tag('div')]);
    }

    /**
     * Returns the list of actions allowed on this item in the edit mode
     *
     * @param stdClass $item
     * @param stdClass $individualfeedback
     * @param cm_info $cm
     * @return action_menu_link[]
     */
    public function edit_actions($item, $individualfeedback, $cm) {
        return array();
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
        return;
    }
}

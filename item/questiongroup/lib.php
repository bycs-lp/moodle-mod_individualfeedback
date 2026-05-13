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

class individualfeedback_item_questiongroup extends individualfeedback_item_base {
    protected $type = "questiongroup";

    public function build_editform($item, $individualfeedback, $cm) {
        global $DB, $CFG;
        require_once('questiongroup_form.php');

        //get the lastposition number of the individualfeedback_items
        $position = $item->position;
        $lastposition = $DB->count_records('individualfeedback_item', array('individualfeedback'=>$individualfeedback->id));
        
        // Get all items to identify positions inside this group
        $allitems = $DB->get_records('individualfeedback_item', array('individualfeedback' => $individualfeedback->id), 'position');
        
        // Find the questiongroupend for this group
        $groupendposition = null;
        if ($item->id) {
            $groupend = $DB->get_record('individualfeedback_item', 
                array('individualfeedback' => $individualfeedback->id, 'typ' => 'questiongroupend', 'dependitem' => $item->id));
            if ($groupend) {
                $groupendposition = $groupend->position;
            }
        }
        
        // Build list of positions to exclude (positions inside the group)
        // Note: We DO include the current position so user can edit text without changing position
        $excludepositions = array();
        if ($item->id && $groupendposition) {
            // Exclude positions of items inside the group
            foreach ($allitems as $checkitem) {
                if ($checkitem->position > $item->position && $checkitem->position < $groupendposition) {
                    $excludepositions[$checkitem->position] = true;
                }
            }
            // Also exclude the groupend position
            $excludepositions[$groupendposition] = true;
        }
        
        if ($position == -1) {
            $i_formselect_last = $lastposition + 1;
            $i_formselect_value = $lastposition + 1;
            $item->position = $lastposition + 1;
        } else {
            $i_formselect_last = $lastposition;
            $i_formselect_value = $item->position;
        }
        
        // Build position list excluding positions inside the group
        $allpositions = array_slice(range(0, $i_formselect_last), 1, $i_formselect_last, true);
        $positionlist = array();
        foreach ($allpositions as $pos => $val) {
            if (!isset($excludepositions[$pos])) {
                $positionlist[$pos] = $val;
            }
        }

        $item->presentation = '';

        //all items for dependitem
        $individualfeedbackitems = individualfeedback_get_depend_candidates_for_item($individualfeedback, $item);
        $commonparams = array('cmid' => $cm->id,
                             'id' => isset($item->id) ? $item->id : null,
                             'typ' => $item->typ,
                             'items' => $individualfeedbackitems,
                             'individualfeedback' => $individualfeedback->id);

        //build the form
        $customdata = array('item' => $item,
                            'common' => $commonparams,
                            'positionlist' => $positionlist,
                            'position' => $position);

        $this->item_form = new individualfeedback_questiongroup_form('edit_item.php', $customdata);
    }

    public function save_item() {
        global $DB;

        if (!$this->get_data()) {
            return false;
        }
        $item = $this->item;
        
        // Get old position before update
        $oldposition = null;
        if ($item->id) {
            $olditem = $DB->get_record('individualfeedback_item', array('id' => $item->id));
            if ($olditem) {
                $oldposition = $olditem->position;
            }
        }

        // SFSUBM-20 - create the group after this group.
        $params = array('individualfeedback' => $item->individualfeedback);
        if (isset($item->clone_item) AND $item->clone_item) {
            $item->id = ''; //to clone this item
            // SFSUBM-20 - create the group after this group.
            $sql = "SELECT MAX(position)
            FROM {individualfeedback_item}
            WHERE individualfeedback = :individualfeedback ";
            $endgroupposition = $DB->get_field_sql($sql, $params);
            $item->position = $endgroupposition + 1;
        }

        $item->hasvalue = $this->get_hasvalue();
        if (!$item->id) {
            $item->id = $DB->insert_record('individualfeedback_item', $item);
        } else {
            $DB->update_record('individualfeedback_item', $item);
        }

        $newitem = $DB->get_record('individualfeedback_item', array('id' => $item->id));

        // SFSUBM-20 - only create a end group if there isn't one yet.
        $params = array('individualfeedback' => $newitem->individualfeedback, 'typ' => 'questiongroupend',
                            'dependitem' => $newitem->id);
        // Also create a end of the group so you can actually determine what the group is.
        if (!$DB->record_exists('individualfeedback_item', $params)) {
            $enditem = new stdClass();
            $enditem->individualfeedback = $item->individualfeedback;
            $enditem->template = 0;
            $enditem->name = '';
            $enditem->label = '';
            $enditem->presentation = '';
            $enditem->typ = 'questiongroupend';
            $enditem->hasvalue = 0;
            $enditem->position = $newitem->position + 1;
            $enditem->required = 0;
            $enditem->dependitem = $newitem->id;
            $enditem->dependvalue = '';
            $enditem->options = '';
            $enditem->id = $DB->insert_record('individualfeedback_item', $enditem);
        }
        
        // If position changed, move the entire group (start + questions inside + end)
        if ($oldposition !== null && $oldposition != $newitem->position && $newitem->id) {
            $this->move_entire_group($newitem, $oldposition, $newitem->position);
        }

        return $newitem;
    }
    
    /**
     * Moves the entire question group (start, all questions inside, and end) to a new position
     * 
     * @param stdClass $groupitem The questiongroup item
     * @param int $oldposition The old position of the group start
     * @param int $newposition The new position for the group start
     */
    private function move_entire_group($groupitem, $oldposition, $newposition) {
        global $DB;
        
        // Get the groupend BEFORE any position changes
        $groupend = $DB->get_record('individualfeedback_item', 
            array('individualfeedback' => $groupitem->individualfeedback, 'typ' => 'questiongroupend', 'dependitem' => $groupitem->id));
        
        if (!$groupend) {
            return;
        }
        
        // Get all items ordered by position BEFORE any changes
        $allitems = $DB->get_records('individualfeedback_item', 
            array('individualfeedback' => $groupitem->individualfeedback), 'position');
        
        // Get all items in the group (between start and end) using OLD positions
        $groupitems = array();
        $oldgroupendposition = $groupend->position;
        foreach ($allitems as $item) {
            if ($item->position > $oldposition && $item->position < $oldgroupendposition) {
                $groupitems[] = $item;
            }
        }
        
        // Calculate group size (start + items inside + end)
        $groupsize = 1 + count($groupitems) + 1;
        
        // Build list of all items outside the group, ordered by position
        $itemsoutside = array();
        foreach ($allitems as $item) {
            // Skip items inside the group
            if ($item->id == $groupitem->id || $item->id == $groupend->id) {
                continue;
            }
            $isinside = false;
            foreach ($groupitems as $gitem) {
                if ($item->id == $gitem->id) {
                    $isinside = true;
                    break;
                }
            }
            if (!$isinside) {
                $itemsoutside[] = $item;
            }
        }
        
        // The newposition from the form is the position where the group should START
        // in the final list (after removing the group from old position)
        // 
        // Strategy: Build the final list by:
        // 1. Remove the group from its old position
        // 2. Insert the group at the new position
        // 3. Renumber everything sequentially
        
        // Calculate new positions
        $newpositions = array();
        $currentpos = 1;
        $groupinserted = false;
        
        foreach ($itemsoutside as $item) {
            // If we reached the new position, insert the group here
            if (!$groupinserted && $currentpos == $newposition) {
                $newpositions[$groupitem->id] = $currentpos++;
                foreach ($groupitems as $gitem) {
                    $newpositions[$gitem->id] = $currentpos++;
                }
                $newpositions[$groupend->id] = $currentpos++;
                $groupinserted = true;
            }
            $newpositions[$item->id] = $currentpos++;
        }
        
        // If new position is at the end or beyond, add group at the end
        if (!$groupinserted) {
            $newpositions[$groupitem->id] = $currentpos++;
            foreach ($groupitems as $gitem) {
                $newpositions[$gitem->id] = $currentpos++;
            }
            $newpositions[$groupend->id] = $currentpos++;
        }
        
        // Update all positions in a single transaction-like approach
        // First, set all positions to temporary high values to avoid conflicts
        foreach ($newpositions as $itemid => $pos) {
            $DB->set_field('individualfeedback_item', 'position', 10000 + $pos, array('id' => $itemid));
        }
        
        // Then set the final positions
        foreach ($newpositions as $itemid => $pos) {
            $DB->set_field('individualfeedback_item', 'position', $pos, array('id' => $itemid));
        }
    }

    public function get_printval($item, $value) {

        if (!isset($value->name)) {
            return '';
        }
        return $value->name;
    }

    public function print_analysed($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo html_writer::start_tag('div', array('class' => 'questiongroup_analysed', 'id' => 'questiongroup_' . $item->id));
        echo html_writer::tag('span', $item->name, array('class' => 'h3'));
    }

    public function print_detail_groups($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        echo $this->print_analysed($item, $itemnr);

        // Get the questions within this group.
        if (!$questions = $this->get_question_in_group($item)) {
            echo html_writer::tag('p', get_string('no_questions_in_group', 'individualfeedback'));
        } else {
            // Get the data for each question.
            $alldata = array();
            foreach ($questions as $question) {
                $questionobj = individualfeedback_get_item_class($question->typ);
                $data = $questionobj->get_answer_data($question);
                $alldata[$question->id] = $data;
            }

            // Check if the number of answers are equal.
            $canprint = true;
            $first = true;
            foreach ($alldata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                echo html_writer::tag('p', get_string('error_calculating_averages', 'individualfeedback'));
            } else {
                // Combine the data.
                $combineddata = array();
                $combineddata['values'] = array();
                $combineddata['totalvalues'] = 0;
                // Set the default values to 0.
                for ($i = 1; $i <= $numberofanswers; $i++) {
                    $combineddata['values'][$i] = 0;
                }

                foreach ($alldata as $data) {
                    foreach ($data['values'] as $key => $value) {
                        $combineddata['values'][$key] += $value;
                    }
                    $combineddata['totalvalues'] += $data['totalvalues'];
                }

                // If there are no answers yet, don't display a chart.
                if (!$combineddata['totalvalues']) {
                    return '';
                }

                $printdata = array();
                foreach ($combineddata['values'] as $key => $value) {
                    $printdata[$key] = new stdClass();
                    $printdata[$key]->answertext = get_string('answer') . " " . $key;
                    $printdata[$key]->answercount = $value;
                    $printdata[$key]->quotient = $value / $combineddata['totalvalues'];
                }

                // Print the combined statistics graph.
                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                echo "<table class=\"analysis itemtype_{$item->typ}\">";
                echo '<tr><th colspan="2" align="left">';
                echo $itemnr . ' ';
                if (strval($item->label) !== '') {
                    echo '('. format_string($item->label).') ';
                }
                echo format_string($itemname);
                echo '</th></tr>';
                echo "</table>";
                $count = 0;
                $data = [];
                foreach ($printdata as $val) {
                    $quotient = round($val->quotient * 100, 2);
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
                $series = new \core\chart_series(format_string(get_string("responses", "individualfeedback")), $data['series']);
                $series->set_labels($data['series_labels']);
                $chart->add_series($series);
                $chart->set_labels($data['labels']);

                echo $OUTPUT->render($chart);
            }
        }
    }

    public function print_overview_questions($item, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    public function print_overview_groups($item, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT;

        echo $this->print_analysed($item, $itemnr);

        // Get the questions within this group.
        if (!$questions = $this->get_question_in_group($item)) {
            echo html_writer::tag('p', get_string('no_questions_in_group', 'individualfeedback'));
        } else {
            // Get the data for each question.
            $alldata = array();
            $selfassessment = array();
            foreach ($questions as $question) {
                $questionobj = individualfeedback_get_item_class($question->typ);
                $data = $questionobj->get_answer_data($question);         
                $alldata[$question->id] = $data;
                
                if (strpos($question->options, 'n') !== FALSE) {
                    $alldata[$question->id]['values'] = array_reverse($alldata[$question->id]['values']);
                    
                    $indizes = array();
                    foreach ($alldata[$question->id]['values'] as $key => $v) {
                        $indizes[$key+1] = $v;
                    }
                    
                    $alldata[$question->id]['values'] = $indizes;
                }
                                
                if ($selfdata = $this->check_and_get_self_assessment_data($question)) {
                    $selfassessment[$question->id] = $selfdata;
                    if (strpos($question->options, 'n') !== FALSE) {
                        $selfassessment[$question->id]->value = $alldata[$question->id]['answers']+1-$selfassessment[$question->id]->value;
                    }
                }
            }

            // Check if the number of answers are equal.
            $canprint = true;
            $first = true;
            foreach ($alldata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                echo html_writer::tag('p', get_string('error_calculating_averages', 'individualfeedback'));
            } else {
                // Calculate the averages.
                $averages = array();
                foreach ($alldata as $data) {
                    if (!$data['totalvalues']) {
                        $averages[] = 0;
                        continue;
                    }

                    $total = 0;
                    foreach ($data['values'] as $key => $value) {
                        $total += ($key * $value);
                    }
                    $averages[] = ($total / $data['totalvalues']);
                }

                $totalofaverages = 0;
                foreach ($averages as $average) {
                    $totalofaverages += $average;
                }

                // Self asessement averages
                $selfaverages = array();
                foreach ($selfassessment as $record) {
                    $selfaverages[] = $record->value;
                }

                $totalselfaverages = 0;
                foreach ($selfaverages as $average) {
                    $totalselfaverages += $average;
                }

                // If there are no answers yet, don't display a chart.
                if (!$totalofaverages && !$totalselfaverages) {
                    return '';
                }

                $totalaverage = 0;
                if ($totalofaverages) {
                    $totalaverage = round(($totalofaverages /  count($averages)), 2);
                }

                $totalselfaverage = 0;
                if ($totalselfaverages) {
                    $totalselfaverage = round(($totalselfaverages /  count($selfaverages)), 2);
                }

                // Print the combined statistics graph.
                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                echo "<table class=\"analysis itemtype_{$item->typ}\">";
                echo '<tr><th colspan="2" align="left">';
                echo $itemnr . ' ';
                if (strval($item->label) !== '') {
                    echo '('. format_string($item->label).') ';
                }
                echo format_string($itemname);
                echo '</th></tr>';
                echo "</table>";
                $graphdata = array();
                $graphdata['series_labels1'] = array($totalaverage);
                $graphdata['series_labels2'] = array($totalselfaverage);
                $graphdata['series1'] = array($totalaverage);
                $graphdata['series2'] = array($totalselfaverage);

                $chart = new \core\chart_bar();
                $chart->set_horizontal(true);
                if ($totalaverage) {
                    $series = new \core\chart_series(format_string(get_string('average', 'individualfeedback')), $graphdata['series1']);
                    $series->set_labels($graphdata['series_labels1']);
                    $chart->add_series($series);
                }
                if ($totalselfaverage) {
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
        }
    }

    public function print_comparison_questions($item, $allindividualfeedbacks, $itemnr = '', $groupid = false, $courseid = false) {
        echo $this->print_analysed($item, $itemnr);
    }

    /**
     * Prints the comparison groups data
     *
     * @param stdClass $item            the item (question) information
     * @param array    $allindividualfeedbacks    Array with all the linked activities
     * @param int      $groupid         the group id to filter data (optional)
     * @param int      $courseid        the course id (optional)
     */
    public function print_comparison_groups($item, $allindividualfeedbacks, $itemnr = '', $groupid = false, $courseid = false) {
        global $OUTPUT, $DB;

        echo $this->print_analysed($item, $itemnr);
        
        // Get the questions within this group for this instance.
        if (!$questions = $this->get_question_in_group($item)) {
            echo html_writer::tag('p', get_string('no_questions_in_group', 'individualfeedback'));
        } else {
            // Get the data for each question of this instance.
            $alldata = array();
            foreach ($allindividualfeedbacks as $oneindividualfeedback) {
                $alldata[$oneindividualfeedback->id] = array();
                
                // TG/MO: Bug getting the questions of the correct feedback fixed
                //if ($item->individualfeedback != $oneindividualfeedback->id) {
                    $params = array('individualfeedback' => $oneindividualfeedback->id, 'position' => $item->position);
                    $otheritem = $DB->get_record('individualfeedback_item', $params);
                    $questions = $this->get_question_in_group($otheritem);
                //}

                foreach ($questions as $question) {
                    $questionobj = individualfeedback_get_item_class($question->typ);
                    $data = $questionobj->get_answer_data($question);
                    $alldata[$oneindividualfeedback->id][$question->id] = $data;

                    if (strpos($question->options, 'n') !== FALSE) {
                        $alldata[$oneindividualfeedback->id][$question->id]['values'] = array_reverse($alldata[$oneindividualfeedback->id][$question->id]['values']);
                    
                        $indizes = array();
                        foreach ($alldata[$oneindividualfeedback->id][$question->id]['values'] as $key => $v) {
                            $indizes[$key+1] = $v;
                        }
                    
                        $alldata[$oneindividualfeedback->id][$question->id]['values'] = $indizes;
                    }   
                    
                    if (!$data['totalvalues']) {
                        $average = 0;
                    } else {
                        $totalvalue = 0;
                        foreach ($alldata[$oneindividualfeedback->id][$question->id]['values'] as $key => $value) {
                            $totalvalue += ($key * $value);
                        }
                        $average = $totalvalue / $data['totalvalues'];
                    }
                    $data['average'] = $average;
                    $alldata[$oneindividualfeedback->id][$question->id]['average'] = $data['average'];
                }
            }

            // There is already a check on individualfeedback_check_linked_questions in the call of this page.
            // So just make sure the items within 1 instance are correct, that is sufficient for checking.
            $checkdata = reset($alldata);
            // Check if the number of answers are equal of this instance.
            $canprint = true;
            $first = true;
            foreach ($checkdata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                echo html_writer::tag('p', get_string('error_calculating_averages', 'individualfeedback'));
            } else {
                $overviewdata = array();
                foreach ($alldata as $individualfeedbackid => $datas) {
                    $individualfeedbacktotal = 0;
                    foreach ($datas as $data) {
                        $individualfeedbacktotal += $data['average'];
                    }
                    $overviewdata[$individualfeedbackid] = round($individualfeedbacktotal / count($datas), 2);
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

                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                echo "<table class=\"analysis itemtype_{$item->typ}\">";
                echo '<tr><th colspan="2" align="left">';
                echo $itemnr . ' ';
                if (strval($item->label) !== '') {
                    echo '('. format_string($item->label).') ';
                }
                echo format_string($itemname);
                echo '</th></tr>';
                echo "</table>";

                $chart = new \core\chart_bar();
                $chart->set_horizontal(true);
                foreach ($overviewdata as $key => $value) {
                    $individualfeedbackname = format_string($allindividualfeedbacks[$key]->name);
                    $series = new \core\chart_series($individualfeedbackname, array($value));
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
        }
    }

    public function excelprint_item(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $worksheet->write_string($row_offset, 0, $item->name, $xls_formats->head2);
        $row_offset++;
        return $row_offset;
    }

    public function excelprint_overview_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_detail_groups(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $worksheet->write_string($row_offset, 0, $item->name, $xls_formats->head2);
        $row_offset++;

        // Get the questions within this group.
        if (!$questions = $this->get_question_in_group($item)) {
            $worksheet->write_string($row_offset, 0, get_string('no_questions_in_group', 'individualfeedback'), $xls_formats->default);
            $row_offset++;
        } else {
            // Get the data for each question.
            $alldata = array();
            $selfassessment = array();
            foreach ($questions as $question) {
                $questionobj = individualfeedback_get_item_class($question->typ);
                $data = $questionobj->get_answer_data($question);
                $alldata[$question->id] = $data;
                if ($selfdata = $this->check_and_get_self_assessment_data($question)) {
                    $selfassessment[$question->id] = $selfdata;
                }
            }

            // Check if the number of answers are equal.
            $canprint = true;
            $first = true;
            foreach ($alldata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                $worksheet->write_string($row_offset, 0, get_string('error_calculating_averages', 'individualfeedback'), $xls_formats->default);
                $row_offset++;
            } else {
                // Combine the data.
                $combineddata = array();
                $combineddata['values'] = array();
                $combineddata['totalvalues'] = 0;
                // Set the default values to 0.
                for ($i = 1; $i <= $numberofanswers; $i++) {
                    $combineddata['values'][$i] = 0;
                }

                foreach ($alldata as $data) {
                    foreach ($data['values'] as $key => $value) {
                        $combineddata['values'][$key] += $value;
                    }
                    $combineddata['totalvalues'] += $data['totalvalues'];
                }

                // If there are no answers yet, don't display a chart.
                if (!$combineddata['totalvalues']) {
                    return $row_offset;
                }

                $printdata = array();
                foreach ($combineddata['values'] as $key => $value) {
                    $printdata[$key] = new stdClass();
                    $printdata[$key]->answertext = get_string('answer') . " " . $key;
                    $printdata[$key]->answercount = $value;
                    $printdata[$key]->quotient = $value / $combineddata['totalvalues'];
                }

                // Print the combined statistics graph.
                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
                $worksheet->write_string($row_offset, 1, $itemname, $xls_formats->head2);

                $column = 2;
                foreach ($printdata as $val) {
                    $answertext = format_text(trim($val->answertext), FORMAT_HTML,
                            array('noclean' => true, 'para' => false));

                    $worksheet->write_string($row_offset,
                                             $column,
                                             $answertext,
                                             $xls_formats->head2);

                    $worksheet->write_number($row_offset + 1,
                                             $column,
                                             $val->answercount,
                                             $xls_formats->default);

                    $worksheet->write_number($row_offset + 2,
                                             $column,
                                             $val->quotient,
                                             $xls_formats->procent);

                    $column++;
                }

                $row_offset += 3;

                // Also print the self asessment answers if they are available.
                if ($numberofselfanswers = count($selfassessment)) {
                    $selfasessmentdata = array();
                    for ($i = 1; $i <= $numberofanswers; $i++) {
                        $selfassessmentdata[$i] = 0;
                    }
                    foreach ($selfassessment as $record) {
                        $selfassessmentdata[$record->value] += 1;
                    }

                    $worksheet->write_string($row_offset,
                                             1,
                                             get_string('selfassessment', 'individualfeedback'),
                                             $xls_formats->head2);
                    $column = 2;
                    foreach ($selfassessmentdata as $totalanswers) {
                        $worksheet->write_number($row_offset,
                                                 $column,
                                                 $totalanswers,
                                                 $xls_formats->default);

                        $worksheet->write_number($row_offset + 1,
                                                 $column,
                                                 $totalanswers / $numberofselfanswers,
                                                 $xls_formats->procent);
                        $column++;
                    }

                    $row_offset += 2;
                }
            }
        }

        return $row_offset;
    }

    public function excelprint_overview_groups(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false) {

        $worksheet->write_string($row_offset, 0, $item->name, $xls_formats->head2);
        $row_offset++;

        // Get the questions within this group.
        if (!$questions = $this->get_question_in_group($item)) {
            $worksheet->write_string($row_offset, 0, get_string('no_questions_in_group', 'individualfeedback'), $xls_formats->default);
            $row_offset++;
        } else {
            // Get the data for each question.
            $alldata = array();
            $selfassessment = array();
            foreach ($questions as $question) {
                $questionobj = individualfeedback_get_item_class($question->typ);
                $data = $questionobj->get_answer_data($question);
                $alldata[$question->id] = $data;

                if (strpos($question->options, 'n') !== FALSE) {
                    $alldata[$question->id]['values'] = array_reverse($alldata[$question->id]['values']);
                    
                    $indizes = array();
                    foreach ($alldata[$question->id]['values'] as $key => $v) {
                        $indizes[$key+1] = $v;
                    }
                    
                    $alldata[$question->id]['values'] = $indizes;
                }
                                
                if ($selfdata = $this->check_and_get_self_assessment_data($question)) {
                    $selfassessment[$question->id] = $selfdata;
                    if (strpos($question->options, 'n') !== FALSE) {
                        $selfassessment[$question->id]->value = $alldata[$question->id]['answers']+1-$selfassessment[$question->id]->value;
                    }
                }
            }

            // Check if the number of answers are equal.
            $canprint = true;
            $first = true;
            foreach ($alldata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                $worksheet->write_string($row_offset, 0, get_string('error_calculating_averages', 'individualfeedback'), $xls_formats->default);
                $row_offset++;
            } else {
                // Calculate the averages.
                $averages = array();
                foreach ($alldata as $data) {
                    if (!$data['totalvalues']) {
                        $averages[] = 0;
                        continue;
                    }

                    $total = 0;
                    foreach ($data['values'] as $key => $value) {
                        $total += ($key * $value);
                    }
                    $averages[] = ($total / $data['totalvalues']);
                }

                $totalofaverages = 0;
                foreach ($averages as $average) {
                    $totalofaverages += $average;
                }

                // Self asessement averages
                $selfaverages = array();
                foreach ($selfassessment as $record) {
                    $selfaverages[] = $record->value;
                }

                $totalselfaverages = 0;
                foreach ($selfaverages as $average) {
                    $totalselfaverages += $average;
                }

                // If there are no answers yet, don't display a chart.
                if (!$totalofaverages && !$totalselfaverages) {
                    return $row_offset;
                }

                $totalaverage = 0;
                if ($totalofaverages) {
                    $totalaverage = round(($totalofaverages /  count($averages)), 2);
                }

                $totalselfaverage = 0;
                if ($totalselfaverages) {
                    $totalselfaverage = round(($totalselfaverages /  count($selfaverages)), 2);
                }

                // Print the combined statistics graph.
                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
                $worksheet->write_string($row_offset, 1, $itemname, $xls_formats->head2);

                $worksheet->write_string($row_offset,
                                         2,
                                         get_string('average', 'individualfeedback'),
                                         $xls_formats->head2);

                $worksheet->write_number($row_offset + 1,
                                         2,
                                         $totalaverage,
                                         $xls_formats->default);

                $worksheet->write_string($row_offset,
                                         3,
                                         get_string('selfassessment', 'individualfeedback'),
                                         $xls_formats->head2);

                $worksheet->write_number($row_offset + 1,
                                         3,
                                         $totalselfaverage,
                                         $xls_formats->default);

                $row_offset += 2;
            }
        }

        return $row_offset;
    }

    public function excelprint_comparison_questions(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false, $allindividualfeedbacks = array()) {

        return $this->excelprint_item($worksheet, $row_offset, $xls_formats, $item, $groupid, $courseid);
    }

    public function excelprint_comparison_groups(&$worksheet, $row_offset,
                             $xls_formats, $item,
                             $groupid, $courseid = false, $allindividualfeedbacks = array()) {
        global $DB;

        $worksheet->write_string($row_offset, 0, $item->name, $xls_formats->head2);
        $row_offset++;

        // Get the questions within this group for this instance.
        if (!$questions = $this->get_question_in_group($item)) {
            $worksheet->write_string($row_offset, 0, get_string('no_questions_in_group', 'individualfeedback'), $xls_formats->default);
            $row_offset++;
        } else {
            // Get the data for each question of this instance.
            $alldata = array();
            foreach ($allindividualfeedbacks as $oneindividualfeedback) {
                $alldata[$oneindividualfeedback->id] = array();
                
                // TG/MO: Bug getting the questions of the correct feedback fixed
                //if ($item->individualfeedback != $oneindividualfeedback->id) {
                    $params = array('individualfeedback' => $oneindividualfeedback->id, 'position' => $item->position);
                    $otheritem = $DB->get_record('individualfeedback_item', $params);
                    $questions = $this->get_question_in_group($otheritem);
                //}

                foreach ($questions as $question) {
                    $questionobj = individualfeedback_get_item_class($question->typ);
                    $data = $questionobj->get_answer_data($question);
                    $alldata[$oneindividualfeedback->id][$question->id] = $data;

                    if (strpos($question->options, 'n') !== FALSE) {
                        $alldata[$oneindividualfeedback->id][$question->id]['values'] = array_reverse($alldata[$oneindividualfeedback->id][$question->id]['values']);
                    
                        $indizes = array();
                        foreach ($alldata[$oneindividualfeedback->id][$question->id]['values'] as $key => $v) {
                            $indizes[$key+1] = $v;
                        }
                    
                        $alldata[$oneindividualfeedback->id][$question->id]['values'] = $indizes;
                    }   
                    
                    if (!$data['totalvalues']) {
                        $average = 0;
                    } else {
                        $totalvalue = 0;
                        foreach ($alldata[$oneindividualfeedback->id][$question->id]['values'] as $key => $value) {
                            $totalvalue += ($key * $value);
                        }
                        $average = $totalvalue / $data['totalvalues'];
                    }
                    $data['average'] = $average;
                    $alldata[$oneindividualfeedback->id][$question->id]['average'] = $data['average'];
                }
            }

            // There is already a check on individualfeedback_check_linked_questions in the call of this page.
            // So just make sure the items within 1 instance are correct, that is sufficient for checking.
            $checkdata = reset($alldata);
            // Check if the number of answers are equal of this instance.
            $canprint = true;
            $first = true;
            foreach ($checkdata as $data) {
                if ($first) {
                    $numberofanswers = $data['answers'];
                }
                $first = false;

                if ($numberofanswers != $data['answers']) {
                    $canprint = false;
                    break;
                }
            }

            if (!$canprint) {
                $worksheet->write_string($row_offset, 0, get_string('error_calculating_averages', 'individualfeedback'), $xls_formats->default);
                $row_offset++;
            } else {
                $overviewdata = array();
                foreach ($alldata as $individualfeedbackid => $datas) {
                    $individualfeedbacktotal = 0;
                    foreach ($datas as $data) {
                        $individualfeedbacktotal += $data['average'];
                    }
                    $overviewdata[$individualfeedbackid] = round($individualfeedbacktotal / count($datas), 2);
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

                $itemname = get_string('analysis_questiongroup', 'individualfeedback', count($questions));
                $worksheet->write_string($row_offset, 0, $item->label, $xls_formats->head2);
                $worksheet->write_string($row_offset, 1, $itemname, $xls_formats->head2);

                $column = 2;
                foreach ($overviewdata as $key => $value) {
                    $individualfeedbackname = format_string($allindividualfeedbacks[$key]->name);
                    $worksheet->write_string($row_offset, $column, $individualfeedbackname, $xls_formats->head2);
                    $worksheet->write_number($row_offset + 1, $column, $value, $xls_formats->default);
                    $column++;
                }

                $row_offset += 2;
            }
        }

        return $row_offset;
    }

    /**
     * Adds an input element to the complete form
     *
     * @param stdClass $item
     * @param mod_individualfeedback_complete_form $form
     */
    public function complete_form_element($item, $form) {
        global $OUTPUT, $PAGE;

        $name = $this->get_display_name($item);

        // Flat structure: no dummy row (it would show as an extra empty "After" in Move this question).
        // Put the group name in the label so it appears in the same cell as the drag handle and menu (beside the arrow).
        $namelabel = html_writer::tag('span', $name, [
            'class' => 'h3 individualfeedback_questiongroup',
            'id' => 'individualfeedback_item_' . $item->id,
        ]);
        $namelabel = html_writer::tag('div', $namelabel, [
            'class' => 'individualfeedback_questiongroup_start questiongroupmoveitem',
            'id' => 'questiongroup_' . $item->id,
        ]);
        $form->add_form_element($item,
            ['static',
                $item->typ.'_'.$item->id,
                $namelabel,
                '&nbsp;'
            ]);
    }

    /**
     * Converts the value from complete_form data to the string value that is stored in the db.
     * @param mixed $value element from mod_individualfeedback_complete_form::get_data() with the name $item->typ.'_'.$item->id
     * @return string
     */
    public function create_value($value) {
        return '';
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

        if (is_array($data->data)) {
            return $data->data; // No need to json, scalar type.
        }
        return $externaldata;
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

        $strupdate = get_string('edit_questiongroup', 'individualfeedback');
        $actions['update'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit_item.php', array('id' => $item->id)),
            new pix_icon('t/edit', $strupdate, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strupdate,
            array('class' => 'editing_update', 'data-action' => 'update')
        );

        $strdelete = get_string('delete_questiongroup', 'individualfeedback');
        $actions['delete'] = new action_menu_link_secondary(
            new moodle_url('/mod/individualfeedback/edit.php', array('id' => $cm->id, 'deleteitem' => $item->id, 'sesskey' => sesskey())),
            new pix_icon('t/delete', $strdelete, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $strdelete,
            array('class' => 'editing_delete questiongroup', 'data-action' => 'delete')
        );

        return $actions;
    }

    /**
     * Wether this item type has a value that is expected from the user and saved in the stored values.
     * @return int
     */
    public function get_hasvalue() {
        return 0;
    }

    /**
     * Returns the question within the question group
     *
     * @param stdClass $item
     * @return array of records
     */
    public function get_question_in_group($item) {
        global $DB;

        $qtypes = individualfeedback_get_statistic_question_types();
        list($where, $qparams) = $DB->get_in_or_equal($qtypes, SQL_PARAMS_NAMED);
        $sql = "SELECT ii.id, ii.individualfeedback, ii.template, ii.name, ii.label,
               ii.presentation, ii.typ, ii.hasvalue, ii.position, ii.required,
               ii.dependitem, ii.dependvalue, ii.options
        FROM {individualfeedback_item} ii
        WHERE ii.individualfeedback = :individualfeedback
        AND ii.typ {$where}
        AND ii.position > :startposition
        AND ii.position <
            (SELECT position
            FROM {individualfeedback_item}
            WHERE dependitem = :itemid)
        ORDER BY ii.position";
        $params = array('individualfeedback' => $item->individualfeedback, 'startposition' => $item->position, 'itemid' => $item->id);
        $params = array_merge($params, $qparams);
        return $DB->get_records_sql($sql, $params);
    }
}

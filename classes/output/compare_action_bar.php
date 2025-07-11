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

namespace mod_individualfeedback\output;

use moodle_url;
use url_select;
use action_menu;
use action_menu_link;

/**
 * Class compare_action_bar. The tertiary nav for the compare page
 *
 * @copyright 2021 Peter Dias
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */
class compare_action_bar extends base_action_bar {
    /** @var moodle_url $currenturl The current page url */
    private $currenturl;

    /**
     * compare_action_bar constructor.
     *
     * @param int $cmid The cmid for the module we are operating on
     * @param moodle_url $pageurl The current page url
     */
    public function __construct(int $cmid, moodle_url $pageurl) {
        parent::__construct($cmid);
        $this->currenturl = $pageurl;
        $this->urlparams['id'] = $cmid;
    }

    /**
     * Return the items to be used in the tertiary nav
     *
     * @return array
     */
    public function get_items(): array {
        global $DB;

        $items = [];

        $addselect = new action_menu();
        $addselect->set_menu_trigger(get_string('comparison_questions', 'mod_individualfeedback'), 'btn btn-primary');
        $addselect->set_menu_left();
        $addselectparams = ['cmid' => $this->cmid, 'sesskey' => sesskey()];
        $module = $DB->get_record('modules', ['name' => 'individualfeedback']);
        $modules = $DB->get_records('course_modules',
            ['course' => $this->course->id, 'module' => $module->id, 'deletioninprogress' => 0]);
        if (has_capability('mod/individualfeedback:viewreports', $this->context)) {
            foreach ($modules as $feedback_item) {
                $feedback = $DB->get_record('individualfeedback', ['id' => $feedback_item->instance]);
                $this->urlparams['id_compare'] = $feedback_item->id;
                $reporturl = new moodle_url('/mod/individualfeedback/analysis_compare.php', $this->urlparams);
                $addselect->add(new action_menu_link(
                    $reporturl,
                    null,
                    $feedback->name,
                    false,
                ));
            }
            return ['addselect' => $addselect,];
        }


        if (has_capability('mod/individualfeedback:viewreports', $this->context)) {
            $module = $DB->get_record('modules', ['name' => 'individualfeedback']);
            $modules = $DB->get_records('course_modules',
                            ['course' => $this->course->id, 'module' => $module->id, 'deletioninprogress' => 0]);
            foreach($modules AS $feedback_item) {
                $this->urlparams['id_compare'] = $feedback_item->id;
                $reporturl = new moodle_url('/mod/individualfeedback/analysis_compare.php', $this->urlparams);
                $options[$reporturl->out(false)] = 'Compare with aaa';
            }

            // Don't show the dropdown if it's only a single item.
            if (count($options) != 1) {
                $items['left'][]['urlselect'] = new url_select($options,
                    null,
                    null);
            }
        }
        return $items;
    }
}

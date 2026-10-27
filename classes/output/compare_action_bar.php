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

defined('MOODLE_INTERNAL') || die();

use moodle_url;
use url_select;
use action_menu;
use action_menu_link;

/**
 * Class compare_action_bar. The tertiary nav for the compare page
 *
 * @copyright 2021 Peter Dias
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package mod_individualfeedback
 */
class compare_action_bar extends base_action_bar {
    /** @var moodle_url $currenturl The current page url */
    private $currenturl;

    /** @var string $mode The active report mode of analysis.php (see hack\lib::ANALYSIS_MODES) */
    private $mode;

    /**
     * compare_action_bar constructor.
     *
     * @param int $cmid The cmid for the module we are operating on
     * @param moodle_url $pageurl The current page url
     * @param string $mode The active report mode
     */
    public function __construct(int $cmid, moodle_url $pageurl, string $mode = 'detail_questions') {
        parent::__construct($cmid);
        $this->currenturl = $pageurl;
        $this->urlparams['id'] = $cmid;
        $this->mode = $mode;
    }

    /**
     * Select for the report modes (detail/overview/comparison × questions/groups), finding #9 of the
     * review 26.10.2026: the report fragments were not reachable from the UI before.
     *
     * @return url_select
     */
    private function get_mode_select(): url_select {
        $options = [];
        $selected = null;
        foreach (\mod_individualfeedback\hack\lib::get_available_analysis_modes($this->individualfeedback) as $mode => $label) {
            $url = new moodle_url('/mod/individualfeedback/analysis.php', ['id' => $this->cmid, 'mode' => $mode]);
            $options[$url->out(false)] = $label;
            if ($mode === $this->mode) {
                $selected = $url->out(false);
            }
        }
        $select = new url_select($options, $selected, null);
        $select->set_label(get_string('report_mode', 'individualfeedback'), ['class' => 'visually-hidden']);
        return $select;
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
        $addselect->set_menu_trigger(get_string('comparison_questions', 'individualfeedback'), 'btn btn-primary');
        $addselect->set_menu_left();
        $addselectparams = ['cmid' => $this->cmid, 'sesskey' => sesskey()];
        $module = $DB->get_record('modules', ['name' => 'individualfeedback']);
        $modules = $DB->get_records(
            'course_modules',
            ['course' => $this->course->id, 'module' => $module->id, 'deletioninprogress' => 0]
        );
        if (has_capability('mod/individualfeedback:viewreports', $this->context)) {
            foreach ($modules as $feedbackitem) {
                $individualfeedback = $DB->get_record('individualfeedback', ['id' => $feedbackitem->instance]);
                $this->urlparams['id_compare'] = $feedbackitem->id;
                $reporturl = new moodle_url('/mod/individualfeedback/analysis_compare.php', $this->urlparams);
                $addselect->add(new action_menu_link(
                    $reporturl,
                    null,
                    format_string($individualfeedback->name),
                    false
                ));
            }
            // The template renders url selects and action links inside the "left" group.
            $items['left'][]['urlselect'] = $this->get_mode_select();
            $items['addselect'] = $addselect;

            // Duplicate and link: creates the second activity of a linked pair for the comparison reports.
            if (
                has_capability('moodle/course:manageactivities', \core\context\course::instance($this->course->id))
                    && has_capability('mod/individualfeedback:edititems', $this->context)
            ) {
                $linkurl = new moodle_url('/mod/individualfeedback/duplicate_and_link.php', ['id' => $this->cmid, 'sesskey' => sesskey()]);
                $items['left'][]['actionlink'] = new \action_link(
                    $linkurl,
                    get_string('duplicate_and_link', 'individualfeedback'),
                    null,
                    ['class' => 'btn btn-secondary']
                );
            }
        }

        return $items;
    }
}

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
 * Contains class mod_individualfeedback_structure
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Stores and manipulates the structure of the feedback or template (items, pages, etc.)
 *
 * @package   mod_individualfeedback
 * @copyright 2016 Marina Glancy
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_individualfeedback_structure {
    /** @var stdClass record from 'individualfeedback' table.
     * Reliably has fields: id, course, timeopen, timeclose, anonymous, completionsubmit.
     * For full object or to access any other field use $this->get_individualfeedback()
     */
    protected $individualfeedback;
    /** @var cm_info */
    protected $cm;
    /** @var int course where the feedback is filled. For feedbacks that are NOT on the front page this is 0 */
    protected $courseid = 0;
    /** @var int */
    protected $templateid;
    /** @var array */
    protected $allitems;
    /** @var array */
    protected $allcourses;
    /** @var array */
    protected $groupeditems;
    /** @var int */
    protected $userid;

    /**
     * Constructor
     *
     * @param stdClass $individualfeedback individualfeedback instance object, in case of the template
     *     this is the current individualfeedback the template is accessed from
     * @param stdClass|cm_info $cm course module object corresponding to the $individualfeedback
     *     (at least one of $individualfeedback or $cm is required)
     * @param int $courseid current course (for site feedbacks only)
     * @param int $templateid template id if this class represents the template structure
     * @param int $userid User id to use for all capability checks, etc. Set to 0 for current user (default).
     */
    public function __construct($individualfeedback, $cm, $courseid = 0, $templateid = null, $userid = 0) {
        global $USER;

        if ((empty($individualfeedback->id) || empty($individualfeedback->course)) && (empty($cm->instance) || empty($cm->course))) {
            throw new coding_exception('Either $individualfeedback or $cm must be passed to constructor');
        }
        $this->individualfeedback = $individualfeedback ?: (object)['id' => $cm->instance, 'course' => $cm->course];
        $this->cm = ($cm && $cm instanceof cm_info) ? $cm :
            get_fast_modinfo($this->individualfeedback->course)->instances['individualfeedback'][$this->individualfeedback->id];
        $this->templateid = $templateid;
        $this->courseid = ($this->individualfeedback->course == SITEID) ? $courseid : 0;

        if (empty($userid)) {
            $this->userid = $USER->id;
        } else {
            $this->userid = $userid;
        }

        if (!$individualfeedback) {
            // If individualfeedback object was not specified, populate object with fields required for the most of methods.
            // These fields were added to course module cache in individualfeedback_get_coursemodule_info().
            // Full instance record can be retrieved by calling mod_individualfeedback_structure::get_individualfeedback().
            $customdata = ($this->cm->customdata ?: []) + ['timeopen' => 0, 'timeclose' => 0, 'anonymous' => 0];
            $this->individualfeedback->timeopen = $customdata['timeopen'];
            $this->individualfeedback->timeclose = $customdata['timeclose'];
            $this->individualfeedback->anonymous = $customdata['anonymous'];
            $this->individualfeedback->completionsubmit = empty($this->cm->customdata['customcompletionrules']['completionsubmit']) ? 0 : 1;
        }
    }

    /**
     * Current individualfeedback instance
     * @return stdClass
     */
    public function get_individualfeedback() {
        global $DB;
        if (!isset($this->individualfeedback->publish_stats) || !isset($this->individualfeedback->name)) {
            // Make sure the full object is retrieved.
            $this->individualfeedback = $DB->get_record('individualfeedback', ['id' => $this->individualfeedback->id], '*', MUST_EXIST);
        }
        return $this->individualfeedback;
    }

    /**
     * Current course module
     * @return stdClass
     */
    public function get_cm() {
        return $this->cm;
    }

    /**
     * Id of the current course (for site feedbacks only)
     * @return stdClass
     */
    public function get_courseid() {
        return $this->courseid;
    }

    /**
     * Template id
     * @return int
     */
    public function get_templateid() {
        return $this->templateid;
    }

    /**
     * Is this feedback open (check timeopen and timeclose)
     * @return bool
     */
    public function is_open() {
        $checktime = time();
        return (!$this->individualfeedback->timeopen || $this->individualfeedback->timeopen <= $checktime) &&
            (!$this->individualfeedback->timeclose || $this->individualfeedback->timeclose >= $checktime);
    }

    /**
     * Get all items in this feedback or this template
     * @param bool $hasvalueonly only count items with a value.
     * @return array of objects from individualfeedback_item with an additional attribute 'itemnr'
     */
    public function get_items($hasvalueonly = false) {
        global $DB;
        if ($this->allitems === null) {
            if ($this->templateid) {
                $this->allitems = $DB->get_records('individualfeedback_item', ['template' => $this->templateid], 'position');
            } else {
                $this->allitems = $DB->get_records('individualfeedback_item', ['individualfeedback' => $this->individualfeedback->id], 'position');
            }
            $idx = 1;
            foreach ($this->allitems as $id => $item) {
                $this->allitems[$id]->itemnr = $item->hasvalue ? ($idx++) : null;
            }
        }
        if ($hasvalueonly && $this->allitems) {
            return array_filter($this->allitems, function($item) {
                return $item->hasvalue;
            });
        }
        return $this->allitems;
    }

    /**
     * Is the items list empty?
     * @return bool
     */
    public function is_empty() {
        $items = $this->get_items();
        $displayeditems = array_filter($items, function($item) {
            return $item->typ !== 'pagebreak';
        });
        return !$displayeditems;
    }

    /**
     * Is this feedback anonymous?
     * @return bool
     */
    public function is_anonymous() {
        return $this->individualfeedback->anonymous == INDIVIDUALFEEDBACK_ANONYMOUS_YES;
    }

    /**
     * Returns the formatted text of the page after submit or null if it is not set
     *
     * @return string|null
     */
    public function page_after_submit() {
        global $CFG;
        require_once($CFG->libdir . '/filelib.php');

        $pageaftersubmit = $this->get_individualfeedback()->page_after_submit;
        if (empty($pageaftersubmit)) {
            return null;
        }
        $pageaftersubmitformat = $this->get_individualfeedback()->page_after_submitformat;

        $context = context_module::instance($this->get_cm()->id);
        $output = file_rewrite_pluginfile_urls($pageaftersubmit,
                'pluginfile.php', $context->id, 'mod_individualfeedback', 'page_after_submit', 0);

        return format_text($output, $pageaftersubmitformat, array('overflowdiv' => true));
    }

    /**
     * Checks if current user is able to view feedback on this course.
     *
     * @return bool
     */
    public function can_view_analysis() {
        global $USER;

        $context = context_module::instance($this->cm->id);
        if (has_capability('mod/individualfeedback:viewreports', $context, $this->userid)) {
            return true;
        }

        if (intval($this->get_individualfeedback()->publish_stats) != 1 ||
                !has_capability('mod/individualfeedback:viewanalysepage', $context, $this->userid)) {
            return false;
        }

        if ((!isloggedin() && $USER->id == $this->userid) || isguestuser($this->userid)) {
            // There is no tracking for the guests, assume that they can view analysis if condition above is satisfied.
            return $this->individualfeedback->course == SITEID;
        }

        return $this->is_already_submitted(true);
    }

    /**
     * check for multiple_submit = false.
     * if the feedback is global so the courseid must be given
     *
     * @param bool $anycourseid if true checks if this feedback was submitted in any course, otherwise checks $this->courseid .
     *     Applicable to frontpage feedbacks only
     * @return bool true if the feedback already is submitted otherwise false
     */
    public function is_already_submitted($anycourseid = false) {
        global $DB, $USER;

        if ((!isloggedin() && $USER->id == $this->userid) || isguestuser($this->userid)) {
            return false;
        }

        $params = array('userid' => $this->userid, 'individualfeedback' => $this->individualfeedback->id);
        if (!$anycourseid && $this->courseid) {
            $params['courseid'] = $this->courseid;
        }
        return $DB->record_exists('individualfeedback_completed', $params);
    }

    /**
     * Check whether the feedback is mapped to the given courseid.
     */
    public function check_course_is_mapped() {
        global $DB;
        if ($this->individualfeedback->course != SITEID) {
            return true;
        }
        if ($DB->get_records('individualfeedback_sitecourse_map', array('individualfeedbackid' => $this->individualfeedback->id))) {
            $params = array('individualfeedbackid' => $this->individualfeedback->id, 'courseid' => $this->courseid);
            if (!$DB->get_record('individualfeedback_sitecourse_map', $params)) {
                return false;
            }
        }
        // No mapping means any course is mapped.
        return true;
    }

    /**
     * If there are any new responses to the anonymous feedback, re-shuffle all
     * responses and assign response number to each of them.
     */
    public function shuffle_anonym_responses() {
        global $DB;
        $params = array('individualfeedback' => $this->individualfeedback->id,
            'random_response' => 0,
            'anonymous_response' => INDIVIDUALFEEDBACK_ANONYMOUS_YES);

        if ($DB->count_records('individualfeedback_completed', $params, 'random_response')) {
            // Get all of the anonymous records, go through them and assign a response id.
            unset($params['random_response']);
            $individualfeedbackcompleteds = $DB->get_records('individualfeedback_completed', $params, 'id');
            shuffle($individualfeedbackcompleteds);
            $num = 1;
            foreach ($individualfeedbackcompleteds as $compl) {
                $compl->random_response = $num++;
                $DB->update_record('individualfeedback_completed', $compl);
            }
        }
    }

    /**
     * Counts records from {individualfeedback_completed} table for a given feedback
     *
     * If $groupid or $this->courseid is set, the records are filtered by the group/course
     *
     * @param int $groupid
     * @return mixed array of found completeds otherwise false
     */
    public function count_completed_responses($groupid = 0) {
        global $DB;
        if (intval($groupid) > 0) {
            $query = "SELECT COUNT(DISTINCT fbc.id)
                        FROM {individualfeedback_completed} fbc, {groups_members} gm
                        WHERE fbc.individualfeedback = :individualfeedback
                            AND gm.groupid = :groupid
                            AND fbc.userid = gm.userid";
        } else if ($this->courseid) {
            $query = "SELECT COUNT(fbc.id)
                        FROM {individualfeedback_completed} fbc
                        WHERE fbc.individualfeedback = :individualfeedback
                            AND fbc.courseid = :courseid";
        } else {
            $query = "SELECT COUNT(fbc.id) FROM {individualfeedback_completed} fbc WHERE fbc.individualfeedback = :individualfeedback";
        }
        $params = ['individualfeedback' => $this->individualfeedback->id, 'groupid' => $groupid, 'courseid' => $this->courseid];
        return $DB->get_field_sql($query, $params);
    }

    /**
     * For the frontpage feedback returns the list of courses with at least one completed feedback
     *
     * @return array id=>name pairs of courses
     */
    public function get_completed_courses() {
        global $DB;

        if ($this->get_individualfeedback()->course != SITEID) {
            return [];
        }

        if ($this->allcourses !== null) {
            return $this->allcourses;
        }

        $courseselect = "SELECT fbc.courseid
            FROM {individualfeedback_completed} fbc
            WHERE fbc.individualfeedback = :individualfeedbackid";

        $ctxselect = context_helper::get_preload_record_columns_sql('ctx');

        $sql = 'SELECT c.id, c.shortname, c.fullname, c.idnumber, c.visible, '. $ctxselect. '
                FROM {course} c
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = :contextcourse
                WHERE c.id IN ('. $courseselect.') ORDER BY c.sortorder';
        $list = $DB->get_records_sql($sql, ['contextcourse' => CONTEXT_COURSE, 'individualfeedbackid' => $this->get_individualfeedback()->id]);

        $this->allcourses = array();
        foreach ($list as $course) {
            context_helper::preload_from_record($course);
            if (!$course->visible &&
                !has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id), $this->userid)) {
                // Do not return courses that current user can not see.
                continue;
            }
            $label = get_course_display_name_for_list($course);
            $this->allcourses[$course->id] = $label;
        }
        return $this->allcourses;
    }

    /**
     * Get all groups and items in this individualfeedback or this template
     * @return array of objects from individualfeedback_item with an additional attribute 'itemnr'
     */
    public function get_groups_and_items() {
        global $DB;
        if (!isset($this->groupeditems) || $this->groupeditems === null) {
            $this->groupeditems = array();
            if ($this->allitems === null) {
                $this->allitems = $this->get_items();
            }

            $ingroup = false;
            $idx = 1;
            foreach ($this->allitems as $id => $item) {
                if ($item->typ == 'questiongroup') {
                    $ingroup = true;
                }

                if ($ingroup) {
                    $this->groupeditems[$id] = $item;
                    $this->groupeditems[$id]->itemnr = $item->hasvalue ? ($idx++) : null;
                }

                if ($item->typ == 'questiongroupend') {
                    $ingroup = false;
                }
            }
        }

        return $this->groupeditems;
    }
}
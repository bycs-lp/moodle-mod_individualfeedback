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
 * individualfeedback external API
 *
 * @package    mod_individualfeedback
 * @category   external
 * @copyright  2017 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.3
 */

use mod_individualfeedback\external\individualfeedback_summary_exporter;
use mod_individualfeedback\external\individualfeedback_completedtmp_exporter;
use mod_individualfeedback\external\individualfeedback_item_exporter;
use mod_individualfeedback\external\individualfeedback_valuetmp_exporter;
use mod_individualfeedback\external\individualfeedback_value_exporter;
use mod_individualfeedback\external\individualfeedback_completed_exporter;
use core_external\external_api;
use core_external\external_function_parameters;
use core_external\external_multiple_structure;
use core_external\external_single_structure;
use core_external\external_value;
use core_external\external_warnings;
use core_external\util;

/**
 * individualfeedback external functions
 *
 * @package    mod_individualfeedback
 * @category   external
 * @copyright  2017 Juan Leyva <juan@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      Moodle 3.3
 */
class mod_individualfeedback_external extends external_api {

    /**
     * Describes the parameters for get_individualfeedbacks_by_courses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_individualfeedbacks_by_courses_parameters() {
        return new external_function_parameters (
            array(
                'courseids' => new external_multiple_structure(
                    new external_value(PARAM_INT, 'Course id'), 'Array of course ids', VALUE_DEFAULT, array()
                ),
            )
        );
    }

    /**
     * Returns a list of individualfeedbacks in a provided list of courses.
     * If no list is provided all individualfeedbacks that the user can view will be returned.
     *
     * @param array $courseids course ids
     * @return array of warnings and individualfeedbacks
     * @since Moodle 3.3
     */
    public static function get_individualfeedbacks_by_courses($courseids = array()) {
        global $PAGE;

        $warnings = array();
        $returnedindividualfeedbacks = array();

        $params = array(
            'courseids' => $courseids,
        );
        $params = self::validate_parameters(self::get_individualfeedbacks_by_courses_parameters(), $params);

        $mycourses = array();
        if (empty($params['courseids'])) {
            $mycourses = enrol_get_my_courses();
            $params['courseids'] = array_keys($mycourses);
        }

        // Ensure there are courseids to loop through.
        if (!empty($params['courseids'])) {

            list($courses, $warnings) = util::validate_courses($params['courseids'], $mycourses);
            $output = $PAGE->get_renderer('core');

            // Get the individualfeedbacks in this course, this function checks users visibility permissions.
            // We can avoid then additional validate_context calls.
            $individualfeedbacks = get_all_instances_in_courses("individualfeedback", $courses);
            foreach ($individualfeedbacks as $individualfeedback) {

                $context = context_module::instance($individualfeedback->coursemodule);

                // Remove fields that are not from the individualfeedback (added by get_all_instances_in_courses).
                unset($individualfeedback->coursemodule, $individualfeedback->context, $individualfeedback->visible, $individualfeedback->section, $individualfeedback->groupmode,
                        $individualfeedback->groupingid);

                // Check permissions.
                if (!has_capability('mod/individualfeedback:edititems', $context)) {
                    // Don't return the optional properties.
                    $properties = individualfeedback_summary_exporter::properties_definition();
                    foreach ($properties as $property => $config) {
                        if (!empty($config['optional'])) {
                            unset($individualfeedback->{$property});
                        }
                    }
                }
                $exporter = new individualfeedback_summary_exporter($individualfeedback, array('context' => $context));
                $returnedindividualfeedbacks[] = $exporter->export($output);
            }
        }

        $result = array(
            'individualfeedbacks' => $returnedindividualfeedbacks,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_individualfeedbacks_by_courses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_individualfeedbacks_by_courses_returns() {
        return new external_single_structure(
            array(
                'individualfeedbacks' => new external_multiple_structure(
                    individualfeedback_summary_exporter::get_read_structure()
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Utility function for validating a individualfeedback.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @return array array containing the individualfeedback persistent, course, context and course module objects
     * @since  Moodle 3.3
     */
    protected static function validate_individualfeedback($individualfeedbackid) {
        global $DB, $USER;

        // Request and permission validation.
        $individualfeedback = $DB->get_record('individualfeedback', array('id' => $individualfeedbackid), '*', MUST_EXIST);
        list($course, $cm) = get_course_and_cm_from_instance($individualfeedback, 'individualfeedback');

        $context = context_module::instance($cm->id);
        self::validate_context($context);

        return array($individualfeedback, $course, $cm, $context);
    }

    /**
     * Utility function for validating access to individualfeedback.
     *
     * @param  stdClass   $individualfeedback individualfeedback object
     * @param  stdClass   $course   course object
     * @param  stdClass   $cm       course module
     * @param  stdClass   $context  context object
     * @throws moodle_exception
     * @return individualfeedback_completion individualfeedback completion instance
     * @since  Moodle 3.3
     */
    protected static function validate_individualfeedback_access($individualfeedback,  $course, $cm, $context, $checksubmit = false) {
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        if (!$individualfeedbackcompletion->can_complete()) {
            throw new required_capability_exception($context, 'mod/individualfeedback:complete', 'nopermission', '');
        }

        if (!$individualfeedbackcompletion->is_open()) {
            throw new moodle_exception('individualfeedback_is_not_open', 'individualfeedback');
        }

        if ($individualfeedbackcompletion->is_empty()) {
            throw new moodle_exception('no_items_available_yet', 'individualfeedback');
        }

        if ($checksubmit && !$individualfeedbackcompletion->can_submit()) {
            throw new moodle_exception('this_individualfeedback_is_already_submitted', 'individualfeedback');
        }
        return $individualfeedbackcompletion;
    }

    /**
     * Describes the parameters for get_individualfeedback_access_information.
     *
     * @return external_external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_individualfeedback_access_information_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id.')
            )
        );
    }

    /**
     * Return access information for a given individualfeedback.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @return array of warnings and the access information
     * @since Moodle 3.3
     * @throws  moodle_exception
     */
    public static function get_individualfeedback_access_information($individualfeedbackid) {
        global $PAGE;

        $params = array(
            'individualfeedbackid' => $individualfeedbackid
        );
        $params = self::validate_parameters(self::get_individualfeedback_access_information_parameters(), $params);

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        $result = array();
        // Capabilities first.
        $result['canviewanalysis'] = $individualfeedbackcompletion->can_view_analysis();
        $result['cancomplete'] = $individualfeedbackcompletion->can_complete();
        $result['cansubmit'] = $individualfeedbackcompletion->can_submit();
        $result['candeletesubmissions'] = has_capability('mod/individualfeedback:deletesubmissions', $context);
        $result['canviewreports'] = has_capability('mod/individualfeedback:viewreports', $context);
        $result['canedititems'] = has_capability('mod/individualfeedback:edititems', $context);

        // Status information.
        $result['isempty'] = $individualfeedbackcompletion->is_empty();
        $result['isopen'] = $individualfeedbackcompletion->is_open();
        $anycourse = ($course->id == SITEID);
        $result['isalreadysubmitted'] = $individualfeedbackcompletion->is_already_submitted($anycourse);
        $result['isanonymous'] = $individualfeedbackcompletion->is_anonymous();

        $result['warnings'] = [];
        return $result;
    }

    /**
     * Describes the get_individualfeedback_access_information return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_individualfeedback_access_information_returns() {
        return new external_single_structure(
            array(
                'canviewanalysis' => new external_value(PARAM_BOOL, 'Whether the user can view the analysis or not.'),
                'cancomplete' => new external_value(PARAM_BOOL, 'Whether the user can complete the individualfeedback or not.'),
                'cansubmit' => new external_value(PARAM_BOOL, 'Whether the user can submit the individualfeedback or not.'),
                'candeletesubmissions' => new external_value(PARAM_BOOL, 'Whether the user can delete submissions or not.'),
                'canviewreports' => new external_value(PARAM_BOOL, 'Whether the user can view the individualfeedback reports or not.'),
                'canedititems' => new external_value(PARAM_BOOL, 'Whether the user can edit individualfeedback items or not.'),
                'isempty' => new external_value(PARAM_BOOL, 'Whether the individualfeedback has questions or not.'),
                'isopen' => new external_value(PARAM_BOOL, 'Whether the individualfeedback has active access time restrictions or not.'),
                'isalreadysubmitted' => new external_value(PARAM_BOOL, 'Whether the individualfeedback is already submitted or not.'),
                'isanonymous' => new external_value(PARAM_BOOL, 'Whether the individualfeedback is anonymous or not.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for view_individualfeedback.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function view_individualfeedback_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
                'moduleviewed' => new external_value(PARAM_BOOL, 'If we need to mark the module as viewed for completion',
                    VALUE_DEFAULT, false),
            )
        );
    }

    /**
     * Trigger the course module viewed event and update the module completion status.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @param bool $moduleviewed If we need to mark the module as viewed for completion
     * @return array of warnings and status result
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function view_individualfeedback($individualfeedbackid, $moduleviewed = false) {

        $params = array('individualfeedbackid' => $individualfeedbackid, 'moduleviewed' => $moduleviewed);
        $params = self::validate_parameters(self::view_individualfeedback_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        // Trigger module viewed event.
        $individualfeedbackcompletion->trigger_module_viewed();
        if ($params['moduleviewed']) {
            if (!$individualfeedbackcompletion->is_open()) {
                throw new moodle_exception('individualfeedback_is_not_open', 'individualfeedback');
            }
            // Mark activity viewed for completion-tracking.
            $individualfeedbackcompletion->set_module_viewed();
        }

        $result = array(
            'status' => true,
            'warnings' => $warnings,
        );
        return $result;
    }

    /**
     * Describes the view_individualfeedback return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function view_individualfeedback_returns() {
        return new external_single_structure(
            array(
                'status' => new external_value(PARAM_BOOL, 'status: true if success'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_current_completed_tmp.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_current_completed_tmp_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
            )
        );
    }

    /**
     * Returns the temporary completion record for the current user.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @return array of warnings and status result
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function get_current_completed_tmp($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::get_current_completed_tmp_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        if ($completed = $individualfeedbackcompletion->get_current_completed_tmp()) {
            $exporter = new individualfeedback_completedtmp_exporter($completed);
            return array(
                'individualfeedback' => $exporter->export($PAGE->get_renderer('core')),
                'warnings' => $warnings,
            );
        }
        throw new moodle_exception('not_started', 'individualfeedback');
    }

    /**
     * Describes the get_current_completed_tmp return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_current_completed_tmp_returns() {
        return new external_single_structure(
            array(
                'individualfeedback' => individualfeedback_completedtmp_exporter::get_read_structure(),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_items.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_items_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
            )
        );
    }

    /**
     * Returns the items (questions) in the given individualfeedback.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @return array of warnings and individualfeedbacks
     * @since Moodle 3.3
     */
    public static function get_items($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::get_items_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);

        $individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm, $course->id);
        $returneditems = array();
        if ($items = $individualfeedbackstructure->get_items()) {
            foreach ($items as $item) {
                $itemnumber = empty($item->itemnr) ? null : $item->itemnr;
                unset($item->itemnr);   // Added by the function, not part of the record.
                $exporter = new individualfeedback_item_exporter($item, array('context' => $context, 'itemnumber' => $itemnumber));
                $returneditems[] = $exporter->export($PAGE->get_renderer('core'));
            }
        }

        $result = array(
            'items' => $returneditems,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_items return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_items_returns() {
        return new external_single_structure(
            array(
                'items' => new external_multiple_structure(
                    individualfeedback_item_exporter::get_read_structure()
                ),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for launch_individualfeedback.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function launch_individualfeedback_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
            )
        );
    }

    /**
     * Starts or continues a individualfeedback submission
     *
     * @param array $individualfeedbackid individualfeedback instance id
     * @return array of warnings and launch information
     * @since Moodle 3.3
     */
    public static function launch_individualfeedback($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::launch_individualfeedback_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        // Check we can do a new submission (or continue an existing).
        $individualfeedbackcompletion = self::validate_individualfeedback_access($individualfeedback,  $course, $cm, $context, true);

        $gopage = $individualfeedbackcompletion->get_resume_page();
        if ($gopage === null) {
            $gopage = -1; // Last page.
        }

        $result = array(
            'gopage' => $gopage,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the launch_individualfeedback return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function launch_individualfeedback_returns() {
        return new external_single_structure(
            array(
                'gopage' => new external_value(PARAM_INT, 'The next page to go (-1 if we were already in the last page). 0 for first page.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_page_items.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_page_items_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
                'page' => new external_value(PARAM_INT, 'The page to get starting by 0'),
            )
        );
    }

    /**
     * Get a single individualfeedback page items.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @param int $page the page to get starting by 0
     * @return array of warnings and launch information
     * @since Moodle 3.3
     */
    public static function get_page_items($individualfeedbackid, $page) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid, 'page' => $page);
        $params = self::validate_parameters(self::get_page_items_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);

        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        $page = $params['page'];
        $pages = $individualfeedbackcompletion->get_pages();
        $pageitems = $pages[$page];
        $hasnextpage = $page < count($pages) - 1; // Until we complete this page we can not trust get_next_page().
        $hasprevpage = $page && ($individualfeedbackcompletion->get_previous_page($page, false) !== null);

        $returneditems = array();
        foreach ($pageitems as $item) {
            $itemnumber = empty($item->itemnr) ? null : $item->itemnr;
            unset($item->itemnr);   // Added by the function, not part of the record.
            $exporter = new individualfeedback_item_exporter($item, array('context' => $context, 'itemnumber' => $itemnumber));
            $returneditems[] = $exporter->export($PAGE->get_renderer('core'));
        }

        $result = array(
            'items' => $returneditems,
            'hasprevpage' => $hasprevpage,
            'hasnextpage' => $hasnextpage,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_page_items return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_page_items_returns() {
        return new external_single_structure(
            array(
                'items' => new external_multiple_structure(
                    individualfeedback_item_exporter::get_read_structure()
                ),
                'hasprevpage' => new external_value(PARAM_BOOL, 'Whether is a previous page.'),
                'hasnextpage' => new external_value(PARAM_BOOL, 'Whether there are more pages.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for process_page.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function process_page_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id.'),
                'page' => new external_value(PARAM_INT, 'The page being processed.'),
                'responses' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'name' => new external_value(PARAM_NOTAGS, 'The response name (usually type[index]_id).'),
                            'value' => new external_value(PARAM_RAW, 'The response value.'),
                        )
                    ), 'The data to be processed.', VALUE_DEFAULT, array()
                ),
                'goprevious' => new external_value(PARAM_BOOL, 'Whether we want to jump to previous page.', VALUE_DEFAULT, false),
            )
        );
    }

    /**
     * Process a jump between pages.
     *
     * @param array $individualfeedbackid individualfeedback instance id
     * @param array $page the page being processed
     * @param array $responses the responses to be processed
     * @param bool $goprevious whether we want to jump to previous page
     * @return array of warnings and launch information
     * @since Moodle 3.3
     */
    public static function process_page($individualfeedbackid, $page, $responses = [], $goprevious = false) {
        global $USER, $SESSION;

        $params = array('individualfeedbackid' => $individualfeedbackid, 'page' => $page, 'responses' => $responses, 'goprevious' => $goprevious);
        $params = self::validate_parameters(self::process_page_parameters(), $params);
        $warnings = array();
        $siteaftersubmit = $completionpagecontents = '';

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        // Check we can do a new submission (or continue an existing).
        $individualfeedbackcompletion = self::validate_individualfeedback_access($individualfeedback,  $course, $cm, $context, true);

        // Create the $_POST object required by the individualfeedback question engine.
        $_POST = array();
        foreach ($responses as $response) {
            // First check if we are handling array parameters.
            if (preg_match('/(.+)\[(.+)\]$/', $response['name'], $matches)) {
                $_POST[$matches[1]][$matches[2]] = $response['value'];
            } else {
                $_POST[$response['name']] = $response['value'];
            }
        }
        // Force fields.
        $_POST['id'] = $cm->id;
        $_POST['courseid'] = $course->id;
        $_POST['gopage'] = $params['page'];
        $_POST['_qf__mod_individualfeedback_complete_form'] = 1;

        // Determine where to go, backwards or forward.
        if (!$params['goprevious']) {
            $_POST['gonextpage'] = 1;   // Even if we are saving values we need this set.
            if ($individualfeedbackcompletion->get_next_page($params['page'], false) === null) {
                $_POST['savevalues'] = 1;   // If there is no next page, it means we are finishing the individualfeedback.
            }
        }

        // Ignore sesskey (deep in some APIs), the request is already validated.
        $USER->ignoresesskey = true;
        individualfeedback_init_individualfeedback_session();
        $SESSION->individualfeedback->is_started = true;

        $individualfeedbackcompletion->process_page($params['page'], $params['goprevious']);
        $completed = $individualfeedbackcompletion->just_completed();
        if ($completed) {
            $jumpto = 0;
            if ($individualfeedback->page_after_submit) {
                $completionpagecontents = $individualfeedbackcompletion->page_after_submit();
            }

            if ($individualfeedback->site_after_submit) {
                $siteaftersubmit = individualfeedback_encode_target_url($individualfeedback->site_after_submit);
            }
        } else {
            $jumpto = $individualfeedbackcompletion->get_jumpto();
        }

        $result = array(
            'jumpto' => $jumpto,
            'completed' => $completed,
            'completionpagecontents' => $completionpagecontents,
            'siteaftersubmit' => $siteaftersubmit,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the process_page return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function process_page_returns() {
        return new external_single_structure(
            array(
                'jumpto' => new external_value(PARAM_INT, 'The page to jump to.'),
                'completed' => new external_value(PARAM_BOOL, 'If the user completed the individualfeedback.'),
                'completionpagecontents' => new external_value(PARAM_RAW, 'The completion page contents.'),
                'siteaftersubmit' => new external_value(PARAM_RAW, 'The link (could be relative) to show after submit.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_analysis.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_analysis_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
                'groupid' => new external_value(PARAM_INT, 'Group id, 0 means that the function will determine the user group',
                                                VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Retrieves the individualfeedback analysis.
     *
     * @param array $individualfeedbackid individualfeedback instance id
     * @return array of warnings and launch information
     * @since Moodle 3.3
     */
    public static function get_analysis($individualfeedbackid, $groupid = 0) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid, 'groupid' => $groupid);
        $params = self::validate_parameters(self::get_analysis_parameters(), $params);
        $warnings = $itemsdata = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);

        // Check permissions.
        $individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm);
        if (!$individualfeedbackstructure->can_view_analysis()) {
            throw new required_capability_exception($context, 'mod/individualfeedback:viewanalysepage', 'nopermission', '');
        }

        $groupid = 0;

        // Summary data.
        $summary = new mod_individualfeedback\output\summary($individualfeedbackstructure, $groupid);
        $summarydata = $summary->export_for_template($PAGE->get_renderer('core'));

        // Get the items of the individualfeedback.
        $items = $individualfeedbackstructure->get_items(true);
        foreach ($items as $item) {
            $itemobj = individualfeedback_get_item_class($item->typ);
            $itemnumber = empty($item->itemnr) ? null : $item->itemnr;
            unset($item->itemnr);   // Added by the function, not part of the record.
            $exporter = new individualfeedback_item_exporter($item, array('context' => $context, 'itemnumber' => $itemnumber));

            $itemsdata[] = array(
                'item' => $exporter->export($PAGE->get_renderer('core')),
                'data' => $itemobj->get_analysed_for_external($item, $groupid),
            );
        }

        $result = array(
            'completedcount' => $summarydata->completedcount,
            'itemscount' => $summarydata->itemscount,
            'itemsdata' => $itemsdata,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_analysis return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_analysis_returns() {
        return new external_single_structure(
            array(
            'completedcount' => new external_value(PARAM_INT, 'Number of completed submissions.'),
            'itemscount' => new external_value(PARAM_INT, 'Number of items (questions).'),
            'itemsdata' => new external_multiple_structure(
                new external_single_structure(
                    array(
                        'item' => individualfeedback_item_exporter::get_read_structure(),
                        'data' => new external_multiple_structure(
                            new external_value(PARAM_RAW, 'The analysis data (can be json encoded)')
                        ),
                    )
                )
            ),
            'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_unfinished_responses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_unfinished_responses_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id.'),
            )
        );
    }

    /**
     * Retrieves responses from the current unfinished attempt.
     *
     * @param array $individualfeedbackid individualfeedback instance id
     * @return array of warnings and launch information
     * @since Moodle 3.3
     */
    public static function get_unfinished_responses($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::get_unfinished_responses_parameters(), $params);
        $warnings = $itemsdata = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        $responses = array();
        $unfinished = $individualfeedbackcompletion->get_unfinished_responses();
        foreach ($unfinished as $u) {
            $exporter = new individualfeedback_valuetmp_exporter($u);
            $responses[] = $exporter->export($PAGE->get_renderer('core'));
        }

        $result = array(
            'responses' => $responses,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_unfinished_responses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_unfinished_responses_returns() {
        return new external_single_structure(
            array(
            'responses' => new external_multiple_structure(
                individualfeedback_valuetmp_exporter::get_read_structure()
            ),
            'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_finished_responses.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_finished_responses_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id.'),
            )
        );
    }

    /**
     * Retrieves responses from the last finished attempt.
     *
     * @param array $individualfeedbackid individualfeedback instance id
     * @return array of warnings and the responses
     * @since Moodle 3.3
     */
    public static function get_finished_responses($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::get_finished_responses_parameters(), $params);
        $warnings = $itemsdata = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        $responses = array();
        // Load and get the responses from the last completed individualfeedback.
        $individualfeedbackcompletion->find_last_completed();
        $unfinished = $individualfeedbackcompletion->get_finished_responses();
        foreach ($unfinished as $u) {
            $exporter = new individualfeedback_value_exporter($u);
            $responses[] = $exporter->export($PAGE->get_renderer('core'));
        }

        $result = array(
            'responses' => $responses,
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_finished_responses return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_finished_responses_returns() {
        return new external_single_structure(
            array(
            'responses' => new external_multiple_structure(
                individualfeedback_value_exporter::get_read_structure()
            ),
            'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_non_respondents.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_non_respondents_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
                'groupid' => new external_value(PARAM_INT, 'Group id, 0 means that the function will determine the user group.',
                                                VALUE_DEFAULT, 0),
                'sort' => new external_value(PARAM_ALPHA, 'Sort param, must be firstname, lastname or lastaccess (default).',
                                                VALUE_DEFAULT, 'lastaccess'),
                'page' => new external_value(PARAM_INT, 'The page of records to return.', VALUE_DEFAULT, 0),
                'perpage' => new external_value(PARAM_INT, 'The number of records to return per page.', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Retrieves a list of students who didn't submit the individualfeedback.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @param int $groupid Group id, 0 means that the function will determine the user group'
     * @param str $sort sort param, must be firstname, lastname or lastaccess (default)
     * @param int $page the page of records to return
     * @param int $perpage the number of records to return per page
     * @return array of warnings and users ids
     * @since Moodle 3.3
     */
    public static function get_non_respondents($individualfeedbackid, $groupid = 0, $sort = 'lastaccess', $page = 0, $perpage = 0) {
        global $CFG;
        require_once($CFG->dirroot . '/mod/individualfeedback/lib.php');

        $params = array('individualfeedbackid' => $individualfeedbackid, 'groupid' => $groupid, 'sort' => $sort, 'page' => $page, 'perpage' => $perpage);
        $params = self::validate_parameters(self::get_non_respondents_parameters(), $params);
        $warnings = $nonrespondents = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);

        if ($individualfeedback->anonymous != INDIVIDUALFEEDBACK_ANONYMOUS_NO || $individualfeedback->course == SITEID) {
            throw new moodle_exception('anonymous', 'individualfeedback');
        }

        // Check permissions.
        require_capability('mod/individualfeedback:viewreports', $context);

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                // Determine is the group is visible to user (this is particullary for the group 0 -> all groups).
                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        if ($params['sort'] !== 'firstname' && $params['sort'] !== 'lastname' && $params['sort'] !== 'lastaccess') {
            throw new invalid_parameter_exception('Invalid sort param, must be firstname, lastname or lastaccess.');
        }

        // Check if we are page filtering.
        if ($params['perpage'] == 0) {
            $page = $params['page'];
            $perpage = INDIVIDUALFEEDBACK_DEFAULT_PAGE_COUNT;
        } else {
            $perpage = $params['perpage'];
            $page = $perpage * $params['page'];
        }
        $users = individualfeedback_get_incomplete_users($cm, $groupid, $params['sort'], $page, $perpage, true);
        foreach ($users as $user) {
            $nonrespondents[] = [
                'courseid' => $course->id,
                'userid'   => $user->id,
                'fullname' => fullname($user),
                'started'  => $user->individualfeedbackstarted
            ];
        }

        $result = array(
            'users' => $nonrespondents,
            'total' => individualfeedback_count_incomplete_users($cm, $groupid),
            'warnings' => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_non_respondents return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_non_respondents_returns() {
        return new external_single_structure(
            array(
                'users' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'courseid' => new external_value(PARAM_INT, 'Course id'),
                            'userid' => new external_value(PARAM_INT, 'The user id'),
                            'fullname' => new external_value(PARAM_TEXT, 'User full name'),
                            'started' => new external_value(PARAM_BOOL, 'If the user has started the attempt'),
                        )
                    )
                ),
                'total' => new external_value(PARAM_INT, 'Total number of non respondents'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_responses_analysis.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_responses_analysis_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
                'groupid' => new external_value(PARAM_INT, 'Group id, 0 means that the function will determine the user group',
                                                VALUE_DEFAULT, 0),
                'page' => new external_value(PARAM_INT, 'The page of records to return.', VALUE_DEFAULT, 0),
                'perpage' => new external_value(PARAM_INT, 'The number of records to return per page', VALUE_DEFAULT, 0),
            )
        );
    }

    /**
     * Return the individualfeedback user responses.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @param int $groupid Group id, 0 means that the function will determine the user group
     * @param int $page the page of records to return
     * @param int $perpage the number of records to return per page
     * @return array of warnings and users attemps and responses
     * @throws moodle_exception
     * @since Moodle 3.3
     */
    public static function get_responses_analysis($individualfeedbackid, $groupid = 0, $page = 0, $perpage = 0) {

        $params = array('individualfeedbackid' => $individualfeedbackid, 'groupid' => $groupid, 'page' => $page, 'perpage' => $perpage);
        $params = self::validate_parameters(self::get_responses_analysis_parameters(), $params);
        $warnings = $itemsdata = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);

        // Check permissions.
        require_capability('mod/individualfeedback:viewreports', $context);

        if (!empty($params['groupid'])) {
            $groupid = $params['groupid'];
            // Determine is the group is visible to user.
            if (!groups_group_visible($groupid, $course, $cm)) {
                throw new moodle_exception('notingroup');
            }
        } else {
            // Check to see if groups are being used here.
            if ($groupmode = groups_get_activity_groupmode($cm)) {
                $groupid = groups_get_activity_group($cm);
                // Determine is the group is visible to user (this is particullary for the group 0 -> all groups).
                if (!groups_group_visible($groupid, $course, $cm)) {
                    throw new moodle_exception('notingroup');
                }
            } else {
                $groupid = 0;
            }
        }

        $individualfeedbackstructure = new mod_individualfeedback_structure($individualfeedback, $cm, $course->id);
        $responsestable = new mod_individualfeedback_responses_table($individualfeedbackstructure, $groupid);
        // Ensure responses number is correct prior returning them.
        $individualfeedbackstructure->shuffle_anonym_responses();
        $anonresponsestable = new mod_individualfeedback_responses_anon_table($individualfeedbackstructure, $groupid);

        $result = array(
            'attempts'          => $responsestable->export_external_structure($params['page'], $params['perpage']),
            'totalattempts'     => $responsestable->get_total_responses_count(),
            'anonattempts'      => $anonresponsestable->export_external_structure($params['page'], $params['perpage']),
            'totalanonattempts' => $anonresponsestable->get_total_responses_count(),
            'warnings'       => $warnings
        );
        return $result;
    }

    /**
     * Describes the get_responses_analysis return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_responses_analysis_returns() {
        $responsestructure = new external_multiple_structure(
            new external_single_structure(
                array(
                    'id' => new external_value(PARAM_INT, 'Response id'),
                    'name' => new external_value(PARAM_RAW, 'Response name'),
                    'printval' => new external_value(PARAM_RAW, 'Response ready for output'),
                    'rawval' => new external_value(PARAM_RAW, 'Response raw value'),
                )
            )
        );

        return new external_single_structure(
            array(
                'attempts' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Completed id'),
                            'courseid' => new external_value(PARAM_INT, 'Course id'),
                            'userid' => new external_value(PARAM_INT, 'User who responded'),
                            'timemodified' => new external_value(PARAM_INT, 'Time modified for the response'),
                            'fullname' => new external_value(PARAM_TEXT, 'User full name'),
                            'responses' => $responsestructure
                        )
                    )
                ),
                'totalattempts' => new external_value(PARAM_INT, 'Total responses count.'),
                'anonattempts' => new external_multiple_structure(
                    new external_single_structure(
                        array(
                            'id' => new external_value(PARAM_INT, 'Completed id'),
                            'courseid' => new external_value(PARAM_INT, 'Course id'),
                            'number' => new external_value(PARAM_INT, 'Response number'),
                            'responses' => $responsestructure
                        )
                    )
                ),
                'totalanonattempts' => new external_value(PARAM_INT, 'Total anonymous responses count.'),
                'warnings' => new external_warnings(),
            )
        );
    }

    /**
     * Describes the parameters for get_last_completed.
     *
     * @return external_function_parameters
     * @since Moodle 3.3
     */
    public static function get_last_completed_parameters() {
        return new external_function_parameters (
            array(
                'individualfeedbackid' => new external_value(PARAM_INT, 'individualfeedback instance id'),
            )
        );
    }

    /**
     * Retrieves the last completion record for the current user.
     *
     * @param int $individualfeedbackid individualfeedback instance id
     * @return array of warnings and the last completed record
     * @since Moodle 3.3
     * @throws moodle_exception
     */
    public static function get_last_completed($individualfeedbackid) {
        global $PAGE;

        $params = array('individualfeedbackid' => $individualfeedbackid);
        $params = self::validate_parameters(self::get_last_completed_parameters(), $params);
        $warnings = array();

        list($individualfeedback, $course, $cm, $context) = self::validate_individualfeedback($params['individualfeedbackid']);
        $individualfeedbackcompletion = new mod_individualfeedback_completion($individualfeedback, $cm, $course->id);

        if ($individualfeedbackcompletion->is_anonymous()) {
             throw new moodle_exception('anonymous', 'individualfeedback');
        }
        if ($completed = $individualfeedbackcompletion->find_last_completed()) {
            $exporter = new individualfeedback_completed_exporter($completed);
            return array(
                'completed' => $exporter->export($PAGE->get_renderer('core')),
                'warnings' => $warnings,
            );
        }
        throw new moodle_exception('not_completed_yet', 'individualfeedback');
    }

    /**
     * Describes the get_last_completed return value.
     *
     * @return external_single_structure
     * @since Moodle 3.3
     */
    public static function get_last_completed_returns() {
        return new external_single_structure(
            array(
                'completed' => individualfeedback_completed_exporter::get_read_structure(),
                'warnings' => new external_warnings(),
            )
        );
    }
}

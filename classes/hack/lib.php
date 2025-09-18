<?php 

namespace mod_individualfeedback\hack;

class lib extends mbs_hack
{
    /**
     * creates a new template-record.
     *
     * @param int $courseid
     * @param string $name the name of template shown in the templatelist
     * @param int $ispublic 0:privat 1:public
     * @return stdClass the new template
     * @global object
     */
    public static function individualfeedback_create_template($courseid, $name, $ispublic = 0) {
        global $DB, $USER;

        $templ = new \stdClass();
        $templ->course   = ($ispublic ? 0 : $courseid);
        $templ->name     = $name;
        $templ->ispublic = $ispublic;
        $templ->userid = $USER->id;

        $templid = $DB->insert_record('individualfeedback_template', $templ);
        return $DB->get_record('individualfeedback_template', array('id'=>$templid));
    }


    /**
     * deletes an item and also deletes all related values
     *
     * @global object
     * @uses CONTEXT_MODULE
     * @param int $itemid
     * @param boolean $renumber should the kept items renumbered Yes/No
     * @param object $template if the template is given so the items are bound to it
     * @return void
     */
    public static function individualfeedback_delete_item($itemid, $renumber = true, $template = false) {
        global $DB;

        // SFSUBM-27 - Make sure it isn't deleted by individualfeedback_delete_group_items yet.
        if (!$item = $DB->get_record('individualfeedback_item', array('id' => $itemid))) {
            return;
        }

        // If we remove a group, make sure all group questions get deleted as well.
        if ($item->typ == 'questiongroup') {
            individualfeedback_delete_group_items($item);
        }

        //deleting the files from the item
        $fs = get_file_storage();

        if ($template) {
            if ($template->ispublic) {
                $context = \context_system::instance();
            } else {
                $context = \context_course::instance($template->course);
            }
            $templatefiles = $fs->get_area_files($context->id,
                                        'mod_individualfeedback',
                                        'template',
                                        $item->id,
                                        "id",
                                        false);

            if ($templatefiles) {
                $fs->delete_area_files($context->id, 'mod_individualfeedback', 'template', $item->id);
            }
        } else {
            if (!$cm = get_coursemodule_from_instance('individualfeedback', $item->feedback)) {
                return false;
            }
            $context = \context_module::instance($cm->id);

            $itemfiles = $fs->get_area_files($context->id,
                                        'mod_individualfeedback',
                                        'item',
                                        $item->id,
                                        "id", false);

            if ($itemfiles) {
                $fs->delete_area_files($context->id, 'mod_individualfeedback', 'item', $item->id);
            }
        }

        $DB->delete_records("individualfeedback_value", array("item"=>$itemid));
        $DB->delete_records("individualfeedback_valuetmp", array("item"=>$itemid));

        //remove all depends
        $DB->set_field('individualfeedback_item', 'dependvalue', '', array('dependitem'=>$itemid));
        $DB->set_field('individualfeedback_item', 'dependitem', 0, array('dependitem'=>$itemid));

        $DB->delete_records("individualfeedback_item", array("id"=>$itemid));
        if ($renumber) {
            individualfeedback_renumber_items($item->feedback);
        }
    }

    /**
     * get the values of an item depending on the given groupid.
     * if the individualfeedback is anonymous so the values are shuffled
     *
     * @global object
     * @global object
     * @param object $item
     * @param int $groupid
     * @param int $courseid
     * @param bool $ignore_empty if this is set true so empty values are not delivered
     * @return array the value-records
     */
    public static function individualfeedback_get_group_values($item,
        $groupid = false,
        $courseid = false,
        $negative_formulated = false,
        $ignore_empty = false,
        $selfassessment = false) {

        global $CFG, $DB, $USER;

        // Get the values except for the self assessment values.
        $params = array('selfassessment' => (int) $selfassessment);
        if ($ignore_empty) {
            $value = $DB->sql_compare_text('value');
            $ignore_empty_select = "AND $value != :emptyvalue AND $value != :zerovalue";
            $params += array('emptyvalue' => '', 'zerovalue' => '0');
        } else {
            $ignore_empty_select = "";
        }

        if ($courseid) {
            $select = "item = :itemid AND course_id = :courseid ".$ignore_empty_select;
            $params += array('itemid' => $item->id, 'courseid' => $courseid);
        } else {
            $select = "item = :itemid ".$ignore_empty_select;
            $params += array('itemid' => $item->id);
        }
        $sql = "SELECT iv.*
        FROM {individualfeedback_value} iv
        JOIN {individualfeedback_completed} ic ON iv.completed = ic.id
        WHERE {$select}
        AND ic.selfassessment = :selfassessment";

        // SFSUBM-26 - only show own users selfassessment.
        if ($selfassessment) {
            $sql .= "AND userid = :userid ";
            $params['userid'] = individualfeedback_hash_userid($USER->id);
        }

        $values = $DB->get_records_sql($sql, $params);

        $params = array('id' => $item->feedback);
        if ($DB->get_field('individualfeedback', 'anonymous', $params) == INDIVIDUALFEEDBACK_ANONYMOUS_YES) {
            if (is_array($values)) {
                shuffle($values);
            }
        }

        return $values;
    }

    /**
     * load the available item plugins to use as dropdown-options
     *
     * @global object
     * @return array pluginnames as string
     */
    public static function individualfeedback_load_individualfeedback_items_options() {
        global $CFG;

        $feedback_options = array("pagebreak" => get_string('add_pagebreak', 'individualfeedback'));
        $feedback_options['questiongroup'] = get_string('questiongroup', 'individualfeedback');

        if (!$feedback_names = individualfeedback_load_individualfeedback_items('mod/individualfeedback/item')) {
            return array();
        }

        foreach ($feedback_names as $fn) {
            $feedback_options[$fn] = get_string($fn, 'individualfeedback');
        }
        asort($feedback_options);
        return $feedback_options;
    }

    /**
     * get the list of available templates.
     * if the $onlyown param is set true so only templates from own course will be served
     * this is important for droping templates
     *
     * @global object
     * @param object $course
     * @param string $onlyownorpublic
     * @return array the template recordsets
     */
    public static function individualfeedback_get_template_list($course, $onlyownorpublic = '') {
        global $DB, $CFG, $USER;

        switch($onlyownorpublic) {
            case '':
                $templates = $DB->get_records_select('individualfeedback_template',
                                                    'course = ? OR ispublic = 1',
                                                    array($course->id),
                                                    'name');
                break;
            case 'own':
                $templates = $DB->get_records('individualfeedback_template',
                                            array('course'=>$course->id),
                                            'name');
                break;
            case 'public':
                $templates = $DB->get_records('individualfeedback_template', array('ispublic'=>1), 'name');
                break;

            case 'private':
                $templates = $DB->get_records('individualfeedback_template', array('ispublic'=>2, 'userid'=>$USER->id), 'name');
                break;
        }
        return $templates;
    }
}

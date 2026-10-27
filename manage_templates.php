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
 * Manage the various templates available
 *
 * @author Peter Dias
 * @copyright 2021 Peter Dias
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package mod_individualfeedback
 */

require_once("../../config.php");
require_once("lib.php");

$id = required_param('id', PARAM_INT);
$templateid = optional_param('deletetemplate', 0, PARAM_INT);

list($course, $cm) = get_course_and_cm_from_cmid($id, 'individualfeedback');
$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/individualfeedback:edititems', $context);

$individualfeedback = $PAGE->activityrecord;
$systemcontext = context_system::instance();

$params = ['id' => $id];
$url = new moodle_url('/mod/individualfeedback/manage_templates.php', $params);

$PAGE->set_url($url);

$PAGE->set_heading($course->fullname);

/** @var \mod_individualfeedback\output\renderer $renderer */
$renderer = $PAGE->get_renderer('mod_individualfeedback');
$renderer->set_title(
        [format_string($individualfeedback->name), format_string($course->fullname)],
        get_string('templates', 'individualfeedback')
);

$PAGE->add_body_class('limitedwidth');

// Process template deletion.
if ($templateid) {
    require_sesskey();
    require_capability('mod/individualfeedback:deletetemplate', $context);
    $template = $DB->get_record('individualfeedback_template', ['id' => $templateid], '*', MUST_EXIST);

    // +++ MBS-Hack (nersesov) : private user templates (ispublic == 2) are owner-only; skip system checks for them.
    \mod_individualfeedback\hack\lib::enforce_private_template_ownership($template, $url);
    if ($template->ispublic == 1) { // MBS-Hack: was `if ($template->ispublic)` — ispublic == 2 means private user template.
    // --- MBS-Hack
        require_capability('mod/individualfeedback:createpublictemplate', $systemcontext);
        require_capability('mod/individualfeedback:deletetemplate', $systemcontext);
    }

    individualfeedback_delete_template($template);
    $successurl = new moodle_url('/mod/individualfeedback/manage_templates.php', ['id' => $id]);
    redirect($url, get_string('template_deleted', 'individualfeedback'), null, \core\output\notification::NOTIFY_SUCCESS);
}
$PAGE->activityheader->set_attrs([
    "hidecompletion" => true,
    "description" => ''
]);
echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('templates', 'mod_individualfeedback'));

// First we get the course templates.
$templates = individualfeedback_get_template_list($course, 'own');
echo $OUTPUT->box_start('coursetemplates');
echo $OUTPUT->heading(get_string('coursetemplates', 'mod_individualfeedback'), 3);

$baseurl = new moodle_url('/mod/individualfeedback/use_templ.php', $params);
$tablecourse = new mod_individualfeedback_templates_table('individualfeedback_template_course_table', $baseurl);
$tablecourse->display($templates);
echo $OUTPUT->box_end();

// +++ MBS-Hack (nersesov) : private user templates section.
echo \mod_individualfeedback\hack\lib::render_private_templates_section($course, $params);
// --- MBS-Hack

$templates = individualfeedback_get_template_list($course, 'public');
echo $OUTPUT->box_start('publictemplates');
echo $OUTPUT->heading(get_string('sitetemplates', 'mod_individualfeedback'), 3);
$tablepublic = new mod_individualfeedback_templates_table('individualfeedback_template_public_table', $baseurl);
$tablepublic->display($templates);
echo $OUTPUT->box_end();
echo $OUTPUT->footer();

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
 * Strings for component 'individualfeedback', language 'en', branch 'MOODLE_20_STABLE'
 *
 * @package mod_individualfeedback
 * @copyright 1999 onwards Martin Dougiamas  {@link http://moodle.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['add_item'] = 'Add question';
$string['add_pagebreak'] = 'Add a page break';
$string['adjustment'] = 'Adjustment';
$string['after_submit'] = 'After submission';
$string['allowfullanonymous'] = 'Allow full anonymous';
$string['analysis'] = 'Analysis';
$string['anonymous'] = 'Anonymous';
$string['anonymous_edit'] = 'Record user names';
$string['anonymous_entries'] = 'Anonymous entries ({$a})';
$string['anonymous_user'] = 'Anonymous user';
$string['answerquestions'] = 'Answer the questions';
$string['append_new_items'] = 'Keep existing questions and add new questions at the end';
$string['autonumbering'] = 'Auto number questions';
$string['autonumbering_help'] = 'Enables or disables automated numbers for each question';
$string['availableforallcourses'] = 'Available for all courses';
$string['average'] = 'Average';
$string['bold'] = 'Bold';
$string['calendarend'] = '{$a} closes';
$string['calendarstart'] = '{$a} opens';
$string['cannotaccess'] = 'You can only access this individualfeedback from a course';
$string['cannotcreatepagebreak'] = 'A page break cannot be added at the beginning, and only one page break can be added at the end.';
$string['cannotsavetempl'] = 'Saving templates is not allowed';
$string['captcha'] = 'Captcha';
$string['captchanotset'] = 'Captcha hasn\'t been set.';
$string['closebeforeopen'] = 'You have specified an end date before the start date.';
$string['completed_individualfeedbacks'] = 'Submitted answers';
$string['complete_the_form'] = 'Answer the questions';
$string['completed'] = 'Completed';
$string['completedon'] = 'Completed on {$a}';
$string['completiondetail:submit'] = 'Submit individualfeedback';
$string['completionsubmit'] = 'Submit individualfeedback';
$string['configallowfullanonymous'] = 'If set to \'yes\', users can complete a individualfeedback activity on the site home without being required to log in.';
$string['confirmdeleteentry'] = 'Are you sure you want to delete this entry?';
$string['confirmdeleteitem'] = 'Are you sure you want to delete this element?';
$string['confirmdeletetemplate'] = 'Are you sure you want to delete this template?';
$string['confirmusetemplate'] = 'Are you sure you want to use this template?';
$string['continue_the_form'] = 'Continue answering the questions';
$string['count_of_nums'] = 'Count of numbers';
$string['courseid'] = 'Course ID';
$string['coursetemplates'] = 'Course templates';
$string['creating_templates'] = 'Save these questions as a new template';
$string['delete_entry'] = 'Delete entry';
$string['delete_item'] = 'Delete question';
$string['delete_old_items'] = 'Override existing questions';
$string['delete_pagebreak'] = 'Delete page break';
$string['delete_template'] = 'Delete template';
$string['delete_templates'] = 'Delete template...';
$string['depending'] = 'Dependencies';
$string['depending_help'] = 'It is possible to show an item depending on the value of another item.<br />
<strong>Here is an example.</strong><br />
<ul>
<li>First, create an item on which another item will depend on.</li>
<li>Next, add a pagebreak.</li>
<li>Then add the items dependant on the value of the item created before. Choose the item from the list labelled "Dependence item" and write the required value in the textbox labelled "Dependence value".</li>
</ul>
<strong>The item structure should look like this.</strong>
<ol>
<li>Item Q: Do you have a car? A: yes/no</li>
<li>Pagebreak</li>
<li>Item Q: What colour is your car?<br />
(this item depends on item 1 with value = yes)</li>
<li>Item Q: Why don\'t you have a car?<br />
(this item depends on item 1 with value = no)</li>
<li> ... other items</li>
</ol>';
$string['dependitem'] = 'Dependence item';
$string['dependvalue'] = 'Dependence value';
$string['description'] = 'Description';
$string['do_not_analyse_empty_submits'] = 'Omit empty submits in analysis';
$string['dropdown'] = 'Multiple choice - single answer allowed (drop-down menu)';
$string['dropdownlist'] = 'Multiple choice - single answer (drop-down menu)';
$string['dropdownrated'] = 'Drop-down menu (rated)';
$string['dropdown_values'] = 'Answers';
$string['drop_individualfeedback'] = 'Remove from this course';
$string['duedate'] = 'Due date';
$string['edit_item'] = 'Edit question';
$string['edit_items'] = 'Edit questions';
$string['email_notification'] = 'Enable notification of submissions';
$string['email_notification_help'] = 'If enabled, teachers will receive notification of individualfeedback submissions.';
$string['emailteachermail'] = '{$a->username} has completed individualfeedback activity : \'{$a->individualfeedback}\'

You can view it here:

{$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} has completed individualfeedback activity : <i>\'{$a->individualfeedback}\'</i>.</p>
<p>It is <a href="{$a->url}">available on the site</a>.</p>';
$string['entries_saved'] = 'Your answers have been saved. Thank you.';
$string['export_questions'] = 'Export questions';
$string['export_to_excel'] = 'Export to Excel';
$string['eventresponsedeleted'] = 'Response deleted';
$string['eventresponsesubmitted'] = 'Response submitted';
$string['individualfeedbackcompleted'] = '{$a->username} completed {$a->individualfeedbackname}';
$string['individualfeedback:addinstance'] = 'Add a new individualfeedback';
$string['individualfeedbackclose'] = 'Allow answers until';
$string['individualfeedback:complete'] = 'Complete a individualfeedback';
$string['individualfeedback:createprivatetemplate'] = 'Create private template';
$string['individualfeedback:createpublictemplate'] = 'Create public template';
$string['individualfeedback:deletesubmissions'] = 'Delete completed submissions';
$string['individualfeedback:deletetemplate'] = 'Delete template';
$string['individualfeedback:edititems'] = 'Edit items';
$string['individualfeedback_is_not_for_anonymous'] = 'Individualfeedback is not for anonymous';
$string['individualfeedback_is_not_open'] = 'The individualfeedback is not open';
$string['individualfeedback:mapcourse'] = 'Map courses to global individualfeedbacks';
$string['individualfeedbackopen'] = 'Allow answers from';
$string['individualfeedback:receivemail'] = 'Receive email notification';
$string['individualfeedback:view'] = 'View a individualfeedback';
$string['individualfeedback:viewanalysepage'] = 'View the analysis page after submit';
$string['individualfeedback:viewreports'] = 'View reports';
$string['individualfeedbackupdated'] = 'Individualfeedback updated.';
$string['file'] = 'File';
$string['filter_by_course'] = 'Filter by course';
$string['handling_error'] = 'Error occurred in individualfeedback module action handling';
$string['hide_no_select_option'] = 'Hide the "Not selected" option';
$string['horizontal'] = 'Horizontal';
$string['check'] = 'Multiple choice - multiple answers';
$string['checkbox'] = 'Multiple choice - multiple answers allowed (check boxes)';
$string['check_values'] = 'Possible responses';
$string['choosefile'] = 'Choose a file';
$string['chosen_individualfeedback_response'] = 'Chosen individualfeedback response';
$string['downloadresponseas'] = 'Download all responses as:';
$string['importfromthisfile'] = 'Import from this file';
$string['import_questions'] = 'Import questions';
$string['import_successfully'] = 'Import successfully';
$string['includeuserinrecipientslist'] = 'Include {$a} in the list of recipients';
$string['indicator:cognitivedepth'] = 'Individualfeedback cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a Individualfeedback activity.';
$string['indicator:cognitivedepthdef'] = 'Individualfeedback cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the Individualfeedback activities during this analysis interval (Levels = No view, View, Submit)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'Individualfeedback social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a Individualfeedback activity.';
$string['indicator:socialbreadthdef'] = 'Individualfeedback social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the Individualfeedback activities during this analysis interval (Levels = No participation, Participant alone, Participant with others)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['info'] = 'Information';
$string['infotype'] = 'Information type';
$string['insufficient_responses_for_this_group'] = 'There are insufficient responses for this group';
$string['insufficient_responses'] = 'insufficient responses';
$string['insufficient_responses_help'] = 'For the individualfeedback to be anonymous, there must be at least 2 responses.';
$string['item_label'] = 'Label';
$string['item_name'] = 'Question';
$string['label'] = 'Text and media area';
$string['labelcontents'] = 'Contents';
$string['mapcourseinfo'] = 'This is a site-wide individualfeedback that is available to all courses using the individualfeedback block. You can however limit the courses to which it will appear by mapping them. Search the course and map it to this individualfeedback.';
$string['mapcoursenone'] = 'No courses mapped. Individualfeedback available to all courses';
$string['mapcourse'] = 'Map individualfeedback to courses';
$string['mapcourse_help'] = 'By default, individualfeedback forms created on your homepage are available site-wide
and will appear in all courses using the individualfeedback block. You can force the individualfeedback form to appear by making it a sticky block or limit the courses in which a individualfeedback form will appear by mapping it to specific courses.';
$string['mapcourses'] = 'Map individualfeedback to courses';
$string['mappedcourses'] = 'Mapped courses';
$string['mappingchanged'] = 'Course mapping has been changed';
$string['minimal'] = 'Minimum';
$string['maximal'] = 'Maximum';
$string['messageprovider:message'] = 'Individualfeedback reminder';
$string['messageprovider:submission'] = 'Individualfeedback notifications';
$string['mode'] = 'Mode';
$string['modulename'] = 'Individualfeedback';
$string['modulename_help'] = '###### Key features
* Build surveys with a variety of question types, including multiple choice, scales, and open text
* Collect responses anonymously or with names
* View responses as a summary, a detailed analysis, or by individual student

###### Ways to use it
* Check for expectations and previous knowledge before starting a course
* Run an end-of-course evaluation to gather student individualfeedback
* Provide a safe, anonymous way for students to report concerns';
$string['modulename_link'] = 'mod/individualfeedback/view';
$string['modulename_summary'] = 'Create custom surveys using different question types.';
$string['modulenameplural'] = 'Individualfeedback';
$string['move_item'] = 'Move this question';
$string['multichoice'] = 'Multiple choice';
$string['multichoiceoption'] = '<span class="weight">({$a->weight}) </span>{$a->name}';
$string['multichoicerated'] = 'Multiple choice (rated)';
$string['multichoicetype'] = 'Multiple choice type';
$string['multichoice_values'] = 'Multiple choice values';
$string['multiplesubmit'] = 'Allow multiple submissions';
$string['multiplesubmit_help'] = 'If set to Yes:

* For anonymous surveys: participants can submit unlimited responses, and all responses will be recorded.
* For non-anonymous surveys: participants can submit unlimited responses, but only their latest response will be recorded.';
$string['name'] = 'Name';
$string['name_required'] = 'Name required';
$string['nameandlabelformat'] = '({$a->label}) {$a->name}';
$string['next_page'] = 'Next page';
$string['no_handler'] = 'No action handler exists for';
$string['no_itemlabel'] = 'No label';
$string['no_itemname'] = 'No itemname';
$string['no_items_available_yet'] = 'No questions have been set up yet';
$string['non_anonymous'] = 'User\'s name will be logged and shown with answers';
$string['non_anonymous_entries'] = 'Non anonymous entries ({$a})';
$string['non_respondents_students'] = 'Non-respondent students ({$a})';
$string['not_completed_yet'] = 'Not completed yet';
$string['not_started'] = 'Not started';
$string['no_templates_available_yet'] = 'No templates available yet';
$string['not_selected'] = 'Not selected';
$string['numberoutofrange'] = 'Number out of range';
$string['numeric'] = 'Numeric answer';
$string['numeric_range_from'] = 'Range from';
$string['numeric_range_to'] = 'Range to';
$string['of'] = 'of';
$string['oldvaluespreserved'] = 'All old questions and the assigned values will be preserved';
$string['oldvalueswillbedeleted'] = 'Current questions and all responses will be deleted.';
$string['only_one_captcha_allowed'] = 'Only one captcha is allowed in a individualfeedback';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['overview'] = 'Overview';
$string['page'] = 'Page';
$string['page-mod-individualfeedback-x'] = 'Any individualfeedback module page';
$string['page_after_submit'] = 'Completion message';
$string['pagebreak'] = 'Page break';
$string['pluginadministration'] = 'Individualfeedback administration';
$string['pluginname'] = 'Individualfeedback';
$string['position'] = 'Position';
$string['previous_page'] = 'Previous page';
$string['previewquestions'] = 'Preview questions';
$string['previewtemplate'] = 'Previewing \'{$a}\' template';
$string['privacy:metadata:completed'] = 'A record of the submissions to the individualfeedback';
$string['privacy:metadata:completed:anonymousresponse'] = 'Whether the submission is to be used anonymously.';
$string['privacy:metadata:completed:timemodified'] = 'The time when the submission was last modified.';
$string['privacy:metadata:completed:userid'] = 'The ID of the user who completed the individualfeedback activity.';
$string['privacy:metadata:completedtmp'] = 'A record of the submissions which are still in progress.';
$string['privacy:metadata:value'] = 'A record of the answer to a question.';
$string['privacy:metadata:value:value'] = 'The chosen answer.';
$string['privacy:metadata:valuetmp'] = 'A record of the answer to a question in a submission in progress.';
$string['question'] = 'Question';
$string['questionandsubmission'] = 'Question and submission settings';
$string['questionmoved'] = 'Question moved';
$string['questions'] = 'Questions';
$string['questionslimited'] = 'Showing only {$a} first questions, view individual answers or download table data to view all.';
$string['radio'] = 'Multiple choice - single answer';
$string['radio_values'] = 'Responses';
$string['ready_individualfeedbacks'] = 'Ready individualfeedbacks';
$string['required'] = 'Required';
$string['resetting_data'] = 'Responses';
$string['resetting_delete'] = 'Delete responses';
$string['resetting_individualfeedbacks'] = 'Resetting individualfeedbacks';
$string['responded'] = 'Responded';
$string['response_nr'] = 'Response number';
$string['responses'] = 'Responses';
$string['responsetime'] = 'Responses time';
$string['save_as_new_item'] = 'Save as new question';
$string['save_as_new_template'] = 'Save as template';
$string['save_entries'] = 'Submit your answers';
$string['save_item'] = 'Save question';
$string['saving_failed'] = 'Saving failed';
$string['search:activity'] = 'Individualfeedback - activity information';
$string['search_course'] = 'Search course';
$string['searchcourses'] = 'Search courses';
$string['searchcourses_help'] = 'Search for the code or name of the course(s) that you wish to associate with this individualfeedback.';
$string['send'] = 'Send';
$string['send_message'] = 'Send notification';
$string['show_all'] = 'Show all';
$string['show_analysepage_after_submit'] = 'Show analysis page';
$string['show_entries'] = 'Show responses';
$string['show_entry'] = 'Show response';
$string['show_nonrespondents'] = 'Show non-respondents';
$string['site_after_submit'] = 'Site after submit';
$string['sitetemplates'] = 'Site templates';
$string['sort_by_course'] = 'Sort by course';
$string['started'] = 'Started';
$string['startedon'] = 'Started on {$a}';
$string['subject'] = 'Subject';
$string['switch_item_to_not_required'] = 'Set as not required';
$string['switch_item_to_required'] = 'Set as required';
$string['template'] = 'Template';
$string['templates'] = 'Templates';
$string['template_deleted'] = 'Template deleted';
$string['template_saved'] = 'Template saved';
$string['textarea'] = 'Longer text answer';
$string['textarea_height'] = 'Number of lines';
$string['textarea_width'] = 'Width';
$string['textfield'] = 'Short text answer';
$string['textfield_maxlength'] = 'Maximum characters accepted';
$string['textfield_size'] = 'Textfield width';
$string['there_are_no_settings_for_recaptcha'] = 'There are no settings for captcha';
$string['this_individualfeedback_is_already_submitted'] = 'You have already submitted this individualfeedback.';
$string['typemissing'] = 'Missing value "type"';
$string['update_item'] = 'Save changes to question';
$string['url_for_continue'] = 'Link to next activity';
$string['url_for_continue_help'] = 'After submitting the individualfeedback, a continue button is displayed, which links to the course page. Alternatively, it may link to the next activity if the URL of the activity is entered here.';
$string['use_one_line_for_each_value'] = 'Use one line for each answer!';
$string['use_this_template'] = 'Use template';
$string['using_templates'] = 'Use a template';
$string['vertical'] = 'Vertical';
$string['whatfor'] = 'How would you like to apply the template?';

// Deprecated since Moodle 4.5.
$string['public'] = 'Public';

// Deprecated since Moodle 5.2.
$string['selected_dump'] = 'Selected indexes of $SESSION variable are dumped below:';

// +++ MBS-Hack (nersesov) : additional string definitions (custom question types, question groups, comparison/detail analysis, selfassessment, privacy).
$string['all_results'] = 'All results';
$string['analysis_questiongroup'] = 'Question group with {$a} questions.';
$string['average_given_answer'] = 'Average given answer';
$string['comparison_groups'] = 'Comparison (Groups)';
$string['comparison_questions'] = 'Comparison (Questions)';
$string['confirmdeleteitem_questiongroup'] = 'Are you sure you want to delete this element?
Please note: all questions within this group will be deleted.';
$string['delete_questiongroup'] = 'Delete question group';
$string['detail_groups'] = 'Detail (Groups)';
$string['detail_questions'] = 'Detail (Questions)';
$string['edit_questiongroup'] = 'Edit question group';
$string['end_of_questiongroup'] = 'End of question group';
$string['error_calculating_averages'] = 'There are questions with varying numbers of answers in this group. No averages could be calculated.';
$string['error_subtab'] = 'No valid subtab selected, can\'t load this page.';
$string['evaluations'] = 'Evaluations';
$string['filter_questiongroups'] = 'Filter question group:';
$string['fivelevelapproval'] = '5 level approval';
$string['fivelevelapproval_options'] = 'Strongly disagree
Disagree
Neither agree nor disagree
Agree
Strongly agree';
$string['fivelevelapprovaltype'] = '5 level approval type';
$string['fourlevelapproval'] = '4 level approval';
$string['fourlevelapproval_options'] = 'Strongly disagree
Disagree
Agree
Strongly agree';
$string['fourlevelapprovaltype'] = '4 level approval type';
$string['fourlevelfrequency'] = '4 level frequency';
$string['fourlevelfrequency_options'] = 'Never
Sometimes
Often
Always';
$string['fourlevelfrequencytype'] = '4 level frequency type';
$string['individualfeedback_not_linked'] = 'This individual feedback is not linked to other activities.';
$string['individualfeedback_questions_not_equal'] = 'The questions of the linked individual feedback activities are not equal and can therefore not be compared.';
$string['individualfeedback:selfassessment'] = 'Self assessment';
$string['move_questiongroup'] = 'Move this question group';
$string['negative_formulated'] = 'Control question';
$string['negative_formulated_help'] = 'Control questions are semantically inverted question, i. e. negatively formulated. In the calculation of averages (in case of question groups) the answer values are inverted.';
$string['no_questions_in_group'] = 'No questions in this group';
$string['overview_groups'] = 'Overview (Groups)';
$string['overview_questions'] = 'Overview (Questions)';
$string['privacy:metadata'] = 'The plugin "individual feedback" anonymizes data and does not allow to assign responses to a single user.';
$string['privacy:metadata:completed:selfassessment'] = 'Whether the submission is a self-assessment response.';
$string['privacy:metadata:template'] = 'A record of feedback templates, which may be linked to the user who created them.';
$string['privacy:metadata:template:userid'] = 'The ID of the user who created the private template.';
$string['questiongroup'] = 'Question group';
$string['questiongroup_name'] = 'Question group name';
$string['selfassessment'] = 'Self assessment';
// --- MBS-Hack

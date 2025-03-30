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
$string['append_new_items'] = 'Append new items';
$string['autonumbering'] = 'Auto number questions';
$string['autonumbering_help'] = 'Enables or disables automated numbers for each question';
$string['average'] = 'Average';
$string['bold'] = 'Bold';
$string['calendarend'] = 'Individual feedback {$a} closes';
$string['calendarstart'] = 'Individual feedback {$a} opens';
$string['cannotaccess'] = 'You can only access this individual feedback from a course';
$string['cannotcreatepagebreak'] = 'Page breaks cannot be added at the beginning of a feedback, and only one page break can be added at the end';
$string['cannotsavetempl'] = 'saving templates is not allowed';
$string['captcha'] = 'Captcha';
$string['captchanotset'] = 'Captcha hasn\'t been set.';
$string['closebeforeopen'] = 'You have specified an end date before the start date.';
$string['completed_individualfeedbacks'] = 'Submitted answers';
$string['complete_the_form'] = 'Answer the questions...';
$string['completed'] = 'Completed';
$string['completedon'] = 'Completed on {$a}';
$string['completiondetail:submit'] = 'Submit Feedback';
$string['completionsubmit'] = 'View as completed if the individual feedback is submitted';
$string['configallowfullanonymous'] = 'If set to \'yes\', users can complete a individual feedback activity on the front page without being required to log in.';
$string['confirmdeleteentry'] = 'Are you sure you want to delete this entry?';
$string['confirmdeleteitem'] = 'Are you sure you want to delete this element?';
$string['confirmdeletetemplate'] = 'Are you sure you want to delete this template?';
$string['confirmusetemplate'] = 'Are you sure you want to use this template?';
$string['continue_the_form'] = 'Continue answering the questions...';
$string['count_of_nums'] = 'Count of numbers';
$string['courseid'] = 'courseid';
$string['creating_templates'] = 'Save these questions as a new template';
$string['delete_entry'] = 'Delete entry';
$string['delete_item'] = 'Delete question';
$string['delete_old_items'] = 'Delete old items';
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
$string['do_not_analyse_empty_submits'] = 'Do not analyse empty submits';
$string['dropdown'] = 'Multiple choice - single answer allowed (dropdownlist)';
$string['dropdownlist'] = 'Multiple choice - single answer (dropdown)';
$string['dropdownrated'] = 'Dropdownlist (rated)';
$string['dropdown_values'] = 'Answers';
$string['drop_individualfeedback'] = 'Remove from this course';
$string['edit_item'] = 'Edit question';
$string['edit_items'] = 'Edit questions';
$string['email_notification'] = 'Enable notification of submissions';
$string['email_notification_help'] = 'If enabled, teachers will receive notification of individual feedback submissions.';
$string['emailteachermail'] = '{$a->username} has completed individual feedback activity : \'{$a->individualfeedback}\'

You can view it here:

{$a->url}';
$string['emailteachermailhtml'] = '<p>{$a->username} has completed individual feedback activity : <i>\'{$a->individualfeedback}\'</i>.</p>
<p>It is <a href="{$a->url}">available on the site</a>.</p>';
$string['entries_saved'] = 'Your answers have been saved. Thank you.';
$string['export_questions'] = 'Export questions';
$string['export_to_excel'] = 'Export to Excel';
$string['eventresponsedeleted'] = 'Response deleted';
$string['eventresponsesubmitted'] = 'Response submitted';
$string['individualfeedbackcompleted'] = '{$a->username} completed {$a->individualfeedbackname}';
$string['individualfeedback:addinstance'] = 'Add a new individual feedback';
$string['individualfeedbackclose'] = 'Allow answers to';
$string['individualfeedback:complete'] = 'Complete a individual feedback';
$string['individualfeedback:createprivatetemplate'] = 'Create private template';
$string['individualfeedback:createpublictemplate'] = 'Create public template';
$string['individualfeedback:deletesubmissions'] = 'Delete completed submissions';
$string['individualfeedback:deleteprivatetemplate'] = 'Delete private template';
$string['individualfeedback:deletepublictemplate'] = 'Delete public template';
$string['individualfeedback:edititems'] = 'Edit items';
$string['individualfeedback_is_not_for_anonymous'] = 'individual feedback is not for anonymous';
$string['individualfeedback_is_not_open'] = 'The individual feedback is not open';
$string['individualfeedback:mapcourse'] = 'Map courses to global feedbacks';
$string['individualfeedbackopen'] = 'Allow answers from';
$string['individualfeedback:receivemail'] = 'Receive email notification';
$string['individualfeedback:view'] = 'View a individual feedback';
$string['individualfeedback:viewanalysepage'] = 'View the analysis page after submit';
$string['individualfeedback:viewreports'] = 'View reports';
$string['file'] = 'File';
$string['filter_by_course'] = 'Filter by course';
$string['handling_error'] = 'Error occurred in individual feedback module action handling';
$string['hide_no_select_option'] = 'Hide the "Not selected" option';
$string['horizontal'] = 'horizontal';
$string['check'] = 'Multiple choice - multiple answers';
$string['checkbox'] = 'Multiple choice - multiple answers allowed (check boxes)';
$string['check_values'] = 'Possible responses';
$string['choosefile'] = 'Choose a file';
$string['chosen_individualfeedback_response'] = 'chosen individual feedback response';
$string['downloadresponseas'] = 'Download all responses as:';
$string['importfromthisfile'] = 'Import from this file';
$string['import_questions'] = 'Import questions';
$string['import_successfully'] = 'Import successfully';
$string['includeuserinrecipientslist'] = 'Include {$a} in the list of recipients';
$string['indicator:cognitivedepth'] = 'Feedback cognitive';
$string['indicator:cognitivedepth_help'] = 'This indicator is based on the cognitive depth reached by the student in a Feedback activity.';
$string['indicator:cognitivedepthdef'] = 'Feedback cognitive';
$string['indicator:cognitivedepthdef_help'] = 'The participant has reached this percentage of the cognitive engagement offered by the Feedback activities during this analysis interval (Levels = No view, View, Submit)';
$string['indicator:cognitivedepthdef_link'] = 'Learning_analytics_indicators#Cognitive_depth';
$string['indicator:socialbreadth'] = 'Feedback social';
$string['indicator:socialbreadth_help'] = 'This indicator is based on the social breadth reached by the student in a Feedback activity.';
$string['indicator:socialbreadthdef'] = 'Feedback social';
$string['indicator:socialbreadthdef_help'] = 'The participant has reached this percentage of the social engagement offered by the Feedback activities during this analysis interval (Levels = No participation, Participant alone, Participant with others)';
$string['indicator:socialbreadthdef_link'] = 'Learning_analytics_indicators#Social_breadth';
$string['info'] = 'Information';
$string['infotype'] = 'Information type';
$string['insufficient_responses_for_this_group'] = 'There are insufficient responses for this group';
$string['insufficient_responses'] = 'insufficient responses';
$string['insufficient_responses_help'] = 'For the individual feedback to be anonymous, there must be at least 2 responses.';
$string['item_label'] = 'Label';
$string['item_name'] = 'Question';
$string['label'] = 'Label';
$string['labelcontents'] = 'Contents';
$string['mapcourseinfo'] = 'This is a site-wide individual feedback that is available to all courses using the individual feedback block. You can however limit the courses to which it will appear by mapping them. Search the course and map it to this individual feedback.';
$string['mapcoursenone'] = 'No courses mapped. Individual feedback available to all courses';
$string['mapcourse'] = 'Map individual feedback to courses';
$string['mapcourse_help'] = 'By default, individual feedback forms created on your homepage are available site-wide
and will appear in all courses using the individual feedback block. You can force the individual feedback form to appear by making it a sticky block or limit the courses in which a individual feedback form will appear by mapping it to specific courses.';
$string['mapcourses'] = 'Map individual feedback to courses';
$string['mappedcourses'] = 'Mapped courses';
$string['mappingchanged'] = 'Course mapping has been changed';
$string['minimal'] = 'minimum';
$string['maximal'] = 'maximum';
$string['messageprovider:message'] = 'Individual feedback reminder';
$string['messageprovider:submission'] = 'Individual feedback notifications';
$string['mode'] = 'Mode';
$string['modulename'] = 'Individual feedback';
$string['modulename_help'] = 'The individual feedback activity module is a cloned and customized version of the feedback activity.

The individual feedback activity module enables a teacher to create a custom survey for collecting individual feedback from participants using a variety of question types including multiple choice, yes/no or text input.

Individual feedback responses is always anonymous, and results may be shown to all participants or restricted to teachers only. Any individual feedback activities on the site front page may also be completed by non-logged-in users.

Individual feedback activities may be used

* For course evaluations, helping improve the content for later participants
* For guest surveys of course choices, school policies etc.
* For anti-bullying surveys in which students can report incidents anonymously';
$string['modulename_link'] = 'mod/individualfeedback/view';
$string['modulenameplural'] = 'Individual feedback';
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
$string['non_respondents_students'] = 'Non respondents students ({$a})';
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
$string['only_one_captcha_allowed'] = 'Only one captcha is allowed in a individual feedback';
$string['openafterclose'] = 'You have specified an open date after the close date';
$string['overview'] = 'Overview';
$string['page'] = 'Page';
$string['page-mod-individualfeedback-x'] = 'Any individual feedback module page';
$string['page_after_submit'] = 'Completion message';
$string['pagebreak'] = 'Page break';
$string['pluginadministration'] = 'Individual feedback administration';
$string['pluginname'] = 'Individual feedback';
$string['position'] = 'Position';
$string['previous_page'] = 'Previous page';
$string['previewquestions'] = 'Preview questions';
$string['privacy:metadata:completed'] = 'A record of the submissions to the feedback';
$string['privacy:metadata:completed:anonymousresponse'] = 'Whether the submission is to be used anonymously.';
$string['privacy:metadata:completed:timemodified'] = 'The time when the submission was last modified.';
$string['privacy:metadata:completed:userid'] = 'The ID of the user who completed the feedback activity.';
$string['privacy:metadata:completedtmp'] = 'A record of the submissions which are still in progress.';
$string['privacy:metadata:value'] = 'A record of the answer to a question.';
$string['privacy:metadata:value:value'] = 'The chosen answer.';
$string['privacy:metadata:valuetmp'] = 'A record of the answer to a question in a submission in progress.';
$string['public'] = 'Public';
$string['question'] = 'Question';
$string['questionandsubmission'] = 'Question and submission settings';
$string['questions'] = 'Questions';
$string['questionslimited'] = 'Showing only {$a} first questions, view individual answers or download table data to view all.';
$string['radio'] = 'Multiple choice - single answer';
$string['radio_values'] = 'Responses';
$string['ready_individualfeedbacks'] = 'Ready individual feedbacks';
$string['required'] = 'Required';
$string['resetting_data'] = 'Reset individual feedback responses';
$string['resetting_delete'] = 'Delete responses';
$string['resetting_individualfeedbacks'] = 'Resetting individual feedbacks';
$string['response_nr'] = 'Response number';
$string['responses'] = 'Responses';
$string['responsetime'] = 'Responsestime';
$string['save_as_new_item'] = 'Save as new question';
$string['save_as_new_template'] = 'Save as new template';
$string['save_entries'] = 'Submit your answers';
$string['save_item'] = 'Save question';
$string['saving_failed'] = 'Saving failed';
$string['search:activity'] = 'Individual feedback - activity information';
$string['search_course'] = 'Search course';
$string['searchcourses'] = 'Search courses';
$string['searchcourses_help'] = 'Search for the code or name of the course(s) that you wish to associate with this individual feedback.';
$string['selected_dump'] = 'Selected indexes of $SESSION variable are dumped below:';
$string['send'] = 'send';
$string['send_message'] = 'send message';
$string['show_all'] = 'Show all';
$string['show_analysepage_after_submit'] = 'Show analysis page';
$string['show_entries'] = 'Show responses';
$string['show_entry'] = 'Show response';
$string['show_nonrespondents'] = 'Show non-respondents';
$string['site_after_submit'] = 'Site after submit';
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
$string['this_individualfeedback_is_already_submitted'] = 'You\'ve already completed this activity.';
$string['typemissing'] = 'missing value "type"';
$string['update_item'] = 'Save changes to question';
$string['url_for_continue'] = 'Link to next activity';
$string['url_for_continue_help'] = 'After submitting the individual feedback, a continue button is displayed, which links to the course page. Alternatively, it may link to the next activity if the URL of the activity is entered here.';
$string['use_one_line_for_each_value'] = 'Use one line for each answer!';
$string['use_this_template'] = 'Use this template';
$string['using_templates'] = 'Use a template';
$string['vertical'] = 'vertical';
// Deprecated since Moodle 3.1.
$string['cannotmapindividualfeedback'] = 'Database problem, unable to map individual feedback to course';
$string['line_values'] = 'Rating';
$string['mapcourses_help'] = 'Once you have selected the relevant course(s) from your search,
you can associate them with this individual feedback using map course(s). Multiple courses may be selected by holding down the Apple or Ctrl key whilst clicking on the course names. A course may be disassociated from a individual feedback at any time.';
$string['max_args_exceeded'] = 'Max 6 arguments can be handled, too many arguments for';
$string['cancel_moving'] = 'Cancel moving';
$string['movedown_item'] = 'Move this question down';
$string['move_here'] = 'Move here';
$string['moveup_item'] = 'Move this question up';
$string['notavailable'] = 'this individual feedback is not available';
$string['saving_failed_because_missing_or_false_values'] = 'Saving failed because missing or false values';
$string['cannotunmap'] = 'Database problem, unable to unmap';
$string['viewcompleted'] = 'completed individual feedbacks';
$string['viewcompleted_help'] = 'You may view completed individual feedback forms, searchable by course and/or by question.
Individual feedback responses may be exported to Excel.';
$string['parameters_missing'] = 'Parameters missing from';
$string['picture'] = 'Picture';
$string['picture_file_list'] = 'List of pictures';
$string['picture_values'] = 'Choose one or more<br />picture files from the list:';
$string['preview'] = 'Preview';
$string['preview_help'] = 'In the preview you can change the order of questions.';
$string['switch_group'] = 'Switch group';
$string['separator_decimal'] = '.';
$string['separator_thousand'] = ',';
$string['relateditemsdeleted'] = 'All responses for this question will also be deleted.';
$string['radiorated'] = 'Radiobutton (rated)';
$string['radiobutton'] = 'Multiple choice - single answer allowed (radio buttons)';
$string['radiobutton_rated'] = 'Radiobutton (rated)';
// Deprecated since Moodle 3.2.
$string['start'] = 'Start';
$string['stop'] = 'End';

$string['fourlevelapproval'] = '4 level approval';
$string['fourlevelapprovaltype'] = '4 level approval type';
$string['fourlevelapproval_options'] = 'Strongly disagree
Disagree
Agree
Strongly agree';
$string['fourlevelfrequency'] = '4 level frequency';
$string['fourlevelfrequencytype'] = '4 level frequency type';
$string['fourlevelfrequency_options'] = 'Never
Sometimes
Often
Always';
$string['fivelevelapproval'] = '5 level approval';
$string['fivelevelapprovaltype'] = '5 level approval type';
$string['fivelevelapproval_options'] = 'Strongly disagree
Disagree
Neither agree nor disagree
Agree
Strongly agree';
$string['questiongroup'] = 'Question group';
$string['questiongroup_name'] = 'Question group name';
$string['edit_questiongroup'] = 'Edit question group';
$string['delete_questiongroup'] = 'Delete question group';
$string['end_of_questiongroup'] = 'End of question group';
$string['confirmdeleteitem_questiongroup'] = 'Are you sure you want to delete this element?
Please note: all questions within this group will be deleted.';
$string['move_questiongroup'] = 'Move this question group';
$string['evaluations'] = 'Evaluations';
$string['detail_questions'] = 'Detail (Questions)';
$string['detail_groups'] = 'Detail (Groups)';
$string['overview_questions'] = 'Overview (Questions)';
$string['overview_groups'] = 'Overview (Groups)';
$string['comparison_questions'] = 'Comparison (Questions)';
$string['comparison_groups'] = 'Comparison (Groups)';
$string['error_subtab'] = 'No valid subtab selected, can\'t load this page.';
$string['all_results'] = 'All results';
$string['filter_questiongroups'] = 'Filter question group:';
$string['individualfeedback:selfassessment'] = 'Self assessment';
$string['no_questions_in_group'] = 'No questions in this group';
$string['error_calculating_averages'] = 'There are questions with varying numbers of answers in this group. No averages could be calculated.';
$string['analysis_questiongroup'] = 'Question group with {$a} questions.';
$string['selfassessment'] = 'Self assessment';
$string['average_given_answer'] = 'Average given answer';
$string['duplicate_and_link'] = 'Duplicate and link activity';
$string['error_duplicating'] = 'Something went wrong duplicating the activity. Try again or contact your system administrator.';
$string['individualfeedback_cloned_and_linked'] = 'Individual feedback activity is duplicated and linked.';
$string['individualfeedback_is_linked'] = 'This individual feedback activity is linked to other activities and can therefore not be edited.';
$string['individualfeedback_not_linked'] = 'This individual feedback is not linked to other activities.';
$string['individualfeedback_questions_not_equal'] = 'The questions of the linked individual feedback activities are not equal and can therefore not be compared.';
$string['negative_formulated'] = 'Control question';
$string['negative_formulated_help'] = 'Control questions are semantically inverted question, i. e. negatively formulated. In the calculation of averages (in case of question groups) the answer values are inverted.';

$string['privacy:metadata'] = 'The plugin "individual feedback" anonymizes data and does not allow to assign responses to a single user.';
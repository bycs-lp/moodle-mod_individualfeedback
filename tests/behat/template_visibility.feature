@mod @mod_individualfeedback
Feature: Course, user and public templates in individualfeedback
  In order to reuse my questions in the right scope
  As a teacher or manager
  I need to choose whether a template is visible in the course, only to me or on the whole site

  # Covers the MBS-Hacks H1/H2/H11/H14: the core "Available for all courses" checkbox is replaced by a
  # Course / User / Public choice; user templates (ispublic = 2) belong to their creator only.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
      | teacher2 | Teacher   | 2        |
      | manager  | Manager   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
      | teacher1 | C2     | editingteacher |
      | teacher2 | C1     | editingteacher |
    And the following "system role assigns" exist:
      | user    | course               | role    |
      | manager | Acceptance test site | manager |
    And the following "activities" exist:
      | activity           | name                         | course | idnumber            |
      | individualfeedback | Learning experience course 1 | C1     | individualfeedback1 |
      | individualfeedback | Learning experience course 2 | C2     | individualfeedback2 |
    And the following "mod_individualfeedback > question" exists:
      | activity | individualfeedback1 |
      | name     | How was the lesson? |
      | label    | q1                  |

  @javascript
  Scenario: Teacher saves a course template and a user template
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher1
    And I navigate to "Questions" in current page administration
    And I press "Actions"
    And I choose "Save as template" in the open action menu
    And I should see "Course" in the ".modal-body" "css_element"
    And I should see "User" in the ".modal-body" "css_element"
    And I set the field "Name" to "My course template"
    And I set the field "Course" to "1"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I should see "Template saved"
    And I press "Actions"
    And I choose "Save as template" in the open action menu
    And I set the field "Name" to "My user template"
    And I set the field "User" to "1"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I should see "Template saved"
    When I navigate to "Templates" in current page administration
    Then "My course template" "text" should exist in the ".coursetemplates" "css_element"
    And "My user template" "text" should exist in the ".coursetemplates" "css_element"
    And "My user template" "text" should not exist in the ".publictemplates" "css_element"
    # The user template follows its owner into another course.
    And I am on the "Learning experience course 2" "individualfeedback activity" page
    And I navigate to "Templates" in current page administration
    And I should see "My user template"
    And I should not see "My course template"
    And I log out
    # Another teacher of the same course sees the course template but not the user template.
    And I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher2
    And I navigate to "Templates" in current page administration
    And I should see "My course template"
    And I should not see "My user template"

  @javascript
  Scenario: Manager saves a public template that is available in every course
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as manager
    And I navigate to "Questions" in current page administration
    And I press "Actions"
    And I choose "Save as template" in the open action menu
    And I set the field "Name" to "Site-wide template"
    And I set the field "Public" to "1"
    And I click on "Save" "button" in the ".modal-dialog" "css_element"
    And I should see "Template saved"
    And I log out
    When I am on the "Learning experience course 2" "individualfeedback activity" page logged in as teacher1
    And I navigate to "Templates" in current page administration
    Then "Site-wide template" "text" should exist in the ".publictemplates" "css_element"
    And I open the action menu in "Site-wide template" "table_row"
    And I choose "Use template" in the open action menu
    And I click on "Save" "button" in the "Use template" "dialogue"
    And I should see "How was the lesson?"

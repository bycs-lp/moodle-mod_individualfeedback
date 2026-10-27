@mod @mod_individualfeedback
Feature: Question groups in the individualfeedback question editor
  In order to structure a feedback with question groups
  As a teacher
  I need to see the question group boundaries by name when I move questions

  # Covers the MBS-Hack in amd/src/edit.js: the sortable list resolves the name of the
  # question group start row and of the "End of question group" row. Without the hack both
  # rows have no title span and appear as 'After ""' in the "Move this question" dialogue.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher  | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user    | course | role           |
      | teacher | C1     | editingteacher |
    And the following "activities" exist:
      | activity           | name                         | course | idnumber            |
      | individualfeedback | Learning experience course 1 | C1     | individualfeedback1 |
    And the following "mod_individualfeedback > question" exists:
      | activity | individualfeedback1 |
      | name     | Before the group    |
      | label    | q1                  |

  @javascript
  Scenario: Question group start and end rows are listed by name in the move dialogue
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I add a "Question group" question to the individualfeedback with:
      | Question group name | Social skills |
    And I add a "Short text answer" question to the individualfeedback with:
      | Question | After the group |
      | Label    | q2              |
    Then I should see "Social skills" in the "region-main" "region"
    And I should see "End of question group" in the "region-main" "region"
    # Move the first question: the dialogue lists every other row by name, the group start row,
    # the group end row and the trailing question. It never lists the row directly above the
    # moved question, so the moved question must be the first one.
    And I click on "Move this question" "button" in the "Before the group" "mod_individualfeedback > Question"
    And I should see "After \"Social skills\"" in the "Move this question" "dialogue"
    And I should see "After \"End of question group\"" in the "Move this question" "dialogue"
    And I should see "After \"(q2) After the group\"" in the "Move this question" "dialogue"
    And I should not see "After \"\"" in the "Move this question" "dialogue"

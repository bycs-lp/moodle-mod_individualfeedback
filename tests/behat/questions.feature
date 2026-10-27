@mod @mod_individualfeedback
Feature: Managing individualfeedback questions
  In order to manage individualfeedback questions
  As a teacher
  I need to be able to create, edit and delete individualfeedback questions

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher  | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name                         | course | idnumber    |
      | individualfeedback   | Learning experience course 1 | C1     | individualfeedback1   |
    And the following "mod_individualfeedback > question" exists:
      | activity        | individualfeedback1                     |
      | name            | Is it me you're looking for?  |
      | label           | q1                            |

  Scenario: Teacher can create a new individualfeedback question
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I add a "Short text answer" question to the individualfeedback with:
      | Question         | I can see it in your eyes |
      | Label            | q2                           |
    Then I should see "(q2) I can see it in your eyes"

  @javascript
  Scenario: Teacher can edit individualfeedback questions
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I click on "Edit" "link" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    And I choose "Edit question" in the open action menu
    And I set the field "Question" to "Can you see it in my eyes?"
    And I press "Save changes to question"
    Then I should see "(q1) Can you see it in my eyes?"
    And I should not see "(q1) Is it me you're looking for?"

  @javascript
  Scenario: Teacher can edit and save as new individualfeedback questions
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I click on "Edit" "link" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    And I choose "Edit question" in the open action menu
    And I set the field "Question" to "You can se it in my eyes?"
    And I press "Save as new question"
    Then I should see "(q1) Is it me you're looking for?"
    And I should see "(q1) You can se it in my eyes?"

  @javascript
  Scenario: Teacher can delete individualfeedback questions
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I click on "Edit" "link" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    And I choose "Delete question" in the open action menu
    And I click on "Yes" "button" in the "Confirmation" "dialogue"
    Then I should not see "(q1) Is it me you're looking for?"

  @javascript
  Scenario: Teacher can mark as required individualfeedback questions
    Given I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I click on "Edit" "link" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    # +++ MBS-Hack (nersesov) : the fork manages the required flag itself (H18, can_switch_require() returns
    # false), so the core "Set as required" / "Set as not required" toggle is not offered in the action menu.
    # Core steps toggling the flag through the menu are replaced by the assertion that the toggle is absent.
    Then "Set as required" "link" should not exist in the ".moodle-actionmenu .dropdown .dropdown-menu.show" "css_element"
    And "Set as not required" "link" should not exist in the ".moodle-actionmenu .dropdown .dropdown-menu.show" "css_element"
    And "Edit question" "link" should exist in the ".moodle-actionmenu .dropdown .dropdown-menu.show" "css_element"
    # --- MBS-Hack

  @javascript
  Scenario: Teacher can move questions
    Given the following "mod_individualfeedback > questions" exist:
      | activity  | label        | name                               |
      | individualfeedback1 | q2           | I can see it in your eyes          |
      | individualfeedback1 | q3           | I can see it in your smile         |
    And I am on the "Learning experience course 1" "individualfeedback activity" page logged in as teacher
    And I click on "Edit questions" "link" in the "region-main" "region"
    When I click on "Move this question" "button" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    Then I should see "After \"(q2) I can see it in your eyes\"" in the "Move this question" "dialogue"
    And I should not see "To the top of the list" in the "Move this question" "dialogue"
    And I click on "After \"(q3) I can see it in your smile\"" "link" in the "Move this question" "dialogue"
    And I click on "Move this question" "button" in the "Is it me you're looking for?" "mod_individualfeedback > Question"
    And I click on "To the top of the list" "link" in the "Move this question" "dialogue"

  Scenario: Admin cannot answer questions if not enrolled as student
    When I am on the "Learning experience course 1" "individualfeedback activity" page logged in as admin
    Then I should not see "Answer the questions"
    But the following "course enrolments" exist:
      | user     | course | role    |
      | admin    | C1     | student |
    And I am on the "Learning experience course 1" "individualfeedback activity" page logged in as admin
    And I should see "Answer the questions"

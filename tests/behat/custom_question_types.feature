@mod @mod_individualfeedback
Feature: Test creating different types of custom individualfeedback questions for anonymous individualfeedback
  In order to create individualfeedbacks
  As a teacher
  I need to be able to add different question types

  @javascript
  Scenario: Create different custom types of questions in anonymous individualfeedback with javascript enabled
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher1 | Teacher   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity   | name                | course | idnumber    |
      | individualfeedback   | Learning experience | C1     | individualfeedback0   |
    When I log in as "teacher1"
    And I am on "Course 1" course homepage
    And I follow "Learning experience"
    And I click on "Edit questions" "link" in the ".nav-tabs" "css_element"
    And I add a "5 level approval" question to the individualfeedback with:
      | Question         | 5 level approval question |
      | Label            | Q01                       |
    And I add a "Question group" question to the individualfeedback with:
      | Question group name | Question group 01 |
    And I add a "4 level frequency" question to the individualfeedback with:
      | Question         | 4 level frequency question |
      | Label            | Q02                        |
      | Position         | 3                          |
    And I add a "4 level approval" question to the individualfeedback with:
      | Question         | 4 level approval question |
      | Label            | Q03                       |
      | Position         | 4                         |
    Then I should see "Learning experience"

    When I click on "Overview" "link" in the ".nav-tabs" "css_element"
    And I follow "Preview"
    Then I should see "5 level approval question"
    And I should see "Strongly disagree"
    And I should see "Disagree"
    And I should see "Neither agree nor disagree"
    And I should see "Agree"
    And I should see "Strongly agree"
    And I should see "Question group 01"
    And I should see "4 level frequency question"
    And I should see "Never"
    And I should see "Sometimes"
    And I should see "Often"
    And I should see "Always"
    And I should see "4 level approval question"
    And I should see "Never"
    And I should see "Sometimes"
    And I should see "Often"
    And I should see "Always"
    And I should see "Strongly disagree"
    And I should see "Disagree"
    And I should see "Agree"
    And I should see "Strongly agree"

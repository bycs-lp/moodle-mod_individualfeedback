@mod @mod_individualfeedback
Feature: Evaluations of individualfeedback
  In order to evaluate individualfeedbacks
  As an teacher
  I need to be able to see statistics of different individualfeedbacks

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | user1    | Username  | 1        |
      | user2    | Username  | 2        |
      | teacher  | Teacher   | 3        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
      | user2 | C1     | student |
      | teacher | C1   | editingteacher |
    And the following "activities" exist:
      | activity             | name | course | idnumber            | anonymous | publish_stats | section |
      | individualfeedback   | IF01 | C1     | individualfeedback1 | 1         | 1             | 0       |

  @javascript
  Scenario: Duplicate and link individualfeedback in a course and see statistics
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "IF01"
    And I click on "Edit questions" "link" in the ".nav-tabs" "css_element"
    And I add a "Question group" question to the individualfeedback with:
      | Question group name | Question group A |
    And I add a "4 level frequency" question to the individualfeedback with:
      | Question         | 4 level frequency question |
      | Label            | Q01                        |
      | Position         | 2                          |
    And I add a "4 level approval" question to the individualfeedback with:
      | Question         | 4 level approval question |
      | Label            | Q02                       |
      | Position         | 3                         |
    And I add a "5 level approval" question to the individualfeedback with:
      | Question                       | 5 level approval question |
      | Label                          | Q03                       |
    When I am on "Course 1" course homepage with editing mode on
    And I click on "Edit" "link" in the "li.activity.individualfeedback ul.menubar li" "css_element"
    And I follow "Duplicate and link activity"
    Then I should see "IF01"
    When I am on "Course 1" course homepage
    And I follow "IF01"
    And I change window size to "large"
    And I click on "Edit settings" "link" in the "div.block_settings div.content ul li" "css_element"
    And I set the following fields to these values:
      | Name | IF02 |
    And I press "Save and return to course"
    And I change window size to "medium"
    And I follow "Logout"
    And I press "Continue"

    When I log in as "user1"
    And I am on "Course 1" course homepage
    And I follow "IF01"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Never | 1 |
      | Strongly disagree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Strongly disagree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    When I am on "Course 1" course homepage
    And I follow "IF02"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Never | 1 |
      | Strongly disagree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Strongly disagree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Logout"
    And I press "Continue"

    When I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "IF01"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Never | 1 |
      | Strongly disagree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Strongly disagree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    When I am on "Course 1" course homepage
    And I follow "IF02"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Sometimes | 1 |
      | Disagree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Disagree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Logout"
    And I press "Continue"

    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "IF01"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Often | 1 |
      | Agree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Agree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    When I am on "Course 1" course homepage
    And I follow "IF02"
    And I follow "Answer the questions..."
    And I should see "Question group A"
    And I set the following fields to these values:
      | Often | 1 |
      | Agree | 1 |
    And I press "Next page"
    And I set the following fields to these values:
      | Agree | 1 |
    And I press "Submit your answers"
    And I follow "Submitted answers"
    Then I should see "Submitted answers: 2"
    When I click on "Detail (Groups)" "link" in the "div.subtabs_placeholder .nav-tabs" "css_element"
    And I change window size to "large"
    And I wait "2" seconds
    And I show chart data for the "Question group with 2 questions." individualfeedback
    Then I should see "2 (50 %)" in the "Answer 1" "table_row"
    And I should see "2 (50 %)" in the "Answer 2" "table_row"
    When I click on "Overview (Questions)" "link" in the "div.subtabs_placeholder .nav-tabs" "css_element"
    And I wait "2" seconds
    And I show chart data for the "4 level frequency question" individualfeedback
    Then I should see "Average"
    And I should see "1.5"
    And I should see "Self assessment"
    And I should see "3"
    When I click on "Overview (Groups)" "link" in the "div.subtabs_placeholder .nav-tabs" "css_element"
    And I wait "2" seconds
    And I show chart data for the "Question group with 2 questions." individualfeedback
    Then I should see "Average"
    And I should see "1.5"
    And I should see "Self assessment"
    And I should see "3"
    When I click on "Comparison (Questions)" "link" in the "div.subtabs_placeholder .nav-tabs" "css_element"
    And I wait "2" seconds
    And I show chart data for the "4 level frequency question" individualfeedback
    Then I should see "IF02"
    And I should see "1.5"
    And I should see "IF01"
    And I should see "1"
    When I click on "Comparison (Groups)" "link" in the "div.subtabs_placeholder .nav-tabs" "css_element"
    And I wait "2" seconds
    And I show chart data for the "Question group with 2 questions." individualfeedback
    Then I should see "IF02"
    And I should see "1.5"
    And I should see "IF01"
    And I should see "1"

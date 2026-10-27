@mod @mod_individualfeedback
Feature: Testing overview integration in mod_individualfeedback
  In order to list all individualfeedbacks in a course
  As a user
  I need to be able to see the individualfeedback overview

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | student1 | Username  | 1        |
      | student2 | Username  | 2        |
      | student3 | Username  | 3        |
      | student4 | Username  | 4        |
      | student5 | Username  | 5        |
      | student6 | Username  | 6        |
      | student7 | Username  | 7        |
      | student8 | Username  | 8        |
      | teacher1 | Teacher   | T        |
    And the following "courses" exist:
      | fullname | shortname | groupmode |
      | Course 1 | C1        | 1         |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | student1 | C1     | student        |
      | student2 | C1     | student        |
      | student3 | C1     | student        |
      | student4 | C1     | student        |
      | student5 | C1     | student        |
      | student6 | C1     | student        |
      | student7 | C1     | student        |
      | student8 | C1     | student        |
      | teacher1 | C1     | editingteacher |
    And the following "activities" exist:
      | activity | name                   | course | idnumber  | timeclose            |
      | individualfeedback | Date individualfeedback          | C1     | individualfeedback1 | ##1 Jan 2040 08:00## |
      | individualfeedback | Not responded individualfeedback | C1     | individualfeedback2 | ##tomorrow noon##    |
      | individualfeedback | No date individualfeedback       | C1     | individualfeedback3 |                      |
    Given the following "mod_individualfeedback > question" exists:
      | activity     | individualfeedback1                               |
      | name         | Do you like this course?                |
      | questiontype | multichoice                             |
      | label        | multichoice1                            |
      | subtype      | r                                       |
      | hidenoselect | 1                                       |
      | values       | Yes of course\nNot at all\nI don't know |
    And the following "mod_individualfeedback > responses" exist:
      | activity  | user     | Do you like this course? |
      | individualfeedback1 | student1 | Not at all               |
      | individualfeedback1 | student2 | I don't know             |
      | individualfeedback1 | student3 | Not at all               |
      | individualfeedback1 | student4 | Yes of course            |
      | individualfeedback3 | student1 | Not at all               |
      | individualfeedback3 | student2 | I don't know             |
      | individualfeedback3 | student3 | Not at all               |

  Scenario: Teacher can see the individualfeedback relevant information in the individualfeedback overview
    When I am on the "Course 1" "course > activities > individualfeedback" page logged in as "teacher1"
    Then the following should exist in the "Table listing all Individualfeedback activities" table:
      | Name                   | Due date       | Responses | Actions  |
      | Date individualfeedback          | 1 January 2040 | 4         | View     |
      | Not responded individualfeedback | Tomorrow       | 0         | View     |
      | No date individualfeedback       | -              | 3         | View     |
    And I should not see "Responded" in the "individualfeedback_overview_collapsible" "region"
    And I click on "View" "link" in the "Date individualfeedback" "table_row"
    And I should see "Show responses"

  Scenario: Students can see the individualfeedback relevant information in the individualfeedback overview
    When I am on the "Course 1" "course > activities > individualfeedback" page logged in as "student1"
    Then the following should exist in the "Table listing all Individualfeedback activities" table:
      | Name                   | Due date       | Responded |
      | Date individualfeedback          | 1 January 2040 |           |
      | Not responded individualfeedback | Tomorrow       | -         |
      | No date individualfeedback       | -              |           |
    And "You have already submitted this individualfeedback" "icon" should exist in the "Date individualfeedback" "table_row"
    And "You have already submitted this individualfeedback" "icon" should exist in the "No date individualfeedback" "table_row"

  Scenario: The individualfeedback overview report should generate log events
    Given I am on the "Course 1" "course > activities > individualfeedback" page logged in as "teacher1"
    When I am on the "Course 1" "course" page logged in as "teacher1"
    And I navigate to "Reports" in current page administration
    And I click on "Logs" "link"
    And I click on "Get these logs" "button"
    Then I should see "Course activities overview page viewed"
    And I should see "viewed the instance list for the module 'individualfeedback'"

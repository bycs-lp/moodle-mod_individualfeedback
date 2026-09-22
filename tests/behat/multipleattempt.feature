@mod @mod_individualfeedback
Feature: Non anonymous individualfeedback with multiple submissions
  In order to modify individualfeedback response
  As a student
  I need to be able to see previous response when I re-submit individualfeedback

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | user1    | Username  | 1        |
      | user2    | Username  | 2        |
      | teacher  | Teacher   | 3        |
      | manager  | Manager   | 4        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user  | course | role    |
      | user1 | C1     | student |
      | user2 | C1     | student |
      | teacher | C1   | editingteacher |
    And the following "activities" exist:
      | activity   | name            | course | idnumber  | anonymous | publish_stats | multiple_submit | section |
      | individualfeedback   | Course individualfeedback | C1     | individualfeedback1 | 2         | 1             | 1               | 0       |

  Scenario: Completing a individualfeedback second time
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I navigate to "Questions" in current page administration
    And I add a "Short text answer" question to the individualfeedback with:
      | Question                    | first      |
      | Label                       | shorttext1 |
      | Maximum characters accepted | 200        |
    And I add a page break to the individualfeedback
    And I add a "Short text answer" question to the individualfeedback with:
      | Question                    | second     |
      | Label                       | shorttext2 |
      | Maximum characters accepted | 200        |
    And I log out
    And I log in as "user1"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I follow "Answer the questions"
    And I set the following fields to these values:
      | first | 111 |
    And I press "Next"
    And I set the following fields to these values:
      | second | 222 |
    And I press "Submit your answers"
    And I log out
    And I log in as "user1"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I follow "Answer the questions"
    Then the field "first" matches value "111"
    And I press "Next"
    And the field "second" matches value "222"
    And I set the following fields to these values:
      | second | 333 |
    And I press "Submit your answers"

@mod @mod_individualfeedback
Feature: Anonymous individualfeedback
  In order to collect individualfeedbacks
  As an teacher
  I need to be able to allow anonymous individualfeedbacks

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
    And the following "system role assigns" exist:
      | user    | course               | role    |
      | manager | Acceptance test site | manager |
    And the following "activities" exist:
      | activity   | name            | course               | idnumber  | anonymous | publish_stats | section |
      | individualfeedback   | Site individualfeedback   | Acceptance test site | individualfeedback0 | 1         | 1             | 1       |
      | individualfeedback   | Course individualfeedback | C1                   | individualfeedback1 | 1         | 1             | 0       |

  @javascript
  Scenario: Anonymous individualfeedback in a course
    # Teacher can not
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I click on "Edit questions" "link" in the ".nav-tabs" "css_element"
    And I add a "4 level approval" question to the individualfeedback with:
      | Question                       | Do you like this course?           |
      | Label                          | 4levelapproval                     |
    And I follow "Logout"
    And I press "Continue"
    
    When I log in as "user1"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I follow "Preview"
    Then I should see "Do you like this course?"
    And I press "Continue"
    And I follow "Answer the questions..."
    And I should see "Do you like this course?"
    And I set the following fields to these values:
      | Strongly disagree | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    And I follow "Logout"
    And I press "Continue"
    
    When I log in as "user2"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I follow "Preview"
    Then I should see "Do you like this course?"
    And I press "Continue"
    And I follow "Answer the questions..."
    Then I should see "Do you like this course?"
    When I set the following fields to these values:
      | Strongly agree | 1 |
    And I press "Submit your answers"
    And I follow "Submitted answers"
    Then I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    When I change window size to "large"
    And I wait "2" seconds
    And I show chart data for the "4levelapproval" individualfeedback
    Then I should see "Do you like this course?"
    And I should see "1 (50 %)" in the "Strongly disagree" "table_row"
    And I should see "1 (50 %)" in the "Strongly agree" "table_row"
    And I follow "Logout"
    And I press "Continue"
    
    When I log in as "teacher"
    And I am on "Course 1" course homepage
    And I follow "Course individualfeedback"
    And I follow "Preview"
    Then I should see "Do you like this course?"
    And I press "Continue"
    And I click on "Show responses" "link" in the ".nav-tabs" "css_element"
    Then I should not see "Username"
    And I should see "Anonymous entries (2)"
    When I follow "Response number: 1"
    Then I should not see "Username"
    And I should see "Response number: 1 (Anonymous)"
    And I should not see "Prev"
    When I follow "Next"
    Then I should see "Response number: 2 (Anonymous)"
    And I should see "Prev"
    And I should not see "Next"

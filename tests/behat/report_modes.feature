@mod @mod_individualfeedback
Feature: Report modes and linked activities in the individualfeedback analysis
  In order to evaluate question groups and compare repeated surveys
  As a teacher
  I need to switch the analysis between report modes and duplicate-and-link an activity

  # Covers finding #9 of the client review 26.10.2026: the report fragments (H25) and the
  # duplicate-and-link action are reachable from the analysis page.

  Background:
    Given the following "users" exist:
      | username | firstname | lastname |
      | teacher  | Teacher   | 1        |
      | student1 | Student   | 1        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
    And the following "course enrolments" exist:
      | user     | course | role           |
      | teacher  | C1     | editingteacher |
      | student1 | C1     | student        |
    And the following "activities" exist:
      | activity           | name              | course | idnumber            | anonymous |
      | individualfeedback | Learning feedback | C1     | individualfeedback1 | 2         |
    And the following "mod_individualfeedback > question" exists:
      | activity     | individualfeedback1 |
      | name         | How was the lesson? |
      | label        | q1                  |
      | questiontype | multichoice         |
      | subtype      | r                   |
      | values       | good\nbad           |
    And the following "mod_individualfeedback > responses" exist:
      | activity            | user     | How was the lesson? |
      | individualfeedback1 | student1 | good                |

  @javascript
  Scenario: Teacher switches the analysis between report modes
    Given I am on the "Learning feedback" "individualfeedback activity" page logged in as teacher
    And I navigate to "Analysis" in current page administration
    And I should see "How was the lesson?"
    When I select "Overview (Groups)" from the "Report" singleselect
    Then I should see "Analysis"
    And the field "Report" matches value "Overview (Groups)"
    And I should not see "Comparison (Questions)" in the "Report" "select"
    When I select "Detail (Groups)" from the "Report" singleselect
    Then the field "Report" matches value "Detail (Groups)"

  @javascript
  Scenario: Teacher duplicates and links the activity and gets the comparison modes
    Given I am on the "Learning feedback" "individualfeedback activity" page logged in as teacher
    And I navigate to "Analysis" in current page administration
    When I click on "Duplicate and link activity" "link"
    Then I should see "Individual feedback activity is duplicated and linked."
    And the field "Report" matches value "Comparison (Questions)"
    And I should see "How was the lesson?"
    And I am on "Course 1" course homepage
    And I should see "Learning feedback (copy)"

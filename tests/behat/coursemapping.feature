@mod @mod_individualfeedback
Feature: Mapping courses in a individualfeedback
  In order to collect the same individualfeedback about multiple courses
  As a manager
  I need to be able to map site individualfeedback to courses

  Background:
    Given the following config values are set as admin:
      | enablemyhome | 1 |
    And the following "users" exist:
      | username | firstname | lastname |
      | user1    | Username  | 1        |
      | user2    | Username  | 2        |
      | user3    | Username  | 3        |
      | teacher  | Teacher   | 4        |
      | manager  | Manager   | 5        |
    And the following "courses" exist:
      | fullname | shortname |
      | Course 1 | C1        |
      | Course 2 | C2        |
      | Course 3 | C3        |
    And the following "course enrolments" exist:
      | user    | course | role    |
      | user1   | C1     | student |
      | user1   | C2     | student |
      | user2   | C1     | student |
      | user2   | C2     | student |
      | user3   | C3     | student |
      | teacher | C1     | editingteacher |
      | teacher | C2     | editingteacher |
      | teacher | C3     | editingteacher |
    And the following "system role assigns" exist:
      | user    | course               | role    |
      | manager | Acceptance test site | manager |
    And the following "activities" exist:
      | activity   | name             | course               | idnumber  | anonymous | publish_stats | section |
      | individualfeedback   | Course individualfeedback  | Acceptance test site | individualfeedback0 | 1         | 1             | 1       |
      | individualfeedback   | Another individualfeedback | C1                   | individualfeedback1 | 1         | 1             | 0       |
    # +++ MBS-Hack (nersesov) : core enables block_feedback here; the fork has no block (the renamed
    # "individualfeedback" block does not exist), so the block plugin and block instances are not created.
    # --- MBS-Hack
    When I log in as "manager"
    And I am on site homepage
    And I follow "Course individualfeedback"
    And I navigate to "Questions" in current page administration
    And I add a "Information" question to the individualfeedback with:
      | Question         | this is an information question |
      | Label            | info                            |
      | Information type | Course                          |
    And I add a "Multiple choice (rated)" question to the individualfeedback with:
      | Question               | this is a multiple choice rated    |
      | Label                  | multichoicerated                   |
      | Multiple choice type   | Multiple choice - single answer    |
      | Multiple choice values | 0/option a\n1/option b\n5/option c |
    And I add a "Multiple choice" question to the individualfeedback with:
      | Question               | this is a simple multiple choice    |
      | Label                  | multichoicesimple                   |
      | Multiple choice type   | Multiple choice - single answer allowed (drop-down menu) |
      | Multiple choice values | option d\noption e\noption f                           |
    And I log out

  Scenario: Course individualfeedback can not be mapped
    And I log in as "manager"
    And I am on "Course 1" course homepage
    And I follow "Another individualfeedback"
    And I should not see "Mapped courses"

  # +++ MBS-Hack (nersesov) : the following three core scenarios are removed in the fork:
  #   - "Site individualfeedback is not mapped to any course"
  #   - "Site individualfeedback is mapped to courses"
  #   - "Site individualfeedback deletion hides individualfeedback block completely"
  # They reach the site feedback from inside a course only through core block_feedback, which lists
  # mod_feedback instances and cannot be renamed for the fork. Course mapping itself (mapcourse.php,
  # per-course analysis) is inherited unchanged from core.
  # --- MBS-Hack

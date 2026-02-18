@mod @mod_individualfeedback
Feature: Anonymous individualfeedback
  In order to collect individualfeedbacks
  As an admin
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
    When I log in as "manager"
    And I am on site homepage
    And I follow "Site individualfeedback"
    And I navigate to "Questions" in current page administration
    And I add a "Multiple choice" question to the individualfeedback with:
      | Question                       | Do you like our site?              |
      | Label                          | multichoice2                       |
      | Multiple choice type           | Multiple choice - single answer    |
      | Hide the "Not selected" option | Yes                                |
      | Multiple choice values         | Yes\nNo\nI don't know              |
    And I log out

  Scenario: Guests can see anonymous individualfeedback on front page but can not complete
    When I follow "Site individualfeedback"
    Then I should not see "Answer the questions"
    And I should not see "Preview questions"

  Scenario: Complete anonymous individualfeedback on the front page as an authenticated user
    And I log in as "user1"
    And I am on site homepage
    When I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I should not see "Analysis"
    And I press "Continue"

  @javascript
  Scenario: Complete anonymous individualfeedback and view analysis on the front page as an authenticated user
    Given the following "role capability" exists:
      | role                         | frontpage |
      | mod/individualfeedback:viewanalysepage | allow     |
    And I log in as "user1"
    And I am on site homepage
    When I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I log out
    And I log in as "user2"
    And I am on site homepage
    And I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I set the following fields to these values:
      | No | 1 |
    And I press "Submit your answers"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303 do not show labels to users who can not edit individualfeedback
    And I show chart data for the "multichoice2" individualfeedback
    And I should see "Do you like our site?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
    And I log out
    And I log in as "manager"
    And I am on site homepage
    And I follow "Site individualfeedback"
    And I navigate to "Responses" in current page administration
    And I should not see "Username"
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should not see "Username"
    And I should see "Response number: 1 (Anonymous)"
    And I log out

  Scenario: Complete fully anonymous individualfeedback on the front page as a guest
    Given the following config values are set as admin:
      | individualfeedback_allowfullanonymous | 1 |
    When I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I should not see "Analysis"
    And I press "Continue"

  @javascript
  Scenario: Complete fully anonymous individualfeedback and view analyze on the front page as a guest
    Given the following config values are set as admin:
      | individualfeedback_allowfullanonymous | 1 |
    And the following "role capability" exists:
      | role                         | guest |
      | mod/individualfeedback:viewanalysepage | allow |
    When I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I press "Continue"
    # Starting new individualfeedback
    When I follow "Site individualfeedback"
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like our site?"
    And I set the following fields to these values:
      | No | 1 |
    And I press "Submit your answers"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303
    And I show chart data for the "multichoice2" individualfeedback
    And I should see "Do you like our site?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
    And I log in as "manager"
    And I am on site homepage
    And I follow "Site individualfeedback"
    And I navigate to "Responses" in current page administration
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should see "Response number: 1 (Anonymous)"
    And I log out

  @javascript
  Scenario: Anonymous individualfeedback in a course
    # Teacher can not
    When I am on the "Course individualfeedback" "individualfeedback activity" page logged in as teacher
    And I navigate to "Questions" in current page administration
    And I add a "Multiple choice" question to the individualfeedback with:
      | Question                       | Do you like this course?           |
      | Label                          | multichoice1                       |
      | Multiple choice type           | Multiple choice - single answer    |
      | Hide the "Not selected" option | Yes                                |
      | Multiple choice values         | Yes\nNo\nI don't know              |
    And I log out

    And I am on the "Course individualfeedback" "individualfeedback activity" page logged in as user1
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like this course?"
    And I set the following fields to these values:
      | Yes | 1 |
    And I press "Submit your answers"
    And I log out
    And I am on the "Course individualfeedback" "individualfeedback activity" page logged in as user2
    And I should not see "Preview questions"
    And I follow "Answer the questions"
    And I should see "Do you like this course?"
    And I set the following fields to these values:
      | No | 1 |
    And I press "Submit your answers"
    And I follow "Analysis"
    And I should see "Submitted answers: 2"
    And I should see "Questions: 1"
    # And I should not see "multichoice2" # TODO MDL-29303
    And I show chart data for the "multichoice1" individualfeedback
    And I should see "Do you like this course?"
    And I should see "1 (50.00 %)" in the "Yes" "table_row"
    And I should see "1 (50.00 %)" in the "No" "table_row"
    And I log out
    And I am on the "Course individualfeedback" "individualfeedback activity" page logged in as teacher
    And I follow "Preview"
    And I should see "Do you like this course?"
    And I press "Continue"
    And I should not see "Answer the questions"
    And I navigate to "Responses" in current page administration
    And I should not see "Username"
    And I should see "Anonymous entries (2)"
    And I follow "Response number: 1"
    And I should not see "Username"
    And I should see "Response number: 1 (Anonymous)"
    And I should not see "Prev"
    And I follow "Next"
    And I should see "Response number: 2 (Anonymous)"
    And I should see "Prev"
    And I should not see "Next"
    And I click on "Back" "link" in the "[role=main]" "css_element"
    # Delete anonymous response
    And I click on "Delete entry" "link" in the "Response number: 1" "table_row"
    And I press "Yes"
    And I should see "Anonymous entries (1)"
    And I should not see "Response number: 1"
    And I should see "Response number: 2"

  Scenario: Collecting new non-anonymous individualfeedback from a previously anonymous individualfeedback activity
    When I am on the "Course individualfeedback" "individualfeedback activity" page logged in as teacher
    And I navigate to "Settings" in current page administration
    And I set the following fields to these values:
      | Allow multiple submissions | Yes |
    And I press "Save and display"
    And I follow "Edit questions"
    And I add a "Short text answer" question to the individualfeedback with:
      | Question               | this is a short text answer |
      | Label                  | shorttext                   |
      | Maximum characters accepted | 200                    |
    And I log out
    When I am on the "Course individualfeedback" "individualfeedback activity" page logged in as user1
    And I follow "Answer the questions"
    And I set the following fields to these values:
      | this is a short text answer  | anontext |
    And I press "Submit your answers"
    And I log out
    # Switch to non-anon responses.
    And I am on the "Course individualfeedback" "individualfeedback activity editing" page logged in as teacher
    And I set the following fields to these values:
        | Record user names | User's name will be logged and shown with answers |
    And I press "Save and display"
    And I log out
    # Now leave a non-anon individualfeedback as user1
    And I am on the "Course individualfeedback" "individualfeedback activity" page logged in as user1
    And I follow "Answer the questions"
    And I set the following fields to these values:
      | this is a short text answer  | usertext |
    And I press "Submit your answers"
    And I log out
    # Now check the responses are correct.
    And I am on the "Course individualfeedback" "individualfeedback activity" page logged in as teacher
    And I follow "Responses"
    And I should see "Anonymous entries (1)"
    And I should see "Non anonymous entries (1)"
    And I click on "," "link" in the "Username 1" "table_row"
    And I should see "(Username 1)"
    Then the field "this is a short text answer" matches value "usertext"
    And I navigate to "Responses" in current page administration
    And I follow "Response number: 1"
    And I should see "Response number: 1 (Anonymous)"
    And the field "this is a short text answer" matches value "anontext"

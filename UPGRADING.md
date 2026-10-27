# mod_individualfeedback Upgrade notes

## 5.2

### Deprecated

- The method `individualfeedback_init_individualfeedback_session()` has been deprecated, along with all other direct access to `$SESSION` from the module

  For more information see [MDL-86607](https://tracker.moodle.org/browse/MDL-86607)

### Removed

- - The following files have been removed:
    - `public/mod/individualfeedback/edit_form.php`.
    - `public/mod/individualfeedback/use_templ_form.php`.

  For more information see [MDL-87425](https://tracker.moodle.org/browse/MDL-87425)

## 5.1

### Added

- Two new methods, `individualfeedback_get_completeds` and `individualfeedback_get_completeds_count`, have been added to the individualfeedback API. These methods allow you to retrieve completed items based on multiple groups.

  For more information see [MDL-85850](https://tracker.moodle.org/browse/MDL-85850)

## 5.0

### Added

- Added new `mod_individualfeedback_questions_reorder` external function

  For more information see [MDL-81745](https://tracker.moodle.org/browse/MDL-81745)

### Deprecated

- The 'mode' parameter has been deprecated from 'edit_template_action_bar' and 'templates_table' contructors.

  For more information see [MDL-81744](https://tracker.moodle.org/browse/MDL-81744)

### Removed

- The 'use_template' template has been removed as it is not needed anymore.

  For more information see [MDL-81744](https://tracker.moodle.org/browse/MDL-81744)

## 4.5

### Deprecated

- The `\individualfeedback_check_is_switchrole()` function has been deprecated as it didn't work.

  For more information see [MDL-72424](https://tracker.moodle.org/browse/MDL-72424)
- The method `\mod_individualfeedback\output\renderer::create_template_form()` has been deprecated. It is not used anymore.

  For more information see [MDL-81742](https://tracker.moodle.org/browse/MDL-81742)

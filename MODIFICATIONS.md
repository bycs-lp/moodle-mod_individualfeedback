# MODIFICATIONS

This document records all functional and structural changes made to the mod_individualfeedback plugin compared to the original mod_feedback plugin.

## JavaScript

### Logic changes in `amd/src/edit.js`
- Added logic to detect when the item to be deleted is a question group and, in this case, display a specific confirmation string (`confirmdeleteitem_questiongroup`).
- In the original, only the standard `confirmdeleteitem` string was used for all items.

### New JavaScript files in `amd/src/`
- `movequestiongroup.js`: Implements drag-and-drop reordering of question groups.
- `filterquestiongroup.js`: Adds filtering functionality to show/hide question groups in the analysis UI.

## Mustache

### Modified templates
- `summary.mustache`:
  - All references to `feedback` were changed to `individualfeedback` (template name, CSS classes, string components).
  - The context and example block were updated to reflect the new plugin's data structure.
  - Additional fields and logic may have been added to support new features (e.g., `timeopen`, `timeclose`).

## PHP Files with new code (hack was not possible)

- ajax.php
- analysis.php
- analysis_to_excel.php
- classes/form/create_template_form.php
- classes/responses_anon_table.php
- edit.php
- edit_item.php
- item/captcha/captcha_form.php
- item/multichoice/lib.php
- item/multichoice/multichoice_form.php
- item/multichoicerated/multichoicerated_form.php
- item/numeric/numeric_form.php
- item/questiongroup/questiongroup_form.php
- item/textarea/textarea_form.php
- item/textfield/textfield_form.php
- lib.php
- manage_templates.php

## Files with hack

The following files use the hack-based extraction/refactorization model. In these cases, the main logic was moved to static methods in hack classes (e.g., `mod_individualfeedback\hack\lib`), and the original file contains only conditional calls to these methods. This approach was used to maximize code reuse and maintainability while ensuring independence from the original plugin.

- classes/output/edit_action_bar.php
- edit.php
- edit_form.php
- item/fivelevelapproval/lib.php
- item/fourlevelapproval/lib.php
- item/fourlevelfrequency/lib.php
- item/individualfeedback_item_class.php
- item/info/lib.php
- item/multichoice/lib.php
- item/multichoicerated/lib.php
- item/numeric/lib.php
- item/textarea/lib.php
- item/textfield/lib.php
- lib.php
- manage_templates.php

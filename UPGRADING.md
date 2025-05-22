# mod_individualfeedback Upgrade notes

## 4.6.1

- As we relay on the same DB structure as before, we DO NOT need to run a /db/upgrade.php script. All we actually only need to do, is to rename / move the current directory.
- And clone from github-repo ( https://github.com/lernlink/moodle-mod_individualfeedback) or copy the code manually into the /mod/individualfeedback/ directory.

## 4.6

- From 4.5 to 4.6 we have a major leap as the main sourcecode was uplifted to the base version of the newest moodle 4.5. 
- With the introduction of version 2024101500 and later all the deperached code that was build upon the base version of mod_feedback for moodle 3.2+ has been replaced.
- The bugs have been removed (like not working drap-and drop function etc.) and all the prior self developed functionality of ByCS has been added and verified.

## 4.5dev+

### Deprecated

- The `individualfeedback_check_is_switchrole` method has been deprecated as it didn't work

  For more information see [MDL-72424](https://tracker.moodle.org/browse/MDL-72424)
- The method `mod_feedback\output\renderer::create_template_form()` has been deprecated. It is not used anymore.

  For more information see [MDL-81742](https://tracker.moodle.org/browse/MDL-81742)
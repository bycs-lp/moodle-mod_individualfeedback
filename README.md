moodle-mod_individualfeedback
===========================


Requirements
------------

This plugin requires Moodle 4.5+


Motivation for this plugin
--------------------------

The motivation of this plugin was to create new options to original feedback plugin. Things like new types of questions and comparisons of reports were added.


Installation
------------

Install the plugin like any other plugin to folder
/mod/individualfeedback

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins

You may install using the command "git clone https://github.com/lernlink/moodle-mod_individualfeedback.git".


Usage & Settings
----------------

After installing the plugin, it creates tables associated with it. There is only one configuration related to anonymous surveys. If the option is set to 'yes', users can complete a individual feedback activity on the front page without being required to log in.


Security implications
---------------------

This tool will only work within the moodle context. Therefore no external sources, scripts or code is being loaded. No security issues for potential breaching or cross site scripting are given. 


Theme support
-------------

This plugin acts behind the scenes, therefore it should work with all Moodle themes.
This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

The latest development version can be found on Github:
https://github.com/lernlink/moodle-mod_individualfeedback


Bug and problem reports
-----------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/lernlink/moodle-mod_individualfeedback/issues



Community feature proposals
---------------------------

The functionality of this plugin is primarily implemented for the needs of our clients and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/lernlink/moodle-mod_individualfeedback/issues

Please create pull requests on Github:
https://github.com/lernlink/moodle-mod_individualfeedback/pulls


Moodle release support
----------------------

This plugin is only maintained for the most recent major release of Moodle as well as the most recent LTS release of Moodle. Bugfixes are backported to the LTS release. However, new features and improvements are not necessarily backported to the LTS release.

Apart from these maintained releases, previous versions of this plugin which work in legacy major releases of Moodle are still available as-is without any further updates in the Moodle Plugins repository.

There may be several weeks after a new major release of Moodle has been published until we can do a compatibility check and fix problems if necessary. If you encounter problems with a new major release of Moodle - or can confirm that this plugin still works with a new major release - please let us know on Github.

If you are running a legacy version of Moodle, but want or need to run the latest version of this plugin, you can get the latest version of the plugin, remove the line starting with $plugin->requires from version.php and use this latest plugin version then on your legacy Moodle. However, please note that you will run this setup completely at your own risk. We can't support this approach in any way and there is an undeniable risk for erratic behavior.


Translating this plugin
-----------------------

This Moodle plugin is shipped with an english and german language pack only. All translations into other languages must be managed through AMOS (https://lang.moodle.org) by what they will become part of Moodle's official language pack.

As the plugin creator, we manage the translation into german for our own local needs on AMOS. Please contribute your translation into all other languages in AMOS where they will be reviewed by the official language pack maintainers for Moodle.


Right-to-left support
---------------------

This plugin has not been tested with Moodle's support for right-to-left (RTL) languages.
If you want to use this plugin with a RTL language and it doesn't work as-is, you are free to send us a pull request on Github with modifications.


Maintainers
-----------

lern.link GmbH


Copyright
---------

lern.link GmbH\
Marcelo Augusto Rauh Schmitt
Vadym Nersesov


Credits
-------
This plugin is based on the feedback plugin from Moodle 4.5.3

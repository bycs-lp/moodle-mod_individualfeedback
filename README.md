moodle-mod_individualfeedback
===========================

[![Moodle Plugin CI](https://github.com/lernlink/moodle-availability_completiondelay/workflows/Moodle%20Plugin%20CI/badge.svg?branch=master)](https://github.com/lernlink/moodle-availability_completiondelay/actions?query=workflow%3A%22Moodle+Plugin+CI%22+branch%3Amaster)

Moodle availability extension to set a minmimal studytime before the element with this availability can be accessed


Requirements
------------

This plugin requires Moodle 4.2+


Motivation for this plugin
--------------------------

A customer had the need, that his participants had to spend a minimal amount of time within a course, before a certificate was allowed to be offered / published. To comply with these legal regulations, we had to measure the startpoint from a course. With this plugin, you are able to define a new availability setting for each of your activities within a course. By doing so, you can reduce the access to the later activity by a amount of time, (e.g. 1,2, or 3 hours)

The setting will be referred to an initial element from within this specific course. This referred element could be anywhere in the course but for our intention it is most usefule to put it right up to the top
As soon as this element is accomplished (e.g. by simply viewing it / displaying it to the user) a timestamp is issued. This timestamp will be very relevant for the functionality of this plugin.


Limitation of this plugin
--------------------------

We are well aware that the tracking of time can be tricked by the user and might result in some inaccuracies. The user could start the course, that go AFK for 2-3 hours and eventually return to click on the final link and overcome this barrier of minimal study time. As he as not really studied, but only stayed within this course for a given time.
Nevertheless the tracking of a given start time point and bringing it into relation to time.now() to evaluate, if the user can already access this limited element was sufficieant for the usercase of the customer. Therefore we decided not to implement a complex minute count or a log-file evaluation.



Installation
------------

Install the plugin like any other plugin to folder
/mod/individualfeedback

See http://docs.moodle.org/en/Installing_plugins for details on installing Moodle plugins


Usage & Settings
----------------

After installing the plugin, it does not do anything to Moodle yet.

To configure the plugin and its behaviour, please visit and activity within a course that you want to limit by this plugins feature.

When you edit the mod's availability conditions and select this plugins, there you find two settings:

### 1. Refering Base Activity

This setting expects that you select any given mod element as a base element from the course. As soon as this element is marked as accomplished, the time is counted untill when the user can access the activity, modified by this plugin.

### 2. Amount of minimal study time in minutes

Moodle will ask you, how long a user must be within this couse before this new condition is activated and opens the attached element for usage or download. 
The possible settings in minutes are (0, 60, 120, 180). So basically 1) deactivated 2) 1h 2) 2h and 3) 3h.


Security implications
---------------------

This tool will only work within the moodle context. Therefore no external sources, scripts or code is being loaded. No security issues for potential breaching or cross site scripting are given. This script only reduces the availability of a given activity, hence it is only reducing a view, but potentially not expanding or adding further content. Thus the security implication must be very low.


Theme support
-------------

This plugin acts behind the scenes, therefore it should work with all Moodle themes.
This plugin is developed and tested on Moodle Core's Boost theme.
It should also work with Boost child themes, including Moodle Core's Classic theme. However, we can't support any other theme than Boost.


Plugin repositories
-------------------

This plugin is published and regularly updated in the Moodle plugins repository:
http://moodle.org/plugins/view/availability_completiondelay

The latest development version can be found on Github:
https://github.com/lernlink/moodle-availability_completiondelay


Bug and problem reports
-----------------------

This plugin is carefully developed and thoroughly tested, but bugs and problems can always appear.

Please report bugs and problems on Github:
https://github.com/lernlink/moodle-availability_completiondelay/issues


Community feature proposals
---------------------------

The functionality of this plugin is primarily implemented for the needs of our clients and published as-is to the community. We are aware that members of the community will have other needs and would love to see them solved by this plugin.

Please issue feature proposals on Github:
https://github.com/lernlink/moodle-availability_completiondelay/issues

Please create pull requests on Github:
https://github.com/lernlink/moodle-availability_completiondelay/pulls


Paid support
------------

We are always interested to read about your issues and feature proposals or even get a pull request from you on Github. However, please note that our time for working on community Github issues is limited.

As certified Moodle Partner, we also offer paid support for this plugin. If you are interested, please have a look at our services on https://lern.link or get in touch with us directly via team@lernlink.de.


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

lern.link GmbH\
Danou Nauck


Copyright
---------

lern.link GmbH\
Danou Nauck


Credits
-------
This plugin is based on the availability_completion plugin from 2014 by The Open University
=== SF Author Url Control ===
Contributors: GregLone
Tags: author, custom, customize, permalink, slug, url, author base, author slug, nicename, user, users
Requires at least: 3.0
Tested up to: 4.5
Stable tag: trunk
License: GPLv3
License URI: https://www.screenfeed.fr/gpl-v3.txt

Allows administrators or capable users to change the users profile url.

== Description ==
With this plugin, administrators can change the default author base in the registered users profile url, and the author slug of each user.
Changing an author slug is a good thing for security (if your login is "This Is Me", your slug will be "this-is-me", a bit easy to guess).
The plugin adds 2 fields for this purpose, one in permalinks settings, the other in a user profile.

* Default: *example.com/author_base/author_nicename/*
* Customized: *example.com/jedi/obiwan/*

= How to edit the slugs =
* Go to *Settings* > *Permalinks* to edit the author base: "author" => "jedi"
* Go to *Users* > *"Any user profile"* to edit the user slug: "agent-smith" => "obiwan"

= Translations =
* English
* French
* German by [Carny88](http://wordpress.org/support/profile/carny88)
* Spanish by [Oliver](http://wordpress.org/support/profile/oliverbar)

= Multisite =
* The plugin is ready for Multisite.


== Installation ==

1. Extract the plugin folder from the downloaded ZIP file.
1. Upload sf-author-url-control folder to your *"/wp-content/plugins/"* directory.
1. Activate the plugin from the "Plugins" page.
1. Go to *Settings* > *Permalinks* to edit the author base.
1. Go to *Users* > *Any user edit page* to edit the user slug (nicename).


== Frequently Asked Questions ==

= Why the fields don't display? =
You probably don't have the edit_users capability (for user slug) and *manage_options* capability (for author base).

Eventually, check out [my blog](https://www.screenfeed.fr/sfauc/) for more infos or tips (sorry guys, it's in french, but feel free to leave a comment in english).

= Will I keep the customized links if I uninstall the plugin? =
Not exactly. The author base won't be customized anymore, but each user will keep his customized author slug.
To retrieve the initial author slugs, empty the field on each user edit page (only the customized ones of course).


== Screenshots ==
1. The permalinks settings page
1. The user edit page


== Changelog ==

= 1.2 =
* 2016/04/03
* Tested with WP 4.5.
* Numerous small code improvements.
* Now the link to *SX User Name Security* displays the plugin infos window instead of installing it directly.

= 1.1.2 =
* 2014/03/26
* If the author base has not been modified, add a link in the profile page to the permalinks settings page (administrators only).
* Added an advice in the permalinks settings page to install the plugin *SX User Name Security*, which is a good companion for *SF Author Url Control*.
* Updated French translation.

= 1.1.1 =
* 2014/03/01
* Bugfix: the activation message was printed too soon in the page.
* Small code improvements.

= 1.1 =
* 2013/11/30
* Lots of code improvements.
* New filter `sf_auc_user_can_edit_user_slug` to allow other roles/users to edit their own profile slug (the editors for example).
* The tests for the author base should be more accurate (check if the slug already exists for something else).
* Updated the i18n domain for WP 3.7.
* Added Spanish translation. Thanks Oliver!
* Updated French translation for the plugin description.

= 1.0.5 =
* 2013/09/21
* Bugfix: the author base couldn't be changed in WP 3.7-alpha

= 1.0.4 =
* 2013/09/13
* Small security fix.

= 1.0.3 =
* 2013/09/12
* Added German translation. Thanks Carny88!

= 1.0.2 =
* 2013/01/27
* Bug fix in permalinks page
* In the user profile page, add a link to the public author page
* Small code improvements

= 1.0.1 =
* 2012/04/16
* Minor bug fix

= 1.0 =
* 2012/04/16
* First public release

== Upgrade Notice ==

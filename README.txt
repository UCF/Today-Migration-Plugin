=== Today Migration Plugin ===
Contributors: ucfwebcom
Requires at least: 4.9.7
Tested up to: 4.9.7
Stable tag: 1.0.0
Requires PHP: 5.4
License: GPLv3 or later
License URI: http://www.gnu.org/copyleft/gpl-3.0.html

Provides a series of wp cli tasks for manipulating data used by the old Today-Bootstrap theme to work with the new theme and associated plugins.


== Description ==

Provides a series of wp cli tasks for manipulating data used by the old Today-Bootstrap theme to work with the new theme and associated plugins.

=== Commands ===

* `wp today migrate meta`
    * Converts post_meta keys from the ones used on the old Today site to the keys used in the new site.
* `wp today migrate featured`
    * Copies the featured image id of each post to a custom post_meta field.
* `wp today migrate classes`
    * Performs a simple regex search/replace using an array of CSS Classes to update old classes to ones available in Athena.
* `wp today migrate all`
    * Runs the above three commands in sequence.


== Changelog ==

= 1.0.0 =
* Initial release


== Upgrade Notice ==

n/a


== Development ==

[Enabling debug mode](https://codex.wordpress.org/Debugging_in_WordPress) in your `wp-config.php` file is recommended during development to help catch warnings and bugs.

= Requirements =
* node
* gulp-cli
* wp cli

= Instructions =
1. Clone the Today-Migration-Plugin repo into your local development environment, within your WordPress installation's `plugins/` directory: `git clone https://github.com/UCF/Today-Migration-Plugin.git`
2. `cd` into the new Today-Migration-Plugin directory, and run `npm install` to install required packages for development into `node_modules/` within the repo
3. Optional: If you'd like to enable [BrowserSync](https://browsersync.io) for local development, or make other changes to this project's default gulp configuration, copy `gulp-config.template.json`, make any desired changes, and save as `gulp-config.json`.

    To enable BrowserSync, set `sync` to `true` and assign `syncTarget` the base URL of a site on your local WordPress instance that will use this plugin, such as `http://localhost/wordpress/my-site/`.  Your `syncTarget` value will vary depending on your local host setup.

    The full list of modifiable config values can be viewed in `gulpfile.js` (see `config` variable).
4. Run `gulp default` to process front-end assets.
5. If you haven't already done so, create a new WordPress site on your development environment to test this plugin against.
6. Activate this plugin on your development WordPress site.

= Composer Installation =
1. Install using `wp package install git@github.com:UCF/Today-Migration-Plugin.git` or `wp package install https://github.com/UCF/Today-Migration-Plugin.git`.

Note: If you get a `WP-CLI ran out of memory` error when trying to install, you can either increase the `memory_limit` parameter in your `php.ini` file, or you can run the following command for a temporary workaround:

```
php -d memory_limit=512M "$(which wp)" package install git@github.com:UCF/Today-Migration-Plugin.git
```

= Other Notes =
* This plugin's README.md file is automatically generated. Please only make modifications to the README.txt file, and make sure the `gulp readme` command has been run before committing README changes.  See the [contributing guidelines](https://github.com/UCF/Today-Migration-Plugin/blob/master/CONTRIBUTING.md) for more information.


== Contributing ==

Want to submit a bug report or feature request?  Check out our [contributing guidelines](https://github.com/UCF/Today-Migration-Plugin/blob/master/CONTRIBUTING.md) for more information.  We'd love to hear from you!

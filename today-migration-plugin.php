<?php
/*
Plugin Name: Today Migration Plugin
Description: Provides a series of wp cli tasks for manipulating data used by the old Today-Bootstrap theme to work with the new theme and associated plugins.
Version: 1.0.0
Author: UCF Web Communications
License: GPL3
GitHub Plugin URI: UCF/Today-Migration-Plugin
*/

if ( ! defined( 'WPINC' ) ) {
    die;
}

if ( defined( 'WP_CLI' ) && WP_CLI ) {
	require_once dirname( __FILE__ ) . '/commands/class-meta-migrate.php';
	require_once dirname( __FILE__ ) . '/commands/class-featured-image-migrate.php';
}

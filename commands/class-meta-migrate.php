<?php
/**
 * Migrates meta data from old Today-Bootstrap structure
 * to the newer structure
 */
if ( ! class_exists( 'Today_Migration_Meta' ) ) {
	class Today_Migration_Meta {
		private
			$mapping = array(
				'old-meta-key' => 'new-meta-key'
			);

		/**
		 * Converts meta fields
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate meta
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			print "Hello World";
		}
	}

	WP_CLI::add_command( 'today migrate meta', 'Today_Migration_Meta' );
}
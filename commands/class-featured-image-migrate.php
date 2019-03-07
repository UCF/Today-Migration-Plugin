<?php
/**
 * Migrates featured story images to a custom meta field
 */
if ( ! class_exists( 'Today_Migration_Featured_Image' ) ) {
	class Today_Migration_Featured_Image {
		private
			$custom_meta_field = 'the-new-custom-meta-field';

		/**
		 * Converts featured images to a custom meta field.
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate featured
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			print "Hello World";
		}
	}

	WP_CLI::add_command( 'today migrate featured', 'Today_Migration_Featured_Image' );
}
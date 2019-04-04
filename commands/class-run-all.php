<?php
/**
 * Runs all the configured tasks.
 */
if ( ! class_exists( 'Today_Migration_All' ) ) {
	class Today_Migration_All {
		/**
		 * Runs all the migrate commands
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate all
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			$ext_stories = new Today_Migration_ExternalStories();
			$ext_stories->__invoke( $args );

			$content = new Today_Migration_Post_Content();
			$content->__invoke( $args );

			$meta = new Today_Migration_Meta();
			$meta->__invoke( $args );

			$feature = new Today_Migration_Featured_Image();
			$feature->__invoke( $args );

			$css = new Today_Migration_CSS_Classes();
			$css->__invoke( $args );

			$tags = new Today_Migration_Tag_Removal();
			$tags->__invoke( $args );

			$dups = new Today_Migration_Duplicate_Images();
			$dups->__invoke( $args );

			WP_CLI::success( "Finished running all tasks." );
		}
	}

	WP_CLI::add_command( 'today migrate all', 'Today_Migration_All' );
}

<?php
/**
 * Migrates External Stories from Today-Bootstrap to Resource Links
 */
if ( ! class_exists( 'Today_Migration_ExternalStories' ) ) {
	class Today_Migration_ExternalStories {
		private
			$progress,
			$count = 0;

		/**
		 * Converts meta fields
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate externalstories
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			$posts = get_posts( array(
				'post_type'      => 'externalstory',
				'posts_per_page' => -1,
				'post_status'    => 'any'
			) );

			$count = count( $posts );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Converting External Stories to Resource Links...",
				$count
			);

			foreach ( $posts as $post ) {
				$this->convert_external_story( $post );
				$this->progress->tick();
			}

			$this->progress->finish();

			WP_CLI::success( "Converted $count External Stories to Resource Links." );
		}

		/**
		 * Helper function to convert a single External Story
		 * to a Resource Link
		 * @author Jo Dickson
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_external_story( $post ) {
			$post_id = $post->ID;

			// TODO
		}
	}

	WP_CLI::add_command( 'today migrate externalstories', 'Today_Migration_ExternalStories' );
}

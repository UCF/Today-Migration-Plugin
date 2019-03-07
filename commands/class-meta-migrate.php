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
			),
			$progress;

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
			$posts = get_posts( array(
				'posts_per_page' => -1,
				'post_status'    => 'any'
			) );

			$count = count( $posts );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Converting post meta...",
				$count
			);

			for ( $posts as $post ) {
				$this->convert_meta( $post );
				$this->progress.tick();
			}

			$this->progress.finish();

			WP_CLI::success( "Covnerted post meta for $count posts." );
		}

		/**
		 * Helper function to convert meta fields
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_meta( $post ) {
			$post_id = $post->ID;

			foreach( $this->mapping as $old_key => $new_key ) {
				$value = get_post_meta( $post_id, $old_key );

				if ( count( $value ) === 1 ) {
					$value = $value[0];
				}

				update_post_meta( $post_id, $new_key, $value );
			}
		}
	}

	WP_CLI::add_command( 'today migrate meta', 'Today_Migration_Meta' );
}
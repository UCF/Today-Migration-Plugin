<?php
/**
 * Migrates featured story images to a custom meta field
 */
if ( ! class_exists( 'Today_Migration_Featured_Image' ) ) {
	class Today_Migration_Featured_Image {
		private
			$custom_meta_field = 'post_header_image',
			$progress,
			$converted;

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
			$posts = get_posts( array(
				'posts_per_page' => -1,
				'post_status'    => 'any'
			) );

			$count = count( $posts );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Converting featured images...",
				$count
			);

			foreach ( $posts as $post ) {
				$this->convert_featured_image( $post );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Converted $this->converted featured images out of $count processed posts." );
		}

		/**
		 * Helper function that converts the featured_image
		 * to a meta field
		 */
		private function convert_featured_image( $post ) {
			$post_id  = $post->ID;
			$image_id = get_post_thumbnail_id( $post_id );

			if ( $image_id ) {
				update_post_meta( $post_id, $this->custom_meta_field, $image_id );
				$this->converted++;
			}
		}
	}

	WP_CLI::add_command( 'today migrate featured', 'Today_Migration_Featured_Image' );
}
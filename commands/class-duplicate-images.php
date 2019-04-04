<?php
/**
 * Removes duplicate images when the featured
 * image also appears in the content
 */
if ( ! class_exists( 'Today_Migration_Duplicate_Images' ) ) {
	class Today_Migration_Duplicate_Images {
		private
			$acf_header_image_id = 'field_5c813f8ac81b8',
			$progress,
			$removed_images = 0;

		/**
		 * Removed duplicate images from post_content when
		 * the image is already set as the header_image
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate duplicate-images
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
				'Removing duplicate images from posts...',
				$count
			);

			foreach( $posts as $post ) {
				$this->remove_duplicate_images( $post );
				$this->progress->tick();
			}

			$this->progress->tick();
			WP_CLI::success( "Removed $this->removed_images images from $count posts." );
		}

		/**
		 * Removes the header image from the post content
		 * if it appears.
		 * @author Jim Barnes
		 * @since 1.0.2
		 * @param WP_Post $post The post object
		 */
		private function remove_duplicate_images( $post ) {
			$image = get_field( $this->acf_header_image_id, $post->ID );

			// No header image, then return
			if ( ! $image ) return;

			var_dump( $image );
		}
	}

	WP_CLI::add_command( 'today migrate duplicate-images', 'Today_Migration_Duplicate_Images' );
}

<?php
/**
 * Migrates featured story images to a custom meta field
 */
if ( ! class_exists( 'Today_Migration_Featured_Image' ) ) {
	class Today_Migration_Featured_Image {
		private
			$acf_field_id = 'field_5c813f8ac81b8', // post_header_image
			$progress,
			$converted = 0;

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
			$image_id = $this->get_post_primary_image_id( $post_id );

			if ( $image_id ) {
				update_field( $this->acf_field_id, $image_id, $post_id );
				$this->converted++;
			}
		}

		/**
		 * Returns the ID of the featured image, if set, or the
		 * ID of the first available attachment for the post.
		 */
		private function get_post_primary_image_id( $post_id ) {
			$image_id = null;

			$thumbnail_id = get_post_thumbnail_id( $post_id );

			if ( $thumbnail_id ) {
				$image_id = $thumbnail_id;
			}
			else {
				$attachments = get_attached_media( 'image', $post_id );
				if ( is_array( $attachments ) && ! empty( $attachments ) ) {
					// Get the first attachment ID returned
					reset( $attachments );
					$image_id = key( $attachments );
				}
			}

			return $image_id;
		}
	}

	WP_CLI::add_command( 'today migrate featured', 'Today_Migration_Featured_Image' );
}

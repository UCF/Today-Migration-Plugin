<?php
/**
 * Migrates featured story images to a custom meta field
 */
if ( ! class_exists( 'Today_Migration_Featured_Image' ) ) {
	class Today_Migration_Featured_Image {
		private
			$acf_header_image_id = 'field_5c813f8ac81b8', // post_header_image
			$acf_header_video_id = 'field_5c814048c81bb', // post_header_video_url
			$acf_header_type_id = 'field_5c813fb7c81b9', // header_media_type
			$progress,
			$converted_images = 0,
			$converted_header_types = 0;

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
				$this->set_header_media_type( $post );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Converted $this->converted_images featured images out of $count processed posts." );
		}

		/**
		 * Helper function that converts the featured_image
		 * to a meta field
		 */
		private function convert_featured_image( $post ) {
			$post_id  = $post->ID;
			$image_id = $this->get_post_primary_image_id( $post_id );

			if ( $image_id ) {
				update_field( $this->acf_header_image_id, $image_id, $post_id );
				$this->converted_images++;
			}
		}

		/**
		 * Updates the "Header Media Type" field for the post
		 * depending on whether a header video or just an image
		 * is available.
		 */
		private function set_header_media_type( $post ) {
			$post_id           = $post->ID;
			$video_url         = get_field( $this->acf_header_image_id, $post_id, false );
			$image_url         = get_field( $this->acf_header_video_id, $post_id, false );
			$header_media_type = '';

			if ( $video_url ) {
				$header_media_type = 'video';
			}
			else if ( $image_url ) {
				$header_media_type = 'image';
			}

			if ( $header_media_type ) {
				update_field( $this->$acf_header_type_id, $header_media_type, $post_id );
				$this->converted_header_types++;
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

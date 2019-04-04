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
			global $wpdb;
			$image = get_field( $this->acf_header_image_id, $post->ID );

			// No header image, then return
			if ( ! $image ) return;

			$pattern = '/(\[caption.*?\])?<img.*?src="(';

			$img_urls = array();

			foreach( $image['sizes'] as $key => $val ) {
				if ( strpos( $key, '-width' ) === false && strpos( $key, '-height' ) === false ) {
					$img_urls[] = preg_quote( $val, '/' );
				}
			}

			$pattern .= implode( '|', $img_urls ) . ')".*?\/?>.*?(\[\/caption\])?/i';

			$post_content = $post->post_content;

			$post_content = preg_replace( $pattern, '', $post_content );

			if ( $post->post_content !== $post_content ) {
				$update_status = $wpdb->update( $wpdb->posts, array( 'post_content' => $post_content ), array( 'ID' => $post->ID ) );
				if ( $update_status !== false ) {
					$this->removed_images++;
					clean_post_cache( $post->ID );
				}
			}
		}
	}

	WP_CLI::add_command( 'today migrate duplicate-images', 'Today_Migration_Duplicate_Images' );
}

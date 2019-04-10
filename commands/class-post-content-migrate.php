<?php
/**
 * Filters old post content to strip out undesirable tags.
 */
if ( ! class_exists( 'Today_Migration_Post_Content' ) ) {
	class Today_Migration_Post_Content {
		private
			$allowed_tags = '<p><a><ol><ul><li><em><strong><img><blockquote><div>',
			$progress,
			$converted = 0;

		/**
		 * Filters old post content to strip out undesirable tags.
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate content
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			$posts = get_posts( array(
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' )
			) );

			$count = count( $posts );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Updating Post Content...",
				$count
			);

			foreach ( $posts as $post ) {
				$this->filter_post_content( $post );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Updated post content within $this->converted posts out of $count processed posts." );
		}

		/**
		 * Helper function that filters post content to remove
		 * undesirable tags from stories using the old 'default'
		 * template
		 */
		private function filter_post_content( $post ) {
			global $wpdb;
			$post_content = $post->post_content;

			// Super special <u> tag removal on everything
			$post_content = preg_replace(
				'/\<\/?u(.|\s)*?\>/i',
				'',
				$post_content
			);

			if ( get_page_template_slug( $post->ID ) === '' ) {
				$post_content = strip_tags( $post_content, $this->allowed_tags );
			}

			// Convert post_date and add post_header_publish_date if needed.
			$updated_date  = $post->post_date;
			$publish_date  = get_post_meta( $post->ID, 'updated_date', true );

			$orig_pub_date = isset( $publish_date ) ? date( 'Y-m-d', strtotime( $publish_date ) ) : $updated_date;

			if ( $post->post_content !== $post_content ) {
				$update_status = $wpdb->update( $wpdb->posts, array( 'post_content' => $post_content ), array( 'ID' => $post->ID ), array( 'post_date' => $updated_date ) );

				update_post_meta( $post->ID, 'post_header_publish_date', $orig_pub_date );

				if ( $update_status !== false ) {
					$this->converted++;
					clean_post_cache( $post->ID );
				}
			}
		}
	}

	WP_CLI::add_command( 'today migrate content', 'Today_Migration_Post_Content' );
}

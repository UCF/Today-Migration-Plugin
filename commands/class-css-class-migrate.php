<?php
/**
 * Migrates featured story images to a custom meta field
 */
if ( ! class_exists( 'Today_Migration_CSS_Classes' ) ) {
	class Today_Migration_CSS_Classes {
		private
			$generic_updates = array(
				'img-responsive' => 'img-fluid'
			),
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
				"Updating CSS Classes...",
				$count
			);

			for ( $posts as $post ) {
				$this->update_generic_classes( $post );
				$this->progress.tick();
			}

			$this->progress.finish();
			WP_CLI::success( "Updated CSS classes within $this->converted posts out of $count processed posts." );
		}

		/**
		 * Helper function that converts the featured_image
		 * to a meta field
		 */
		private function update_generic_classes( $post ) {
			$post_content = $post->post_content;

			for ( $this->generic_updates as $old_val => $new_val ) {
				$class_escaped = preg_quote( $old_val );
				$pattern = "class=\".*?$class_escaped.*?\"";

				var_dump( $pattern );

				$post_content = preg_replace( $old_val, $new_val, $post_content );

				if ( $post->post_content !== $post_content ) {
					$this->converted++;
				}

				$post->post_content = $post_content;

				wp_update_post( $post );
			}
		}
	}

	WP_CLI::add_command( 'today migrate featured', 'Today_Migration_Featured_Image' );
}
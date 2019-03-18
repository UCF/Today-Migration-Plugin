<?php
/**
 * Converts CSS Classes in the old theme to newer classes.
 */
if ( ! class_exists( 'Today_Migration_CSS_Classes' ) ) {
	class Today_Migration_CSS_Classes {
		private
			$generic_updates = array(
				'float-left' => '',
				'float-right' => '',
				'alignleft' => 'float-left',
				'alignright' => 'float-right',
				'aligncenter' => 'mx-auto d-block',
				'alignnone' => '',
				'pull-left' => 'float-left',
				'pull-right' => 'float-right',
				'wp-image-' => 'img-fluid wp-image-',
				'img-responsive' => 'img-fluid',
				'img-circle' => 'rounded-circle',

				'external' => '',
				's1' => '',
				'MsoNormal' => '',
				'column' => '',
				'first' => '',
				'border' => '',
				'hiddenSpellError' => '',
				'apple-converted-space' => '',
				'large' => '',
				'dropcap2' => '',
				'main-video' => '',
				'main-interior' => '',
				'container' => '',
				'p1' => '',
				'first-p' => '',
				'bodytext' => '',
				'Body' => '',
				'_2cuy' => '',
				'_3dgx' => '',
				'_2vxa' => '',
				'uBlogsy_post_container' => '',
				'uBlogsy_post' => '',
				'uBlogsy_bottom_border' => '',
				'uBlogsy_post_date' => '',
				'title' => '',
				'story-left' => '',
				'article-content' => '',
				'colum' => '',
				'docs' => '',
				'wp-menu-arrow' => '',
				'interest-add' => '',
				'media' => '',
				'media-element-container' => '',
				'media--view-mode--three_by_four_hundred' => '',
				'watch-the-video' => ''
			),
			$progress,
			$converted;

		/**
		 * Converts CSS Classes in the old theme to newer classes.
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate classes
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

			foreach ( $posts as $post ) {
				$this->update_generic_classes( $post );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Updated CSS classes within $this->converted posts out of $count processed posts." );
		}

		/**
		 * Helper function that converts CSS Classes
		 * to their updated equivilents in the new theme
		 */
		private function update_generic_classes( $post ) {
			$post_content = $post->post_content;

			foreach ( $this->generic_updates as $old_val => $new_val ) {
				$class_escaped = preg_quote( $old_val );
				$replacement   = 'class="$1' . $new_val . '$2"';
				$pattern       = "/class=\"(.*)?$class_escaped(.*)?\"/i";

				$post_content = preg_replace( $old_val, $replacement, $post_content );

				if ( $post->post_content !== $post_content ) {
					$this->converted++;
				}

				$post->post_content = $post_content;

				wp_update_post( $post );
			}
		}
	}

	WP_CLI::add_command( 'today migrate classes', 'Today_Migration_CSS_Classes' );
}

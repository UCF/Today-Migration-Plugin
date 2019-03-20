<?php
/**
 * Converts CSS Classes in the old theme to newer classes.
 */
if ( ! class_exists( 'Today_Migration_CSS_Classes' ) ) {
	class Today_Migration_CSS_Classes {
		private
			$generic_updates = array(
				'media--view-mode--three_by_four_hundred' => '',
				'media-element-container' => '',
				'media' => '',
				'container' => '',
				'column' => '',
				'colum' => '',
				'uBlogsy_post_container' => '',
				'uBlogsy_post_date' => '',
				'uBlogsy_post' => '',
				'uBlogsy_bottom_border' => '',
				'first-p' => '',
				'first' => '',
				'bodytext' => '',
				'Body' => '',

				'_2cuy' => '',
				'_3dgx' => '',
				'_2vxa' => '',
				'apple-converted-space' => '',
				'article-content' => '',
				'border' => '',
				'docs' => '',
				'dropcap2' => '',
				'external' => '',
				'hiddenSpellError' => '',
				'interest-add' => '',
				'large' => '',
				'main-video' => '',
				'main-interior' => '',
				'MsoNormal' => '',
				'p1' => '',
				's1' => '',
				'story-left' => '',
				'title' => '',
				'watch-the-video' => '',
				'wp-menu-arrow' => '',

				// Translations for Athena compatibility
				'float-left' => '',
				'float-right' => '',
				'alignleft' => 'float-left',
				'alignright' => 'float-right',
				'aligncenter' => 'mx-auto d-block',
				'alignnone' => '',
				'pull-left' => 'float-left',
				'pull-right' => 'float-right',
				'img-responsive' => 'img-fluid',
				'img-circle' => 'rounded-circle'
			),
			$regex_updates = array(
				// Translations for Athena compatibility
				'wp-image-(?P<wpimgid>[0-9a-zA-Z]+)' => 'callback_wp_img_class'
			),
			$progress,
			$converted = 0;

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
			// Fetch posts of all post types.
			// Make sure revisions are excluded.
			$posts = get_posts( array(
				'post_type'      => 'any',
				'posts_per_page' => -1,
				'post_status'    => array( 'publish', 'pending', 'draft', 'future', 'private' )
			) );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Updating CSS Classes...",
				$count
			);

			foreach ( $posts as $post ) {
				$this->update_classes( $post );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Updated CSS classes within $this->converted posts out of $count processed posts." );
		}

		/**
		 * Helper function that converts CSS Classes
		 * to their updated equivalents in the new theme
		 */
		private function update_classes( $post ) {
			global $wpdb;
			$post_content = $post->post_content;

			foreach ( $this->generic_updates as $old_val => $new_val ) {
				$post_content = $this->update_class( preg_quote( $old_val ), $new_val, $post_content );
			}

			foreach ( $this->regex_updates as $old_val => $new_val ) {
				// Assume values in $regex_updates are regex-safe
				$post_content = $this->update_class( $old_val, $new_val, $post_content );
			}

			if ( $post->post_content !== $post_content ) {
				$update_status = $wpdb->update( $wpdb->posts, array( 'post_content' => $post_content ), array( 'ID' => $post->ID ) );
				if ( $update_status !== false ) {
					// echo "Updated post $post->ID ('$post->post_title')\n";
					$this->converted++;
					clean_post_cache( $post->ID );
				}
				// else {
				// 	echo "Sadtimes";
				// }
			}
		}

		/**
		 * Performs string replacement of a single class within
		 * all class attributes in the given $string.
		 */
		private function update_class( $old, $new, $string ) {
			// Backreferences: $1=<quote>, $2=<before>, $3=<after>
			// TODO need to fix <quote> backreferences; anything with "q","u","o","t",or "e" in <before> or <after> causes a no-match
			$pattern = "/class=(?P<quote>\'|\")(?P<before>(?:[^(?P=quote)]+[ ])?(?:[ ]*)?)$old(?P<after>(?:[ ]*)?(?:[ ][^(?P=quote)]+)?)(?P=quote)/i";

			if ( method_exists( $this, $new ) ) {
				return preg_replace_callback( $pattern, array( $this, $new ), $string );
			}
			else {
				$replacement = 'class=$1$2' . $new . '$3$1';
				return preg_replace( $pattern, $replacement, $string );
			}
		}

		/**
		 * Custom callback function for updating 'wp-image-*' classes
		 */
		static function callback_wp_img_class( $matches ) {
			return 'class=' . $matches['quote'] . $matches['before'] . 'img-fluid wp-image-' . $matches['wpimgid'] . $matches['after'] . $matches['quote'];
		}
	}

	WP_CLI::add_command( 'today migrate classes', 'Today_Migration_CSS_Classes' );
}

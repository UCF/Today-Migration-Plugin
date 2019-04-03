<?php
/**
 * Removes tags that only have one post assigned to them
 */
if ( ! class_exists( 'Today_Migration_Tag_Removal' ) ) {
	class Today_Migration_Tag_Removal {
		private
			$progress,
			$total_tags = 0,
			$removed = 0;

		/**
		 * Removes tags that only have one or less posts assigned to them
		 *
		 * ## EXAMPLES
		 *
		 * 		wp today migrate tags
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			// Get all tags
			$terms = get_terms( array(
				'taxonomy'   => 'post_tag',
				'hide_empty' => false
			) );

			$this->total_tags = count( $terms );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				'Removing tags with 1 or less posts assigned...',
				$this->total_tags
			);

			foreach ( $terms as $term ) {
				$this->maybe_remove_tag( $term );
				$this->progress->tick();
			}

			$this->progress->finish();
			WP_CLI::success( "Removed $this->removed out of $this->total_tags tags." );
		}

		/**
		 * Removes a tag from WordPress if it's count is less than 2
		 * @author Jim Barnes
		 * @since 1.0.2
		 * @
		 */
		private function maybe_remove_tag( $term ) {
			if ( $term->count < 2 ) {
				wp_delete_term( $term->term_id, 'post_tag' );
				$this->removed++;
			}
		}
	}

	WP_CLI::add_command( 'today migrate tags', 'Today_Migration_Tag_Removal' );
}

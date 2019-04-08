<?php
/**
 * Migrates meta data from old Today-Bootstrap structure
 * to the newer structure
 */
if ( ! class_exists( 'Today_Migration_Sources' ) ) {
	class Today_Migration_Sources {
		private
			$key_mapping = array(
				'news_source_image'  => 'field_5c9d07cbed834' // 'sources_icon',
			),
			$progress;

		/**
		 * Converts the source_icon meta field
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate sources
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			$terms = get_terms( array(
				'taxonomy'   => 'sources',
				'hide_empty' => false
			) );

			$count = count( $terms );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Converting Sources term meta...",
				$count
			);

			foreach ( $terms as $term ) {
				$this->convert_meta_keys( $term );
				$this->progress->tick();
			}

			$this->progress->finish();

			WP_CLI::success( "Converted resource link meta for $count posts." );
		}

		/**
		 * Helper function to convert meta fields to new ACF fields
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_meta_keys( $term ) {
			$term_id = $term->term_id;

			foreach ( $this->key_mapping as $old_key => $new_key ) {
				$value = get_term_meta( $term_id, $old_key, true );
				update_field( $new_key, $value, $term_id );
			}
		}
	}

	WP_CLI::add_command( 'today migrate sources', 'Today_Migration_Sources' );
}

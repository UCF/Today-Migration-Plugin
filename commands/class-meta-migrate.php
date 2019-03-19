<?php
/**
 * Migrates meta data from old Today-Bootstrap structure
 * to the newer structure
 */
if ( ! class_exists( 'Today_Migration_Meta' ) ) {
	class Today_Migration_Meta {
		private
			$key_mapping = array(
				'updated_date'  => 'post_header_updated_date',
				'subtitle'      => 'post_header_subtitle',
				'deck'          => 'post_header_deck',
				'author_title'  => 'post_author_title',
				'author_byline' => 'post_author_byline',
				'author_bio'    => 'post_author_bio',
				'source'        => 'post_source',
				'primary_tag'   => 'post_primary_tag',
				'video_url'     => 'post_header_video_url'
			),
			$progress;

		/**
		 * Converts meta fields
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate meta
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
				"Converting post meta...",
				$count
			);

			foreach ( $posts as $post ) {
				$this->convert_meta_keys( $post );
				$this->convert_meta_values( $post );
				$this->progress->tick();
			}

			$this->progress->finish();

			WP_CLI::success( "Converted post meta for $count posts." );
		}

		/**
		 * Helper function to convert meta fields
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_meta_keys( $post ) {
			$post_id = $post->ID;

			foreach ( $this->key_mapping as $old_key => $new_key ) {
				$value = get_post_meta( $post_id, $old_key, true );
				update_post_meta( $post_id, $new_key, $value );
			}
		}

		/**
		 * Helper function to convert specific meta values
		 * @author Jo Dickson
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_meta_values( $post ) {
			// Update templates for single posts. The old 'featured'
			// template is the new default in the Today Child Theme,
			// and the old default is now `template-twocol.php`.
			$page_template = get_post_meta( $post->ID, '_wp_page_template', true );
			switch ( $page_template ) {
				case 'default':
				case '':
					update_post_meta( $post->ID, '_wp_page_template', 'template-twocol.php' );
					break;
				case 'featured-single-post.php':
					update_post_meta( $post->ID, '_wp_page_template', 'default' );
					break;
				default:
					break;
			}
		}
	}

	WP_CLI::add_command( 'today migrate meta', 'Today_Migration_Meta' );
}

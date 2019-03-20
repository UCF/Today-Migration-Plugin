<?php
/**
 * Migrates meta data from old Today-Bootstrap structure
 * to the newer structure
 */
if ( ! class_exists( 'Today_Migration_Meta' ) ) {
	class Today_Migration_Meta {
		private
			$key_mapping = array(
				'updated_date'  => 'field_5c813a34c81af', // 'post_header_updated_date',
				'subtitle'      => 'field_5c813b75c81b0', // 'post_header_subtitle',
				'deck'          => 'field_5c813eaac81b1', // 'post_header_deck',
				'author_title'  => 'field_5c813ebec81b2', // 'post_author_title',
				'author_byline' => 'field_5c813f0fc81b4', // 'post_author_byline',
				'author_bio'    => 'field_5c813f22c81b5', // 'post_author_bio',
				'source'        => 'field_5c8140e7c81bf', // 'post_source',
				'primary_tag'   => 'field_5c8140a1c81bd', // 'post_primary_tag',
				'video_url'     => 'field_5c814048c81bb', // 'post_header_video_url'
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
		 * Helper function to convert meta fields to new ACF fields
		 * @author Jim Barnes
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_meta_keys( $post ) {
			$post_id = $post->ID;

			foreach ( $this->key_mapping as $old_key => $new_key ) {
				$value = get_post_meta( $post_id, $old_key, true );
				update_field( $new_key, $value, $post_id );
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

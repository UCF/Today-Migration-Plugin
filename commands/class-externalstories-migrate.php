<?php
/**
 * Migrates External Stories from Today-Bootstrap to Resource Links
 */
if ( ! class_exists( 'Today_Migration_ExternalStories' ) ) {
	class Today_Migration_ExternalStories {
		private
			$resource_link_type_name = 'External Story',
			$resource_link_type_term,
			$progress,
			$converted = 0;

		/**
		 * Converts meta fields
		 *
		 * ## EXAMPLES
		 *
		 *     wp today migrate externalstories
		 *
		 * @when after_wp_load
		 */
		public function __invoke( $args ) {
			$posts = get_posts( array(
				'post_type'      => 'externalstory',
				'posts_per_page' => -1,
				'post_status'    => 'any'
			) );

			$count = count( $posts );

			$this->progress = WP_CLI\Utils\make_progress_bar(
				"Converting External Stories to Resource Links...",
				$count
			);

			$this->resource_link_type_term = $this->get_or_create_resource_link_type();

			foreach ( $posts as $post ) {
				$this->convert_external_story( $post );
				$this->progress->tick();
			}

			$this->progress->finish();

			WP_CLI::success( "Converted $this->converted of $count External Stories to Resource Links." );
		}

		/**
		 * Returns or creates a WP_Term object for the
		 * 'External Story' Resource Link Type.
		 *
		 * @author Jo Dickson
		 * @since 1.0.0
		 */
		private function get_or_create_resource_link_type() {
			return get_term_by( 'name', $this->resource_link_type_name, 'resource_link_types' ) ?: wp_insert_term( $this->resource_link_type_name, 'resource_link_types' );
		}

		/**
		 * Helper function to convert a single External Story
		 * to a Resource Link
		 * @author Jo Dickson
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_external_story( $post ) {
			$post_id = $post->ID;

			// Back out early if no URL is set
			$url = get_post_meta( $post_id, 'externalstory_url', true ); // Link to the external article
			if ( ! $url ) { return; }

			$post       = $post->to_array();
			$text       = get_post_meta( $post_id, 'externalstory_text', true ); // Actual link text/external article name
			$desc       = get_post_meta( $post_id, 'externalstory_description', true ); // Short description of the external story
			$source     = wp_get_post_terms( $post_id, 'sources' ); // Source terms already assigned to the external story
			$source_old = get_post_meta( $post_id, 'externalstory_source', true ); // Deprecated 'source' value

			// Set the link text. Back out early if no title text is set.
			$post['post_title'] = $text ?: $post['post_title'];
			if ( ! $post['post_title'] ) { return; }

			$post['guid'] = ''; // force the GUID to be regenerated
			$post['post_type'] = 'ucf_resource_link';

			// Actually convert the post
			$updated = wp_insert_post( $post );

			if ( $post_id === $updated ) {
				// Set Resource Link Type
				wp_set_post_terms( $post_id, array( $this->resource_link_type_term->term_id ), 'resource_link_types' );
				// Set link URL
				update_post_meta( $post_id, 'ucf_resource_link_url', $url );
				// Set link description
				update_field( 'field_5c9d1e7a9ec0c', $desc, $post_id ); // ucf_resource_link_description

				// Set link source
				if ( empty( $source ) ) {
					$source_old_term = get_term_by( 'name', $source_old, 'sources', ARRAY_A ) ?: wp_insert_term( $source_old, 'sources' );
					if ( ! empty( $source_old_term ) && ! is_wp_error( $source_old_term ) ) {
						wp_set_post_terms( $post_id, array( $source_old_term['term_id'] ), 'sources' );
					}
				}

				$this->converted++;
			}
		}
	}

	WP_CLI::add_command( 'today migrate externalstories', 'Today_Migration_ExternalStories' );
}

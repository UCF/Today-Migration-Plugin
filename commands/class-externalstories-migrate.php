<?php
/**
 * Migrates External Stories from Today-Bootstrap to Resource Links
 */
if ( ! class_exists( 'Today_Migration_ExternalStories' ) ) {
	class Today_Migration_ExternalStories {
		private
			$resource_link_type_name = 'External Story',
			$resource_link_type_term_id,
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

			$this->resource_link_type_term_id = $this->get_or_create_resource_link_type();

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
			$term_id = null;
			$term_data = get_term_by( 'name', $this->resource_link_type_name, 'resource_link_types', ARRAY_A );

			if ( ! $term_data ) {
				$term_data = wp_insert_term( $this->resource_link_type_name, 'resource_link_types' );
			}

			if ( isset( $term_data['term_id'] ) ) {
				$term_id = $term_data['term_id'];
			}

			return $term_id;
		}

		/**
		 * Helper function to convert a single External Story
		 * to a Resource Link
		 * @author Jo Dickson
		 * @since 1.0.0
		 * @param WP_Post $post The post object
		 */
		private function convert_external_story( $post ) {
			$post_id_old = $post->ID;

			// Back out early if no URL is set
			$url = get_post_meta( $post_id_old, 'externalstory_url', true ); // Link to the external article
			if ( ! $url ) { return; }

			$post       = $post->to_array();
			$text       = get_post_meta( $post_id_old, 'externalstory_text', true ); // Actual link text/external article name
			$desc       = get_post_meta( $post_id_old, 'externalstory_description', true ); // Short description of the external story
			$source     = wp_get_post_terms( $post_id_old, 'sources', array( 'fields' => 'ids' ) ); // Source terms already assigned to the external story
			$source_old = get_post_meta( $post_id_old, 'externalstory_source', true ); // Deprecated 'source' value

			// Set the link text. Back out early if no title text is set.
			$post['post_title'] = $text ?: $post['post_title'];
			if ( ! $post['post_title'] ) { return; }

			unset( $post['id'] );
			unset( $post['guid'] );
			$post['post_type'] = 'ucf_resource_link';

			// Actually convert the post
			$post_id_new = wp_insert_post( $post );

			if ( $post_id_new && ! is_wp_error( $post_id_new ) ) {
				// Set Resource Link Type
				wp_set_post_terms( $post_id_new, array( $this->resource_link_type_term ), 'resource_link_types' );
				// Set link URL
				update_post_meta( $post_id_new, 'ucf_resource_link_url', $url );
				// Set link description
				update_field( 'field_5c9d1e7a9ec0c', $desc, $post_id_new ); // ucf_resource_link_description

				// Set link source
				if ( empty( $source ) ) {
					$source_old_term = get_term_by( 'name', $source_old, 'sources', ARRAY_A ) ?: wp_insert_term( $source_old, 'sources' );
					if ( ! empty( $source_old_term ) && ! is_wp_error( $source_old_term ) ) {
						wp_set_post_terms( $post_id_new, array( $source_old_term['term_id'] ), 'sources' );
					}
				}
				else {
					wp_set_post_terms( $post_id_new, $source, 'sources' );
				}

				$this->converted++;
			}
		}
	}

	WP_CLI::add_command( 'today migrate externalstories', 'Today_Migration_ExternalStories' );
}

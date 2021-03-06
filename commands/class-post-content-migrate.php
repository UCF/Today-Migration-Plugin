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

			// Remove this action, as it is the automatic publish_date setter.
			if ( function_exists( 'today_post_insert_override' ) ) {
				remove_action( 'wp_insert_post', 'today_post_insert_override', 10 );
			}

			foreach ( $posts as $post ) {
				$this->filter_post_content( $post );
				$this->progress->tick();
			}

			if ( function_exists( 'today_post_insert_override' ) ) {
				add_action( 'wp_insert_post', 'today_post_insert_override', 10, 3 );
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

			// Sanitize <a href> values on everything
			$post_content = preg_replace_callback(
				'/href=(?P<quote>\'|\")(?P<href>(?:[^\'\"])*)(?P=quote)/i',
				array( $this, 'sanitize_url' ),
				$post_content
			);

			if ( get_page_template_slug( $post->ID ) === '' ) {
				$post_content = strip_tags( $post_content, $this->allowed_tags );
			}

			// Convert post_date and add post_header_publish_date if needed.
			$updated_date  = get_post_meta( $post->ID, 'updated_date', true );
			$publish_date  = $post->post_date;

			$updated_date_formatted = ( ! empty( $updated_date ) ) ? date( 'Y-m-d H:i:s', strtotime( $updated_date ) ) : $publish_date;
			$publish_date_formatted = date( 'Y-m-d', strtotime( $publish_date ) );

			if ( $publish_date_formatted ) {
				update_field( 'field_5c813a34c81af', $publish_date_formatted, $post->ID );
			}

			if ( $post->post_content !== $post_content || $updated_date_formatted !== $post->post_date ) {
				$update_status = $wpdb->update( $wpdb->posts, array( 'post_content' => $post_content, 'post_date' => $updated_date_formatted ), array( 'ID' => $post->ID ) );

				if ( $update_status !== false ) {
					$this->converted++;
					clean_post_cache( $post->ID );
				}
			}
		}

		private function sanitize_url( $match ) {
			$link = $match[0];
			$href = isset( $match['href'] ) ? $match['href'] : '';
			if ( ! $href ) return $link;

			$href_clean = $this->strip_postmaster_redirects( $this->strip_outlook_safelinks( $href ) );

			if ( $href_clean !== $href ) {
				$link = str_replace( $href, $href_clean, $link );
			}
			return $link;
		}

		/**
		 * Generic function that replaces a URL with a query param value
		 * based on specific search criteria in the URL
		 */
		private function strip_link_prefix( $url, $search_regex, $query_param ) {
			if ( preg_match( $search_regex, $url ) ) {
				$query_params = array();
				parse_str( parse_url( $url, PHP_URL_QUERY ), $query_params );
				if ( isset( $query_params[$query_param] ) ) {
					$url = urldecode( $query_params[$query_param] );
				}
			}

			return $url;
		}

		/**
		 * Replaces Outlook safelink URLs with the actual redirected URL
		 */
		private function strip_outlook_safelinks( $url ) {
			return $this->strip_link_prefix(
				$url,
				'/^https\:\/\/(.*\.)safelinks\.protection\.outlook\.com\//i',
				'url'
			);
		}

		/**
		 * Replaces Postmaster redirects with the actual redirected URL
		 */
		private function strip_postmaster_redirects( $url ) {
			return $this->strip_link_prefix(
				$url,
				'/^https\:\/\/postmaster\.smca\.ucf\.edu\//i',
				'url'
			);
		}
	}

	WP_CLI::add_command( 'today migrate content', 'Today_Migration_Post_Content' );
}

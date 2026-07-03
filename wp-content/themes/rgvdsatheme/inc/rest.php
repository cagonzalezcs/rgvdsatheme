<?php
/**
 * REST domain: the first-party read API (`/wp-json/rgvdsa/v1`).
 *
 * GET-only, public, publish-only. Handlers reuse the domain serializers and
 * the shared query builders so REST shapes cannot drift from the embedded
 * Twig contexts. Payloads are transient-cached (content-version
 * invalidation); anonymous responses get Cache-Control + ETag/304.
 *
 * Routes:
 * - GET /posts            → { posts: BlogPost[], page, perPage, total, totalPages }
 * - GET /posts/{slug}     → SinglePostData + { readNext: BlogPost[] }
 * - GET /events           → { events: ChapterEvent[], categories: EventCategory[] }
 * - GET /categories       → { categories: EventCategory[] }
 *
 * Versioning policy: additive changes stay on /v1; renames/removals go to /v2.
 */

add_action( 'rest_api_init', 'rgvdsa_rest_register_routes' );

function rgvdsa_rest_register_routes() {
	register_rest_route(
		'rgvdsa/v1',
		'/posts',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rgvdsa_rest_posts',
			'permission_callback' => '__return_true',
			'args'                => array(
				'page'     => array(
					'type'    => 'integer',
					'default' => 1,
					'minimum' => 1,
				),
				'per_page' => array(
					'type'    => 'integer',
					'default' => 24,
					'minimum' => 1,
					'maximum' => 50,
				),
				'category' => array(
					'type' => 'string',
					'enum' => array_keys( rgvdsa_category_registry() ),
				),
				's'        => array(
					'type'              => 'string',
					'maxLength'         => 100,
					'sanitize_callback' => 'sanitize_text_field',
				),
				'lang'     => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);

	register_rest_route(
		'rgvdsa/v1',
		'/posts/(?P<slug>[a-z0-9-]+)',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rgvdsa_rest_single_post',
			'permission_callback' => '__return_true',
			'args'                => array(
				'slug' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_title',
				),
				'lang' => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);

	register_rest_route(
		'rgvdsa/v1',
		'/events',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rgvdsa_rest_events',
			'permission_callback' => '__return_true',
			'args'                => array(
				'after'  => array(
					'type'              => 'string',
					'validate_callback' => 'rgvdsa_rest_validate_date',
				),
				'before' => array(
					'type'              => 'string',
					'validate_callback' => 'rgvdsa_rest_validate_date',
				),
				'lang'   => array(
					'type'              => 'string',
					'sanitize_callback' => 'sanitize_key',
				),
			),
		)
	);

	register_rest_route(
		'rgvdsa/v1',
		'/categories',
		array(
			'methods'             => WP_REST_Server::READABLE,
			'callback'            => 'rgvdsa_rest_categories',
			'permission_callback' => '__return_true',
		)
	);
}

/**
 * `Y-m-d` arg validator (core handles the 400 envelope).
 */
function rgvdsa_rest_validate_date( $value ) {
	if ( ! is_string( $value ) || ! preg_match( '/^(\d{4})-(\d{2})-(\d{2})$/', $value, $m ) ) {
		return false;
	}

	return checkdate( (int) $m[2], (int) $m[3], (int) $m[1] );
}

/**
 * Resolve the request language for the first-party API.
 *
 * The islands send the page language as `?lang=` (Polylang does not resolve the
 * language of a bare `/wp-json/rgvdsa/v1` request on its own). A valid slug is
 * honored; anything else falls back to the site default language so a param-less
 * hit behaves as the English site. Returns '' only when Polylang is inactive
 * (queries then run unfiltered across all languages). The value is threaded into
 * the shared query builders and the transient cache keys, so each language keeps
 * its own cached payload.
 *
 * @param WP_REST_Request $request Current request.
 * @return string Language slug, or '' when Polylang is unavailable.
 */
function rgvdsa_rest_resolve_lang( WP_REST_Request $request ) {
	if ( ! function_exists( 'pll_languages_list' ) ) {
		return '';
	}

	$requested = sanitize_key( (string) ( $request['lang'] ?? '' ) );
	if ( '' !== $requested && in_array( $requested, (array) pll_languages_list(), true ) ) {
		return $requested;
	}

	return function_exists( 'pll_default_language' ) ? (string) pll_default_language() : '';
}

/* -------------------------------------------------------------------------
 * Handlers.
 * ---------------------------------------------------------------------- */

/**
 * GET /posts — paginated envelope over the shared blog query (same code
 * path as the Twig archive context, so shapes cannot drift).
 */
function rgvdsa_rest_posts( WP_REST_Request $request ) {
	$page     = (int) $request['page'];
	$per_page = (int) $request['per_page'];
	$category = (string) ( $request['category'] ?? '' );
	$search   = trim( (string) ( $request['s'] ?? '' ) );
	$lang     = rgvdsa_rest_resolve_lang( $request );

	$payload = rgvdsa_cache_remember(
		'rest_posts_' . md5( wp_json_encode( array( $lang, $page, $per_page, $category, $search ) ) ),
		static function () use ( $lang, $page, $per_page, $category, $search ) {
			$args = array(
				'posts_per_page' => $per_page,
				'paged'          => $page,
				'lang'           => $lang,
			);
			if ( '' !== $category ) {
				$args['category_name'] = $category;
			}
			if ( '' !== $search ) {
				$args['s'] = $search;
			}

			$query = rgvdsa_blog_posts_query( $args );

			return array(
				'posts'      => array_map( 'rgvdsa_post_to_blog_post', $query->posts ),
				'page'       => $page,
				'perPage'    => $per_page,
				'total'      => (int) $query->found_posts,
				'totalPages' => (int) $query->max_num_pages,
			);
		}
	);

	return rest_ensure_response( $payload );
}

/**
 * GET /posts/{slug} — SinglePostData plus the Read Next pool.
 */
function rgvdsa_rest_single_post( WP_REST_Request $request ) {
	$slug = (string) $request['slug'];
	$lang = rgvdsa_rest_resolve_lang( $request );

	$payload = rgvdsa_cache_remember(
		'rest_single_' . md5( $lang . '|' . $slug ),
		static function () use ( $lang, $slug ) {
			// Language-scoped slug lookup: a translated post can share its
			// slug, so resolve within the requested language.
			$found = rgvdsa_blog_posts_query(
				array(
					'name'           => $slug,
					'posts_per_page' => 1,
					'lang'           => $lang,
				)
			)->posts;
			if ( ! $found ) {
				return array();
			}

			$post = $found[0];
			$pool = rgvdsa_blog_posts_query(
				array(
					'posts_per_page' => 12,
					'post__not_in'   => array( (int) $post->ID ),
					'lang'           => $lang,
				)
			);

			$payload             = rgvdsa_post_to_single( $post );
			$payload['readNext'] = array_map( 'rgvdsa_post_to_blog_post', $pool->posts );

			return $payload;
		}
	);

	if ( ! $payload ) {
		return new WP_Error(
			'rgvdsa_post_not_found',
			'No published post matches that slug.',
			array( 'status' => 404 )
		);
	}

	return rest_ensure_response( $payload );
}

/**
 * GET /events — the calendar window (defaults −1 month → +12 months).
 */
function rgvdsa_rest_events( WP_REST_Request $request ) {
	$lang   = rgvdsa_rest_resolve_lang( $request );
	$now    = new DateTimeImmutable( 'now', rgvdsa_events_timezone() );
	$after  = (string) ( $request['after'] ?? $now->modify( '-1 month' )->format( 'Y-m-d' ) );
	$before = (string) ( $request['before'] ?? $now->modify( '+12 months' )->format( 'Y-m-d' ) );

	$payload = rgvdsa_cache_remember(
		'rest_events_' . md5( $lang . '|' . $after . '|' . $before ),
		static function () use ( $lang, $after, $before ) {
			$posts = rgvdsa_events_query(
				array(
					'lang'       => $lang,
					'meta_query' => array(
						array(
							'key'     => 'start_datetime',
							'value'   => array( $after . ' 00:00:00', $before . ' 23:59:59' ),
							'compare' => 'BETWEEN',
							'type'    => 'DATETIME',
						),
					),
				)
			);

			return array(
				'events'     => array_values( array_filter( array_map( 'rgvdsa_event_to_chapter_event', $posts ) ) ),
				'categories' => rgvdsa_event_categories(),
			);
		}
	);

	return rest_ensure_response( $payload );
}

/**
 * GET /categories — the six canonical blog categories.
 */
function rgvdsa_rest_categories() {
	return rest_ensure_response(
		array( 'categories' => rgvdsa_cache_remember( 'rest_categories', 'rgvdsa_post_categories' ) )
	);
}

/* -------------------------------------------------------------------------
 * HTTP caching — namespace-scoped headers on the dispatched response.
 * ---------------------------------------------------------------------- */

add_filter( 'rest_post_dispatch', 'rgvdsa_rest_cache_headers', 10, 3 );

function rgvdsa_rest_cache_headers( $result, $server, $request ) {
	if ( 0 !== strpos( $request->get_route(), '/rgvdsa/v1' ) ) {
		return $result;
	}
	if ( ! ( $result instanceof WP_REST_Response ) || $result->get_status() >= 400 ) {
		return $result;
	}

	// Editors always see fresh data.
	if ( is_user_logged_in() ) {
		$result->header( 'Cache-Control', 'no-store' );

		return $result;
	}

	$etag = '"' . md5( (string) wp_json_encode( $result->get_data() ) ) . '"';
	$result->header( 'Cache-Control', 'public, max-age=300, stale-while-revalidate=3600' );
	$result->header( 'ETag', $etag );

	if ( trim( (string) $request->get_header( 'if_none_match' ) ) === $etag ) {
		$result->set_status( 304 );
		$result->set_data( null );
	}

	return $result;
}

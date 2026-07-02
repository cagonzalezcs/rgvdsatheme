<?php
/**
 * SEO head output: meta description, canonical, robots, Open Graph /
 * Twitter cards, and JSON-LD structured data.
 *
 * Owns: everything <head>-facing that search engines and link scrapers
 * read. Hand-rolled (no SEO plugin) — every copy/image source is already
 * serialized first-party by the blog/events/options domains.
 *
 * Public contract (tests call these):
 * - rgvdsa_seo_description(): string — per-surface description ladder.
 * - rgvdsa_seo_canonical(): string — canonical URL ('' when none).
 * - rgvdsa_seo_is_noindex(): bool — thin surfaces get noindex,follow.
 * - rgvdsa_seo_image(): array — share-image ladder { src, width?, height?,
 *   alt?, large } where `large` marks a per-content image (drives the
 *   summary_large_image card).
 * - rgvdsa_seo_json_ld(): array — @graph with Organization (+ Article on
 *   posts, + Event on event permalinks).
 */

add_action( 'wp_head', 'rgvdsa_seo_head', 5 );

// Core emits its own singular canonical at wp_head 10 — ours owns the tag.
remove_action( 'wp_head', 'rel_canonical' );

// Robots go through core's wp_robots so the page gets ONE merged meta
// (core already contributes max-image-preview:large and search noindex).
add_filter( 'wp_robots', 'rgvdsa_seo_robots' );

/**
 * noindex,follow directives for thin surfaces (overrides core's
 * search-results nofollow — we want crawlers to follow through to posts).
 *
 * @param array $robots wp_robots directives.
 * @return array
 */
function rgvdsa_seo_robots( $robots ) {
	if ( rgvdsa_seo_is_noindex() ) {
		$robots['noindex'] = true;
		$robots['follow']  = true;
		unset( $robots['nofollow'] );
	}

	return $robots;
}

/**
 * Emit the full SEO head block. Hooked once at wp_head 5, reads only from
 * the main query so output is deterministic per URL.
 */
function rgvdsa_seo_head() {
	$description = rgvdsa_seo_description();
	$canonical   = rgvdsa_seo_canonical();
	$image       = rgvdsa_seo_image();

	if ( '' !== $description ) {
		printf( '<meta name="description" content="%s">' . "\n", esc_attr( $description ) );
	}

	if ( '' !== $canonical ) {
		printf( '<link rel="canonical" href="%s">' . "\n", esc_url( $canonical ) );
	}

	$title = is_singular() && ! is_front_page()
		? html_entity_decode( get_the_title( get_queried_object_id() ), ENT_QUOTES, 'UTF-8' )
		: get_bloginfo( 'name' );

	$og = array(
		'og:site_name'   => get_bloginfo( 'name' ),
		'og:type'        => is_singular( 'post' ) ? 'article' : 'website',
		'og:title'       => $title,
		'og:description' => $description,
		'og:url'         => $canonical ?: home_url( '/' ),
		'og:image'       => $image['src'],
	);
	if ( ! empty( $image['width'] ) ) {
		$og['og:image:width'] = (string) $image['width'];
	}
	if ( ! empty( $image['height'] ) ) {
		$og['og:image:height'] = (string) $image['height'];
	}
	if ( ! empty( $image['alt'] ) ) {
		$og['og:image:alt'] = $image['alt'];
	}

	foreach ( $og as $property => $content ) {
		if ( '' === (string) $content ) {
			continue;
		}
		$escaped = in_array( $property, array( 'og:url', 'og:image' ), true ) ? esc_url( $content ) : esc_attr( $content );
		printf( '<meta property="%s" content="%s">' . "\n", esc_attr( $property ), $escaped );
	}

	// Per-content image → big card; chapter default / logo fallback → summary.
	$card = ! empty( $image['large'] ) ? 'summary_large_image' : 'summary';
	printf( '<meta name="twitter:card" content="%s">' . "\n", esc_attr( $card ) );

	$json = wp_json_encode( rgvdsa_seo_json_ld(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE );
	if ( $json ) {
		echo '<script type="application/ld+json">' . $json . '</script>' . "\n";
	}
}

/**
 * Plain-text + ~155-char word-boundary trim for meta/OG copy.
 *
 * @param mixed $text  Raw copy (may contain markup).
 * @param int   $limit Character budget.
 * @return string
 */
function rgvdsa_seo_plain_trim( $text, $limit = 155 ) {
	$text = function_exists( 'rgvdsa_blog_kses_plain' )
		? rgvdsa_blog_kses_plain( $text )
		: trim( wp_strip_all_tags( (string) $text ) );
	$text = trim( (string) preg_replace( '/\s+/u', ' ', $text ) );

	if ( mb_strlen( $text ) <= $limit ) {
		return $text;
	}

	$cut   = mb_substr( $text, 0, $limit );
	$space = mb_strrpos( $cut, ' ' );
	if ( false !== $space && $space > 0 ) {
		$cut = mb_substr( $cut, 0, $space );
	}

	return rtrim( $cut, " \t.,;:—–-" ) . '…';
}

/**
 * Per-surface description ladder (design D2):
 * post dek → excerpt; page seo_description → lede → tagline; event
 * content; front hero lede → tagline; posts page uses the page ladder on
 * the page_for_posts page.
 *
 * @return string
 */
function rgvdsa_seo_description() {
	$tagline = trim( (string) get_bloginfo( 'description', 'display' ) );
	// Tagline unset → hero-lede design copy, so the ladder never bottoms
	// out empty (same fixture-fallback philosophy as the templates).
	if ( '' === $tagline && function_exists( 'rgvdsa_front_hero' ) ) {
		$hero    = rgvdsa_front_hero( (int) get_option( 'page_on_front' ) );
		$tagline = (string) ( $hero['lede'] ?? '' );
	}

	if ( is_front_page() ) {
		$hero = function_exists( 'rgvdsa_front_hero' )
			? rgvdsa_front_hero( (int) get_option( 'page_on_front' ) )
			: array();
		$lede = trim( (string) ( $hero['lede'] ?? '' ) );

		return rgvdsa_seo_plain_trim( '' !== $lede ? $lede : $tagline );
	}

	if ( is_singular( 'post' ) ) {
		$post_id = get_queried_object_id();
		$dek     = trim( (string) rgvdsa_blog_field( 'dek', $post_id ) );
		if ( '' !== $dek ) {
			return rgvdsa_seo_plain_trim( $dek );
		}

		return rgvdsa_seo_plain_trim( get_the_excerpt( $post_id ) );
	}

	if ( is_singular( 'event' ) ) {
		$post = get_queried_object();
		$desc = rgvdsa_seo_plain_trim( $post ? $post->post_content : '' );

		return '' !== $desc ? $desc : rgvdsa_seo_plain_trim( $tagline );
	}

	// Posts page and interior pages share the page ladder.
	$page_id = 0;
	if ( is_home() ) {
		$page_id = (int) get_option( 'page_for_posts' );
	} elseif ( is_page() ) {
		$page_id = get_queried_object_id();
	}

	if ( $page_id && function_exists( 'get_field' ) ) {
		foreach ( array( 'seo_description', 'lede' ) as $field ) {
			$value = get_field( $field, $page_id );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				return rgvdsa_seo_plain_trim( $value );
			}
		}
	}

	return rgvdsa_seo_plain_trim( $tagline );
}

/**
 * Island filter params present on the request? (?s= / ?category= / ?paged=
 * are client filter state on the posts page — inc/blog.php reads the same
 * params. Server-paged /page/N/ does NOT hit this.)
 *
 * @return bool
 */
function rgvdsa_seo_has_filter_params() {
	foreach ( array( 's', 'category', 'paged' ) as $param ) {
		if ( isset( $_GET[ $param ] ) && '' !== $_GET[ $param ] ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			return true;
		}
	}

	return false;
}

/**
 * noindex,follow surfaces (design D3): search results, ?s=/?category=
 * filtered archive states, date/author archives, 404.
 *
 * @return bool
 */
function rgvdsa_seo_is_noindex() {
	if ( is_search() || is_404() || is_date() || is_author() ) {
		return true;
	}

	if ( ( is_home() || is_archive() ) && ( isset( $_GET['s'] ) || isset( $_GET['category'] ) ) ) { // phpcs:ignore WordPress.Security.NonceVerification.Recommended
		return true;
	}

	return false;
}

/**
 * Canonical URL (design D3). Filtered states (?s=/?category=/?paged=)
 * canonicalize to the clean posts-page URL; server-paged archives keep
 * their own /page/N/ canonical. '' when there is no meaningful canonical
 * (404).
 *
 * @return string
 */
function rgvdsa_seo_canonical() {
	if ( is_404() ) {
		return '';
	}

	$posts_page = (int) get_option( 'page_for_posts' );
	$blog_url   = $posts_page ? (string) get_permalink( $posts_page ) : home_url( '/' );

	if ( is_search() ) {
		return $blog_url;
	}

	if ( ( is_home() || is_archive() ) && rgvdsa_seo_has_filter_params() ) {
		return $blog_url;
	}

	if ( is_front_page() ) {
		$paged = max( 1, (int) get_query_var( 'paged' ) );

		return $paged > 1 ? (string) get_pagenum_link( $paged, false ) : home_url( '/' );
	}

	if ( is_singular() ) {
		return (string) get_permalink( get_queried_object_id() );
	}

	if ( is_home() || is_archive() ) {
		$paged = max( 1, (int) get_query_var( 'paged' ) );
		if ( $paged > 1 ) {
			return (string) get_pagenum_link( $paged, false );
		}

		return is_home() ? $blog_url : (string) get_pagenum_link( 1, false );
	}

	return home_url( '/' );
}

/**
 * Share-image ladder (design D4): featured image (large) → Chapter
 * Settings default_share_image → theme logo. `large` is true only for a
 * per-content image (spec: fallback images card as `summary`).
 *
 * @return array{src:string,width?:int,height?:int,alt?:string,large:bool}
 */
function rgvdsa_seo_image() {
	if ( is_singular() ) {
		$thumb_id = (int) get_post_thumbnail_id( get_queried_object_id() );
		if ( $thumb_id ) {
			$image = rgvdsa_seo_attachment_image( $thumb_id );
			if ( $image ) {
				$image['large'] = true;

				return $image;
			}
		}
	}

	if ( function_exists( 'get_field' ) ) {
		$default    = get_field( 'default_share_image', 'option' );
		$default_id = is_array( $default ) ? (int) ( $default['ID'] ?? 0 ) : (int) $default;
		if ( $default_id ) {
			$image = rgvdsa_seo_attachment_image( $default_id );
			if ( $image ) {
				$image['large'] = false;

				return $image;
			}
		}
	}

	return array(
		'src'   => get_theme_file_uri( 'static/images/logos/logo-lg.png' ),
		'large' => false,
	);
}

/**
 * Attachment → { src, width?, height?, alt? } at the `large` size.
 *
 * @param int $attachment_id Attachment ID.
 * @return array|null Null when the attachment has no image source.
 */
function rgvdsa_seo_attachment_image( $attachment_id ) {
	$src = wp_get_attachment_image_src( $attachment_id, 'large' );
	if ( ! $src || empty( $src[0] ) ) {
		return null;
	}

	$image = array( 'src' => (string) $src[0] );
	if ( ! empty( $src[1] ) ) {
		$image['width'] = (int) $src[1];
	}
	if ( ! empty( $src[2] ) ) {
		$image['height'] = (int) $src[2];
	}

	$alt = (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true );
	if ( '' !== $alt ) {
		$image['alt'] = $alt;
	}

	return $image;
}

/**
 * JSON-LD @graph (design D5): Organization site-wide, Article on posts,
 * Event on event permalinks. One script per page.
 *
 * @return array
 */
function rgvdsa_seo_json_ld() {
	$org_id = home_url( '/#organization' );

	$organization = array(
		'@type' => 'Organization',
		'@id'   => $org_id,
		'name'  => get_bloginfo( 'name' ),
		'url'   => home_url( '/' ),
		'logo'  => get_theme_file_uri( 'static/images/logos/logo-lg.png' ),
	);
	$same_as      = rgvdsa_seo_same_as();
	if ( $same_as ) {
		$organization['sameAs'] = $same_as;
	}

	$graph = array( $organization );

	if ( is_singular( 'post' ) ) {
		$article = rgvdsa_seo_article_schema( get_queried_object(), $org_id );
		if ( $article ) {
			$graph[] = $article;
		}
	} elseif ( is_singular( 'event' ) ) {
		$event = rgvdsa_seo_event_schema( get_queried_object(), $org_id );
		if ( $event ) {
			$graph[] = $event;
		}
	}

	return array(
		'@context' => 'https://schema.org',
		'@graph'   => $graph,
	);
}

/**
 * Organization sameAs profiles — same option fields + defaults as the
 * StarterSite `chapter.socials` context.
 *
 * @return string[]
 */
function rgvdsa_seo_same_as() {
	$defaults = array(
		'facebook_url'  => 'https://facebook.com/dsargv',
		'instagram_url' => 'https://instagram.com/dsa_rgv',
		'twitter_url'   => 'https://twitter.com/dsa_rgv',
	);

	$urls = array();
	foreach ( $defaults as $field => $fallback ) {
		$value  = function_exists( 'get_field' ) ? get_field( $field, 'option' ) : null;
		$urls[] = ( is_string( $value ) && '' !== trim( $value ) ) ? trim( $value ) : $fallback;
	}

	return array_values( array_unique( array_filter( $urls ) ) );
}

/**
 * Article schema for a post (byline mode → Person vs committee
 * Organization, mirroring rgvdsa_post_to_single()).
 *
 * @param WP_Post|null $post   Queried post.
 * @param string       $org_id Organization @id reference.
 * @return array|null
 */
function rgvdsa_seo_article_schema( $post, $org_id ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}

	$committee = trim( (string) rgvdsa_blog_field( 'committee', $post->ID ) );
	if ( 'committee' === rgvdsa_blog_field( 'byline_mode', $post->ID ) && '' !== $committee ) {
		$author = array(
			'@type' => 'Organization',
			'name'  => $committee,
		);
	} else {
		$author = array(
			'@type' => 'Person',
			'name'  => get_the_author_meta( 'display_name', (int) $post->post_author ),
		);
	}

	$article = array(
		'@type'            => 'Article',
		'headline'         => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
		'description'      => rgvdsa_seo_description(),
		'datePublished'    => get_the_date( 'c', $post ),
		'dateModified'     => get_the_modified_date( 'c', $post ),
		'mainEntityOfPage' => get_permalink( $post ),
		'author'           => $author,
		'publisher'        => array( '@id' => $org_id ),
	);

	$thumb = get_the_post_thumbnail_url( $post, 'large' );
	if ( $thumb ) {
		$article['image'] = $thumb;
	}

	return $article;
}

/**
 * Event schema for an event permalink — same ACF fields as the ICS feed
 * (start/end in chapter tz, venue/city Place, rsvp_url offer).
 *
 * @param WP_Post|null $post   Queried event.
 * @param string       $org_id Organization @id reference.
 * @return array|null Null when the event has no parseable start.
 */
function rgvdsa_seo_event_schema( $post, $org_id ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return null;
	}

	$start = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $post->ID, 'start_datetime' ) );
	if ( ! $start ) {
		return null;
	}
	$end = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $post->ID, 'end_datetime' ) );

	$event = array(
		'@type'     => 'Event',
		'name'      => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
		'startDate' => $start->format( 'c' ),
		'url'       => get_permalink( $post ),
		'organizer' => array( '@id' => $org_id ),
	);
	if ( $end ) {
		$event['endDate'] = $end->format( 'c' );
	}

	$desc = trim( wp_strip_all_tags( $post->post_content ) );
	if ( '' !== $desc ) {
		$event['description'] = $desc;
	}

	$venue = trim( (string) rgvdsa_events_get_field( $post->ID, 'venue' ) );
	$city  = trim( (string) rgvdsa_events_get_field( $post->ID, 'city' ) );
	if ( $venue || $city ) {
		$place = array(
			'@type' => 'Place',
			'name'  => $venue ?: $city,
		);
		if ( $city ) {
			$place['address'] = array(
				'@type'           => 'PostalAddress',
				'addressLocality' => $city,
			);
		}
		$event['location'] = $place;
	}

	$rsvp = trim( (string) rgvdsa_events_get_field( $post->ID, 'rsvp_url' ) );
	if ( $rsvp ) {
		$event['offers'] = array(
			'@type' => 'Offer',
			'url'   => $rsvp,
		);
	}

	$thumb = get_the_post_thumbnail_url( $post, 'large' );
	if ( $thumb ) {
		$event['image'] = $thumb;
	}

	return $event;
}

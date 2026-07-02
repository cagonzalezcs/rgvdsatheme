<?php
/**
 * Transient cache helper with content-version invalidation.
 *
 * Keys embed `rgvdsa_content_ver` (an option bumped on every content
 * write), so a version bump is the real invalidation and the TTL is only
 * a backstop. Chapter-scale: one global version beats granular purging.
 *
 * Public contract (other domains call these):
 * - rgvdsa_cache_remember( $key, $cb, $ttl = 900 ): mixed — transient-backed
 *   memoization of $cb(), invalidated by content-version bumps.
 */

/**
 * Current content version (bumped on post/event/term/options saves).
 *
 * @return int
 */
function rgvdsa_content_version() {
	return max( 1, (int) get_option( 'rgvdsa_content_ver', 1 ) );
}

/**
 * Transient-backed memoization keyed `rgvdsa_{$key}_{ver}`.
 *
 * @param string   $key Cache key fragment (unique per payload).
 * @param callable $cb  Produces the value on miss. Must not return false —
 *                      get_transient() can't distinguish it from a miss.
 * @param int      $ttl Backstop TTL in seconds (default 900).
 * @return mixed
 */
function rgvdsa_cache_remember( $key, $cb, $ttl = 900 ) {
	$transient = 'rgvdsa_' . $key . '_' . rgvdsa_content_version();

	$cached = get_transient( $transient );
	if ( false !== $cached ) {
		return $cached;
	}

	$value = $cb();
	set_transient( $transient, $value, $ttl );

	return $value;
}

/* -------------------------------------------------------------------------
 * Version bumps — every content write invalidates all rgvdsa transients.
 * ---------------------------------------------------------------------- */

function rgvdsa_cache_bump_version() {
	update_option( 'rgvdsa_content_ver', rgvdsa_content_version() + 1 );
}

add_action( 'save_post_post', 'rgvdsa_cache_bump_version' );
add_action( 'save_post_event', 'rgvdsa_cache_bump_version' );
add_action( 'deleted_post', 'rgvdsa_cache_bump_version' );

// Term edits — only the two canonical-category taxonomies matter.
add_action( 'edited_term', 'rgvdsa_cache_bump_on_term_edit', 10, 3 );

function rgvdsa_cache_bump_on_term_edit( $term_id, $tt_id, $taxonomy ) {
	if ( in_array( $taxonomy, array( 'category', 'event_category' ), true ) ) {
		rgvdsa_cache_bump_version();
	}
}

// Chapter Settings (ACF options page) saves.
add_action( 'acf/save_post', 'rgvdsa_cache_bump_on_options_save' );

function rgvdsa_cache_bump_on_options_save( $post_id ) {
	if ( 'options' === $post_id || 'option' === $post_id ) {
		rgvdsa_cache_bump_version();
	}
}

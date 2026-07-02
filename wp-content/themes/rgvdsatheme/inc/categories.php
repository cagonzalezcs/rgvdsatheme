<?php
/**
 * Category registry: the single source of truth for the six canonical
 * category slugs, labels, and colors.
 *
 * Reads `categories.json` (theme root) — the same file `src/lib/events.ts`
 * imports and the vitest drift test checks the Tailwind `--color-cat-*`
 * tokens against. Slugs are load-bearing (URLs + Vue types); a
 * `wp_update_term_data` guard forces canonical slugs back on rename.
 *
 * Public contract (other domains call these):
 * - rgvdsa_category_registry(): array<slug, array{label, color}> — JSON
 *   defaults, static-cached.
 * - rgvdsa_categories( $taxonomy ): array — [{ id, label, color }] with the
 *   WP term name and ACF term-meta `color` merged over the defaults.
 */

/**
 * Canonical category registry from categories.json, keyed by slug.
 *
 * @return array<string, array{label: string, color: string}>
 */
function rgvdsa_category_registry() {
	static $registry = null;
	if ( null !== $registry ) {
		return $registry;
	}

	$registry = array();
	$raw      = file_get_contents( dirname( __DIR__ ) . '/categories.json' );
	$entries  = $raw ? json_decode( $raw, true ) : null;

	if ( is_array( $entries ) ) {
		foreach ( $entries as $entry ) {
			if ( ! is_array( $entry ) || empty( $entry['id'] ) ) {
				continue;
			}
			$registry[ (string) $entry['id'] ] = array(
				'label' => (string) $entry['label'],
				'color' => (string) $entry['color'],
			);
		}
	}

	return $registry;
}

/**
 * The six canonical categories for a taxonomy, in registry order.
 * Term name and ACF term-meta `color` win when the term exists; the
 * registry is the fallback.
 *
 * @param string $taxonomy 'category' or 'event_category'.
 * @return array [{ id: slug, label: string, color: hex }]
 */
function rgvdsa_categories( $taxonomy = 'category' ) {
	$by_slug = array();
	$terms   = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $term ) {
			$by_slug[ $term->slug ] = $term;
		}
	}

	$categories = array();
	foreach ( rgvdsa_category_registry() as $slug => $fallback ) {
		$term  = isset( $by_slug[ $slug ] ) ? $by_slug[ $slug ] : null;
		$color = $fallback['color'];

		if ( $term && function_exists( 'get_field' ) ) {
			$term_color = get_field( 'color', $taxonomy . '_' . $term->term_id );
			if ( is_string( $term_color ) && '' !== $term_color ) {
				$color = $term_color;
			}
		}

		$categories[] = array(
			'id'    => $slug,
			'label' => $term ? $term->name : $fallback['label'],
			'color' => $color,
		);
	}

	return $categories;
}

/* -------------------------------------------------------------------------
 * Canonical slug protection.
 * ---------------------------------------------------------------------- */

add_filter( 'wp_update_term_data', 'rgvdsa_guard_canonical_term_slugs', 10, 3 );

/**
 * Force canonical slugs back on rename: if a `category`/`event_category`
 * term currently has a canonical slug, keep it — the slugs appear in URLs
 * and the Vue category union type, so a rename silently breaks routing
 * and degrades posts to the "chapter" fallback.
 *
 * @param array  $data     Term data to be updated.
 * @param int    $term_id  Term ID.
 * @param string $taxonomy Taxonomy slug.
 * @return array Term data with the canonical slug restored when needed.
 */
function rgvdsa_guard_canonical_term_slugs( $data, $term_id, $taxonomy ) {
	if ( ! in_array( $taxonomy, array( 'category', 'event_category' ), true ) ) {
		return $data;
	}

	$term = get_term( $term_id, $taxonomy );
	if ( ! $term instanceof WP_Term ) {
		return $data;
	}

	if ( array_key_exists( $term->slug, rgvdsa_category_registry() ) ) {
		$data['slug'] = $term->slug;
	}

	return $data;
}

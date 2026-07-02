<?php
/**
 * Translation gate: decides where GTranslate-driven EN/ES translation is
 * active.
 *
 * Owns: the single predicate templates and client scripts consult. When it
 * says yes, base.twig bootstraps the GTranslate plugin (hidden gt-link →
 * plugin base.js + settings blob) and the header toggle translates in
 * place; when it says no, the page ships zero gtranslate assets — a stale
 * googtrans cookie is inert without Google's element.js, so asset absence
 * IS the enforcement.
 *
 * Public contract:
 * - rgvdsa_translation_active(): bool — filterable via
 *   `rgvdsa/translation/active`.
 * - Timber context: `translation.active`.
 */

/**
 * Whether machine translation is active for the current request.
 *
 * @return bool
 */
function rgvdsa_translation_active() {
	// is_front_page() is the deliberate home-only restriction: lift it here
	// (or via the filter below) when the in-company team ships inner-page
	// translations. See openspec translations-layer design D6.
	$active = ! is_admin()
		&& function_exists( 'get_field' )
		&& (bool) get_field( 'es_enabled', 'option' )
		&& is_front_page();

	return (bool) apply_filters( 'rgvdsa/translation/active', $active );
}

add_filter( 'timber/context', 'rgvdsa_translation_context' );

/**
 * Expose the gate to Twig as `translation.active`.
 *
 * @param array $context Timber context.
 * @return array
 */
function rgvdsa_translation_context( $context ) {
	$context['translation'] = array(
		'active' => rgvdsa_translation_active(),
	);

	return $context;
}

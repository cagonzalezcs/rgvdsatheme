<?php
/**
 * Internationalization layer (Polylang Pro).
 *
 * Replaces the former GTranslate gate (inc/translation.php). Polylang serves
 * real translated content at distinct URLs (English at `/`, Spanish at `/es/`),
 * so there is no client-side machine-translation bridge and no home-only gate:
 * every page can have a translation.
 *
 * Owns:
 * - Registering the custom `event` post type as translatable (page/post are
 *   translatable by default).
 * - The `languages` switcher context the header toggle renders as <a> links
 *   (per language: code, label, name, active, translation url).
 * - A `pll__` / `pll_e` Twig function so views can translate static strings.
 * - Registration of the theme's static UI strings (chrome + home headings +
 *   empty states) so their Spanish values live in Polylang → Strings.
 * - Translated header nav / about menu labels passed to the SiteHeader island.
 *
 * Front-page BODY copy (hero, who-we-are, get-involved) is NOT translated here:
 * it comes from the Spanish page's own ACF fields (see bin/seed.php).
 */

/**
 * Make the custom `event` CPT translatable. `post` and `page` already are.
 *
 * @param string[] $types    Translatable post types keyed by name.
 * @param bool     $settings Whether called from the Polylang settings screen.
 * @return string[]
 */
function rgvdsa_i18n_translatable_post_types( $types, $settings ) {
	$types['event'] = 'event';
	return $types;
}
add_filter( 'pll_get_post_types', 'rgvdsa_i18n_translatable_post_types', 10, 2 );

/**
 * Static UI strings the theme renders outside ACF, registered for translation.
 *
 * Keyed slug => English source string. `pll__( <source> )` translates by the
 * source string, so views call e.g. `{{ pll__('Upcoming events') }}`.
 *
 * @return array<string,string>
 */
function rgvdsa_i18n_strings() {
	return array(
		// Chrome — header.
		'nav_about'          => 'About',
		'nav_calendar'       => 'Calendar',
		'nav_blog'           => 'Blog',
		'nav_get_involved'   => 'Get Involved',
		'cta_join'           => 'Join DSA',
		'about_chapter'      => 'About the Chapter',
		'about_mission'      => 'Mission & History',
		'about_counties'     => 'Counties We Serve',
		'about_committees'   => 'Committees',
		'about_bylaws'       => 'Bylaws & Code of Conduct',
		'about_faq'          => 'FAQ',
		// Home — section headings + links.
		'home_events_head'   => 'Upcoming events',
		'home_events_all'    => 'Full calendar →',
		'home_events_empty_h' => 'No events on the books yet',
		'home_view_event'    => 'View event',
		'home_blog_head'     => 'From the blog',
		'home_blog_all'      => 'All posts →',
		'home_blog_read'     => 'Read the post →',
		'home_blog_empty_h'  => 'Posts coming soon',
		'home_blog_empty_p'  => 'The chapter is writing its first dispatches — check back shortly.',
		'home_follow'        => 'Follow along:',
		'home_email_us'      => 'Email us',
		'home_communities'   => 'Communities we serve',
		// Interior page chrome (page.twig / page-about / page-get-involved).
		'chrome_on_this_page' => 'On this page',
		'chrome_related'      => 'Related',
		'chrome_document'     => 'Document',
		'chrome_what_covers'  => 'What it covers',
		'chrome_action'       => 'Action',
	);
}

/**
 * Register the static strings with Polylang so editors can translate them.
 */
function rgvdsa_i18n_register_strings() {
	if ( ! function_exists( 'pll_register_string' ) ) {
		return;
	}
	foreach ( rgvdsa_i18n_strings() as $slug => $string ) {
		pll_register_string( 'rgvdsa_' . $slug, $string, 'RGV DSA', false );
	}
}
add_action( 'init', 'rgvdsa_i18n_register_strings' );

/**
 * The language switcher model for the current request.
 *
 * Uses Polylang's own per-request translation resolution: each entry's `url`
 * is the translation of the current page in that language, falling back to the
 * language home when the current page has no translation.
 *
 * @return array<int,array{code:string,label:string,name:string,active:bool,url:string}>
 */
function rgvdsa_i18n_languages() {
	if ( ! function_exists( 'pll_the_languages' ) ) {
		return array();
	}

	$raw = pll_the_languages(
		array(
			'raw'                    => 1,
			'hide_if_no_translation' => 0,
			'hide_current'           => 0,
			'display_names_as'       => 'name',
		)
	);

	if ( empty( $raw ) || ! is_array( $raw ) ) {
		return array();
	}

	$languages = array();
	foreach ( $raw as $lang ) {
		$slug        = isset( $lang['slug'] ) ? (string) $lang['slug'] : '';
		$languages[] = array(
			'code'   => $slug,
			'label'  => strtoupper( $slug ),
			'name'   => isset( $lang['name'] ) ? (string) $lang['name'] : strtoupper( $slug ),
			'active' => ! empty( $lang['current_lang'] ),
			'url'    => isset( $lang['url'] ) ? (string) $lang['url'] : '',
		);
	}

	return $languages;
}

/**
 * The language switcher model for a specific post, independent of the global
 * queried object.
 *
 * The REST single-post handler runs outside the main query, so
 * `pll_the_languages()` (which reads the queried object) can't resolve the
 * switcher there. This builds the same shape as rgvdsa_i18n_languages() for a
 * given post by resolving each language's translation permalink directly, so
 * the JSON fast-path can refresh the header switcher after a client-side
 * navigation to a single post (otherwise it stays frozen at the archive's URLs).
 *
 * @param int $post_id Post whose translations to resolve.
 * @return array<int,array{code:string,label:string,name:string,active:bool,url:string}>
 */
function rgvdsa_i18n_languages_for_post( $post_id ) {
	if ( ! function_exists( 'pll_languages_list' ) || ! function_exists( 'pll_get_post' ) ) {
		return array();
	}

	$slugs = (array) pll_languages_list();
	if ( empty( $slugs ) ) {
		return array();
	}
	$names   = (array) pll_languages_list( array( 'fields' => 'name' ) );
	$current = function_exists( 'pll_get_post_language' )
		? (string) pll_get_post_language( (int) $post_id )
		: '';

	$languages = array();
	foreach ( $slugs as $i => $slug ) {
		$slug       = (string) $slug;
		$translated = pll_get_post( (int) $post_id, $slug );
		if ( $translated ) {
			$url = (string) get_permalink( $translated );
		} else {
			$url = function_exists( 'pll_home_url' ) ? (string) pll_home_url( $slug ) : home_url( '/' );
		}
		$languages[] = array(
			'code'   => $slug,
			'label'  => strtoupper( $slug ),
			'name'   => isset( $names[ $i ] ) ? (string) $names[ $i ] : strtoupper( $slug ),
			'active' => $slug === $current,
			'url'    => $url,
		);
	}

	return $languages;
}

/**
 * Localize an internal path to the current language's translation URL.
 *
 * On the default language (or for external / mailto links) the path is returned
 * unchanged. On a secondary language, the page whose slug matches the path is
 * resolved to its translation and that permalink is returned (preserving any
 * `#fragment`); when no translation exists, the language home is used so a link
 * never lands on the wrong-language page.
 *
 * @param string $path Internal path (e.g. '/about/#mission') or absolute URL.
 * @return string
 */
function rgvdsa_i18n_localize_url( $path ) {
	if ( ! is_string( $path ) || '' === $path
		|| preg_match( '#^(https?:)?//#', $path ) || 0 === strpos( $path, 'mailto:' ) ) {
		return $path;
	}
	if ( ! function_exists( 'pll_current_language' ) ) {
		return $path;
	}

	$current = (string) pll_current_language();
	$default = function_exists( 'pll_default_language' ) ? (string) pll_default_language() : '';
	if ( '' === $current || $current === $default ) {
		return $path;
	}

	$fragment = '';
	$hash     = strpos( $path, '#' );
	if ( false !== $hash ) {
		$fragment = substr( $path, $hash );
		$path     = substr( $path, 0, $hash );
	}

	$slug = trim( (string) wp_parse_url( $path, PHP_URL_PATH ), '/' );
	if ( '' !== $slug && function_exists( 'pll_get_post' ) ) {
		$en_page = get_page_by_path( $slug );
		if ( $en_page ) {
			$translated = pll_get_post( $en_page->ID, $current );
			if ( $translated ) {
				return get_permalink( $translated ) . $fragment;
			}
		}
	}

	return function_exists( 'pll_home_url' ) ? pll_home_url( $current ) : home_url( '/' );
}

/**
 * Header nav / about menu items, labels translated via the registered strings
 * and hrefs resolved to the current language's translation URLs (falling back
 * to the language home when a target is untranslated). Mirrors the Vue fixture
 * defaults in SiteHeader.vue.
 *
 * @return array{nav:array<int,array{label:string,href:string}>,about:array<int,array{label:string,href:string}>}
 */
function rgvdsa_i18n_header_menus() {
	$t = function_exists( 'pll__' ) ? 'pll__' : 'strval';

	$nav = array(
		array(
			'label' => $t( 'Calendar' ),
			'href'  => '/calendar/',
		),
		array(
			'label' => $t( 'Blog' ),
			'href'  => '/blog/',
		),
		array(
			'label' => $t( 'Get Involved' ),
			'href'  => '/get-involved/',
		),
	);

	$about = array(
		array(
			'label' => $t( 'About the Chapter' ),
			'href'  => '/about/',
		),
		array(
			'label' => $t( 'Mission & History' ),
			'href'  => '/about/#mission',
		),
		array(
			'label' => $t( 'Counties We Serve' ),
			'href'  => '/about/#counties',
		),
		array(
			'label' => $t( 'Committees' ),
			'href'  => '/about/#committees',
		),
		array(
			'label' => $t( 'Bylaws & Code of Conduct' ),
			'href'  => '/about/#bylaws',
		),
		array(
			'label' => $t( 'FAQ' ),
			'href'  => '/about/#faq',
		),
	);

	foreach ( $nav as &$nav_item ) {
		$nav_item['href'] = rgvdsa_i18n_localize_url( $nav_item['href'] );
	}
	unset( $nav_item );
	foreach ( $about as &$about_item ) {
		$about_item['href'] = rgvdsa_i18n_localize_url( $about_item['href'] );
	}
	unset( $about_item );

	return array(
		'nav'   => $nav,
		'about' => $about,
	);
}

/**
 * Expose i18n data + a translated header menu to every Timber render.
 *
 * @param array $context Timber context.
 * @return array
 */
function rgvdsa_i18n_context( $context ) {
	$context['languages']        = rgvdsa_i18n_languages();
	$context['current_language'] = function_exists( 'pll_current_language' ) ? pll_current_language() : 'en';
	$context['home_url']         = function_exists( 'pll_home_url' )
		? pll_home_url( $context['current_language'] )
		: home_url( '/' );

	$menus                        = rgvdsa_i18n_header_menus();
	$context['header_nav_items']  = $menus['nav'];
	$context['header_about_items'] = $menus['about'];
	$context['join_label']        = function_exists( 'pll__' ) ? pll__( 'Join DSA' ) : 'Join DSA';
	$context['about_label']       = function_exists( 'pll__' ) ? pll__( 'About' ) : 'About';

	return $context;
}
add_filter( 'timber/context', 'rgvdsa_i18n_context' );

/**
 * Register `pll__` and `pll_e` as Twig functions so views can localize strings.
 *
 * @param \Twig\Environment $twig Timber's Twig environment.
 * @return \Twig\Environment
 */
function rgvdsa_i18n_twig( $twig ) {
	if ( function_exists( 'pll__' ) ) {
		$twig->addFunction( new \Twig\TwigFunction( 'pll__', 'pll__' ) );
		$twig->addFunction( new \Twig\TwigFunction( 'pll_e', 'pll_e' ) );
	} else {
		// Graceful fallback if Polylang is deactivated: echo the source string.
		$twig->addFunction( new \Twig\TwigFunction( 'pll__', 'strval' ) );
		$twig->addFunction( new \Twig\TwigFunction( 'pll_e', 'strval' ) );
	}
	return $twig;
}
add_filter( 'timber/twig', 'rgvdsa_i18n_twig' );

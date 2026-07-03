<?php

use Timber\Site;
use Kucrut\Vite;

/**
 * Class StarterSite
 */
class StarterSite extends Site {
	public function __construct() {
		add_action( 'after_setup_theme', array( $this, 'theme_supports' ) );
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'theme_enqueue_scripts' ) );
		// Preload above-the-fold fonts before the stylesheet prints (wp_print_styles is priority 8).
		add_action( 'wp_head', array( $this, 'preload_fonts' ), 2 );

		add_filter( 'timber/context', array( $this, 'add_to_context' ) );
		add_filter( 'timber/twig', array( $this, 'add_to_twig' ) );
		add_filter( 'timber/twig/environment/options', [ $this, 'update_twig_environment_options' ] );

		parent::__construct();
	}

	/**
	 * This is where you can register custom post types.
	 */
	public function register_post_types() {

	}

	/**
	 * This is where you can register custom taxonomies.
	 */
	public function register_taxonomies() {

	}

	/**
	 * This is where you add some context
	 *
	 * @param string $context context['this'] Being the Twig's {{ this }}.
	 */
	public function add_to_context( $context ) {
		$primary         = Timber::get_menu( 'primary' );
		$context['menu'] = $primary ?: Timber::get_menu();
		$context['site'] = $this;

		$path                    = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
		$context['current_path'] = is_string( $path ) ? $path : '';

		$context['chapter'] = array(
			'join_url'       => $this->option_field( 'join_url', 'https://act.dsausa.org/donate/membership' ),
			'newsletter_url' => $this->option_field( 'newsletter_url', 'https://actionnetwork.org/forms/dsa-rgv-newsletter-sign-up' ),
			'contact_email'  => $this->option_field( 'contact_email', '' ),
			'footer_tagline' => $this->option_field( 'footer_tagline', '' ),
			'instagram_url'  => $this->option_field( 'instagram_url', 'https://www.instagram.com/dsa_rgv/' ),
			'committees'     => function_exists( 'rgvdsa_chapter_committees' ) ? rgvdsa_chapter_committees() : array(),
			'socials'        => array(
				array(
					'name' => 'Facebook',
					'url'  => $this->option_field( 'facebook_url', 'https://facebook.com/dsargv' ),
				),
				array(
					'name' => 'Instagram',
					'url'  => $this->option_field( 'instagram_url', 'https://instagram.com/dsa_rgv' ),
				),
				array(
					'name' => 'Twitter',
					'url'  => $this->option_field( 'twitter_url', 'https://twitter.com/dsa_rgv' ),
				),
			),
		);

		$context['header_nav_items'] = $this->menu_nav_items( $primary );
		// About▾ dropdown: `about` menu location; SiteHeader.vue's fixture
		// default holds when no menu is assigned (same contract as navItems).
		$context['header_about_items'] = has_nav_menu( 'about' ) ? $this->menu_nav_items( Timber::get_menu( 'about' ) ) : null;
		$context['footer_columns']     = $this->footer_columns();

		return $context;
	}

	/**
	 * Read an ACF options field, falling back when ACF is inactive or the
	 * value is empty.
	 *
	 * @param string $name     Field name on the Chapter Settings options page.
	 * @param mixed  $fallback Value used when unset/empty.
	 *
	 * @return mixed
	 */
	private function option_field( $name, $fallback ) {
		if ( ! function_exists( 'get_field' ) ) {
			return $fallback;
		}

		$value = get_field( $name, 'option' );

		return ( null === $value || '' === $value ) ? $fallback : $value;
	}

	/**
	 * Map a Timber menu to the NavLink island shape.
	 *
	 * @param \Timber\Menu|null $menu Menu to map.
	 *
	 * @return array|null [{ label, href }] or null when the menu is missing/empty.
	 */
	private function menu_nav_items( $menu ) {
		if ( ! $menu || empty( $menu->items ) ) {
			return null;
		}

		$items = array();
		foreach ( $menu->items as $item ) {
			$items[] = array(
				'label' => (string) $item->title(),
				'href'  => (string) $item->link(),
			);
		}

		return $items ?: null;
	}

	/**
	 * Build SiteFooter columns from the four footer menu locations.
	 *
	 * @return array|null [{ title, links: [{ label, href, external? }] }] or
	 *                    null when no footer location has a menu assigned.
	 */
	private function footer_columns() {
		$locations = array(
			'footer_about'     => __( 'About', 'rgvdsatheme' ),
			'footer_involved'  => __( 'Get Involved', 'rgvdsatheme' ),
			'footer_resources' => __( 'Resources', 'rgvdsatheme' ),
			'footer_contact'   => __( 'Contact', 'rgvdsatheme' ),
		);

		$site_host = parse_url( home_url(), PHP_URL_HOST );
		$columns   = array();

		foreach ( $locations as $location => $title ) {
			if ( ! has_nav_menu( $location ) ) {
				continue;
			}

			$menu = Timber::get_menu( $location );
			if ( ! $menu || empty( $menu->items ) ) {
				continue;
			}

			$links = array();
			foreach ( $menu->items as $item ) {
				$href = (string) $item->link();
				$link = array(
					'label' => (string) $item->title(),
					'href'  => $href,
				);

				$link_host = parse_url( $href, PHP_URL_HOST );
				if ( $link_host && $site_host && 0 !== strcasecmp( $link_host, $site_host ) ) {
					$link['external'] = true;
				}

				$links[] = $link;
			}

			if ( $links ) {
				$columns[] = array(
					'title' => $title,
					'links' => $links,
				);
			}
		}

		return $columns ?: null;
	}

	public function theme_supports() {
		// Add default posts and comments RSS feed links to head.
		add_theme_support( 'automatic-feed-links' );

		/*
		 * Let WordPress manage the document title.
		 * By adding theme support, we declare that this theme does not use a
		 * hard-coded <title> tag in the document head, and expect WordPress to
		 * provide it for us.
		 */
		add_theme_support( 'title-tag' );

		/*
		 * Enable support for Post Thumbnails on posts and pages.
		 *
		 * @link https://developer.wordpress.org/themes/functionality/featured-images-post-thumbnails/
		 */
		add_theme_support( 'post-thumbnails' );

		/*
		 * Switch default core markup for search form, comment form, and comments
		 * to output valid HTML5.
		 */
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
			)
		);

		/*
		 * Enable support for Post Formats.
		 *
		 * See: https://codex.wordpress.org/Post_Formats
		 */
		add_theme_support(
			'post-formats',
			array(
				'aside',
				'image',
				'video',
				'quote',
				'link',
				'gallery',
				'audio',
			)
		);

		add_theme_support( 'menus' );

		register_nav_menus(
			array(
				'primary'          => __( 'Primary Menu', 'rgvdsatheme' ),
				'about'            => __( 'Header — About Dropdown', 'rgvdsatheme' ),
				'footer_about'     => __( 'Footer — About', 'rgvdsatheme' ),
				'footer_involved'  => __( 'Footer — Get Involved', 'rgvdsatheme' ),
				'footer_resources' => __( 'Footer — Resources', 'rgvdsatheme' ),
				'footer_contact'   => __( 'Footer — Contact', 'rgvdsatheme' ),
			)
		);
	}

	/**
	 * This is where you can add your own functions to twig.
	 *
	 * @param Twig\Environment $twig get extension.
	 */
	public function add_to_twig( $twig ) {
		/**
		 * Required when you want to use Twig’s template_from_string.
		 * @link https://twig.symfony.com/doc/3.x/functions/template_from_string.html
		 */
		// $twig->addExtension( new Twig\Extension\StringLoaderExtension() );

		return $twig;
	}

	/**
	 * Updates Twig environment options.
	 *
	 * @link https://twig.symfony.com/doc/2.x/api.html#environment-options
	 *
	 * \@param array $options An array of environment options.
	 *
	 * @return array
	 */
	function update_twig_environment_options( $options ) {
	    // $options['autoescape'] = true;

	    return $options;
	}

    /**
     * Enqueue scripts used within the theme
     */
    public function theme_enqueue_scripts() {
        Vite\enqueue_asset(
            dirname( __DIR__ )  . '/dist',
            'src/ts/app.ts',
            [
                'handle' => 'main-app-script',
                'in-footer' => true,
            ]
        );
    }

    /**
     * Emit <link rel="preload"> for the above-the-fold font faces so the
     * browser fetches them in parallel with the stylesheet instead of
     * discovering them only after the CSS parses.
     *
     * Vite content-hashes the filenames (they change every build), so the
     * hashed paths are resolved from dist/manifest.json rather than hard-coded.
     * Preload only the faces the first paint needs: Open Sans 400 (body) and
     * Montserrat 700/800 (headings) — over-preloading wastes bandwidth.
     */
    public function preload_fonts() {
        $manifest_path = dirname( __DIR__ ) . '/dist/manifest.json';
        if ( ! is_readable( $manifest_path ) ) {
            return;
        }
        $manifest = json_decode( file_get_contents( $manifest_path ), true );
        if ( empty( $manifest['src/ts/app.ts']['assets'] ) ) {
            return;
        }

        // Source basenames of the faces worth preloading (hash + extension appended by Vite).
        $wanted   = array( 'OpenSans-Regular', 'Montserrat-Bold', 'Montserrat-ExtraBold' );
        $base_url = trailingslashit( get_template_directory_uri() ) . 'dist/';

        foreach ( $manifest['src/ts/app.ts']['assets'] as $asset ) {
            if ( substr( $asset, -6 ) !== '.woff2' ) {
                continue;
            }
            $name = basename( $asset );
            foreach ( $wanted as $prefix ) {
                if ( strpos( $name, $prefix . '-' ) === 0 ) {
                    printf(
                        '<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
                        esc_url( $base_url . $asset )
                    );
                    break;
                }
            }
        }
    }
}

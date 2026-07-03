<?php
/**
 * Chapter settings + site chrome wiring.
 *
 * Owns: ACF options page (join URL, contact email, socials, EN/ES flag,
 * event count, counties strip, committees repeater), WP menu locations,
 * StarterSite `chapter` context sourcing, and header/footer island props.
 */

/**
 * ACF options page + "Chapter Settings" field group.
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_options_page' ) || ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_options_page(
			array(
				'page_title' => __( 'Chapter Settings', 'rgvdsatheme' ),
				'menu_title' => __( 'Chapter Settings', 'rgvdsatheme' ),
				'menu_slug'  => 'rgvdsa-chapter-settings',
				'capability' => 'edit_theme_options',
				'icon_url'   => 'dashicons-groups',
				'redirect'   => false,
				'autoload'   => true,
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_rgvdsa_options_chapter',
				'title'    => 'Chapter Settings',
				'fields'   => array(
					array(
						'key'          => 'field_rgvdsa_options_join_url',
						'label'        => 'Join URL',
						'name'         => 'join_url',
						'type'         => 'url',
						'instructions' => 'National DSA membership signup link. Leave blank to use the theme default.',
					),
					array(
						'key'          => 'field_rgvdsa_options_newsletter_url',
						'label'        => 'Newsletter URL',
						'name'         => 'newsletter_url',
						'type'         => 'url',
						'instructions' => 'Newsletter signup form link. Leave blank to use the theme default.',
					),
					array(
						'key'          => 'field_rgvdsa_options_contact_email',
						'label'        => 'Contact email',
						'name'         => 'contact_email',
						'type'         => 'email',
						'instructions' => 'Used for the footer accessibility contact link.',
					),
					array(
						'key'   => 'field_rgvdsa_options_instagram_url',
						'label' => 'Instagram URL',
						'name'  => 'instagram_url',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_rgvdsa_options_facebook_url',
						'label' => 'Facebook URL',
						'name'  => 'facebook_url',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_rgvdsa_options_twitter_url',
						'label' => 'Twitter URL',
						'name'  => 'twitter_url',
						'type'  => 'url',
					),
					array(
						'key'           => 'field_rgvdsa_options_default_share_image',
						'label'         => 'Default share image',
						'name'          => 'default_share_image',
						'type'          => 'image',
						'return_format' => 'id',
						'preview_size'  => 'medium',
						'instructions'  => 'Shown in link previews (social, messengers) when content has no featured image. Falls back to the theme logo.',
					),
					array(
						'key'           => 'field_rgvdsa_options_event_count',
						'label'         => 'Home: upcoming event count',
						'name'          => 'event_count',
						'type'          => 'number',
						'min'           => 1,
						'max'           => 6,
						'step'          => 1,
						'default_value' => 5,
					),
					array(
						'key'           => 'field_rgvdsa_options_show_counties_strip',
						'label'         => 'Home: show counties strip',
						'name'          => 'show_counties_strip',
						'type'          => 'true_false',
						'default_value' => 1,
						'ui'            => 1,
					),
					array(
						'key'          => 'field_rgvdsa_options_counties',
						'label'        => 'Home: counties strip',
						'name'         => 'counties',
						'type'         => 'repeater',
						'instructions' => 'Communities listed in the home counties strip. Leave empty to use the theme defaults.',
						'layout'       => 'table',
						'button_label' => 'Add community',
						'sub_fields'   => array(
							array(
								'key'      => 'field_rgvdsa_options_county_name',
								'label'    => 'Name',
								'name'     => 'name',
								'type'     => 'text',
								'required' => 1,
							),
						),
					),
					array(
						'key'          => 'field_rgvdsa_options_footer_tagline',
						'label'        => 'Footer tagline',
						'name'         => 'footer_tagline',
						'type'         => 'text',
						'instructions' => 'Short line under the footer logo. Leave blank to use the theme default.',
					),
					array(
						'key'          => 'field_rgvdsa_options_newhere_heading',
						'label'        => '"New here?" card heading',
						'name'         => 'newhere_heading',
						'type'         => 'text',
						'instructions' => 'Sidebar card shown on About and interior pages. Leave blank to use the theme defaults.',
					),
					array(
						'key'          => 'field_rgvdsa_options_newhere_body',
						'label'        => '"New here?" card body',
						'name'         => 'newhere_body',
						'type'         => 'textarea',
						'rows'         => 2,
					),
					array(
						'key'          => 'field_rgvdsa_options_newhere_link_label',
						'label'        => '"New here?" card button label',
						'name'         => 'newhere_link_label',
						'type'         => 'text',
					),
					array(
						'key'          => 'field_rgvdsa_options_newhere_link_url',
						'label'        => '"New here?" card button URL',
						'name'         => 'newhere_link_url',
						'type'         => 'text',
						'instructions' => 'Full URL, relative path, or #anchor.',
					),
					array(
						'key'          => 'field_rgvdsa_options_committees',
						'label'        => 'Committees',
						'name'         => 'committees',
						'type'         => 'repeater',
						'instructions' => 'Rendered on Get Involved and About. Leave empty to use the theme defaults.',
						'layout'       => 'block',
						'button_label' => 'Add committee',
						'sub_fields'   => array(
							array(
								'key'      => 'field_rgvdsa_options_committee_name',
								'label'    => 'Name',
								'name'     => 'name',
								'type'     => 'text',
								'required' => 1,
							),
							array(
								'key'   => 'field_rgvdsa_options_committee_desc',
								'label' => 'Description',
								'name'  => 'desc',
								'type'  => 'textarea',
								'rows'  => 3,
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'options_page',
							'operator' => '==',
							'value'    => 'rgvdsa-chapter-settings',
						),
					),
				),
			)
		);
	}
);

/**
 * Chapter committees: ACF options repeater, falling back to the design fixture.
 *
 * Shared contract — other domains call this via function_exists() guard.
 *
 * @return array [{ name: string, desc: string }]
 */
function rgvdsa_chapter_committees() {
	// Design fixture — rendered on both Get Involved and About (03-DESIGN-SPEC.md § About).
	$fixture = array(
		array(
			'name' => 'Political Education',
			'desc' => 'Reading groups, night school, and workshops that build our shared analysis.',
		),
		array(
			'name' => 'Mutual Aid',
			'desc' => "Meeting our neighbors' immediate needs while organizing for lasting change.",
		),
		array(
			'name' => 'Labor',
			'desc' => 'Supporting workers organizing on the job across the Valley.',
		),
		array(
			'name' => 'Communications',
			'desc' => "Social media, design, and this website — telling the chapter's story.",
		),
		array(
			'name' => 'Electoral',
			'desc' => 'Backing candidates and ballot measures that fight for working people.',
		),
		array(
			'name' => 'Membership & Onboarding',
			'desc' => 'Welcoming new members and making sure no one falls through the cracks.',
		),
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $fixture;
	}

	$rows = get_field( 'committees', 'option' );
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return $fixture;
	}

	$committees = array();
	foreach ( $rows as $row ) {
		$name = trim( (string) ( $row['name'] ?? '' ) );
		if ( '' === $name ) {
			continue;
		}
		$committees[] = array(
			'name' => $name,
			'desc' => trim( (string) ( $row['desc'] ?? '' ) ),
		);
	}

	return $committees ?: $fixture;
}

/**
 * Chapter counties/communities for the home strip: ACF options repeater,
 * falling back to the design fixture.
 *
 * @return string[] Community names.
 */
function rgvdsa_chapter_counties() {
	// Design fixture (03-DESIGN-SPEC.md § Home — counties strip).
	$fixture = array(
		'McAllen', 'Edinburg', 'Brownsville', 'Harlingen', 'Pharr', 'San Juan',
		'San Benito', 'Raymondville', 'Roma', 'La Joya', 'Rio Grande City',
		'Zapata', 'La Grulla', 'La Feria', 'Rio Hondo',
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $fixture;
	}

	$rows = get_field( 'counties', 'option' );
	if ( empty( $rows ) || ! is_array( $rows ) ) {
		return $fixture;
	}

	$counties = array();
	foreach ( $rows as $row ) {
		$name = trim( (string) ( $row['name'] ?? '' ) );
		if ( '' !== $name ) {
			$counties[] = $name;
		}
	}

	return $counties ?: $fixture;
}

/**
 * Home hero copy: front-page ACF group, falling back to the design copy so
 * the section renders before it is seeded. Editors own the canonical copy.
 *
 * @param int $front_id Front page ID (get_option( 'page_on_front' )).
 * @return array{heading:string,lede:string,cta_primary_label:string,cta_primary_url:string,cta_secondary_label:string,cta_secondary_url:string}
 */
function rgvdsa_front_hero( $front_id ) {
	$defaults = array(
		'heading'             => 'A better world is possible. We’re building it in the Valley.',
		'lede'                => 'We’re the Rio Grande Valley chapter of the Democratic Socialists of America — the largest socialist organization in the United States — organizing working-class power across our border communities.',
		'cta_primary_label'   => 'Join DSA',
		'cta_primary_url'     => 'https://act.dsausa.org/donate/membership',
		'cta_secondary_label' => 'Come to a meeting ↓',
		'cta_secondary_url'   => '#events',
		'badge'               => 'New here? Start with <strong class="notranslate">RGV-DSA 101</strong> — no experience needed.',
	);

	if ( ! function_exists( 'get_field' ) || ! $front_id ) {
		return $defaults;
	}

	$hero = $defaults;
	foreach ( array_keys( $defaults ) as $key ) {
		$value = get_field( 'hero_' . $key, $front_id );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			$hero[ $key ] = trim( $value );
		}
	}

	// Rendered unescaped in the Twig (inline markup allowed).
	$hero['badge'] = wp_kses_post( $hero['badge'] );

	return $hero;
}

/**
 * "Who we are" front-page section copy: ACF fields on the front page,
 * falling back to the design copy.
 *
 * @param int $front_id Front page ID.
 * @return array{eyebrow:string,heading:string,p1:string,p2:string,link_label:string,link_url:string}
 */
function rgvdsa_front_who( $front_id ) {
	$defaults = array(
		'eyebrow'    => 'Who we are',
		'heading'    => 'We are <span class="notranslate">DSA-RGV</span>',
		'p1'         => 'The RGV is one of the most economically unequal regions in the country — but it doesn’t have to stay that way. As democratic socialists, we’re building working-class power to challenge the dominance of the wealthy and the powerful across our border communities.',
		'p2'         => 'Together, we’re fighting for a Valley where working people have real power, and where everyone can live a dignified life — regardless of where they were born or how they got here.',
		'link_label' => 'More about our chapter →',
		'link_url'   => '/about/',
	);

	if ( ! function_exists( 'get_field' ) || ! $front_id ) {
		return $defaults;
	}

	$who = $defaults;
	foreach ( array_keys( $defaults ) as $key ) {
		$value = get_field( 'who_' . $key, $front_id );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			$who[ $key ] = trim( $value );
		}
	}

	// Heading is rendered unescaped (inline markup allowed).
	$who['heading'] = wp_kses_post( $who['heading'] );

	return $who;
}

/**
 * Front-page "Get involved" section: eyebrow, heading, and the steps
 * repeater, falling back to the design copy. Step numbers are positional
 * (01, 02, …) — computed here, not stored.
 *
 * @param int    $front_id Front page ID.
 * @param string $join_url Chapter join URL (default step 1 href).
 * @return array{eyebrow:string,heading:string,steps:array}
 */
function rgvdsa_front_involved( $front_id, $join_url ) {
	$steps = array(
		array( 'title' => 'Join DSA', 'body' => 'Become a national DSA member — dues are sliding-scale, and membership automatically connects you to our chapter.', 'link_label' => 'Sign up at dsausa.org →', 'href' => $join_url, 'external' => true ),
		array( 'title' => 'Come to RGV-DSA 101', 'body' => 'Our intro session for new and curious folks — what we do, how the chapter works, and how you can plug in. Virtual and in-person options.', 'link_label' => 'Find a session →', 'href' => '#events', 'external' => false ),
		array( 'title' => 'Plug into the work', 'body' => 'Join a committee, get on our WhatsApp, and show up. Members receive an invite to our communication channels after onboarding.', 'link_label' => 'See committees →', 'href' => '/get-involved/#committees', 'external' => false ),
	);

	$involved = array(
		'eyebrow' => 'Get involved',
		'heading' => 'Three steps to start organizing',
	);

	if ( function_exists( 'get_field' ) && $front_id ) {
		foreach ( array( 'eyebrow', 'heading' ) as $key ) {
			$value = get_field( 'home_involved_' . $key, $front_id );
			if ( is_string( $value ) && '' !== trim( $value ) ) {
				$involved[ $key ] = trim( $value );
			}
		}

		$rows = get_field( 'home_steps', $front_id );
		if ( is_array( $rows ) && $rows ) {
			$mapped = array();
			foreach ( $rows as $row ) {
				$title = trim( (string) ( $row['title'] ?? '' ) );
				if ( '' === $title ) {
					continue;
				}
				$url      = trim( (string) ( $row['link_url'] ?? '' ) );
				$mapped[] = array(
					'title'      => $title,
					'body'       => trim( (string) ( $row['body'] ?? '' ) ),
					'link_label' => trim( (string) ( $row['link_label'] ?? '' ) ),
					'href'       => $url,
					'external'   => function_exists( 'rgvdsa_pages_external' ) ? rgvdsa_pages_external( $url ) : false,
				);
			}
			if ( $mapped ) {
				$steps = $mapped;
			}
		}
	}

	$involved['steps'] = $steps;

	return $involved;
}

/**
 * "New here?" sidebar card (About + interior pages): Chapter Settings fields,
 * falling back to the design copy.
 *
 * @return array{heading:string,body:string,link_label:string,url:string}
 */
function rgvdsa_newhere_card() {
	$card = array(
		'heading'    => 'New here?',
		'body'       => 'Come to an <span class="notranslate">RGV-DSA 101</span> — our intro session for new and curious folks.',
		'link_label' => 'Find a session',
		'url'        => '/calendar/',
	);

	if ( ! function_exists( 'get_field' ) ) {
		return $card;
	}

	$fields = array(
		'heading'    => 'newhere_heading',
		'body'       => 'newhere_body',
		'link_label' => 'newhere_link_label',
		'url'        => 'newhere_link_url',
	);
	foreach ( $fields as $key => $name ) {
		$value = get_field( $name, 'option' );
		if ( is_string( $value ) && '' !== trim( $value ) ) {
			$card[ $key ] = trim( $value );
		}
	}

	// Body is rendered unescaped in the Twig (inline markup allowed).
	$card['body'] = wp_kses_post( $card['body'] );

	return $card;
}

/**
 * "Who we are" photo: front-page ACF image, returned as { src, alt } or null
 * so the Twig owns the stripe fallback (no fake placeholder caption).
 *
 * @param int $front_id Front page ID (get_option( 'page_on_front' )).
 * @return array{src:string,alt:string}|null
 */
function rgvdsa_front_about_image( $front_id ) {
	if ( ! function_exists( 'get_field' ) || ! $front_id ) {
		return null;
	}

	$image = get_field( 'about_image', $front_id );
	if ( empty( $image['url'] ) ) {
		return null;
	}

	return array(
		'src' => $image['url'],
		'alt' => ! empty( $image['alt'] ) ? $image['alt'] : 'Chapter members organizing in the Rio Grande Valley',
	);
}

/**
 * Front page: inject options-driven knobs early (priority 5) so the events
 * domain can read `event_count` / `show_counties_strip` at priority 10.
 */
add_filter(
	'rgvdsa/context/front_page',
	function ( $context ) {
		$event_count         = 5;
		$show_counties_strip = true;

		if ( function_exists( 'get_field' ) ) {
			$count = (int) get_field( 'event_count', 'option' );
			if ( $count >= 1 ) {
				$event_count = min( 6, $count );
			}

			$strip = get_field( 'show_counties_strip', 'option' );
			if ( null !== $strip && '' !== $strip ) {
				$show_counties_strip = (bool) $strip;
			}
		}

		// Read the ACF hero/who/get-involved copy from the CURRENT front page so
		// Polylang serves the Spanish page's own fields on `/es/`. The queried
		// object is the front page (EN at `/`, its ES translation at `/es/`);
		// fall back to the configured English front page.
		$front_id = get_queried_object_id();
		if ( ! $front_id ) {
			$front_id = (int) get_option( 'page_on_front' );
		}
		$join_url = isset( $context['chapter']['join_url'] ) ? (string) $context['chapter']['join_url'] : 'https://act.dsausa.org/donate/membership';

		$context['event_count']         = $event_count;
		$context['show_counties_strip'] = $show_counties_strip;
		$context['counties']            = rgvdsa_chapter_counties();
		$context['hero']                = rgvdsa_front_hero( $front_id );
		$context['about_image']         = rgvdsa_front_about_image( $front_id );
		$context['who']                 = rgvdsa_front_who( $front_id );
		$context['home_involved']       = rgvdsa_front_involved( $front_id, $join_url );

		return $context;
	},
	5
);

/**
 * Home hero ACF group — lives on the front page so editors own the hero copy
 * and CTAs (rgvdsa_front_hero() reads these; the Twig fixture is the fallback).
 */
add_action(
	'acf/init',
	function () {
		if ( ! function_exists( 'acf_add_local_field_group' ) ) {
			return;
		}

		acf_add_local_field_group(
			array(
				'key'      => 'group_rgvdsa_front_hero',
				'title'    => 'Home hero',
				'fields'   => array(
					array(
						'key'   => 'field_rgvdsa_hero_heading',
						'label' => 'Heading',
						'name'  => 'hero_heading',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_rgvdsa_hero_lede',
						'label' => 'Lede',
						'name'  => 'hero_lede',
						'type'  => 'textarea',
						'rows'  => 3,
					),
					array(
						'key'   => 'field_rgvdsa_hero_cta_primary_label',
						'label' => 'Primary CTA label',
						'name'  => 'hero_cta_primary_label',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_rgvdsa_hero_cta_primary_url',
						'label' => 'Primary CTA URL',
						'name'  => 'hero_cta_primary_url',
						'type'  => 'url',
					),
					array(
						'key'   => 'field_rgvdsa_hero_cta_secondary_label',
						'label' => 'Secondary CTA label',
						'name'  => 'hero_cta_secondary_label',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_rgvdsa_hero_cta_secondary_url',
						'label'        => 'Secondary CTA URL',
						'name'         => 'hero_cta_secondary_url',
						'type'         => 'text',
						'instructions' => 'An in-page anchor (e.g. #events) or a full URL.',
					),
					array(
						'key'          => 'field_rgvdsa_hero_badge',
						'label'        => 'Badge line',
						'name'         => 'hero_badge',
						'type'         => 'text',
						'instructions' => 'Small pill under the CTAs. Basic HTML (e.g. <strong>) allowed. Leave blank for the theme default.',
					),
					array(
						'key'           => 'field_rgvdsa_about_image',
						'label'         => 'Who we are photo',
						'name'          => 'about_image',
						'type'          => 'image',
						'return_format' => 'array',
						'preview_size'  => 'medium',
						'instructions'  => 'Optional. Shown in the "Who we are" section; a decorative panel renders when empty.',
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page_type',
							'operator' => '==',
							'value'    => 'front_page',
						),
					),
				),
			)
		);

		acf_add_local_field_group(
			array(
				'key'      => 'group_rgvdsa_front_sections',
				'title'    => 'Home sections',
				'fields'   => array(
					array(
						'key'   => 'field_rgvdsa_front_tab_who',
						'label' => 'Who we are',
						'type'  => 'tab',
					),
					array(
						'key'   => 'field_rgvdsa_who_eyebrow',
						'label' => 'Eyebrow',
						'name'  => 'who_eyebrow',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_rgvdsa_who_heading',
						'label'        => 'Heading',
						'name'         => 'who_heading',
						'type'         => 'text',
						'instructions' => 'Basic HTML allowed.',
					),
					array(
						'key'   => 'field_rgvdsa_who_p1',
						'label' => 'First paragraph',
						'name'  => 'who_p1',
						'type'  => 'textarea',
						'rows'  => 4,
					),
					array(
						'key'   => 'field_rgvdsa_who_p2',
						'label' => 'Second paragraph',
						'name'  => 'who_p2',
						'type'  => 'textarea',
						'rows'  => 4,
					),
					array(
						'key'   => 'field_rgvdsa_who_link_label',
						'label' => 'Link label',
						'name'  => 'who_link_label',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_rgvdsa_who_link_url',
						'label'        => 'Link URL',
						'name'         => 'who_link_url',
						'type'         => 'text',
						'instructions' => 'Full URL, relative path, or #anchor.',
					),
					array(
						'key'   => 'field_rgvdsa_front_tab_involved',
						'label' => 'Get involved',
						'type'  => 'tab',
					),
					array(
						'key'   => 'field_rgvdsa_home_involved_eyebrow',
						'label' => 'Eyebrow',
						'name'  => 'home_involved_eyebrow',
						'type'  => 'text',
					),
					array(
						'key'   => 'field_rgvdsa_home_involved_heading',
						'label' => 'Heading',
						'name'  => 'home_involved_heading',
						'type'  => 'text',
					),
					array(
						'key'          => 'field_rgvdsa_home_steps',
						'label'        => 'Steps',
						'name'         => 'home_steps',
						'type'         => 'repeater',
						'layout'       => 'block',
						'button_label' => 'Add step',
						'instructions' => 'Numbered automatically (01, 02, …). Leave empty for the theme defaults.',
						'sub_fields'   => array(
							array(
								'key'      => 'field_rgvdsa_home_steps_title',
								'label'    => 'Title',
								'name'     => 'title',
								'type'     => 'text',
								'required' => 1,
							),
							array(
								'key'   => 'field_rgvdsa_home_steps_body',
								'label' => 'Body',
								'name'  => 'body',
								'type'  => 'textarea',
								'rows'  => 3,
							),
							array(
								'key'   => 'field_rgvdsa_home_steps_link_label',
								'label' => 'Link label',
								'name'  => 'link_label',
								'type'  => 'text',
							),
							array(
								'key'          => 'field_rgvdsa_home_steps_link_url',
								'label'        => 'Link URL',
								'name'         => 'link_url',
								'type'         => 'text',
								'instructions' => 'Full URL, relative path, or #anchor.',
							),
						),
					),
				),
				'location' => array(
					array(
						array(
							'param'    => 'page_type',
							'operator' => '==',
							'value'    => 'front_page',
						),
					),
				),
			)
		);
	}
);

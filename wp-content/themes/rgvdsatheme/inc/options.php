<?php
/**
 * Chapter settings + site chrome wiring.
 *
 * Owns: ACF options page (join URL, contact email, socials, EN/ES flag,
 * event count, counties + committees repeaters), WP menu locations,
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
						'key'          => 'field_rgvdsa_options_counties',
						'label'        => 'Counties & communities',
						'name'         => 'counties',
						'type'         => 'repeater',
						'instructions' => 'Communities the chapter serves. Not rendered on the v3 home (the county map covers it); kept as chapter data.',
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
 * Home hero copy: front-page ACF group, falling back to the design copy so
 * the section renders before it is seeded. Editors own the canonical copy.
 *
 * @param int $front_id Front page ID (get_option( 'page_on_front' )).
 * @return array{subhead:string,lede:string,cta_primary_label:string,cta_primary_url:string,cta_secondary_label:string,cta_secondary_url:string}
 */
function rgvdsa_front_hero( $front_id ) {
	// The on-page <h1> is designer-supplied art (06-V3-BRAND-REFRESH.md), so
	// there is no heading field; `lede` is the front page's SEO/share
	// description (inc/seo.php) and is not shown in the hero. `subhead` is the
	// line under the headline art; the secondary CTA is the dashed box.
	$defaults = array(
		'subhead'             => 'We’re fighting for the Rio Grande Valley we deserve.',
		'lede'                => 'We’re the Rio Grande Valley chapter of the Democratic Socialists of America — the largest socialist organization in the United States — organizing working-class power across our border communities.',
		'cta_primary_label'   => 'Join DSA',
		'cta_primary_url'     => 'https://act.dsausa.org/donate/membership',
		'cta_secondary_label' => 'New member? Start with DSARGV 101. Sign up here',
		'cta_secondary_url'   => function_exists( 'rgvdsa_i18n_localize_url' ) ? rgvdsa_i18n_localize_url( '/get-involved/' ) : '/get-involved/',
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

	return $hero;
}

/**
 * "Who we are" front-page section copy: ACF fields on the front page,
 * falling back to the design copy.
 *
 * @param int $front_id Front page ID.
 * @return array{eyebrow:string,heading:string,p1:string,p2:string,p3:string,link_label:string,link_url:string}
 */
function rgvdsa_front_who( $front_id ) {
	// v3 prototype copy (designs/RGV DSA Home v3.dc.html) — final per the handoff.
	$defaults = array(
		'eyebrow'    => 'Who we are',
		'heading'    => 'We are <span class="notranslate">DSARGV</span>',
		'p1'         => 'In the Rio Grande Valley, we’re on the frontlines of fascism. We have a billionaire in our backyard, ICE in our neighborhoods, and jobs that leave us overworked and underpaid.',
		'p2'         => 'But it doesn’t have to stay that way.',
		'p3'         => 'As democratic socialists, we’re building working class power on multiple fronts so that every person in the valley can live a life with dignity, respect, and solidarity. Organizing our workplaces and organizing our community to make sure our future is for workers and by workers. A better RGV is possible.<br>We’re gonna win.',
		'link_label' => 'More about our chapter',
		'link_url'   => function_exists( 'rgvdsa_i18n_localize_url' ) ? rgvdsa_i18n_localize_url( '/about/' ) : '/about/',
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

	// Heading + third paragraph are rendered unescaped (inline markup / <br> allowed).
	$who['heading'] = wp_kses_post( $who['heading'] );
	$who['p3']      = wp_kses_post( $who['p3'] );
	// v3 draws the arrow as a shared SVG; strip a trailing "→" editors may have typed.
	$who['link_label'] = rtrim( $who['link_label'], " \t→" );

	return $who;
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
 * Front page: inject options-driven knobs early (priority 5) so the events
 * domain can read `event_count` at priority 10. Receives the front page post
 * from front-page.php (filter arity 2).
 */
add_filter(
	'rgvdsa/context/front_page',
	function ( $context, $timber_post = null ) {
		$event_count = 5;

		if ( function_exists( 'get_field' ) ) {
			$count = (int) get_field( 'event_count', 'option' );
			if ( $count >= 1 ) {
				$event_count = min( 6, $count );
			}
		}

		// Read the ACF hero/who copy from the CURRENT front page so Polylang
		// serves the Spanish page's own fields on `/es/` (EN at `/`, its ES
		// translation at `/es/`); fall back to the configured English front page.
		$front_id = $timber_post ? (int) $timber_post->ID : (int) get_queried_object_id();
		if ( ! $front_id ) {
			$front_id = (int) get_option( 'page_on_front' );
		}

		$context['event_count'] = $event_count;
		$context['hero']        = rgvdsa_front_hero( $front_id );
		$context['who']         = rgvdsa_front_who( $front_id );

		return $context;
	},
	5,
	2
);

/**
 * Home hero ACF group — lives on the front page so editors own the hero copy
 * and CTAs (rgvdsa_front_hero() reads these; the PHP defaults are the fallback).
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
						'key'          => 'field_rgvdsa_hero_subhead',
						'label'        => 'Subhead',
						'name'         => 'hero_subhead',
						'type'         => 'text',
						'instructions' => 'One line under the headline artwork. Leave blank for the theme default.',
					),
					array(
						'key'          => 'field_rgvdsa_hero_lede',
						'label'        => 'Lede (search / share description)',
						'name'         => 'hero_lede',
						'type'         => 'textarea',
						'rows'         => 3,
						'instructions' => 'Used as the home page description in search results and link previews when the site tagline is empty. Not shown in the v3 hero.',
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
						'instructions' => 'Destination of the dashed "New member?" box. A relative path (e.g. /get-involved/), in-page anchor, or full URL.',
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
						'key'          => 'field_rgvdsa_who_p3',
						'label'        => 'Third paragraph',
						'name'         => 'who_p3',
						'type'         => 'textarea',
						'rows'         => 5,
						'new_lines'    => 'br',
						'instructions' => 'Line breaks are kept.',
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

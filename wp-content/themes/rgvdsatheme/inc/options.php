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
						'key'           => 'field_rgvdsa_options_es_enabled',
						'label'         => 'Spanish site enabled',
						'name'          => 'es_enabled',
						'type'          => 'true_false',
						'instructions'  => 'Turns the header ES toggle into a live link.',
						'default_value' => 0,
						'ui'            => 1,
					),
					array(
						'key'               => 'field_rgvdsa_options_es_url',
						'label'             => 'Spanish site URL',
						'name'              => 'es_url',
						'type'              => 'url',
						'conditional_logic' => array(
							array(
								array(
									'field'    => 'field_rgvdsa_options_es_enabled',
									'operator' => '==',
									'value'    => '1',
								),
							),
						),
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

		$context['event_count']         = $event_count;
		$context['show_counties_strip'] = $show_counties_strip;

		return $context;
	},
	5
);

<?php
/**
 * Events domain: CPT, taxonomy, calendar + home wiring.
 *
 * Owns: `event` CPT, `event_category` taxonomy + color term meta, event ACF
 * fields, the events ICS feed, and serialization to the island contracts.
 *
 * Public contract (other domains call these):
 * - rgvdsa_event_to_chapter_event( $post ): array — ChapterEvent shape
 *   { id, date (Y-m-d), time (display), cat (slug), title, location, desc,
 *     rsvpUrl?, gcalUrl? }.
 * - rgvdsa_event_categories(): array — [{ id, label, color }] from terms.
 */

/**
 * Chapter timezone for event display, gcal links, and the ICS feed.
 *
 * @return DateTimeZone
 */
function rgvdsa_events_timezone() {
	return new DateTimeZone( 'America/Chicago' );
}

/**
 * CPT + taxonomy registration.
 */
add_action( 'init', 'rgvdsa_events_register_post_type' );
function rgvdsa_events_register_post_type() {
	register_post_type(
		'event',
		array(
			'labels'       => array(
				'name'          => 'Events',
				'singular_name' => 'Event',
				'add_new_item'  => 'Add New Event',
				'edit_item'     => 'Edit Event',
				'not_found'     => 'No events found.',
			),
			'public'       => true,
			'has_archive'  => false, // The calendar page is the archive surface.
			'menu_icon'    => 'dashicons-calendar-alt',
			'supports'     => array( 'title', 'editor', 'thumbnail' ),
			'rewrite'      => array( 'slug' => 'events' ),
			'show_in_rest' => true,
		)
	);

	register_taxonomy(
		'event_category',
		array( 'event' ),
		array(
			'labels'            => array(
				'name'          => 'Event Categories',
				'singular_name' => 'Event Category',
			),
			'hierarchical'      => false,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'event-category' ),
		)
	);
}

/**
 * ACF field groups: event details + category term color.
 */
add_action( 'acf/init', 'rgvdsa_events_register_fields' );
function rgvdsa_events_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_events_details',
			'title'    => 'Event details',
			'fields'   => array(
				array(
					'key'            => 'field_rgvdsa_events_start_datetime',
					'label'          => 'Start date & time',
					'name'           => 'start_datetime',
					'type'           => 'date_time_picker',
					'required'       => 1,
					'return_format'  => 'Y-m-d H:i:s',
					'display_format' => 'M j, Y g:i a',
					'first_day'      => 0,
				),
				array(
					'key'            => 'field_rgvdsa_events_end_datetime',
					'label'          => 'End date & time',
					'name'           => 'end_datetime',
					'type'           => 'date_time_picker',
					'return_format'  => 'Y-m-d H:i:s',
					'display_format' => 'M j, Y g:i a',
					'first_day'      => 0,
				),
				array(
					'key'   => 'field_rgvdsa_events_venue',
					'label' => 'Venue',
					'name'  => 'venue',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_rgvdsa_events_city',
					'label' => 'City',
					'name'  => 'city',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_rgvdsa_events_rsvp_url',
					'label' => 'RSVP URL',
					'name'  => 'rsvp_url',
					'type'  => 'url',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'event',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_events_term_color',
			'title'    => 'Event category color',
			'fields'   => array(
				array(
					'key'   => 'field_rgvdsa_events_term_color',
					'label' => 'Color',
					'name'  => 'color',
					'type'  => 'color_picker',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'event_category',
					),
				),
			),
		)
	);
}

/**
 * Parse an ACF "Y-m-d H:i:s" value as chapter-local wall time.
 *
 * @param mixed $value Raw ACF/meta value.
 * @return DateTimeImmutable|null
 */
function rgvdsa_events_parse_datetime( $value ) {
	if ( ! is_string( $value ) || '' === $value ) {
		return null;
	}
	$dt = DateTimeImmutable::createFromFormat( 'Y-m-d H:i:s', $value, rgvdsa_events_timezone() );

	return $dt ?: null;
}

/**
 * Display time range, en-dash, meridiem deduped when start/end share it.
 * "7:00–8:30 PM" · "9:00 AM–12:00 PM" · "7:00 PM" (no end).
 *
 * @param DateTimeImmutable|null $start Start datetime.
 * @param DateTimeImmutable|null $end   End datetime.
 * @return string
 */
function rgvdsa_events_format_time_range( $start, $end ) {
	if ( ! $start ) {
		return '';
	}
	$start_meridiem = $start->format( 'A' );
	if ( ! $end ) {
		return $start->format( 'g:i' ) . ' ' . $start_meridiem;
	}
	$end_meridiem = $end->format( 'A' );
	if ( $start_meridiem === $end_meridiem ) {
		return $start->format( 'g:i' ) . '–' . $end->format( 'g:i' ) . ' ' . $end_meridiem;
	}

	return $start->format( 'g:i' ) . ' ' . $start_meridiem . '–' . $end->format( 'g:i' ) . ' ' . $end_meridiem;
}

/**
 * Event field value with an ACF-less fallback (values are stored raw in meta).
 *
 * @param int    $post_id Event post ID.
 * @param string $name    Field name.
 * @return mixed
 */
function rgvdsa_events_get_field( $post_id, $name ) {
	if ( function_exists( 'get_field' ) ) {
		return get_field( $name, $post_id );
	}

	return get_post_meta( $post_id, $name, true );
}

/**
 * Serialize an event post to the ChapterEvent island contract.
 *
 * @param int|WP_Post|\Timber\Post $post Event post (ID or object).
 * @return array ChapterEvent assoc array, or empty array if the post is gone.
 */
function rgvdsa_event_to_chapter_event( $post ) {
	$post_id = is_object( $post ) ? (int) $post->ID : (int) $post;
	$wp_post = get_post( $post_id );
	if ( ! $wp_post ) {
		return array();
	}

	$start = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $post_id, 'start_datetime' ) );
	$end   = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $post_id, 'end_datetime' ) );

	$registry = rgvdsa_category_registry();
	$cat      = 'chapter';
	$terms    = get_the_terms( $post_id, 'event_category' );
	if ( $terms && ! is_wp_error( $terms ) && isset( $registry[ $terms[0]->slug ] ) ) {
		$cat = $terms[0]->slug;
	}

	$venue    = trim( (string) rgvdsa_events_get_field( $post_id, 'venue' ) );
	$city     = trim( (string) rgvdsa_events_get_field( $post_id, 'city' ) );
	$location = $venue;
	if ( $city && $venue ) {
		$location = $city . ' — ' . $venue;
	} elseif ( $city ) {
		$location = $city;
	}

	$event = array(
		'id'       => (string) $post_id,
		'date'     => $start ? $start->format( 'Y-m-d' ) : get_the_date( 'Y-m-d', $wp_post ),
		'time'     => rgvdsa_events_format_time_range( $start, $end ),
		'cat'      => $cat,
		'title'    => html_entity_decode( get_the_title( $wp_post ), ENT_QUOTES, 'UTF-8' ),
		'location' => $location,
		'desc'     => trim( wp_strip_all_tags( $wp_post->post_content ) ),
	);

	$rsvp = trim( (string) rgvdsa_events_get_field( $post_id, 'rsvp_url' ) );
	if ( $rsvp ) {
		$event['rsvpUrl'] = $rsvp;
	}

	if ( $start ) {
		$gcal_end         = $end && $end > $start ? $end : $start->modify( '+1 hour' );
		$event['gcalUrl'] = 'https://calendar.google.com/calendar/render?' . http_build_query(
			array(
				'action'   => 'TEMPLATE',
				'text'     => $event['title'],
				'dates'    => $start->format( 'Ymd\THis' ) . '/' . $gcal_end->format( 'Ymd\THis' ),
				'details'  => $event['desc'],
				'location' => $location,
				'ctz'      => 'America/Chicago',
			),
			'',
			'&',
			PHP_QUERY_RFC3986
		);
	}

	return $event;
}

/**
 * The 6 event categories for the island `categories` prop.
 * Label from the term when it exists, color from the term's ACF "color"
 * field; both fall back to the registry (categories.json) pre-seed.
 *
 * @return array [{ id: slug, label: string, color: hex }]
 */
function rgvdsa_event_categories() {
	return rgvdsa_categories( 'event_category' );
}

/**
 * Published events ordered by start_datetime meta.
 *
 * @param array $args Overrides merged over the defaults (meta_query etc.).
 * @return WP_Post[]
 */
function rgvdsa_events_query( $args = array() ) {
	return get_posts(
		wp_parse_args(
			$args,
			array(
				'post_type'      => 'event',
				'post_status'    => 'publish',
				'posts_per_page' => -1,
				'meta_key'       => 'start_datetime',
				'orderby'        => 'meta_value',
				'meta_type'      => 'DATETIME',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		)
	);
}

/**
 * Calendar page context: subscribe URLs + API base. The island fetches its
 * own event window from /rgvdsa/v1/events on mount (island-data-fetch), so
 * nothing is embedded. Keys are always set.
 */
add_filter( 'rgvdsa/context/page', 'rgvdsa_events_calendar_context', 10, 2 );
function rgvdsa_events_calendar_context( $context, $timber_post ) {
	// Key off the assigned page template, not a magic `calendar` slug, so
	// renaming the page's slug/title never breaks the events wiring (D9).
	if ( ! $timber_post || ! is_page_template( 'page-templates/calendar.php' ) ) {
		return $context;
	}

	$ics_url = get_feed_link( 'rgvdsa-events' );

	$context['calendar_api_base'] = rest_url( 'rgvdsa/v1' );
	$context['calendar_ics_url']  = $ics_url;
	$context['calendar_gcal_url'] = 'https://calendar.google.com/calendar/r?cid=' . urlencode( preg_replace( '#^https?://#', 'webcal://', $ics_url ) );

	return $context;
}

/**
 * Home page context: next N upcoming events. `event_count` is injected by
 * the options domain at priority 5; we run at 10. Always set (possibly
 * empty) — Twig owns the designed empty state.
 */
add_filter( 'rgvdsa/context/front_page', 'rgvdsa_events_front_page_context' );
function rgvdsa_events_front_page_context( $context ) {
	$count = isset( $context['event_count'] ) ? max( 1, (int) $context['event_count'] ) : 5;
	$now   = new DateTimeImmutable( 'now', rgvdsa_events_timezone() );
	$posts = rgvdsa_events_query(
		array(
			'posts_per_page' => $count,
			'meta_query'     => array(
				array(
					'key'     => 'start_datetime',
					'value'   => $now->format( 'Y-m-d H:i:s' ),
					'compare' => '>=',
					'type'    => 'DATETIME',
				),
			),
		)
	);

	$home_events = array();
	foreach ( $posts as $event_post ) {
		$start = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $event_post->ID, 'start_datetime' ) );
		if ( ! $start ) {
			continue;
		}
		$city          = trim( (string) rgvdsa_events_get_field( $event_post->ID, 'city' ) );
		$venue         = trim( (string) rgvdsa_events_get_field( $event_post->ID, 'venue' ) );
		$home_events[] = array(
			'day'   => $start->format( 'd' ),
			'month' => strtoupper( $start->format( 'M' ) ),
			'title' => html_entity_decode( get_the_title( $event_post ), ENT_QUOTES, 'UTF-8' ),
			'when'  => $start->format( 'l, F j' ) . ' · ' . $start->format( 'g:i A' ),
			'where' => $city ? $city : $venue,
		);
	}
	$context['home_events'] = $home_events;

	return $context;
}

/**
 * ICS feed: /?feed=rgvdsa-events (pretty: /feed/rgvdsa-events/).
 * NOTE: registering the feed requires a rewrite flush (the seed step does it).
 */
add_action( 'init', 'rgvdsa_events_register_feed' );
function rgvdsa_events_register_feed() {
	add_feed( 'rgvdsa-events', 'rgvdsa_events_render_ics' );
}

add_filter( 'feed_content_type', 'rgvdsa_events_feed_content_type', 10, 2 );
function rgvdsa_events_feed_content_type( $content_type, $type ) {
	return 'rgvdsa-events' === $type ? 'text/calendar' : $content_type;
}

/**
 * Escape a text value per RFC 5545 (TEXT).
 *
 * @param string $text Raw text.
 * @return string
 */
function rgvdsa_events_ics_escape( $text ) {
	$text = str_replace( array( '\\', ';', ',' ), array( '\\\\', '\\;', '\\,' ), (string) $text );

	return preg_replace( "/\r\n|\r|\n/", '\\n', $text );
}

/**
 * Fold a content line at 75 octets (RFC 5545 § 3.1), UTF-8 safe.
 *
 * @param string $line Unfolded line.
 * @return string
 */
function rgvdsa_events_ics_fold( $line ) {
	$out = '';
	while ( strlen( $line ) > 75 ) {
		$chunk = mb_strcut( $line, 0, 75, 'UTF-8' );
		if ( '' === $chunk ) {
			break;
		}
		$out .= $chunk . "\r\n ";
		$line = substr( $line, strlen( $chunk ) );
	}

	return $out . $line;
}

/**
 * Render the VCALENDAR. Datetimes are chapter-local (America/Chicago)
 * converted to UTC so no VTIMEZONE block is needed.
 */
function rgvdsa_events_render_ics() {
	header( 'Content-Type: text/calendar; charset=utf-8' );
	header( 'Content-Disposition: inline; filename="rgvdsa-events.ics"' );

	$utc  = new DateTimeZone( 'UTC' );
	$host = wp_parse_url( home_url(), PHP_URL_HOST );

	$lines = array(
		'BEGIN:VCALENDAR',
		'VERSION:2.0',
		'PRODID:-//RGV DSA//Events//EN',
		'CALSCALE:GREGORIAN',
		'METHOD:PUBLISH',
		'X-WR-CALNAME:RGV DSA Events',
		'X-WR-TIMEZONE:America/Chicago',
	);

	foreach ( rgvdsa_events_query() as $event_post ) {
		$start = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $event_post->ID, 'start_datetime' ) );
		if ( ! $start ) {
			continue;
		}
		$end = rgvdsa_events_parse_datetime( rgvdsa_events_get_field( $event_post->ID, 'end_datetime' ) );
		if ( ! $end || $end <= $start ) {
			$end = $start->modify( '+1 hour' );
		}

		$chapter_event = rgvdsa_event_to_chapter_event( $event_post );
		$desc          = $chapter_event['desc'];
		if ( isset( $chapter_event['rsvpUrl'] ) ) {
			$desc .= ( $desc ? "\n" : '' ) . 'RSVP: ' . $chapter_event['rsvpUrl'];
		}

		$lines[] = 'BEGIN:VEVENT';
		$lines[] = 'UID:rgvdsa-event-' . $event_post->ID . '@' . $host;
		$lines[] = 'DTSTAMP:' . get_post_modified_time( 'Ymd\THis', true, $event_post ) . 'Z';
		$lines[] = 'DTSTART:' . $start->setTimezone( $utc )->format( 'Ymd\THis\Z' );
		$lines[] = 'DTEND:' . $end->setTimezone( $utc )->format( 'Ymd\THis\Z' );
		$lines[] = 'SUMMARY:' . rgvdsa_events_ics_escape( $chapter_event['title'] );
		if ( $chapter_event['location'] ) {
			$lines[] = 'LOCATION:' . rgvdsa_events_ics_escape( $chapter_event['location'] );
		}
		if ( $desc ) {
			$lines[] = 'DESCRIPTION:' . rgvdsa_events_ics_escape( $desc );
		}
		$lines[] = 'URL:' . esc_url_raw( get_permalink( $event_post ) );
		$lines[] = 'END:VEVENT';
	}

	$lines[] = 'END:VCALENDAR';

	echo implode( "\r\n", array_map( 'rgvdsa_events_ics_fold', $lines ) ) . "\r\n";
}

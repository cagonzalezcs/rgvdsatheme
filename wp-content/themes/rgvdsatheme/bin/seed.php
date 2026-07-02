<?php
/**
 * Idempotent demo-content seed for the RGV-DSA theme.
 *
 * Run:
 *   wp eval-file wp-content/themes/rgvdsatheme/bin/seed.php
 *
 * Safe to re-run — every insert is guarded by a slug/name lookup; menus are
 * rebuilt in place; update_field/update_option calls are naturally idempotent.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit( "Run via: wp eval-file bin/seed.php\n" );
}

function rgvdsa_seed_log( $msg ) {
	echo $msg . "\n";
}

if ( ! function_exists( 'update_field' ) ) {
	rgvdsa_seed_log( 'FATAL: ACF is not active (update_field missing). Aborting.' );
	return;
}

/* -------------------------------------------------------------------------
 * 1. Terms — 6 canonical categories in event_category AND category.
 * ---------------------------------------------------------------------- */

$rgvdsa_seed_palette = rgvdsa_category_registry();

$rgvdsa_seed_color_field_keys = array(
	'event_category' => 'field_rgvdsa_events_term_color',
	'category'       => 'field_rgvdsa_blog_category_color',
);

foreach ( array( 'event_category', 'category' ) as $tax ) {
	foreach ( $rgvdsa_seed_palette as $slug => $def ) {
		$term = get_term_by( 'slug', $slug, $tax );
		if ( ! $term ) {
			$result = wp_insert_term( $def['label'], $tax, array( 'slug' => $slug ) );
			if ( is_wp_error( $result ) ) {
				rgvdsa_seed_log( "ERROR term {$tax}/{$slug}: " . $result->get_error_message() );
				continue;
			}
			$term_id = (int) $result['term_id'];
			rgvdsa_seed_log( "term created: {$tax}/{$slug} (#{$term_id})" );
		} else {
			$term_id = (int) $term->term_id;
			if ( $term->name !== $def['label'] ) {
				wp_update_term( $term_id, $tax, array( 'name' => $def['label'] ) );
			}
			rgvdsa_seed_log( "term exists: {$tax}/{$slug} (#{$term_id})" );
		}
		update_field( $rgvdsa_seed_color_field_keys[ $tax ], $def['color'], $tax . '_' . $term_id );
	}
}

/* -------------------------------------------------------------------------
 * 2. Events — the 14 SAMPLE_EVENTS from src/lib/events.ts.
 * ---------------------------------------------------------------------- */

/**
 * "2:00–4:00 PM" / "9:00 AM–12:00 PM" → [ 'Y-m-d H:i:s' start, end ].
 */
function rgvdsa_seed_parse_times( $date, $time ) {
	$parts     = preg_split( '/\x{2013}|\x{2014}/u', $time ); // en/em dash
	$start_raw = trim( $parts[0] );
	$end_raw   = isset( $parts[1] ) ? trim( $parts[1] ) : '';

	$end_mer   = preg_match( '/(AM|PM)/i', $end_raw, $m ) ? strtoupper( $m[1] ) : '';
	$start_mer = preg_match( '/(AM|PM)/i', $start_raw, $m ) ? strtoupper( $m[1] ) : $end_mer;

	$to24 = function ( $raw, $mer ) {
		if ( ! preg_match( '/(\d{1,2}):(\d{2})/', $raw, $m ) ) {
			return null;
		}
		$h = (int) $m[1];
		$i = (int) $m[2];
		if ( 'PM' === $mer && 12 !== $h ) {
			$h += 12;
		} elseif ( 'AM' === $mer && 12 === $h ) {
			$h = 0;
		}
		return sprintf( '%02d:%02d:00', $h, $i );
	};

	$start = $to24( $start_raw, $start_mer );
	$end   = '' !== $end_raw ? $to24( $end_raw, $end_mer ) : null;

	return array(
		$start ? "{$date} {$start}" : '',
		$end ? "{$date} {$end}" : '',
	);
}

/** "City — Venue" → [venue, city]; no em dash → whole string is the venue. */
function rgvdsa_seed_split_location( $location ) {
	if ( false !== strpos( $location, ' — ' ) ) {
		list( $city, $venue ) = explode( ' — ', $location, 2 );
		return array( trim( $venue ), trim( $city ) );
	}
	return array( trim( $location ), '' );
}

$rgvdsa_seed_events = array(
	array( 'date' => '2026-07-02', 'time' => '7:00–8:30 PM', 'cat' => 'poled', 'title' => 'Night School: What Is Democratic Socialism?', 'location' => 'McAllen Public Library, Community Room B', 'desc' => 'First session of our summer night school. No reading required — just bring your questions. We cover what democratic socialism is (and isn’t), and what it looks like here in the Valley.' ),
	array( 'date' => '2026-07-07', 'time' => '6:30–8:00 PM', 'cat' => 'mutual', 'title' => 'Community Fridge Restock & Cleanup', 'location' => 'Community fridge at 10th & Pecan, Edinburg', 'desc' => 'Help us restock, clean, and inventory the community fridge. Bring shelf-stable goods if you can — but hands are what we need most.' ),
	array( 'date' => '2026-07-09', 'time' => '7:00–8:00 PM', 'cat' => 'labor', 'title' => 'Know Your Rights at Work', 'location' => 'Online (Zoom)', 'desc' => 'A workshop on your rights on the job in Texas: concerted activity, retaliation, and what to document. Led by members of the Labor committee with guest organizers.' ),
	array( 'date' => '2026-07-11', 'time' => '2:00–4:00 PM', 'cat' => 'chapter', 'title' => 'July General Meeting', 'location' => 'Brownsville — Market Square Hall (+ Zoom)', 'desc' => 'Our monthly all-member meeting. Committee report-backs, votes on new business, and planning for the fall. Open to visitors — come see how the chapter works.' ),
	array( 'date' => '2026-07-15', 'time' => '7:00–8:00 PM', 'cat' => 'chapter', 'title' => 'RGV-DSA 101 (New Member Orientation)', 'location' => 'Online (Zoom)', 'desc' => 'New or curious? This one’s for you. A friendly intro to the chapter: who we are, what we’re working on, and how to plug in at whatever capacity you have.' ),
	array( 'date' => '2026-07-18', 'time' => '9:00 AM–12:00 PM', 'cat' => 'mutual', 'title' => 'Brake Light Clinic', 'location' => 'Parking lot, 500 W Ferguson Ave, Pharr', 'desc' => 'Free brake light replacement for anyone who pulls up — a broken light shouldn’t mean a traffic stop. Volunteers get a quick training at 8:30 AM. Tools and bulbs provided.' ),
	array( 'date' => '2026-07-21', 'time' => '7:00–9:00 PM', 'cat' => 'electoral', 'title' => 'Candidate Endorsement Forum', 'location' => 'Harlingen — Casa de Amistad', 'desc' => 'Hear from candidates seeking the chapter’s endorsement ahead of the fall elections. Members vote on endorsements at the August general meeting.' ),
	array( 'date' => '2026-07-23', 'time' => '7:30–9:00 PM', 'cat' => 'poled', 'title' => 'Reading Circle: A People’s Guide to Capitalism', 'location' => 'Weslaco — Cafecito on Texas Blvd', 'desc' => 'Chapters 1–2. New readers welcome; we always start with a recap. Copies available to borrow from the chapter library.' ),
	array( 'date' => '2026-07-25', 'time' => '6:00–9:00 PM', 'cat' => 'social', 'title' => 'Paleta Social', 'location' => 'Archer Park, McAllen', 'desc' => 'No agenda, no sign-in sheet — just paletas, lawn games, and comrades. Families welcome. First round of paletas is on the chapter.' ),
	array( 'date' => '2026-07-28', 'time' => '7:00–8:30 PM', 'cat' => 'labor', 'title' => 'Picket Support Training', 'location' => 'Online (Zoom)', 'desc' => 'How to show up well for striking workers: picket line etiquette, marshaling basics, and what support locals actually ask for.' ),
	array( 'date' => '2026-08-01', 'time' => '2:00–4:00 PM', 'cat' => 'chapter', 'title' => 'August General Meeting', 'location' => 'McAllen — Lark Community Center (+ Zoom)', 'desc' => 'Monthly all-member meeting. Endorsement votes from the July forum are on the agenda — members in good standing can vote.' ),
	array( 'date' => '2026-08-04', 'time' => '6:30–8:00 PM', 'cat' => 'mutual', 'title' => 'School Supply Distro Prep', 'location' => 'Alamo — member’s garage (address in WhatsApp)', 'desc' => 'Sorting and packing backpacks for the back-to-school distribution on the 15th. Snacks provided.' ),
	array( 'date' => '2026-08-08', 'time' => '10:00 AM–1:00 PM', 'cat' => 'electoral', 'title' => 'Voter Registration Drive', 'location' => 'Flea market, N 23rd St, Edinburg', 'desc' => 'Tabling and registering voters ahead of the October deadline. Volunteer deputy registrars will be on site — come learn how it’s done.' ),
	array( 'date' => '2026-08-13', 'time' => '7:00–8:30 PM', 'cat' => 'poled', 'title' => 'Night School: Socialism & the Border', 'location' => 'McAllen Public Library, Community Room B', 'desc' => 'Session two of summer night school: a Valley-centered look at labor, migration, and the border economy.' ),
);

$rgvdsa_seed_event_ids = array(); // title → post ID

foreach ( $rgvdsa_seed_events as $ev ) {
	$slug     = sanitize_title( $ev['title'] );
	$existing = get_posts( array(
		'post_type'      => 'event',
		'name'           => $slug,
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( $existing ) {
		$post_id = (int) $existing[0];
		rgvdsa_seed_log( "event exists: {$slug} (#{$post_id})" );
	} else {
		$post_id = wp_insert_post( array(
			'post_type'    => 'event',
			'post_status'  => 'publish',
			'post_title'   => $ev['title'],
			'post_name'    => $slug,
			'post_content' => $ev['desc'],
		), true );
		if ( is_wp_error( $post_id ) ) {
			rgvdsa_seed_log( "ERROR event {$slug}: " . $post_id->get_error_message() );
			continue;
		}
		rgvdsa_seed_log( "event created: {$slug} (#{$post_id})" );
	}

	$rgvdsa_seed_event_ids[ $ev['title'] ] = $post_id;

	wp_set_object_terms( $post_id, $ev['cat'], 'event_category' );

	list( $start, $end )  = rgvdsa_seed_parse_times( $ev['date'], $ev['time'] );
	list( $venue, $city ) = rgvdsa_seed_split_location( $ev['location'] );

	update_field( 'field_rgvdsa_events_start_datetime', $start, $post_id );
	update_field( 'field_rgvdsa_events_end_datetime', $end, $post_id );
	update_field( 'field_rgvdsa_events_venue', $venue, $post_id );
	update_field( 'field_rgvdsa_events_city', $city, $post_id );
}

/* -------------------------------------------------------------------------
 * 3. Blog — posts page, the 9 SAMPLE_POSTS, p1 post_blocks.
 * ---------------------------------------------------------------------- */

$rgvdsa_seed_blog_page = get_page_by_path( 'blog' );
if ( ! $rgvdsa_seed_blog_page ) {
	$blog_page_id = wp_insert_post( array(
		'post_type'   => 'page',
		'post_status' => 'publish',
		'post_title'  => 'Blog',
		'post_name'   => 'blog',
	), true );
	if ( is_wp_error( $blog_page_id ) ) {
		rgvdsa_seed_log( 'ERROR blog page: ' . $blog_page_id->get_error_message() );
		$blog_page_id = 0;
	} else {
		rgvdsa_seed_log( "page created: blog (#{$blog_page_id})" );
	}
} else {
	$blog_page_id = (int) $rgvdsa_seed_blog_page->ID;
	rgvdsa_seed_log( "page exists: blog (#{$blog_page_id})" );
}

if ( $blog_page_id ) {
	update_option( 'page_for_posts', $blog_page_id );
}
if ( 'page' !== get_option( 'show_on_front' ) ) {
	update_option( 'show_on_front', 'page' );
	rgvdsa_seed_log( 'option fixed: show_on_front=page' );
}
rgvdsa_seed_log(
	'reading options: show_on_front=' . get_option( 'show_on_front' )
	. ' page_on_front=' . get_option( 'page_on_front' )
	. ' page_for_posts=' . get_option( 'page_for_posts' )
);

$rgvdsa_seed_posts = array(
	array( 'slug' => 'lorem-ipsum-dolor', 'title' => 'Lorem ipsum dolor sit amet, consectetur adipiscing elit sed do eiusmod', 'cat' => 'mutual', 'date' => '2026-06-14 10:00:00', 'excerpt' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'dek' => 'Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.', 'byline_mode' => 'named', 'committee' => 'Mutual Aid Committee', 'sticky' => true ),
	array( 'slug' => 'sed-ut-perspiciatis', 'title' => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem', 'cat' => 'poled', 'date' => '2026-06-28 10:00:00', 'excerpt' => 'Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur.', 'byline_mode' => 'named' ),
	array( 'slug' => 'nemo-enim-ipsam', 'title' => 'Nemo enim ipsam voluptatem quia voluptas sit aspernatur', 'cat' => 'labor', 'date' => '2026-06-21 10:00:00', 'excerpt' => 'Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.', 'byline_mode' => 'committee', 'committee' => 'Labor Committee' ),
	array( 'slug' => 'ut-enim-ad-minima', 'title' => 'Ut enim ad minima veniam quis nostrum', 'cat' => 'chapter', 'date' => '2026-06-08 10:00:00', 'excerpt' => 'Totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae.', 'byline_mode' => 'named' ),
	array( 'slug' => 'quis-autem-vel-eum', 'title' => 'Quis autem vel eum iure reprehenderit', 'cat' => 'electoral', 'date' => '2026-05-30 10:00:00', 'excerpt' => 'At vero eos et accusamus et iusto odio dignissimos ducimus qui blanditiis praesentium.', 'byline_mode' => 'committee', 'committee' => 'Electoral Committee' ),
	array( 'slug' => 'neque-porro-quisquam', 'title' => 'Neque porro quisquam est qui dolorem', 'cat' => 'social', 'date' => '2026-05-22 10:00:00', 'excerpt' => 'Et harum quidem rerum facilis est et expedita distinctio nam libero tempore.', 'byline_mode' => 'named' ),
	array( 'slug' => 'temporibus-autem', 'title' => 'Temporibus autem quibusdam et aut officiis debitis', 'cat' => 'labor', 'date' => '2026-05-16 10:00:00', 'excerpt' => 'Itaque earum rerum hic tenetur a sapiente delectus, ut aut reiciendis voluptatibus.', 'byline_mode' => 'named' ),
	array( 'slug' => 'nam-libero-tempore', 'title' => 'Nam libero tempore cum soluta nobis', 'cat' => 'poled', 'date' => '2026-05-09 10:00:00', 'excerpt' => 'Omnis voluptas assumenda est, omnis dolor repellendus maiores alias consequatur.', 'byline_mode' => 'committee', 'committee' => 'Political Education Committee' ),
	array( 'slug' => 'at-vero-eos', 'title' => 'At vero eos et accusamus et iusto odio', 'cat' => 'mutual', 'date' => '2026-05-02 10:00:00', 'excerpt' => 'Quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt.', 'byline_mode' => 'named' ),
);

$rgvdsa_seed_p1_id = 0;

foreach ( $rgvdsa_seed_posts as $sp ) {
	$existing = get_posts( array(
		'post_type'      => 'post',
		'name'           => $sp['slug'],
		'post_status'    => 'any',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );

	if ( $existing ) {
		$post_id = (int) $existing[0];
		rgvdsa_seed_log( "post exists: {$sp['slug']} (#{$post_id})" );
	} else {
		$post_id = wp_insert_post( array(
			'post_type'    => 'post',
			'post_status'  => 'publish',
			'post_title'   => $sp['title'],
			'post_name'    => $sp['slug'],
			'post_excerpt' => $sp['excerpt'],
			'post_content' => '<p>' . $sp['excerpt'] . '</p>',
			'post_date'    => $sp['date'],
			'post_author'  => 1,
		), true );
		if ( is_wp_error( $post_id ) ) {
			rgvdsa_seed_log( "ERROR post {$sp['slug']}: " . $post_id->get_error_message() );
			continue;
		}
		rgvdsa_seed_log( "post created: {$sp['slug']} (#{$post_id})" );
	}

	wp_set_object_terms( $post_id, $sp['cat'], 'category' );

	if ( isset( $sp['dek'] ) ) {
		update_field( 'field_rgvdsa_blog_dek', $sp['dek'], $post_id );
	}
	update_field( 'field_rgvdsa_blog_byline_mode', $sp['byline_mode'], $post_id );
	if ( isset( $sp['committee'] ) ) {
		update_field( 'field_rgvdsa_blog_committee', $sp['committee'], $post_id );
	}

	if ( ! empty( $sp['sticky'] ) ) {
		if ( ! is_sticky( $post_id ) ) {
			stick_post( $post_id );
			rgvdsa_seed_log( "post stuck: {$sp['slug']}" );
		}
		$rgvdsa_seed_p1_id = $post_id;
	}
}

/* --- Placeholder PDF (interior documents + p1 document block need a file). */

function rgvdsa_seed_placeholder_pdf() {
	$existing = get_posts( array(
		'post_type'      => 'attachment',
		'name'           => 'rgvdsa-placeholder-pdf',
		'post_status'    => 'inherit',
		'posts_per_page' => 1,
		'fields'         => 'ids',
	) );
	if ( $existing ) {
		return (int) $existing[0];
	}

	// Minimal valid one-page PDF, built with correct xref offsets.
	$pdf  = "%PDF-1.4\n";
	$objs = array(
		"1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n",
		"2 0 obj<</Type/Pages/Kids[3 0 R]/Count 1>>endobj\n",
		"3 0 obj<</Type/Page/Parent 2 0 R/MediaBox[0 0 612 792]>>endobj\n",
	);
	$offsets = array();
	foreach ( $objs as $obj ) {
		$offsets[] = strlen( $pdf );
		$pdf      .= $obj;
	}
	$xref = strlen( $pdf );
	$pdf .= "xref\n0 4\n0000000000 65535 f \n";
	foreach ( $offsets as $off ) {
		$pdf .= sprintf( "%010d 00000 n \n", $off );
	}
	$pdf .= "trailer<</Size 4/Root 1 0 R>>\nstartxref\n{$xref}\n%%EOF\n";

	$upload = wp_upload_bits( 'rgvdsa-placeholder.pdf', null, $pdf );
	if ( ! empty( $upload['error'] ) ) {
		rgvdsa_seed_log( 'ERROR placeholder PDF upload: ' . $upload['error'] );
		return 0;
	}

	$att_id = wp_insert_attachment( array(
		'post_title'     => 'RGV DSA Placeholder PDF',
		'post_name'      => 'rgvdsa-placeholder-pdf',
		'post_mime_type' => 'application/pdf',
		'post_status'    => 'inherit',
	), $upload['file'] );

	if ( is_wp_error( $att_id ) || ! $att_id ) {
		rgvdsa_seed_log( 'ERROR placeholder PDF attachment insert failed' );
		return 0;
	}

	require_once ABSPATH . 'wp-admin/includes/image.php';
	$meta = wp_generate_attachment_metadata( $att_id, $upload['file'] );
	if ( $meta ) {
		wp_update_attachment_metadata( $att_id, $meta );
	}

	rgvdsa_seed_log( "attachment created: rgvdsa-placeholder-pdf (#{$att_id})" );

	return (int) $att_id;
}

$rgvdsa_seed_pdf_id = rgvdsa_seed_placeholder_pdf();

/* --- p1: one post_blocks row of every layout (SAMPLE_SINGLE fixture). */

if ( $rgvdsa_seed_p1_id ) {
	$brake_light_id = isset( $rgvdsa_seed_event_ids['Brake Light Clinic'] ) ? $rgvdsa_seed_event_ids['Brake Light Clinic'] : 0;

	$blocks = array(
		array(
			'acf_fc_layout' => 'prose',
			'content'       => '<p>Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat.</p><p>Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.</p>',
		),
		array(
			'acf_fc_layout' => 'image',
			'image'         => '',
			'alt_text'      => 'Photo',
			'caption'       => 'Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium.',
			'credit'        => '',
			'breakout'      => 0,
		),
		array(
			'acf_fc_layout' => 'prose',
			'content'       => '<p>Sed ut perspiciatis unde omnis iste natus error sit voluptatem accusantium doloremque laudantium, totam rem aperiam, eaque ipsa quae ab illo inventore veritatis et quasi architecto beatae vitae dicta sunt explicabo.</p>',
		),
		array(
			'acf_fc_layout' => 'pull_quote',
			'quote'         => '“Lorem ipsum dolor sit amet, consectetur adipiscing elit, sed do eiusmod tempor incididunt.”',
			'attribution'   => 'Attribution line',
		),
		array(
			'acf_fc_layout' => 'gallery',
			'layout'        => 'essay',
			'images'        => array(
				array( 'image' => '', 'alt_text' => 'Wide photo', 'caption' => 'Lorem ipsum dolor sit amet.' ),
				array( 'image' => '', 'alt_text' => 'Photo', 'caption' => 'Consectetur adipiscing elit.' ),
				array( 'image' => '', 'alt_text' => 'Photo', 'caption' => 'Sed do eiusmod tempor.' ),
			),
		),
		array(
			'acf_fc_layout' => 'person_quote',
			'photo'         => '',
			'alt_text'      => 'Portrait',
			'quote'         => '“Lorem ipsum dolor sit amet, consectetur adipiscing elit.”',
			'translation'   => '“Translation of the quote appears here.”',
			'name'          => 'Person Name',
			'role'          => 'Role or affiliation',
			'lang'          => 'es',
		),
		array(
			'acf_fc_layout'  => 'video',
			'url'            => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
			'poster'         => '',
			'caption'        => 'Watch: lorem ipsum dolor sit amet consectetur. Captioned in English and Spanish.',
			'transcript_url' => '#',
		),
		array(
			'acf_fc_layout' => 'prose',
			'content'       => '<h2>Lorem ipsum dolor sit amet</h2><p>Nemo enim ipsam voluptatem quia voluptas sit aspernatur aut odit aut fugit, sed quia consequuntur magni dolores eos qui ratione voluptatem sequi nesciunt, neque porro quisquam est qui dolorem ipsum quia dolor sit amet.</p>',
		),
		array(
			'acf_fc_layout' => 'audio',
			'file'          => '',
			'title'         => 'Listen: lorem ipsum audio title',
			'duration'      => '3:12',
			'transcript'    => '',
		),
		array(
			'acf_fc_layout' => 'document',
			'file'          => $rgvdsa_seed_pdf_id ? $rgvdsa_seed_pdf_id : '',
			'title'         => 'Lorem ipsum document title',
			'description'   => 'Bilingual · 2 pages · 340 KB',
		),
		array(
			'acf_fc_layout' => 'event_embed',
			'event'         => $brake_light_id ? array( $brake_light_id ) : array(),
		),
		array(
			'acf_fc_layout' => 'action_callout',
			'heading'       => 'Lorem ipsum dolor sit amet',
			'body'          => 'Consectetur adipiscing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris.',
			'buttons'       => array(
				array( 'label' => 'Primary action', 'url' => '/get-involved/', 'style' => 'primary' ),
				array( 'label' => 'Secondary action', 'url' => '#', 'style' => 'outline' ),
			),
		),
	);

	update_field( 'field_rgvdsa_blog_post_blocks', $blocks, $rgvdsa_seed_p1_id );
	update_field( 'field_rgvdsa_blog_featured_caption', 'Lorem ipsum dolor sit amet, consectetur adipiscing elit.', $rgvdsa_seed_p1_id );
	update_field( 'field_rgvdsa_blog_featured_credit', 'Photo: RGV DSA', $rgvdsa_seed_p1_id );
	update_field( 'field_rgvdsa_blog_read_minutes', 6, $rgvdsa_seed_p1_id );
	wp_set_post_terms( $rgvdsa_seed_p1_id, array( 'tag one', 'tag two' ), 'post_tag' );
	rgvdsa_seed_log( "p1 post_blocks seeded (#{$rgvdsa_seed_p1_id}, " . count( $blocks ) . ' rows)' );
}

/* -------------------------------------------------------------------------
 * 4. Menus — rebuild each named menu to exactly the given items + location.
 * ---------------------------------------------------------------------- */

function rgvdsa_seed_menu( $name, $location, $items ) {
	$menu    = wp_get_nav_menu_object( $name );
	$menu_id = $menu ? (int) $menu->term_id : (int) wp_create_nav_menu( $name );
	if ( is_wp_error( $menu_id ) || ! $menu_id ) {
		rgvdsa_seed_log( "ERROR menu {$name}: could not create" );
		return;
	}

	// Wipe existing items so the menu is exactly the given set (idempotent).
	$existing = wp_get_nav_menu_items( $menu_id, array( 'post_status' => 'any' ) );
	if ( is_array( $existing ) ) {
		foreach ( $existing as $item ) {
			wp_delete_post( $item->ID, true );
		}
	}

	$position = 1;
	foreach ( $items as $label => $url ) {
		wp_update_nav_menu_item( $menu_id, 0, array(
			'menu-item-title'    => $label,
			'menu-item-url'      => $url,
			'menu-item-type'     => 'custom',
			'menu-item-status'   => 'publish',
			'menu-item-position' => $position++,
		) );
	}

	$locations              = get_nav_menu_locations();
	$locations[ $location ] = $menu_id;
	set_theme_mod( 'nav_menu_locations', $locations );

	rgvdsa_seed_log( "menu seeded: {$name} → {$location} (" . count( $items ) . ' items)' );
}

rgvdsa_seed_menu( 'Primary', 'primary', array(
	'Calendar'     => '/calendar/',
	'Blog'         => '/blog/',
	'Get Involved' => '/get-involved/',
) );

rgvdsa_seed_menu( 'Footer — About', 'footer_about', array(
	'About the Chapter'        => '/about/#chapter',
	'Mission & History'        => '/about/#mission',
	'Counties We Serve'        => '/about/#counties',
	'Bylaws & Code of Conduct' => '/about/#bylaws',
	'FAQ'                      => '/about/#faq',
) );

rgvdsa_seed_menu( 'Footer — Get Involved', 'footer_involved', array(
	'Join DSA'               => 'https://act.dsausa.org/donate/membership',
	'Event Calendar'         => '/calendar/',
	'Committees'             => '/get-involved/#committees',
	'Communication Channels' => '/get-involved/#channels',
) );

rgvdsa_seed_menu( 'Footer — Resources', 'footer_resources', array(
	'Documents & Minutes' => '/bylaws-code-of-conduct/#documents',
	'Resolutions'         => '/bylaws-code-of-conduct/',
	'Education Library'   => '/bylaws-code-of-conduct/',
	'Grievance Contact'   => '/bylaws-code-of-conduct/#grievance',
) );

rgvdsa_seed_menu( 'Footer — Contact', 'footer_contact', array(
	'Email'     => 'mailto:hello@example.org',
	'Instagram' => 'https://www.instagram.com/dsa_rgv/',
) );

/* -------------------------------------------------------------------------
 * 5. Chapter Settings options.
 * ---------------------------------------------------------------------- */

update_field( 'field_rgvdsa_options_join_url', 'https://act.dsausa.org/donate/membership', 'option' );
update_field( 'field_rgvdsa_options_contact_email', 'hello@example.org', 'option' );
update_field( 'field_rgvdsa_options_instagram_url', 'https://www.instagram.com/dsa_rgv/', 'option' );
update_field( 'field_rgvdsa_options_event_count', 5, 'option' );
update_field( 'field_rgvdsa_options_show_counties_strip', 1, 'option' );
update_field( 'field_rgvdsa_options_committees', array(
	array( 'name' => 'Political Education', 'desc' => 'Reading groups, night school, and workshops that build our shared analysis.' ),
	array( 'name' => 'Mutual Aid', 'desc' => "Meeting our neighbors' immediate needs while organizing for lasting change." ),
	array( 'name' => 'Labor', 'desc' => 'Supporting workers organizing on the job across the Valley.' ),
	array( 'name' => 'Communications', 'desc' => "Social media, design, and this website — telling the chapter's story." ),
	array( 'name' => 'Electoral', 'desc' => 'Backing candidates and ballot measures that fight for working people.' ),
	array( 'name' => 'Membership & Onboarding', 'desc' => 'Welcoming new members and making sure no one falls through the cracks.' ),
), 'option' );
rgvdsa_seed_log( 'chapter settings options seeded' );

/* -------------------------------------------------------------------------
 * 6. Interior — bylaws page governing-documents repeater.
 * ---------------------------------------------------------------------- */

$rgvdsa_seed_bylaws = get_page_by_path( 'bylaws-code-of-conduct' );
if ( $rgvdsa_seed_bylaws && $rgvdsa_seed_pdf_id ) {
	update_field( 'field_rgvdsa_interior_documents', array(
		array( 'title' => 'Chapter Bylaws', 'description' => 'Last amended March 2026', 'file' => $rgvdsa_seed_pdf_id ),
		array( 'title' => 'Code of Conduct', 'description' => 'Adopted January 2026', 'file' => $rgvdsa_seed_pdf_id ),
		array( 'title' => 'Grievance Policy', 'description' => 'Adopted January 2026', 'file' => $rgvdsa_seed_pdf_id ),
	), $rgvdsa_seed_bylaws->ID );
	rgvdsa_seed_log( "bylaws documents seeded (#{$rgvdsa_seed_bylaws->ID}, 3 rows)" );
} else {
	rgvdsa_seed_log( 'WARN: bylaws-code-of-conduct page or placeholder PDF missing — documents not seeded' );
}

/* -------------------------------------------------------------------------
 * 7. Rewrites (event CPT + rgvdsa-events feed need a flush).
 * ---------------------------------------------------------------------- */

flush_rewrite_rules();
rgvdsa_seed_log( 'rewrite rules flushed' );
rgvdsa_seed_log( 'SEED COMPLETE' );

<?php
/**
 * Blog domain: post fields + archive/single wiring.
 *
 * Owns: category color term meta, the post settings field group (dek,
 * byline_mode, …), and serialization of post_content blocks to the
 * BlogPost / SinglePostData island contracts (block registration and the
 * per-block field groups live in inc/blocks.php).
 *
 * Public contract (other domains call these):
 * - rgvdsa_post_categories(): array — [{ id, label, color }] for the six
 *   canonical category slugs (term name/color when the term exists).
 * - rgvdsa_post_to_blog_post( $post ): array — BlogPost shape.
 * - rgvdsa_post_to_single( $post ): array — SinglePostData shape.
 */

/**
 * ACF value with a guard so templates survive ACF being disabled.
 */
function rgvdsa_blog_field( $name, $post_id ) {
	return function_exists( 'get_field' ) ? get_field( $name, $post_id ) : null;
}

/**
 * Committees for byline choices — delegates to the single chapter-options
 * source (inc/options.php owns the ACF repeater + design fixture fallback).
 */
function rgvdsa_blog_committees() {
	return function_exists( 'rgvdsa_chapter_committees' ) ? rgvdsa_chapter_committees() : array();
}

/**
 * [{ id: slug, label, color }] for the six canonical category slugs.
 * Term name/ACF color win when the term exists; the registry
 * (categories.json via inc/categories.php) is the fallback.
 */
function rgvdsa_post_categories() {
	return rgvdsa_categories( 'category' );
}

/* -------------------------------------------------------------------------
 * ACF field groups.
 * ---------------------------------------------------------------------- */

add_action( 'acf/init', 'rgvdsa_blog_register_fields' );

function rgvdsa_blog_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Color per category term — shared accent across archive chips, tags, blocks.
	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_blog_category_color',
			'title'    => 'Category color',
			'fields'   => array(
				array(
					'key'          => 'field_rgvdsa_blog_category_color',
					'label'        => 'Color',
					'name'         => 'color',
					'type'         => 'color_picker',
					'instructions' => 'Accent used for this category everywhere on the blog. Leave empty to use the chapter palette.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'taxonomy',
						'operator' => '==',
						'value'    => 'category',
					),
				),
			),
		)
	);

	// Per-post settings.
	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_blog_post_settings',
			'title'    => 'Post settings',
			'fields'   => array(
				array(
					'key'          => 'field_rgvdsa_blog_dek',
					'label'        => 'Dek',
					'name'         => 'dek',
					'type'         => 'text',
					'instructions' => 'Standfirst shown under the title on the post hero and featured cards.',
				),
				array(
					'key'           => 'field_rgvdsa_blog_byline_mode',
					'label'         => 'Byline mode',
					'name'          => 'byline_mode',
					'type'          => 'select',
					'choices'       => array(
						'named'     => 'Named author',
						'committee' => 'Committee',
					),
					'default_value' => 'named',
					'return_format' => 'value',
				),
				array(
					'key'           => 'field_rgvdsa_blog_committee',
					'label'         => 'Committee',
					'name'          => 'committee',
					'type'          => 'select',
					'instructions'  => 'Shown in the byline; the collective author in committee mode.',
					'choices'       => array(),
					'allow_null'    => 1,
					'return_format' => 'value',
				),
				array(
					'key'          => 'field_rgvdsa_blog_read_minutes',
					'label'        => 'Read minutes',
					'name'         => 'read_minutes',
					'type'         => 'number',
					'instructions' => 'Optional override. Computed from word count (200 wpm) when empty.',
					'min'          => 1,
					'step'         => 1,
				),
				array(
					'key'           => 'field_rgvdsa_blog_show_meta_rail',
					'label'         => 'Show meta rail',
					'name'          => 'show_meta_rail',
					'type'          => 'true_false',
					'default_value' => 0,
					'ui'            => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'post',
					),
				),
			),
			'position' => 'side',
		)
	);

}

// Committee choices come from the chapter options repeater.
add_filter( 'acf/load_field/key=field_rgvdsa_blog_committee', 'rgvdsa_blog_load_committee_choices' );

function rgvdsa_blog_load_committee_choices( $field ) {
	$names = array_filter( array_map( 'strval', wp_list_pluck( rgvdsa_blog_committees(), 'name' ) ) );

	$field['choices'] = array_combine( $names, $names );

	return $field;
}

/* -------------------------------------------------------------------------
 * Serializers → island contracts (src/lib/posts.ts).
 * ---------------------------------------------------------------------- */

/**
 * First canonical category slug on the post, fallback "chapter".
 */
function rgvdsa_blog_post_cat( $post ) {
	$canonical = array_keys( rgvdsa_category_registry() );

	$terms = get_the_category( $post->ID );
	if ( is_array( $terms ) ) {
		foreach ( $terms as $term ) {
			if ( in_array( $term->slug, $canonical, true ) ) {
				return $term->slug;
			}
		}
	}

	return 'chapter';
}

/**
 * Word count of post_content (where block bodies live) at 200 wpm.
 */
function rgvdsa_blog_compute_read_minutes( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return 1;
	}

	$words = str_word_count( wp_strip_all_tags( $post->post_content ) );

	return max( 1, (int) round( $words / 200 ) );
}

// Precompute at save.
add_action( 'save_post_post', 'rgvdsa_blog_store_read_minutes', 20 );

function rgvdsa_blog_store_read_minutes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	update_post_meta( $post_id, '_rgvdsa_read_minutes', rgvdsa_blog_compute_read_minutes( $post_id ) );
}

/**
 * ACF read_minutes override, else precomputed `_rgvdsa_read_minutes` meta
 * (primed by WP_Query's meta cache — no per-card body parse).
 * Computes + stores once when the meta is absent (pre-hook posts).
 */
function rgvdsa_blog_read_minutes( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return 1;
	}

	$override = (int) rgvdsa_blog_field( 'read_minutes', $post->ID );
	if ( $override > 0 ) {
		return $override;
	}

	$stored = (int) get_post_meta( $post->ID, '_rgvdsa_read_minutes', true );
	if ( $stored > 0 ) {
		return $stored;
	}

	$minutes = rgvdsa_blog_compute_read_minutes( $post->ID );
	update_post_meta( $post->ID, '_rgvdsa_read_minutes', $minutes );

	return $minutes;
}

/**
 * Sanitize prose HTML (the one field that reaches BlockProse's `v-html`).
 * Allowlist matches the styleguide prose set (design D4); extend it
 * deliberately, not reactively. Reused verbatim by the Gutenberg serializer.
 */
function rgvdsa_blog_kses_prose( $html ) {
	$allowed = array(
		'p'          => array(),
		'h2'         => array(),
		'h3'         => array(),
		'h4'         => array(),
		'ul'         => array(),
		'ol'         => array(),
		'li'         => array(),
		'a'          => array(
			'href'   => true,
			'title'  => true,
			'rel'    => true,
			'target' => true,
		),
		'strong'     => array(),
		'em'         => array(),
		'b'          => array(),
		'i'          => array(),
		'br'         => array(),
		'blockquote' => array(),
		'cite'       => array(),
		'code'       => array(),
		'sub'        => array(),
		'sup'        => array(),
		'mark'       => array(),
		's'          => array(),
	);

	return wp_kses( (string) $html, $allowed );
}

/**
 * Plain-text pass for captions, quotes, attributions, and callout fields:
 * strips all markup (the islands render these as escaped text, so this is
 * defense-in-depth + parity with the Gutenberg serializer).
 */
function rgvdsa_blog_kses_plain( $text ) {
	return trim( wp_strip_all_tags( (string) $text ) );
}

/* -------------------------------------------------------------------------
 * Gutenberg serialization — post_content blocks → PostBlock[].
 * ---------------------------------------------------------------------- */

/**
 * Raw ACF block data from a parsed block comment. ACF stores values in the
 * flat postmeta layout: `name => value`, repeaters as `name => count` plus
 * `name_{i}_{sub}` rows; image/file/post_object fields hold attachment or
 * post IDs regardless of return_format.
 */
function rgvdsa_blog_block_data( $block ) {
	$data = $block['attrs']['data'] ?? array();

	return is_array( $data ) ? $data : array();
}

/**
 * Repeater rows out of flat ACF block data (tolerates the nested-array
 * form the migration script and some ACF saves emit).
 */
function rgvdsa_blog_block_repeater( $data, $name, $subs ) {
	if ( is_array( $data[ $name ] ?? null ) ) {
		return $data[ $name ];
	}

	$rows = array();
	for ( $i = 0, $count = (int) ( $data[ $name ] ?? 0 ); $i < $count; $i++ ) {
		$row = array();
		foreach ( $subs as $sub ) {
			$row[ $sub ] = $data[ "{$name}_{$i}_{$sub}" ] ?? null;
		}
		$rows[] = $row;
	}

	return $rows;
}

/**
 * <figcaption> text out of a core block's saved markup.
 */
function rgvdsa_blog_block_figcaption( $html ) {
	return preg_match( '#<figcaption[^>]*>(.*?)</figcaption>#s', (string) $html, $m )
		? rgvdsa_blog_kses_plain( $m[1] )
		: '';
}

/**
 * PostImage from a core/image block (attachment ID in attrs, caption in the
 * saved figcaption, credit from the attachment `credit` field).
 */
function rgvdsa_blog_block_image_contract( $block ) {
	$attachment_id = (int) ( $block['attrs']['id'] ?? 0 );
	$html          = (string) ( $block['innerHTML'] ?? '' );

	$src = $attachment_id ? ( wp_get_attachment_image_url( $attachment_id, 'large' ) ?: null ) : null;
	if ( ! $src && preg_match( '#<img[^>]*\ssrc="([^"]+)"#', $html, $m ) ) {
		$src = $m[1];
	}

	$alt = $attachment_id ? (string) get_post_meta( $attachment_id, '_wp_attachment_image_alt', true ) : '';
	if ( '' === $alt && preg_match( '#<img[^>]*\salt="([^"]*)"#', $html, $m ) ) {
		$alt = $m[1];
	}

	$out = array(
		'src' => $src ?: null,
		'alt' => rgvdsa_blog_kses_plain( $alt ),
	);

	$caption = rgvdsa_blog_block_figcaption( $html );
	if ( '' !== $caption ) {
		$out['caption'] = $caption;
	}

	$credit = $attachment_id ? rgvdsa_blog_kses_plain( get_post_meta( $attachment_id, 'credit', true ) ) : '';
	if ( '' !== $credit ) {
		$out['credit'] = $credit;
	}

	return $out;
}

/**
 * post_content blocks → PostBlock[] (block-serialization spec). Consecutive
 * prose-class core blocks (paragraph/heading/list/quote — plus classic
 * freeform chunks) coalesce into one kses-sanitized `prose` entry; the rest
 * map 1:1 onto the contract union. Same sanitization rules as the legacy
 * ACF path.
 */
function rgvdsa_blog_blocks_from_content( $post ) {
	$post = get_post( $post );
	if ( ! $post ) {
		return array();
	}

	$prose_types = array( 'core/paragraph', 'core/heading', 'core/list', 'core/quote' );

	$blocks = array();
	$prose  = '';

	$flush_prose = function () use ( &$prose, &$blocks ) {
		$html = trim( rgvdsa_blog_kses_prose( $prose ) );
		if ( '' !== $html ) {
			$blocks[] = array(
				'type' => 'prose',
				'html' => $html,
			);
		}
		$prose = '';
	};

	foreach ( parse_blocks( $post->post_content ) as $block ) {
		$name = $block['blockName'];

		// Classic/freeform chunks count as prose (kses keeps them safe).
		if ( null === $name ) {
			if ( '' !== trim( (string) $block['innerHTML'] ) ) {
				$prose .= $block['innerHTML'];
			}
			continue;
		}

		if ( in_array( $name, $prose_types, true ) ) {
			$prose .= render_block( $block );
			continue;
		}

		$flush_prose();

		switch ( $name ) {
			case 'core/image':
				$image = rgvdsa_blog_block_image_contract( $block );
				if ( null === $image['src'] && '' === $image['alt'] && empty( $image['caption'] ) ) {
					break;
				}
				$out = array(
					'type'  => 'image',
					'image' => $image,
				);
				if ( in_array( $block['attrs']['align'] ?? '', array( 'wide', 'full' ), true ) ) {
					$out['breakout'] = true;
				}
				$blocks[] = $out;
				break;

			case 'core/pullquote':
				$html        = (string) $block['innerHTML'];
				$attribution = preg_match( '#<cite[^>]*>(.*?)</cite>#s', $html, $m )
					? rgvdsa_blog_kses_plain( $m[1] )
					: '';
				$quote       = rgvdsa_blog_kses_plain( preg_replace( '#<cite[^>]*>.*?</cite>#s', '', $html ) );
				if ( '' === $quote ) {
					break;
				}
				$out = array(
					'type'  => 'pull_quote',
					'quote' => $quote,
				);
				if ( '' !== $attribution ) {
					$out['attribution'] = $attribution;
				}
				$blocks[] = $out;
				break;

			case 'core/gallery':
				$images = array();
				foreach ( (array) $block['innerBlocks'] as $inner ) {
					if ( 'core/image' !== $inner['blockName'] ) {
						continue;
					}
					$image = rgvdsa_blog_block_image_contract( $inner );
					if ( null === $image['src'] && '' === $image['alt'] && empty( $image['caption'] ) ) {
						continue;
					}
					$images[] = $image;
				}
				if ( empty( $images ) ) {
					break;
				}
				$class    = (string) ( $block['attrs']['className'] ?? '' );
				$blocks[] = array(
					'type'   => 'gallery',
					'layout' => false !== strpos( $class, 'is-style-grid' ) ? 'grid' : 'essay',
					'images' => $images,
				);
				break;

			case 'rgvdsa/person-quote':
				$data  = rgvdsa_blog_block_data( $block );
				$quote = rgvdsa_blog_kses_plain( $data['quote'] ?? '' );
				$name_ = rgvdsa_blog_kses_plain( $data['name'] ?? '' );
				if ( '' === $quote || '' === $name_ ) {
					break;
				}
				$photo_id = (int) ( $data['photo'] ?? 0 );
				$out      = array(
					'type'  => 'person_quote',
					'photo' => $photo_id ? ( wp_get_attachment_image_url( $photo_id, 'medium' ) ?: null ) : null,
					'alt'   => rgvdsa_blog_kses_plain( $data['alt_text'] ?? '' ),
					'quote' => $quote,
					'name'  => $name_,
					'lang'  => 'es' === ( $data['lang'] ?? '' ) ? 'es' : 'en',
				);
				$translation = rgvdsa_blog_kses_plain( $data['translation'] ?? '' );
				if ( '' !== $translation ) {
					$out['translation'] = $translation;
				}
				$role = rgvdsa_blog_kses_plain( $data['role'] ?? '' );
				if ( '' !== $role ) {
					$out['role'] = $role;
				}
				$blocks[] = $out;
				break;

			case 'rgvdsa/video':
				$data = rgvdsa_blog_block_data( $block );
				$out  = array(
					'type' => 'video',
					'url'  => (string) ( $data['url'] ?? '' ),
				);
				$poster_id = (int) ( $data['poster'] ?? 0 );
				if ( $poster_id ) {
					$out['poster'] = wp_get_attachment_image_url( $poster_id, 'large' ) ?: null;
				}
				$caption = rgvdsa_blog_kses_plain( $data['caption'] ?? '' );
				if ( '' !== $caption ) {
					$out['caption'] = $caption;
				}
				if ( '' !== (string) ( $data['transcript_url'] ?? '' ) ) {
					$out['transcriptUrl'] = (string) $data['transcript_url'];
				}
				$blocks[] = $out;
				break;

			case 'rgvdsa/audio':
				$data  = rgvdsa_blog_block_data( $block );
				$title = rgvdsa_blog_kses_plain( $data['title'] ?? '' );
				if ( '' === $title ) {
					break;
				}
				$file_id       = (int) ( $data['file'] ?? 0 );
				$transcript_id = (int) ( $data['transcript'] ?? 0 );
				$out           = array(
					'type'          => 'audio',
					'file'          => $file_id ? ( wp_get_attachment_url( $file_id ) ?: null ) : null,
					'title'         => $title,
					'transcriptUrl' => $transcript_id ? (string) wp_get_attachment_url( $transcript_id ) : '',
				);
				if ( '' !== (string) ( $data['duration'] ?? '' ) ) {
					$out['duration'] = (string) $data['duration'];
				}
				$blocks[] = $out;
				break;

			case 'rgvdsa/document':
				$data    = rgvdsa_blog_block_data( $block );
				$title   = rgvdsa_blog_kses_plain( $data['title'] ?? '' );
				$file_id = (int) ( $data['file'] ?? 0 );
				$url     = $file_id ? (string) wp_get_attachment_url( $file_id ) : '';
				if ( '' === $title || '' === $url ) {
					break;
				}
				$out = array(
					'type'  => 'document',
					'url'   => $url,
					'title' => $title,
				);
				$description = rgvdsa_blog_kses_plain( $data['description'] ?? '' );
				if ( '' !== $description ) {
					$out['description'] = $description;
				}
				$blocks[] = $out;
				break;

			case 'rgvdsa/event-embed':
				$data     = rgvdsa_blog_block_data( $block );
				$event_id = (int) ( $data['event'] ?? 0 );
				if ( ! $event_id ) {
					break;
				}
				// Nullable by contract: unpublished/deleted events serialize
				// null and the island renders the fallback card.
				$event_post = get_post( $event_id );
				$event      = null;
				if ( $event_post && 'publish' === $event_post->post_status && function_exists( 'rgvdsa_event_to_chapter_event' ) ) {
					$event = rgvdsa_event_to_chapter_event( $event_post );
				}
				$blocks[] = array(
					'type'  => 'event_embed',
					'event' => $event,
				);
				break;

			case 'rgvdsa/action-callout':
				$data    = rgvdsa_blog_block_data( $block );
				$heading = rgvdsa_blog_kses_plain( $data['heading'] ?? '' );
				if ( '' === $heading ) {
					break;
				}
				$buttons = array();
				foreach ( rgvdsa_blog_block_repeater( $data, 'buttons', array( 'label', 'url', 'style' ) ) as $btn ) {
					$label = rgvdsa_blog_kses_plain( $btn['label'] ?? '' );
					if ( '' === $label ) {
						continue;
					}
					$buttons[] = array(
						'label' => $label,
						'url'   => esc_url_raw( (string) ( $btn['url'] ?? '' ) ),
						'style' => 'outline' === ( $btn['style'] ?? '' ) ? 'outline' : 'primary',
					);
				}
				$blocks[] = array(
					'type'    => 'action_callout',
					'heading' => $heading,
					'body'    => rgvdsa_blog_kses_plain( $data['body'] ?? '' ),
					'buttons' => $buttons,
				);
				break;
		}
	}

	$flush_prose();

	return $blocks;
}

/**
 * PostBlock[] for a post. Everything serializes from post_content — block
 * posts through the contract map, classic/imported content lands in the
 * freeform-prose branch (kses applied either way).
 */
function rgvdsa_blog_map_blocks( $post_id ) {
	return rgvdsa_blog_blocks_from_content( get_post( $post_id ) );
}

/**
 * BlogPost shape (archive cards, read-next pool, home teasers).
 */
function rgvdsa_post_to_blog_post( $post ) {
	$post = get_post( $post );

	$title       = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
	$dek         = (string) rgvdsa_blog_field( 'dek', $post->ID );
	$committee   = (string) rgvdsa_blog_field( 'committee', $post->ID );
	$byline_mode = 'committee' === rgvdsa_blog_field( 'byline_mode', $post->ID ) ? 'committee' : 'named';

	$image     = null;
	$thumb_src = get_the_post_thumbnail_url( $post, 'large' );
	if ( $thumb_src ) {
		$thumb_alt = (string) get_post_meta( get_post_thumbnail_id( $post ), '_wp_attachment_image_alt', true );
		$image     = array(
			'src' => $thumb_src,
			'alt' => '' !== $thumb_alt ? $thumb_alt : $title,
		);
	}

	$out = array(
		'id'          => (string) $post->ID,
		'title'       => $title,
		'slug'        => $post->post_name,
		'cat'         => rgvdsa_blog_post_cat( $post ),
		'date'        => get_the_date( 'M j, Y', $post ),
		'excerpt'     => wp_strip_all_tags( get_the_excerpt( $post ) ),
		'bylineMode'  => $byline_mode,
		'author'      => get_the_author_meta( 'display_name', (int) $post->post_author ),
		'featured'    => is_sticky( $post->ID ),
		'readMinutes' => rgvdsa_blog_read_minutes( $post ),
		'url'         => get_permalink( $post ),
		'image'       => $image,
	);

	if ( '' !== $dek ) {
		$out['dek'] = $dek;
	}
	if ( '' !== $committee ) {
		$out['committee'] = $committee;
	}

	return $out;
}

/**
 * SinglePostData shape (post hero + block stack + end matter).
 */
function rgvdsa_post_to_single( $post ) {
	$post      = get_post( $post );
	$author_id = (int) $post->post_author;
	$title     = html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' );
	$committee = (string) rgvdsa_blog_field( 'committee', $post->ID );

	$committee_bio = '';
	if ( '' !== $committee ) {
		foreach ( rgvdsa_blog_committees() as $entry ) {
			if ( ( $entry['name'] ?? '' ) === $committee ) {
				$committee_bio = (string) ( $entry['desc'] ?? '' );
				break;
			}
		}
	}

	$thumb_id  = get_post_thumbnail_id( $post );
	$thumb_alt = $thumb_id ? (string) get_post_meta( $thumb_id, '_wp_attachment_image_alt', true ) : '';

	$featured_image = array(
		'src' => get_the_post_thumbnail_url( $post, 'large' ) ?: null,
		'alt' => '' !== $thumb_alt ? $thumb_alt : $title,
	);
	// Caption/credit travel with the attachment (native caption + the
	// `credit` ACF field from inc/blocks.php).
	if ( $thumb_id ) {
		$featured_caption = rgvdsa_blog_kses_plain( wp_get_attachment_caption( $thumb_id ) );
		if ( '' !== $featured_caption ) {
			$featured_image['caption'] = $featured_caption;
		}
		$featured_credit = rgvdsa_blog_kses_plain( get_post_meta( $thumb_id, 'credit', true ) );
		if ( '' !== $featured_credit ) {
			$featured_image['credit'] = $featured_credit;
		}
	}

	$tags = wp_get_post_terms( $post->ID, 'post_tag', array( 'fields' => 'names' ) );

	return array(
		'title'         => $title,
		'dek'           => (string) rgvdsa_blog_field( 'dek', $post->ID ),
		'cat'           => rgvdsa_blog_post_cat( $post ),
		'date'          => get_the_date( 'F j, Y', $post ),
		'readMinutes'   => rgvdsa_blog_read_minutes( $post ),
		'bylineMode'    => 'committee' === rgvdsa_blog_field( 'byline_mode', $post->ID ) ? 'committee' : 'named',
		'author'        => get_the_author_meta( 'display_name', $author_id ),
		'authorAvatar'  => get_avatar_url( $author_id ) ?: null,
		'committee'     => $committee,
		'authorBio'     => (string) get_the_author_meta( 'description', $author_id ),
		'committeeBio'  => $committee_bio,
		'featuredImage' => $featured_image,
		'blocks'        => rgvdsa_blog_map_blocks( $post->ID ),
		'tags'          => is_array( $tags ) ? array_values( array_map( 'strval', $tags ) ) : array(),
	);
}

/* -------------------------------------------------------------------------
 * Context wiring.
 * ---------------------------------------------------------------------- */

/**
 * Shared post-list query (archive / read-next / home teasers; REST later).
 * Primes author + thumbnail caches for the result set so serializers hit
 * caches instead of issuing per-post queries (WP_Query already primes
 * meta/terms).
 *
 * @param array $args WP_Query overrides merged over the blog defaults.
 * @return WP_Query
 */
function rgvdsa_blog_posts_query( $args = array() ) {
	$query = new WP_Query(
		wp_parse_args(
			$args,
			array(
				'post_type'           => 'post',
				'post_status'         => 'publish',
				'ignore_sticky_posts' => true,
			)
		)
	);

	if ( ! empty( $query->posts ) ) {
		update_post_author_caches( $query->posts );
		update_post_thumbnail_cache( $query );
	}

	return $query;
}

// Blog archive / posts page / search — the BlogArchive island payload.
add_filter( 'rgvdsa/context/blog_archive', 'rgvdsa_blog_archive_context' );

function rgvdsa_blog_archive_context( $context ) {
	$context['archive_categories'] = rgvdsa_post_categories();

	// Editable posts-page lede (interior `lede` field on the page_for_posts
	// page) → PageHeader; replaces the index.twig lorem when set.
	$posts_page_id = (int) get_option( 'page_for_posts' );
	if ( $posts_page_id && function_exists( 'get_field' ) ) {
		$lede = get_field( 'lede', $posts_page_id );
		if ( is_string( $lede ) && '' !== trim( $lede ) ) {
			$context['posts_page_lede'] = trim( $lede );
		}
	}

	$paged = max( 1, (int) get_query_var( 'paged' ) );

	$args = array(
		'posts_per_page' => 24,
		'paged'          => $paged,
	);

	// Custom ?category= param (island filter state) → category_name.
	$category = isset( $_GET['category'] ) ? sanitize_key( (string) wp_unslash( $_GET['category'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
	if ( array_key_exists( $category, rgvdsa_category_registry() ) ) {
		$args['category_name'] = $category;
	}

	// Native ?s= search.
	$search = trim( (string) get_query_var( 's' ) );
	if ( '' !== $search ) {
		$args['s'] = $search;
	}

	$query = rgvdsa_blog_posts_query( $args );

	// Leave archive_posts unset pre-seed so the island fixture holds.
	if ( empty( $query->posts ) ) {
		return $context;
	}

	// Sticky posts stay in date order here with featured:true; the island
	// picks posts.find(featured) ?? posts[0] for the featured card.
	$context['archive_posts'] = array_map( 'rgvdsa_post_to_blog_post', $query->posts );

	$pagination = array();
	if ( $paged > 1 ) {
		$pagination['newerUrl'] = get_pagenum_link( $paged - 1, false );
	}
	if ( $paged < (int) $query->max_num_pages ) {
		$pagination['olderUrl'] = get_pagenum_link( $paged + 1, false );
	}
	if ( ! empty( $pagination ) ) {
		$context['archive_pagination'] = $pagination;
	}

	return $context;
}

// Single post — the SinglePost island payload.
add_filter( 'rgvdsa/context/single', 'rgvdsa_blog_single_context', 10, 2 );

function rgvdsa_blog_single_context( $context, $timber_post ) {
	if ( ! $timber_post || 'post' !== $timber_post->post_type ) {
		return $context;
	}

	$context['single_post']           = rgvdsa_post_to_single( $timber_post->ID );
	$context['single_categories']     = rgvdsa_post_categories();
	$context['single_show_meta_rail'] = (bool) rgvdsa_blog_field( 'show_meta_rail', $timber_post->ID );

	$posts_page                 = (int) get_option( 'page_for_posts' );
	$context['single_blog_url'] = $posts_page ? get_permalink( $posts_page ) : '/blog/';
	$context['single_home_url'] = home_url( '/' );

	// Read Next pool — latest 12, current excluded; the island narrows to
	// same-category latest 3.
	$pool = rgvdsa_blog_posts_query(
		array(
			'posts_per_page' => 12,
			'post__not_in'   => array( (int) $timber_post->ID ),
		)
	);

	$context['single_posts'] = array_map( 'rgvdsa_post_to_blog_post', $pool->posts );

	return $context;
}

// Home "From the blog" teasers — blog_featured + blog_rows fixture keys.
add_filter( 'rgvdsa/context/front_page', 'rgvdsa_blog_front_page_context' );

function rgvdsa_blog_front_page_context( $context ) {
	$query = rgvdsa_blog_posts_query( array( 'posts_per_page' => 3 ) );

	// Always set both keys (null / empty allowed) so Twig owns the empty
	// state instead of falling back to lorem fixtures. Emit the raw `cat`
	// slug; Twig maps it to the category pill class.
	$context['blog_featured'] = null;
	$context['blog_rows']     = array();

	if ( empty( $query->posts ) ) {
		return $context;
	}

	$labels = array();
	foreach ( rgvdsa_post_categories() as $category ) {
		$labels[ $category['id'] ] = $category['label'];
	}

	// Featured card = sticky among the latest 3, else the latest.
	$featured = null;
	foreach ( $query->posts as $post ) {
		if ( is_sticky( $post->ID ) ) {
			$featured = $post;
			break;
		}
	}
	if ( ! $featured ) {
		$featured = $query->posts[0];
	}

	$cat                      = rgvdsa_blog_post_cat( $featured );
	$context['blog_featured'] = array(
		'cat'       => $cat,
		'cat_label' => $labels[ $cat ],
		'date'      => get_the_date( 'F j, Y', $featured ),
		'read'      => rgvdsa_blog_read_minutes( $featured ) . ' min read',
		'title'     => html_entity_decode( get_the_title( $featured ), ENT_QUOTES, 'UTF-8' ),
		'excerpt'   => wp_strip_all_tags( get_the_excerpt( $featured ) ),
	);

	foreach ( $query->posts as $post ) {
		if ( $post->ID === $featured->ID || count( $context['blog_rows'] ) >= 2 ) {
			continue;
		}
		$cat                    = rgvdsa_blog_post_cat( $post );
		$context['blog_rows'][] = array(
			'cat'       => $cat,
			'cat_label' => $labels[ $cat ],
			'title'     => html_entity_decode( get_the_title( $post ), ENT_QUOTES, 'UTF-8' ),
			'date'      => get_the_date( 'F j, Y', $post ),
		);
	}

	return $context;
}

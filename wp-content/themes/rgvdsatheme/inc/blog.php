<?php
/**
 * Blog domain: post fields + archive/single wiring.
 *
 * Owns: category color term meta, post settings field group (dek,
 * byline_mode, …), the post_blocks flexible content group, and
 * serialization to the BlogPost / SinglePostData island contracts.
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
					'key'   => 'field_rgvdsa_blog_featured_caption',
					'label' => 'Featured image caption',
					'name'  => 'featured_caption',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_rgvdsa_blog_featured_credit',
					'label' => 'Featured image credit',
					'name'  => 'featured_credit',
					'type'  => 'text',
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

	// Article body — flexible content stack (see 03-DESIGN-SPEC.md § Blog Post).
	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_blog_post_blocks',
			'title'    => 'Post blocks',
			'fields'   => array(
				array(
					'key'          => 'field_rgvdsa_blog_post_blocks',
					'label'        => 'Blocks',
					'name'         => 'post_blocks',
					'type'         => 'flexible_content',
					'button_label' => 'Add block',
					'layouts'      => array(
						'layout_rgvdsa_blog_prose'          => array(
							'key'        => 'layout_rgvdsa_blog_prose',
							'name'       => 'prose',
							'label'      => 'Prose',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'          => 'field_rgvdsa_blog_pb_prose_content',
									'label'        => 'Content',
									'name'         => 'content',
									'type'         => 'wysiwyg',
									'media_upload' => 0,
								),
							),
						),
						'layout_rgvdsa_blog_image'          => array(
							'key'        => 'layout_rgvdsa_blog_image',
							'name'       => 'image',
							'label'      => 'Image',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_image_image',
									'label'         => 'Image',
									'name'          => 'image',
									'type'          => 'image',
									'required'      => 1,
									'return_format' => 'array',
									'preview_size'  => 'medium',
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_image_alt_text',
									'label'    => 'Alt text',
									'name'     => 'alt_text',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_image_caption',
									'label' => 'Caption',
									'name'  => 'caption',
									'type'  => 'text',
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_image_credit',
									'label' => 'Credit',
									'name'  => 'credit',
									'type'  => 'text',
								),
								array(
									'key'          => 'field_rgvdsa_blog_pb_image_breakout',
									'label'        => 'Breakout',
									'name'         => 'breakout',
									'type'         => 'true_false',
									'instructions' => 'Let the image break out of the prose measure.',
									'ui'           => 1,
								),
							),
						),
						'layout_rgvdsa_blog_pull_quote'     => array(
							'key'        => 'layout_rgvdsa_blog_pull_quote',
							'name'       => 'pull_quote',
							'label'      => 'Pull quote',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'      => 'field_rgvdsa_blog_pb_pull_quote_quote',
									'label'    => 'Quote',
									'name'     => 'quote',
									'type'     => 'textarea',
									'required' => 1,
									'rows'     => 3,
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_pull_quote_attribution',
									'label' => 'Attribution',
									'name'  => 'attribution',
									'type'  => 'text',
								),
							),
						),
						'layout_rgvdsa_blog_gallery'        => array(
							'key'        => 'layout_rgvdsa_blog_gallery',
							'name'       => 'gallery',
							'label'      => 'Gallery',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_gallery_layout',
									'label'         => 'Layout',
									'name'          => 'layout',
									'type'          => 'select',
									'choices'       => array(
										'essay' => 'Essay',
										'grid'  => 'Grid',
									),
									'default_value' => 'essay',
									'return_format' => 'value',
								),
								array(
									'key'          => 'field_rgvdsa_blog_pb_gallery_images',
									'label'        => 'Images',
									'name'         => 'images',
									'type'         => 'repeater',
									'layout'       => 'block',
									'button_label' => 'Add image',
									'sub_fields'   => array(
										array(
											'key'           => 'field_rgvdsa_blog_pb_gallery_img_image',
											'label'         => 'Image',
											'name'          => 'image',
											'type'          => 'image',
											'required'      => 1,
											'return_format' => 'array',
											'preview_size'  => 'medium',
										),
										array(
											'key'      => 'field_rgvdsa_blog_pb_gallery_img_alt_text',
											'label'    => 'Alt text',
											'name'     => 'alt_text',
											'type'     => 'text',
											'required' => 1,
										),
										array(
											'key'   => 'field_rgvdsa_blog_pb_gallery_img_caption',
											'label' => 'Caption',
											'name'  => 'caption',
											'type'  => 'text',
										),
									),
								),
							),
						),
						'layout_rgvdsa_blog_person_quote'   => array(
							'key'        => 'layout_rgvdsa_blog_person_quote',
							'name'       => 'person_quote',
							'label'      => 'Person quote',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_person_quote_photo',
									'label'         => 'Photo',
									'name'          => 'photo',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'thumbnail',
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_person_quote_alt_text',
									'label'    => 'Alt text',
									'name'     => 'alt_text',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_person_quote_quote',
									'label'    => 'Quote',
									'name'     => 'quote',
									'type'     => 'textarea',
									'required' => 1,
									'rows'     => 3,
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_person_quote_translation',
									'label' => 'Translation',
									'name'  => 'translation',
									'type'  => 'textarea',
									'rows'  => 3,
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_person_quote_name',
									'label'    => 'Name',
									'name'     => 'name',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_person_quote_role',
									'label' => 'Role',
									'name'  => 'role',
									'type'  => 'text',
								),
								array(
									'key'           => 'field_rgvdsa_blog_pb_person_quote_lang',
									'label'         => 'Quote language',
									'name'          => 'lang',
									'type'          => 'select',
									'choices'       => array(
										'en' => 'English',
										'es' => 'Español',
									),
									'default_value' => 'en',
									'return_format' => 'value',
								),
							),
						),
						'layout_rgvdsa_blog_video'          => array(
							'key'        => 'layout_rgvdsa_blog_video',
							'name'       => 'video',
							'label'      => 'Video',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'   => 'field_rgvdsa_blog_pb_video_url',
									'label' => 'Video URL',
									'name'  => 'url',
									'type'  => 'url',
								),
								array(
									'key'           => 'field_rgvdsa_blog_pb_video_poster',
									'label'         => 'Poster',
									'name'          => 'poster',
									'type'          => 'image',
									'return_format' => 'array',
									'preview_size'  => 'medium',
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_video_caption',
									'label' => 'Caption',
									'name'  => 'caption',
									'type'  => 'text',
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_video_transcript_url',
									'label' => 'Transcript URL',
									'name'  => 'transcript_url',
									'type'  => 'url',
								),
							),
						),
						'layout_rgvdsa_blog_audio'          => array(
							'key'        => 'layout_rgvdsa_blog_audio',
							'name'       => 'audio',
							'label'      => 'Audio',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_audio_file',
									'label'         => 'Audio file',
									'name'          => 'file',
									'type'          => 'file',
									'required'      => 1,
									'return_format' => 'array',
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_audio_title',
									'label'    => 'Title',
									'name'     => 'title',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'          => 'field_rgvdsa_blog_pb_audio_duration',
									'label'        => 'Duration',
									'name'         => 'duration',
									'type'         => 'text',
									'instructions' => 'Display string, e.g. 3:12.',
								),
								array(
									'key'           => 'field_rgvdsa_blog_pb_audio_transcript',
									'label'         => 'Transcript',
									'name'          => 'transcript',
									'type'          => 'file',
									'return_format' => 'array',
								),
							),
						),
						'layout_rgvdsa_blog_document'       => array(
							'key'        => 'layout_rgvdsa_blog_document',
							'name'       => 'document',
							'label'      => 'Document',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_document_file',
									'label'         => 'File',
									'name'          => 'file',
									'type'          => 'file',
									'required'      => 1,
									'return_format' => 'array',
								),
								array(
									'key'      => 'field_rgvdsa_blog_pb_document_title',
									'label'    => 'Title',
									'name'     => 'title',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'          => 'field_rgvdsa_blog_pb_document_description',
									'label'        => 'Description',
									'name'         => 'description',
									'type'         => 'text',
									'instructions' => 'Meta line, e.g. Bilingual · 2 pages · 340 KB.',
								),
							),
						),
						'layout_rgvdsa_blog_event_embed'    => array(
							'key'        => 'layout_rgvdsa_blog_event_embed',
							'name'       => 'event_embed',
							'label'      => 'Event embed',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'           => 'field_rgvdsa_blog_pb_event_embed_event',
									'label'         => 'Event',
									'name'          => 'event',
									'type'          => 'relationship',
									'required'      => 1,
									'post_type'     => array( 'event' ),
									'filters'       => array( 'search' ),
									'max'           => 1,
									'return_format' => 'id',
								),
							),
						),
						'layout_rgvdsa_blog_action_callout' => array(
							'key'        => 'layout_rgvdsa_blog_action_callout',
							'name'       => 'action_callout',
							'label'      => 'Action callout',
							'display'    => 'block',
							'sub_fields' => array(
								array(
									'key'      => 'field_rgvdsa_blog_pb_action_callout_heading',
									'label'    => 'Heading',
									'name'     => 'heading',
									'type'     => 'text',
									'required' => 1,
								),
								array(
									'key'   => 'field_rgvdsa_blog_pb_action_callout_body',
									'label' => 'Body',
									'name'  => 'body',
									'type'  => 'textarea',
									'rows'  => 3,
								),
								array(
									'key'          => 'field_rgvdsa_blog_pb_action_callout_buttons',
									'label'        => 'Buttons',
									'name'         => 'buttons',
									'type'         => 'repeater',
									'layout'       => 'table',
									'button_label' => 'Add button',
									'sub_fields'   => array(
										array(
											'key'   => 'field_rgvdsa_blog_pb_action_callout_btn_label',
											'label' => 'Label',
											'name'  => 'label',
											'type'  => 'text',
										),
										array(
											'key'   => 'field_rgvdsa_blog_pb_action_callout_btn_url',
											'label' => 'URL',
											'name'  => 'url',
											'type'  => 'text',
										),
										array(
											'key'           => 'field_rgvdsa_blog_pb_action_callout_btn_style',
											'label'         => 'Style',
											'name'          => 'style',
											'type'          => 'select',
											'choices'       => array(
												'primary' => 'Primary',
												'outline' => 'Outline',
											),
											'default_value' => 'primary',
											'return_format' => 'value',
										),
									),
								),
							),
						),
					),
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
 * Word count (post_content + prose blocks) at 200 wpm. Loads the
 * post_blocks flexible field — save-time / self-heal only, never per card.
 */
function rgvdsa_blog_compute_read_minutes( $post_id ) {
	$post = get_post( $post_id );
	if ( ! $post ) {
		return 1;
	}

	$text   = $post->post_content;
	$blocks = rgvdsa_blog_field( 'post_blocks', $post->ID );
	if ( is_array( $blocks ) ) {
		foreach ( $blocks as $block ) {
			if ( 'prose' === ( $block['acf_fc_layout'] ?? '' ) ) {
				$text .= ' ' . (string) ( $block['content'] ?? '' );
			}
		}
	}

	$words = str_word_count( wp_strip_all_tags( $text ) );

	return max( 1, (int) round( $words / 200 ) );
}

// Precompute at save (priority 20 — after ACF has written post_blocks meta).
add_action( 'save_post_post', 'rgvdsa_blog_store_read_minutes', 20 );

function rgvdsa_blog_store_read_minutes( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	update_post_meta( $post_id, '_rgvdsa_read_minutes', rgvdsa_blog_compute_read_minutes( $post_id ) );
}

/**
 * ACF read_minutes override, else precomputed `_rgvdsa_read_minutes` meta
 * (primed by WP_Query's meta cache — no per-card post_blocks load).
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
 * PostImage assoc array from an ACF image array (caption/credit keys omitted when empty).
 */
function rgvdsa_blog_post_image( $image, $alt, $caption = '', $credit = '' ) {
	$out = array(
		'src' => null,
		'alt' => rgvdsa_blog_kses_plain( $alt ),
	);

	if ( is_array( $image ) ) {
		$out['src'] = $image['sizes']['large'] ?? $image['url'] ?? null;
	}
	$caption = rgvdsa_blog_kses_plain( $caption );
	if ( '' !== $caption ) {
		$out['caption'] = $caption;
	}
	$credit = rgvdsa_blog_kses_plain( $credit );
	if ( '' !== $credit ) {
		$out['credit'] = $credit;
	}

	return $out;
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

/**
 * post_blocks ACF rows → PostBlock[] (camelCase keys, optional keys omitted).
 */
function rgvdsa_blog_map_blocks( $post_id ) {
	$rows = rgvdsa_blog_field( 'post_blocks', $post_id );
	if ( ! is_array( $rows ) ) {
		return array();
	}

	$blocks = array();

	foreach ( $rows as $row ) {
		switch ( $row['acf_fc_layout'] ?? '' ) {
			case 'prose':
				$html = rgvdsa_blog_kses_prose( trim( (string) ( $row['content'] ?? '' ) ) );
				if ( '' === $html ) {
					break;
				}
				$blocks[] = array(
					'type' => 'prose',
					'html' => $html,
				);
				break;

			case 'image':
				// Imageless rows still render (ImageSlot placeholder) as long as
				// the editor filled anything in; skip only truly empty rows.
				if ( ! is_array( $row['image'] ?? null )
					&& '' === trim( (string) ( $row['alt_text'] ?? '' ) )
					&& '' === trim( (string) ( $row['caption'] ?? '' ) ) ) {
					break;
				}
				$block = array(
					'type'  => 'image',
					'image' => rgvdsa_blog_post_image(
						is_array( $row['image'] ?? null ) ? $row['image'] : null,
						$row['alt_text'] ?? '',
						$row['caption'] ?? '',
						$row['credit'] ?? ''
					),
				);
				if ( ! empty( $row['breakout'] ) ) {
					$block['breakout'] = true;
				}
				$blocks[] = $block;
				break;

			case 'pull_quote':
				$quote = rgvdsa_blog_kses_plain( $row['quote'] ?? '' );
				if ( '' === $quote ) {
					break;
				}
				$block       = array(
					'type'  => 'pull_quote',
					'quote' => $quote,
				);
				$attribution = rgvdsa_blog_kses_plain( $row['attribution'] ?? '' );
				if ( '' !== $attribution ) {
					$block['attribution'] = $attribution;
				}
				$blocks[] = $block;
				break;

			case 'gallery':
				$images = array();
				foreach ( (array) ( $row['images'] ?? array() ) as $img_row ) {
					// Imageless rows keep their placeholder slot.
					if ( ! is_array( $img_row['image'] ?? null )
						&& '' === trim( (string) ( $img_row['alt_text'] ?? '' ) )
						&& '' === trim( (string) ( $img_row['caption'] ?? '' ) ) ) {
						continue;
					}
					$images[] = rgvdsa_blog_post_image(
						is_array( $img_row['image'] ?? null ) ? $img_row['image'] : null,
						$img_row['alt_text'] ?? '',
						$img_row['caption'] ?? ''
					);
				}
				if ( empty( $images ) ) {
					break;
				}
				$blocks[] = array(
					'type'   => 'gallery',
					'layout' => 'grid' === ( $row['layout'] ?? '' ) ? 'grid' : 'essay',
					'images' => $images,
				);
				break;

			case 'person_quote':
				$quote = rgvdsa_blog_kses_plain( $row['quote'] ?? '' );
				$name  = rgvdsa_blog_kses_plain( $row['name'] ?? '' );
				if ( '' === $quote || '' === $name ) {
					break;
				}
				$photo = null;
				if ( is_array( $row['photo'] ?? null ) ) {
					$photo = $row['photo']['sizes']['medium'] ?? $row['photo']['url'] ?? null;
				}
				$block = array(
					'type'  => 'person_quote',
					'photo' => $photo,
					'alt'   => (string) ( $row['alt_text'] ?? '' ),
					'quote' => $quote,
					'name'  => $name,
					'lang'  => 'es' === ( $row['lang'] ?? '' ) ? 'es' : 'en',
				);
				$translation = rgvdsa_blog_kses_plain( $row['translation'] ?? '' );
				if ( '' !== $translation ) {
					$block['translation'] = $translation;
				}
				$role = rgvdsa_blog_kses_plain( $row['role'] ?? '' );
				if ( '' !== $role ) {
					$block['role'] = $role;
				}
				$blocks[] = $block;
				break;

			case 'video':
				$block = array(
					'type' => 'video',
					'url'  => (string) ( $row['url'] ?? '' ),
				);
				if ( is_array( $row['poster'] ?? null ) ) {
					$block['poster'] = $row['poster']['sizes']['large'] ?? $row['poster']['url'] ?? null;
				}
				$caption = rgvdsa_blog_kses_plain( $row['caption'] ?? '' );
				if ( '' !== $caption ) {
					$block['caption'] = $caption;
				}
				if ( '' !== (string) ( $row['transcript_url'] ?? '' ) ) {
					$block['transcriptUrl'] = (string) $row['transcript_url'];
				}
				$blocks[] = $block;
				break;

			case 'audio':
				$title = trim( (string) ( $row['title'] ?? '' ) );
				if ( '' === $title ) {
					break;
				}
				$file = null;
				if ( is_array( $row['file'] ?? null ) ) {
					$file = $row['file']['url'] ?? null;
				}
				$transcript = is_array( $row['transcript'] ?? null ) ? (string) ( $row['transcript']['url'] ?? '' ) : '';
				$block      = array(
					'type'          => 'audio',
					'file'          => $file,
					'title'         => $title,
					'transcriptUrl' => $transcript,
				);
				if ( '' !== (string) ( $row['duration'] ?? '' ) ) {
					$block['duration'] = (string) $row['duration'];
				}
				$blocks[] = $block;
				break;

			case 'document':
				$title = trim( (string) ( $row['title'] ?? '' ) );
				$url   = is_array( $row['file'] ?? null ) ? (string) ( $row['file']['url'] ?? '' ) : '';
				if ( '' === $title || '' === $url ) {
					break;
				}
				$block = array(
					'type'  => 'document',
					'url'   => $url,
					'title' => $title,
				);
				if ( '' !== (string) ( $row['description'] ?? '' ) ) {
					$block['description'] = (string) $row['description'];
				}
				$blocks[] = $block;
				break;

			case 'event_embed':
				// Drop the block when the events domain or the event is missing.
				if ( ! function_exists( 'rgvdsa_event_to_chapter_event' ) ) {
					break;
				}
				$ids      = array_filter( array_map( 'intval', (array) ( $row['event'] ?? array() ) ) );
				$event_id = $ids ? (int) reset( $ids ) : 0;
				if ( ! $event_id ) {
					break;
				}
				$event_post = get_post( $event_id );
				if ( ! $event_post || 'publish' !== $event_post->post_status ) {
					break;
				}
				$blocks[] = array(
					'type'  => 'event_embed',
					'event' => rgvdsa_event_to_chapter_event( $event_post ),
				);
				break;

			case 'action_callout':
				$heading = rgvdsa_blog_kses_plain( $row['heading'] ?? '' );
				if ( '' === $heading ) {
					break;
				}
				$buttons = array();
				foreach ( (array) ( $row['buttons'] ?? array() ) as $btn ) {
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
					'body'    => rgvdsa_blog_kses_plain( $row['body'] ?? '' ),
					'buttons' => $buttons,
				);
				break;
		}
	}

	return $blocks;
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
 * SinglePostData shape (post hero + post_blocks stack + end matter).
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
	$featured_caption = rgvdsa_blog_kses_plain( rgvdsa_blog_field( 'featured_caption', $post->ID ) );
	if ( '' !== $featured_caption ) {
		$featured_image['caption'] = $featured_caption;
	}
	$featured_credit = rgvdsa_blog_kses_plain( rgvdsa_blog_field( 'featured_credit', $post->ID ) );
	if ( '' !== $featured_credit ) {
		$featured_image['credit'] = $featured_credit;
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

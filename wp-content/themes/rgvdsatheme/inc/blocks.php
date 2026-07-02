<?php
/**
 * Blocks domain: Gutenberg authoring for posts.
 *
 * Owns: registration of the six rgvdsa ACF blocks (each blocks/<name> dir
 * holds a block.json), the core/gallery block styles, the post block
 * template, the restricted
 * post inserter, and the per-block + attachment-credit ACF field groups.
 *
 * Serialization to the PostBlock island contracts lives in inc/blog.php
 * (rgvdsa_blog_blocks_from_content); render.php files are editor-preview
 * only — the front end always renders via the SinglePost island.
 */

/* -------------------------------------------------------------------------
 * Block registration.
 * ---------------------------------------------------------------------- */

add_action( 'init', 'rgvdsa_blocks_register' );

function rgvdsa_blocks_register() {
	// The rgvdsa/* blocks are ACF blocks — without ACF Pro they would
	// register as empty shells with no fields or preview.
	if ( ! function_exists( 'acf_register_block_type' ) ) {
		return;
	}

	foreach ( glob( __DIR__ . '/../blocks/*/block.json' ) as $manifest ) {
		register_block_type( dirname( $manifest ) );
	}

	register_block_style(
		'core/gallery',
		array(
			'name'       => 'essay',
			'label'      => 'Essay',
			'is_default' => true,
		)
	);
	register_block_style(
		'core/gallery',
		array(
			'name'  => 'grid',
			'label' => 'Grid',
		)
	);

	// Fresh posts open with one paragraph; free-form after that (no lock).
	$post_type = get_post_type_object( 'post' );
	if ( $post_type ) {
		$post_type->template = array( array( 'core/paragraph' ) );
	}
}

/* -------------------------------------------------------------------------
 * Restricted inserter — posts get exactly the contract-complete 14 blocks.
 * ---------------------------------------------------------------------- */

add_filter( 'allowed_block_types_all', 'rgvdsa_blocks_allowed_types', 10, 2 );

function rgvdsa_blocks_allowed_types( $allowed, $editor_context ) {
	if ( empty( $editor_context->post ) || 'post' !== $editor_context->post->post_type ) {
		return $allowed;
	}

	return array(
		'core/paragraph',
		'core/heading',
		'core/list',
		'core/list-item',
		'core/quote',
		'core/image',
		'core/pullquote',
		'core/gallery',
		'rgvdsa/person-quote',
		'rgvdsa/video',
		'rgvdsa/audio',
		'rgvdsa/document',
		'rgvdsa/event-embed',
		'rgvdsa/action-callout',
	);
}

/* -------------------------------------------------------------------------
 * ACF field groups — one per rgvdsa/* block, plus the attachment credit.
 * Field names mirror the PostBlock contracts (src/lib/posts.ts); the
 * serializer reads them raw from the block comment `data`.
 * ---------------------------------------------------------------------- */

add_action( 'acf/init', 'rgvdsa_blocks_register_fields' );

function rgvdsa_blocks_register_fields() {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	// Credit travels with the photo — surfaces on core/image, gallery
	// images, and the featured image (replaces the per-post credit field).
	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_attachment_credit',
			'title'    => 'Image credit',
			'fields'   => array(
				array(
					'key'          => 'field_rgvdsa_attachment_credit',
					'label'        => 'Credit',
					'name'         => 'credit',
					'type'         => 'text',
					'instructions' => 'Photographer / source line, e.g. “Photo: RGV DSA”.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'attachment',
						'operator' => '==',
						'value'    => 'all',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_person_quote',
			'title'    => 'Person quote',
			'fields'   => array(
				array(
					'key'           => 'field_rgvdsa_block_pq_photo',
					'label'         => 'Photo',
					'name'          => 'photo',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'thumbnail',
				),
				array(
					'key'      => 'field_rgvdsa_block_pq_alt_text',
					'label'    => 'Alt text',
					'name'     => 'alt_text',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'      => 'field_rgvdsa_block_pq_quote',
					'label'    => 'Quote',
					'name'     => 'quote',
					'type'     => 'textarea',
					'required' => 1,
					'rows'     => 3,
				),
				array(
					'key'   => 'field_rgvdsa_block_pq_translation',
					'label' => 'Translation',
					'name'  => 'translation',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'      => 'field_rgvdsa_block_pq_name',
					'label'    => 'Name',
					'name'     => 'name',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_rgvdsa_block_pq_role',
					'label' => 'Role',
					'name'  => 'role',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_rgvdsa_block_pq_lang',
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
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/person-quote',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_video',
			'title'    => 'Video',
			'fields'   => array(
				array(
					'key'      => 'field_rgvdsa_block_video_url',
					'label'    => 'Video URL',
					'name'     => 'url',
					'type'     => 'url',
					'required' => 1,
				),
				array(
					'key'           => 'field_rgvdsa_block_video_poster',
					'label'         => 'Poster',
					'name'          => 'poster',
					'type'          => 'image',
					'return_format' => 'id',
					'preview_size'  => 'medium',
				),
				array(
					'key'   => 'field_rgvdsa_block_video_caption',
					'label' => 'Caption',
					'name'  => 'caption',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_rgvdsa_block_video_transcript_url',
					'label' => 'Transcript URL',
					'name'  => 'transcript_url',
					'type'  => 'url',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/video',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_audio',
			'title'    => 'Audio',
			'fields'   => array(
				array(
					'key'           => 'field_rgvdsa_block_audio_file',
					'label'         => 'Audio file',
					'name'          => 'file',
					'type'          => 'file',
					'required'      => 1,
					'return_format' => 'id',
				),
				array(
					'key'      => 'field_rgvdsa_block_audio_title',
					'label'    => 'Title',
					'name'     => 'title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'          => 'field_rgvdsa_block_audio_duration',
					'label'        => 'Duration',
					'name'         => 'duration',
					'type'         => 'text',
					'instructions' => 'Display string, e.g. 3:12.',
				),
				array(
					'key'           => 'field_rgvdsa_block_audio_transcript',
					'label'         => 'Transcript',
					'name'          => 'transcript',
					'type'          => 'file',
					'return_format' => 'id',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/audio',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_document',
			'title'    => 'Document',
			'fields'   => array(
				array(
					'key'           => 'field_rgvdsa_block_document_file',
					'label'         => 'File',
					'name'          => 'file',
					'type'          => 'file',
					'required'      => 1,
					'return_format' => 'id',
				),
				array(
					'key'      => 'field_rgvdsa_block_document_title',
					'label'    => 'Title',
					'name'     => 'title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'          => 'field_rgvdsa_block_document_description',
					'label'        => 'Description',
					'name'         => 'description',
					'type'         => 'text',
					'instructions' => 'Meta line, e.g. Bilingual · 2 pages · 340 KB.',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/document',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_event_embed',
			'title'    => 'Event embed',
			'fields'   => array(
				array(
					'key'           => 'field_rgvdsa_block_event_embed_event',
					'label'         => 'Event',
					'name'          => 'event',
					'type'          => 'post_object',
					'required'      => 1,
					'post_type'     => array( 'event' ),
					'return_format' => 'id',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/event-embed',
					),
				),
			),
		)
	);

	acf_add_local_field_group(
		array(
			'key'      => 'group_rgvdsa_block_action_callout',
			'title'    => 'Action callout',
			'fields'   => array(
				array(
					'key'      => 'field_rgvdsa_block_ac_heading',
					'label'    => 'Heading',
					'name'     => 'heading',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_rgvdsa_block_ac_body',
					'label' => 'Body',
					'name'  => 'body',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'          => 'field_rgvdsa_block_ac_buttons',
					'label'        => 'Buttons',
					'name'         => 'buttons',
					'type'         => 'repeater',
					'layout'       => 'table',
					'button_label' => 'Add button',
					'sub_fields'   => array(
						array(
							'key'   => 'field_rgvdsa_block_ac_btn_label',
							'label' => 'Label',
							'name'  => 'label',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_rgvdsa_block_ac_btn_url',
							'label' => 'URL',
							'name'  => 'url',
							'type'  => 'text',
						),
						array(
							'key'           => 'field_rgvdsa_block_ac_btn_style',
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
			'location' => array(
				array(
					array(
						'param'    => 'block',
						'operator' => '==',
						'value'    => 'rgvdsa/action-callout',
					),
				),
			),
		)
	);
}

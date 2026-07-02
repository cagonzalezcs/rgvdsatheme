<?php
/**
 * Interior page template wiring.
 *
 * Owns: governing-documents ACF repeater on pages and the page.twig
 * documents context.
 */

/**
 * ACF field group: Interior page (documents repeater + lede override).
 */
add_action( 'acf/init', function () {
	if ( ! function_exists( 'acf_add_local_field_group' ) ) {
		return;
	}

	acf_add_local_field_group( array(
		'key'      => 'group_rgvdsa_interior_page',
		'title'    => 'Interior page',
		'location' => array(
			array(
				array(
					'param'    => 'post_type',
					'operator' => '==',
					'value'    => 'page',
				),
			),
		),
		'fields'   => array(
			array(
				'key'          => 'field_rgvdsa_interior_lede',
				'label'        => 'Lede',
				'name'         => 'lede',
				'type'         => 'text',
				'instructions' => 'Optional override for the page-header lede. Falls back to the excerpt when empty.',
			),
			array(
				'key'          => 'field_rgvdsa_interior_documents',
				'label'        => 'Documents',
				'name'         => 'documents',
				'type'         => 'repeater',
				'instructions' => 'Governing documents listed on the page. Leave empty to show the prototype fixture.',
				'layout'       => 'block',
				'button_label' => 'Add document',
				'sub_fields'   => array(
					array(
						'key'      => 'field_rgvdsa_interior_documents_title',
						'label'    => 'Title',
						'name'     => 'title',
						'type'     => 'text',
						'required' => 1,
					),
					array(
						'key'          => 'field_rgvdsa_interior_documents_description',
						'label'        => 'Description',
						'name'         => 'description',
						'type'         => 'text',
						'instructions' => 'Short meta line, e.g. "Last amended March 2026". Falls back to the file size.',
					),
					array(
						'key'           => 'field_rgvdsa_interior_documents_file',
						'label'         => 'File',
						'name'          => 'file',
						'type'          => 'file',
						'return_format' => 'array',
					),
				),
			),
		),
	) );
} );

/**
 * Map an ACF documents row to the page.twig documents shape.
 *
 * Twig fixture keys: { title, meta, url }. Meta line is
 * "TYPE · description" (or "TYPE · 340 KB" when no description).
 *
 * @param array $row ACF repeater row (title, description, file array).
 * @return array|null Null when the row has no downloadable file.
 */
function rgvdsa_interior_document_row( $row ) {
	$file = isset( $row['file'] ) && is_array( $row['file'] ) ? $row['file'] : array();
	$url  = isset( $file['url'] ) ? $file['url'] : '';

	if ( '' === $url ) {
		return null;
	}

	$meta_parts = array();

	$type = ! empty( $file['subtype'] ) ? $file['subtype'] : pathinfo( $url, PATHINFO_EXTENSION );
	if ( $type ) {
		$meta_parts[] = strtoupper( $type );
	}

	$description = isset( $row['description'] ) ? trim( (string) $row['description'] ) : '';
	if ( '' !== $description ) {
		$meta_parts[] = $description;
	} elseif ( ! empty( $file['filesize'] ) ) {
		$meta_parts[] = size_format( (int) $file['filesize'] );
	}

	return array(
		'title' => isset( $row['title'] ) ? $row['title'] : '',
		'meta'  => implode( ' · ', $meta_parts ),
		'url'   => $url,
	);
}

/**
 * Inject interior-page ACF data into the page.twig context.
 *
 * Only sets keys when real data exists so the twig |default() fixtures
 * keep rendering on unseeded pages.
 */
add_filter( 'rgvdsa/context/page', function ( $context, $timber_post ) {
	if ( ! function_exists( 'get_field' ) || ! $timber_post ) {
		return $context;
	}

	$lede = get_field( 'lede', $timber_post->ID );
	if ( is_string( $lede ) && '' !== trim( $lede ) ) {
		$context['page_lede'] = trim( $lede );
	}

	$rows = get_field( 'documents', $timber_post->ID );
	if ( is_array( $rows ) && $rows ) {
		$documents = array_values( array_filter( array_map( 'rgvdsa_interior_document_row', $rows ) ) );
		if ( $documents ) {
			$context['documents'] = $documents;
		}
	}

	return $context;
}, 10, 2 );

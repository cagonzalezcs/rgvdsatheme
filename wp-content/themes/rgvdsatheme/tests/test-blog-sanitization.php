<?php
/**
 * Blog sanitization (inc/blog.php): the prose kses allowlist and the
 * plain-text strip on captions/quotes/attributions/callout fields, applied
 * at serialize time in rgvdsa_blog_map_blocks().
 *
 * WorDBless has no ACF; post_blocks is supplied through the get_post_metadata
 * seam (same approach the category suite uses for terms).
 */

use WorDBless\BaseTestCase;

class TestBlogSanitization extends BaseTestCase {

	public function set_up() {
		switch_theme( basename( dirname( __DIR__ ) ) );

		// Plain require: WorDBless restores hooks to a pre-theme snapshot
		// after every test, so hooks must re-register per test.
		require dirname( __DIR__ ) . '/functions.php';

		do_action( 'after_setup_theme' );

		parent::set_up();
	}

	public function tear_down() {
		parent::tear_down();
	}

	/**
	 * Feed rgvdsa_blog_field( 'post_blocks', … ) via the meta short-circuit.
	 * get_post_meta( …, true ) returns element [0], so wrap the rows once.
	 */
	private function supply_post_blocks( array $rows ) {
		add_filter(
			'get_post_metadata',
			function ( $value, $object_id, $meta_key ) use ( $rows ) {
				return 'post_blocks' === $meta_key ? array( $rows ) : $value;
			},
			10,
			3
		);
	}

	/** Allowlisted prose tags survive; scripts, handlers, and iframes do not. */
	public function test_kses_prose_keeps_allowlist_drops_scripts() {
		$html = rgvdsa_blog_kses_prose(
			'<p>Hello <strong>world</strong> '
			. '<a href="https://example.org" onclick="steal()">link</a></p>'
			. '<h2>Heading</h2><script>evil()</script><iframe src="x"></iframe>'
		);

		$this->assertStringContainsString( '<p>', $html );
		$this->assertStringContainsString( '<strong>', $html );
		$this->assertStringContainsString( '<h2>', $html );
		$this->assertStringContainsString( 'href="https://example.org"', $html );

		$this->assertStringNotContainsString( '<script', $html );
		$this->assertStringNotContainsString( 'onclick', $html );
		$this->assertStringNotContainsString( '<iframe', $html );
	}

	/** Plain pass strips every tag (and the contents of script blocks) + trims. */
	public function test_kses_plain_strips_all_tags() {
		$this->assertSame(
			'Just text',
			rgvdsa_blog_kses_plain( '  <b>Just</b> <script>x()</script>text ' )
		);
	}

	/** map_blocks sanitizes the prose block and strips the pull-quote fields. */
	public function test_map_blocks_sanitizes_prose_and_quote() {
		$this->supply_post_blocks(
			array(
				array(
					'acf_fc_layout' => 'prose',
					'content'       => '<p>ok</p><script>bad()</script>',
				),
				array(
					'acf_fc_layout' => 'pull_quote',
					'quote'         => '<em>Quote</em><script>x()</script>',
					'attribution'   => '<b>Name</b>',
				),
			)
		);

		$blocks = rgvdsa_blog_map_blocks( 123 );

		$this->assertSame( 'prose', $blocks[0]['type'] );
		$this->assertStringContainsString( '<p>', $blocks[0]['html'] );
		$this->assertStringNotContainsString( '<script', $blocks[0]['html'] );

		$this->assertSame( 'pull_quote', $blocks[1]['type'] );
		$this->assertSame( 'Quote', $blocks[1]['quote'] );
		$this->assertSame( 'Name', $blocks[1]['attribution'] );
	}
}

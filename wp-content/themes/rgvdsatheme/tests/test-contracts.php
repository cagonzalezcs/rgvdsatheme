<?php
/**
 * Contract governance (dual-sided fixture tests): deterministic seeded
 * content → serializer + REST output must equal the committed
 * tests/fixtures/*.json byte-for-byte (volatile keys — IDs, permalinks,
 * avatar URLs — are normalized). vitest parses the same files with the zod
 * schemas (src/lib/__tests__/contracts.spec.ts); a contract change fails
 * one side until both layers agree.
 *
 * Regenerate fixtures intentionally with:
 *   RGVDSA_WRITE_FIXTURES=1 vendor/bin/phpunit --filter TestContracts
 */

use WorDBless\BaseTestCase;

class TestContracts extends BaseTestCase {

	private $fixture_dir;

	public function set_up() {
		switch_theme( basename( dirname( __DIR__ ) ) );

		// Plain require: WorDBless restores hooks to a pre-theme snapshot
		// after every test, so hooks must re-register per test.
		require dirname( __DIR__ ) . '/functions.php';

		do_action( 'after_setup_theme' );

		parent::set_up();

		add_action( 'rest_api_init', 'rgvdsa_rest_register_routes' );
		$GLOBALS['wp_rest_server'] = null;

		kses_remove_filters();

		$this->fixture_dir = dirname( __DIR__ ) . '/tests/fixtures';
		if ( ! is_dir( $this->fixture_dir ) ) {
			mkdir( $this->fixture_dir, 0755, true );
		}
	}

	public function tear_down() {
		parent::tear_down();
	}

	/* ---------------------------------------------------------------------
	 * Helpers.
	 * ------------------------------------------------------------------ */

	/**
	 * Compare $actual to the committed fixture. Volatile keys (per-run IDs
	 * and URLs) are overwritten in the EXPECTED tree with the actual values
	 * before comparison, so the fixture pins every stable byte. With
	 * RGVDSA_WRITE_FIXTURES=1 the fixture is (re)written instead.
	 */
	private function assert_matches_fixture( $name, array $actual, array $volatile_paths = array() ) {
		$file = $this->fixture_dir . '/' . $name . '.json';

		if ( getenv( 'RGVDSA_WRITE_FIXTURES' ) ) {
			file_put_contents(
				$file,
				json_encode( $actual, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE ) . "\n"
			);
			$this->assertFileExists( $file );
			return;
		}

		$this->assertFileExists( $file, "Missing fixture {$name}.json — generate with RGVDSA_WRITE_FIXTURES=1" );
		$expected = json_decode( (string) file_get_contents( $file ), true );
		$this->assertIsArray( $expected );

		foreach ( $volatile_paths as $path ) {
			$value = $this->array_get( $actual, $path );
			if ( null !== $value || null === $this->array_get( $expected, $path ) ) {
				$expected = $this->array_set( $expected, $path, $value );
			}
		}

		$this->assertSame( $expected, $actual, "Serializer output drifted from fixtures/{$name}.json" );
	}

	private function array_get( array $tree, array $path ) {
		foreach ( $path as $key ) {
			if ( ! is_array( $tree ) || ! array_key_exists( $key, $tree ) ) {
				return null;
			}
			$tree = $tree[ $key ];
		}
		return $tree;
	}

	private function array_set( array $tree, array $path, $value ) {
		$ref =& $tree;
		foreach ( array_slice( $path, 0, -1 ) as $key ) {
			if ( ! isset( $ref[ $key ] ) || ! is_array( $ref[ $key ] ) ) {
				$ref[ $key ] = array();
			}
			$ref =& $ref[ $key ];
		}
		$ref[ $path[ count( $path ) - 1 ] ] = $value;
		return $tree;
	}

	private function seed_post() {
		return wp_insert_post(
			array(
				'post_type'    => 'post',
				'post_status'  => 'publish',
				'post_title'   => 'Contract Test Post',
				'post_name'    => 'contract-test-post',
				'post_date'    => '2026-06-01 12:00:00',
				'post_excerpt' => 'A deterministic excerpt.',
				'post_content' => "<!-- wp:paragraph -->\n<p>Deterministic body prose for the contract test.</p>\n<!-- /wp:paragraph -->\n\n"
					. "<!-- wp:pullquote -->\n<figure class=\"wp-block-pullquote\"><blockquote><p>Fixed quote.</p><cite>Fixture</cite></blockquote></figure>\n<!-- /wp:pullquote -->",
			)
		);
	}

	/** Serve the seeded posts through the WorDBless posts_pre_query seam. */
	private function supply_posts( array $ids ) {
		add_filter(
			'posts_pre_query',
			function ( $pre, $query ) use ( $ids ) {
				if ( 'post' !== $query->get( 'post_type' ) ) {
					return $pre;
				}
				$posts = array_values( array_filter( array_map( 'get_post', $ids ) ) );

				$name = (string) $query->get( 'name' );
				if ( '' !== $name ) {
					$posts = array_values(
						array_filter(
							$posts,
							static function ( $p ) use ( $name ) {
								return $p->post_name === $name;
							}
						)
					);
				}
				$not_in = array_map( 'intval', (array) $query->get( 'post__not_in' ) );
				if ( $not_in ) {
					$posts = array_values(
						array_filter(
							$posts,
							static function ( $p ) use ( $not_in ) {
								return ! in_array( (int) $p->ID, $not_in, true );
							}
						)
					);
				}

				$query->found_posts   = count( $posts );
				$query->max_num_pages = $posts ? 1 : 0;

				return $posts;
			},
			10,
			2
		);
	}

	/* ---------------------------------------------------------------------
	 * Fixture assertions.
	 * ------------------------------------------------------------------ */

	public function test_blog_post_matches_fixture() {
		$id = $this->seed_post();

		$this->assert_matches_fixture(
			'blog-post',
			rgvdsa_post_to_blog_post( $id ),
			array( array( 'id' ), array( 'url' ) )
		);
	}

	public function test_single_post_matches_fixture() {
		$id = $this->seed_post();
		$this->supply_posts( array( $id ) );

		$request = new WP_REST_Request( 'GET', '/rgvdsa/v1/posts/contract-test-post' );
		$data    = rest_do_request( $request )->get_data();

		$this->assert_matches_fixture(
			'single-post',
			$data,
			array( array( 'authorAvatar' ) )
		);
	}

	public function test_posts_envelope_matches_fixture() {
		$id = $this->seed_post();
		$this->supply_posts( array( $id ) );

		$data = rest_do_request( new WP_REST_Request( 'GET', '/rgvdsa/v1/posts' ) )->get_data();

		$this->assert_matches_fixture(
			'posts-envelope',
			$data,
			array( array( 'posts', 0, 'id' ), array( 'posts', 0, 'url' ) )
		);
	}

	public function test_chapter_event_matches_fixture() {
		$id = wp_insert_post(
			array(
				'post_type'    => 'event',
				'post_status'  => 'publish',
				'post_title'   => 'Contract Test Event',
				'post_name'    => 'contract-test-event',
				'post_content' => 'March at dawn.',
			)
		);
		update_post_meta( $id, 'start_datetime', '2026-07-04 18:00:00' );
		update_post_meta( $id, 'end_datetime', '2026-07-04 20:00:00' );
		update_post_meta( $id, 'venue', 'Union Hall' );
		update_post_meta( $id, 'city', 'McAllen' );

		$this->assert_matches_fixture(
			'chapter-event',
			rgvdsa_event_to_chapter_event( $id ),
			array( array( 'id' ), array( 'url' ) )
		);
	}

	public function test_categories_envelope_matches_fixture() {
		$data = rest_do_request( new WP_REST_Request( 'GET', '/rgvdsa/v1/categories' ) )->get_data();

		$this->assert_matches_fixture( 'categories', $data );
	}
}

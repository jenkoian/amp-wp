<?php
/**
 * Test Gfycat embed.
 *
 * @package AMP.
 */

/**
 * Class AMP_Gfycat_Embed_Handler_Test
 *
 * @covers AMP_Gfycat_Embed_Handler
 */
class AMP_Gfycat_Embed_Handler_Test extends WP_UnitTestCase {

	/**
	 * Set up.
	 *
	 * @global WP_Post $post
	 */
	public function setUp() {
		global $post;
		parent::setUp();

		// Mock the HTTP request.
		add_filter(
			'pre_oembed_result',
			static function( $pre, $url ) {
				if ( in_array( 'external-http', $_SERVER['argv'], true ) ) {
					return $pre;
				}

				if ( false === strpos( $url, 'tautwhoppingcougar' ) ) {
					return $pre;
				}
				return '<iframe src=\'https://gfycat.com/ifr/tautwhoppingcougar#?secret=aBCUbiiIh5\' frameborder=\'0\' scrolling=\'no\' width=\'100\' height=\'100\'  allowfullscreen></iframe>';
			},
			10,
			2
		);

		/*
		 * As #34115 in 4.9 a post is not needed for context to run oEmbeds. Prior ot 4.9, the WP_Embed::shortcode()
		 * method would short-circuit when this is the case:
		 * https://github.com/WordPress/wordpress-develop/blob/4.8.4/src/wp-includes/class-wp-embed.php#L192-L193
		 * So on WP<4.9 we set a post global to ensure oEmbeds get processed.
		 */
		if ( version_compare( strtok( get_bloginfo( 'version' ), '-' ), '4.9', '<' ) ) {
			$post = self::factory()->post->create_and_get();
		}
	}

	/**
	 * Get conversion data.
	 *
	 * @return array
	 */
	public function get_conversion_data() {
		return [
			'no_embed'        => [
				'<p>Hello world.</p>',
				'<p>Hello world.</p>' . PHP_EOL,
			],

			'url_simple'      => [
				'https://gfycat.com/tautwhoppingcougar' . PHP_EOL,
				'<amp-gfycat data-gfyid="tautwhoppingcougar" layout="responsive" height="100" width="100"></amp-gfycat>' . PHP_EOL,
			],

			'url_with_detail' => [
				'https://gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				'<amp-gfycat data-gfyid="tautwhoppingcougar" layout="responsive" height="100" width="100"></amp-gfycat>' . PHP_EOL,
			],

			'url_with_params' => [
				'https://gfycat.com/gifs/detail/tautwhoppingcougar?foo=bar' . PHP_EOL,
				'<amp-gfycat data-gfyid="tautwhoppingcougar" layout="responsive" height="100" width="100"></amp-gfycat>' . PHP_EOL,
			],

		];
	}

	/**
	 * Test conversion.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_conversion_data
	 */
	public function test__conversion( $source, $expected ) {
		$embed = new AMP_Gfycat_Embed_Handler();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$content = AMP_DOM_Utils::get_content_from_dom( $dom );

		$this->assertEquals( $expected, $content );
	}

	/**
	 * Get scripts data.
	 *
	 * @return array
	 */
	public function get_scripts_data() {
		return [
			'not_converted' => [
				'<p>Hello World.</p>',
				[],
			],
			'converted'     => [
				'https://www.gfycat.com/gifs/detail/tautwhoppingcougar' . PHP_EOL,
				[ 'amp-gfycat' => true ],
			],
		];
	}

	/**
	 * Test scripts.
	 *
	 * @param string $source Source.
	 * @param string $expected Expected.
	 * @dataProvider get_scripts_data
	 */
	public function test__get_scripts( $source, $expected ) {
		$embed = new AMP_Gfycat_Embed_Handler();

		$filtered_content = apply_filters( 'the_content', $source );
		$dom              = AMP_DOM_Utils::get_dom_from_content( $filtered_content );
		$embed->sanitize_raw_embeds( $dom );

		$whitelist_sanitizer = new AMP_Tag_And_Attribute_Sanitizer( $dom );
		$whitelist_sanitizer->sanitize();

		$scripts = array_merge(
			$embed->get_scripts(),
			$whitelist_sanitizer->get_scripts()
		);

		$this->assertEquals( $expected, $scripts );
	}
}

<?php
/**
 * Class AMP_Gfycat_Embed_Handler
 *
 * @package AMP
 * @since 1.0
 */

use AmpProject\Dom\Document;

/**
 * Class AMP_Gfycat_Embed_Handler
 */
class AMP_Gfycat_Embed_Handler extends AMP_Base_Embed_Handler {

	/**
	 * Default AMP tag to be used when sanitizing embeds.
	 *
	 * @var string
	 */
	protected $amp_tag = 'amp-gfycat';

	/**
	 * Base URL used for identifying embeds.
	 *
	 * @var string
	 */
	protected $base_embed_url = 'https://gfycat.com/ifr/';

	/**
	 * Make embed AMP compatible.
	 *
	 * @param DOMElement $node DOM element.
	 */
	protected function sanitize_raw_embed( DOMElement $node ) {
		$iframe_src = $node->getAttribute( 'src' );

		$gfycat_id = strtok( substr( $iframe_src, strlen( $this->base_embed_url ) ), '/?#' );
		if ( empty( $gfycat_id ) ) {
			// Nothing to do if the ID could not be found.
			return;
		}

		$attributes = [
			'data-gfyid' => $gfycat_id,
			'layout'     => 'responsive',
			'height'     => $this->args['height'],
			'width'      => $this->args['width'],
		];

		if ( $node->hasAttribute( 'width' ) ) {
			$attributes['width'] = $node->getAttribute( 'width' );
		}

		if ( $node->hasAttribute( 'height' ) ) {
			$attributes['height'] = $node->getAttribute( 'height' );
		}

		$amp_node = AMP_DOM_Utils::create_node(
			Document::fromNode( $node ),
			'amp-gfycat',
			$attributes
		);

		$this->maybe_unwrap_p_element( $node );

		$node->parentNode->replaceChild( $amp_node, $node );
	}
}


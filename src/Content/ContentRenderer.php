<?php
/**
 * Content renderer foundation.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

use IDB\Content\Contracts\ContentInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Defines future rendering extension points without producing output.
 */
final class ContentRenderer {

	/**
	 * Render a content object.
	 *
	 * Rendering is intentionally inert in this foundation sprint. Future
	 * implementations can filter the returned markup without changing the
	 * renderer API.
	 *
	 * @param ContentInterface|null $content Content object.
	 * @param array<string,mixed>   $context Future render context.
	 *
	 * @return string
	 */
	public function render( ?ContentInterface $content = null, array $context = array() ): string {
		/**
		 * Filters LSOS content rendering output.
		 *
		 * @param string                $output   Rendered output.
		 * @param ContentInterface|null $content  Content object.
		 * @param array<string,mixed>   $context  Future render context.
		 * @param ContentRenderer       $renderer Renderer instance.
		 */
		return (string) apply_filters( 'lsos/content/render', '', $content, $context, $this );
	}

	/**
	 * Check whether the renderer has content to output.
	 *
	 * @param ContentInterface|null $content Content object.
	 *
	 * @return bool
	 */
	public function can_render( ?ContentInterface $content = null ): bool {
		/**
		 * Filters whether LSOS content can render.
		 *
		 * @param bool                  $can_render Whether content can render.
		 * @param ContentInterface|null $content    Content object.
		 * @param ContentRenderer       $renderer   Renderer instance.
		 */
		return (bool) apply_filters( 'lsos/content/can_render', false, $content, $this );
	}
}

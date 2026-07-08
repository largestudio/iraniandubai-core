<?php
/**
 * Content resolver foundation.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Provides the future API for resolving LSOS content contexts.
 */
final class ContentResolver {

	/**
	 * Resolve the current content context.
	 *
	 * This method intentionally returns an empty context until future sprints
	 * implement full context detection.
	 *
	 * @return string
	 */
	public function resolve_context(): string {
		/**
		 * Filters the resolved LSOS content context.
		 *
		 * @param string          $context  Resolved context identifier.
		 * @param ContentResolver $resolver Resolver instance.
		 */
		return (string) apply_filters( 'lsos/content/resolve_context', '', $this );
	}

	/**
	 * Check whether the current request should resolve as blog content.
	 *
	 * @return bool
	 */
	public function is_blog(): bool {
		return $this->matches_context( 'blog' );
	}

	/**
	 * Check whether the current request should resolve as archive content.
	 *
	 * @return bool
	 */
	public function is_archive(): bool {
		return $this->matches_context( 'archive' );
	}

	/**
	 * Check whether the current request should resolve as search content.
	 *
	 * @return bool
	 */
	public function is_search(): bool {
		return $this->matches_context( 'search' );
	}

	/**
	 * Check whether the current request should resolve as single content.
	 *
	 * @return bool
	 */
	public function is_single(): bool {
		return $this->matches_context( 'single' );
	}

	/**
	 * Check whether the current request should resolve as category content.
	 *
	 * @return bool
	 */
	public function is_category(): bool {
		return $this->matches_context( 'category' );
	}

	/**
	 * Check whether the current request should resolve as landing content.
	 *
	 * @return bool
	 */
	public function is_landing(): bool {
		return $this->matches_context( 'landing' );
	}

	/**
	 * Check whether the current request should resolve as 404 content.
	 *
	 * @return bool
	 */
	public function is_404(): bool {
		return $this->matches_context( '404' );
	}

	/**
	 * Compare the resolved context with an expected context.
	 *
	 * @param string $context Expected context identifier.
	 *
	 * @return bool
	 */
	private function matches_context( string $context ): bool {
		return sanitize_key( $context ) === sanitize_key( $this->resolve_context() );
	}
}

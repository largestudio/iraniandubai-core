<?php
/**
 * Dynamic content matcher.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Selects the best content entity for the current contexts.
 */
final class ContentMatcher {

	/**
	 * Selectable content statuses.
	 *
	 * @var array<int,string>
	 */
	private const SELECTABLE_STATUSES = array( 'publish', 'published', 'active', 'enabled' );

	/**
	 * Match the best content entity for the provided contexts.
	 *
	 * @param array<int,Content> $contents Content entities.
	 * @param array<mixed>       $contexts Current contexts.
	 *
	 * @return ContentMatch|null
	 */
	public function match( array $contents, array $contexts = array() ): ?ContentMatch {
		$current_contexts = $this->normalize_contexts( $contexts );
		$best_match       = null;

		foreach ( $contents as $content ) {
			if ( ! $content instanceof Content || ! $this->is_selectable( $content ) ) {
				continue;
			}

			$candidate_contexts = $this->get_content_contexts( $content );
			$matched_contexts   = $this->get_matched_contexts( $candidate_contexts, $current_contexts );

			if ( array() !== $candidate_contexts && array() === $matched_contexts ) {
				continue;
			}

			$match = new ContentMatch(
				$content,
				$this->calculate_score( $content, $matched_contexts ),
				$matched_contexts
			);

			if ( null === $best_match || $match->get_score() > $best_match->get_score() ) {
				$best_match = $match;
			}
		}

		return $best_match;
	}

	/**
	 * Check whether a content entity can be selected.
	 *
	 * @param Content $content Content entity.
	 *
	 * @return bool
	 */
	private function is_selectable( Content $content ): bool {
		return in_array( strtolower( $content->get_status() ), self::SELECTABLE_STATUSES, true );
	}

	/**
	 * Calculate a deterministic match score.
	 *
	 * @param Content           $content          Content entity.
	 * @param array<int,string> $matched_contexts Matched contexts.
	 *
	 * @return int
	 */
	private function calculate_score( Content $content, array $matched_contexts ): int {
		return 100000 + ( count( $matched_contexts ) * 1000 ) + $content->get_priority();
	}

	/**
	 * Get all declared contexts for a content entity.
	 *
	 * @param Content $content Content entity.
	 *
	 * @return array<int,string>
	 */
	private function get_content_contexts( Content $content ): array {
		$conditions = $content->get_conditions();
		$contexts   = array();

		if ( '' !== $content->get_context() ) {
			$contexts[] = $content->get_context();
		}

		if ( isset( $conditions['context'] ) ) {
			$contexts = array_merge( $contexts, $this->normalize_contexts( (array) $conditions['context'] ) );
		}

		if ( isset( $conditions['contexts'] ) && is_array( $conditions['contexts'] ) ) {
			$contexts = array_merge( $contexts, $this->normalize_contexts( $conditions['contexts'] ) );
		}

		return $this->normalize_contexts( $contexts );
	}

	/**
	 * Get contexts shared by content and current runtime contexts.
	 *
	 * @param array<int,string> $candidate_contexts Candidate contexts.
	 * @param array<int,string> $current_contexts   Current runtime contexts.
	 *
	 * @return array<int,string>
	 */
	private function get_matched_contexts( array $candidate_contexts, array $current_contexts ): array {
		return array_values( array_intersect( $candidate_contexts, $current_contexts ) );
	}

	/**
	 * Normalize context values to unique string identifiers.
	 *
	 * @param array<mixed> $contexts Context values.
	 *
	 * @return array<int,string>
	 */
	private function normalize_contexts( array $contexts ): array {
		$normalized = array();

		foreach ( $contexts as $key => $value ) {
			if ( is_string( $key ) && is_bool( $value ) ) {
				if ( true === $value ) {
					$normalized[] = $key;
				}

				continue;
			}

			if ( is_scalar( $value ) ) {
				$context = trim( (string) $value );

				if ( '' !== $context ) {
					$normalized[] = $context;
				}
			}
		}

		return array_values( array_unique( $normalized ) );
	}
}

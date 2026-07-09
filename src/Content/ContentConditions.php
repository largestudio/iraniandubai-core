<?php
/**
 * Content conditions foundation.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

use IDB\Content\Conditions\ConditionRegistry;
use IDB\Content\Conditions\WordPressConditionProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Public facade for content condition evaluation.
 */
final class ContentConditions {

	/**
	 * Condition registry.
	 *
	 * @var ConditionRegistry
	 */
	private ConditionRegistry $registry;

	/**
	 * Constructor.
	 *
	 * @param ConditionRegistry|null $registry Optional condition registry.
	 */
	public function __construct( ?ConditionRegistry $registry = null ) {
		$this->registry = $registry ?? new ConditionRegistry();

		if ( null === $registry ) {
			( new WordPressConditionProvider() )->register( $this->registry );
		}
	}

	/**
	 * Determine whether a condition set matches the current content context.
	 *
	 * @param array<string,mixed> $conditions Future condition configuration.
	 * @param array<string,mixed> $context    Future resolved context payload.
	 *
	 * @return bool
	 */
	public function matches( array $conditions = array(), array $context = array() ): bool {
		$normalized = $this->normalize( $conditions );
		$matches    = $this->registry->matchAll( $this->condition_ids( $normalized, $context ) );

		/**
		 * Filters the content condition result.
		 *
		 * @param bool                $matches    Whether conditions match.
		 * @param array<string,mixed> $conditions Condition configuration.
		 * @param array<string,mixed> $context    Resolved context payload.
		 * @param ContentConditions   $conditions_service Conditions service.
		 */
		return (bool) apply_filters( 'lsos/content/conditions/matches', $matches, $normalized, $context, $this );
	}

	/**
	 * Normalize future condition data into a predictable array.
	 *
	 * @param mixed $conditions Raw condition data.
	 *
	 * @return array<string,mixed>
	 */
	public function normalize( mixed $conditions ): array {
		return is_array( $conditions ) ? $conditions : array();
	}

	/**
	 * Get the condition registry.
	 *
	 * @return ConditionRegistry
	 */
	public function registry(): ConditionRegistry {
		return $this->registry;
	}

	/**
	 * Extract condition identifiers from a condition payload.
	 *
	 * @param array<string,mixed> $conditions Condition configuration.
	 * @param array<string,mixed> $context    Resolved context payload.
	 *
	 * @return array<int|string,mixed>
	 */
	private function condition_ids( array $conditions, array $context ): array {
		if ( isset( $conditions['conditions'] ) && is_array( $conditions['conditions'] ) ) {
			return $conditions['conditions'];
		}

		if ( isset( $conditions['contexts'] ) && is_array( $conditions['contexts'] ) ) {
			return $conditions['contexts'];
		}

		if ( isset( $conditions['context'] ) ) {
			return (array) $conditions['context'];
		}

		if ( isset( $context['conditions'] ) && is_array( $context['conditions'] ) ) {
			return $context['conditions'];
		}

		return $conditions;
	}
}

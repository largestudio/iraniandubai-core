<?php
/**
 * Content resolver foundation.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

use IDB\Content\Conditions\ConditionInterface;
use IDB\Content\Conditions\ConditionRegistry;
use IDB\Content\Conditions\WordPressConditionProvider;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Resolves future LSOS content contexts.
 */
final class ContentResolver {

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
	 * Get the primary resolved content context.
	 *
	 * @return string
	 */
	public function context(): string {
		$contexts = $this->contexts();

		return $contexts[0] ?? '';
	}

	/**
	 * Get all resolved content context identifiers.
	 *
	 * @return string[]
	 */
	public function contexts(): array {
		$contexts = array_keys(
			array_filter(
				$this->registry->all(),
				static function ( ConditionInterface $condition ): bool {
					return $condition->matches();
				}
			)
		);

		/**
		 * Filters resolved LSOS content contexts.
		 *
		 * @param string[]        $contexts Resolved context identifiers.
		 * @param ContentResolver $resolver Resolver instance.
		 */
		$contexts = apply_filters( 'lsos/content/contexts', $contexts, $this );

		if ( ! is_array( $contexts ) ) {
			$contexts = array();
		}

		$contexts = array_values(
			array_unique(
				array_filter(
					array_map(
						static function ( mixed $context ): string {
							return sanitize_key( (string) $context );
						},
						$contexts
					)
				)
			)
		);

		/**
		 * Filters the primary resolved LSOS content context.
		 *
		 * @param string          $context  Primary context identifier.
		 * @param string[]        $contexts Resolved context identifiers.
		 * @param ContentResolver $resolver Resolver instance.
		 */
		$primary = sanitize_key( (string) apply_filters( 'lsos/content/context', $contexts[0] ?? '', $contexts, $this ) );

		if ( '' !== $primary && ! in_array( $primary, $contexts, true ) ) {
			array_unshift( $contexts, $primary );
		}

		return $contexts;
	}

	/**
	 * Check whether a context exists in the resolved context list.
	 *
	 * @param string $context Expected context identifier.
	 *
	 * @return bool
	 */
	public function matches( string $context ): bool {
		$context = sanitize_key( $context );

		if ( '' === $context ) {
			return false;
		}

		return in_array( $context, $this->contexts(), true );
	}

	/**
	 * Get the condition registry.
	 *
	 * @return ConditionRegistry
	 */
	public function registry(): ConditionRegistry {
		return $this->registry;
	}
}

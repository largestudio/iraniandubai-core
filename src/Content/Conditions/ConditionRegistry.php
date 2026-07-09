<?php
/**
 * Content condition registry.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content\Conditions;

/**
 * Stores and evaluates registered content conditions.
 */
final class ConditionRegistry {

	/**
	 * Registered conditions.
	 *
	 * @var array<string,ConditionInterface>
	 */
	private array $conditions = array();

	/**
	 * Register a condition.
	 *
	 * @param ConditionInterface $condition Condition instance.
	 *
	 * @return void
	 */
	public function register( ConditionInterface $condition ): void {
		$id = $this->normalize_id( $condition->id() );

		if ( '' === $id ) {
			return;
		}

		$this->conditions[ $id ] = $condition;
	}

	/**
	 * Get all registered conditions.
	 *
	 * @return array<string,ConditionInterface>
	 */
	public function all(): array {
		return $this->conditions;
	}

	/**
	 * Find a registered condition.
	 *
	 * @param string $id Condition identifier.
	 *
	 * @return ConditionInterface|null
	 */
	public function find( string $id ): ?ConditionInterface {
		$id = $this->normalize_id( $id );

		return $this->conditions[ $id ] ?? null;
	}

	/**
	 * Determine whether all requested conditions match.
	 *
	 * @param array<int|string,mixed> $condition_ids Condition identifiers.
	 *
	 * @return bool
	 */
	public function matchAll( array $condition_ids ): bool {
		$condition_ids = $this->normalize_ids( $condition_ids );

		if ( array() === $condition_ids ) {
			return true;
		}

		foreach ( $condition_ids as $condition_id ) {
			$condition = $this->find( $condition_id );

			if ( null === $condition || ! $condition->matches() ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Normalize a list of condition identifiers.
	 *
	 * @param array<int|string,mixed> $condition_ids Condition identifiers.
	 *
	 * @return array<int,string>
	 */
	private function normalize_ids( array $condition_ids ): array {
		$ids = array();

		foreach ( $condition_ids as $key => $value ) {
			if ( is_string( $key ) && is_bool( $value ) ) {
				if ( true === $value ) {
					$ids[] = $this->normalize_id( $key );
				}

				continue;
			}

			if ( is_scalar( $value ) ) {
				$ids[] = $this->normalize_id( (string) $value );
			}
		}

		return array_values( array_filter( array_unique( $ids ) ) );
	}

	/**
	 * Normalize one condition identifier.
	 *
	 * @param string $id Condition identifier.
	 *
	 * @return string
	 */
	private function normalize_id( string $id ): string {
		return strtolower( trim( $id ) );
	}
}

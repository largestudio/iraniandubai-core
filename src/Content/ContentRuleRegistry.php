<?php
/**
 * Content rule registry.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Stores content rules without persistence.
 */
final class ContentRuleRegistry {

	/**
	 * Registered content rules.
	 *
	 * @var array<string,ContentRule>
	 */
	private array $rules = array();

	/**
	 * Register a content rule.
	 *
	 * @param ContentRule $rule Content rule.
	 *
	 * @return bool
	 */
	public function register( ContentRule $rule ): bool {
		$id = $rule->get_id();

		if ( '' === $id ) {
			return false;
		}

		$this->rules[ $id ] = $rule;

		return true;
	}

	/**
	 * Check whether a rule is registered.
	 *
	 * @param string $id Rule identifier.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->rules[ $id ] );
	}

	/**
	 * Get one registered rule.
	 *
	 * @param string $id Rule identifier.
	 *
	 * @return ContentRule|null
	 */
	public function get( string $id ): ?ContentRule {
		return $this->rules[ $id ] ?? null;
	}

	/**
	 * Get all registered rules.
	 *
	 * @return array<string,ContentRule>
	 */
	public function all(): array {
		return $this->rules;
	}

	/**
	 * Remove a registered rule.
	 *
	 * @param string $id Rule identifier.
	 *
	 * @return bool
	 */
	public function remove( string $id ): bool {
		if ( ! isset( $this->rules[ $id ] ) ) {
			return false;
		}

		unset( $this->rules[ $id ] );

		return true;
	}

	/**
	 * Get all active rules.
	 *
	 * @return array<string,ContentRule>
	 */
	public function active(): array {
		return array_filter(
			$this->rules,
			static function ( ContentRule $rule ): bool {
				return 'active' === strtolower( $rule->get_status() );
			}
		);
	}
}

<?php
/**
 * Content condition contract.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content\Conditions;

/**
 * Defines a single content condition.
 */
interface ConditionInterface {

	/**
	 * Get the condition identifier.
	 *
	 * @return string
	 */
	public function id(): string;

	/**
	 * Determine whether the condition matches the current request.
	 *
	 * @return bool
	 */
	public function matches(): bool;
}

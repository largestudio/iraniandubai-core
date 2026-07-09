<?php
/**
 * WordPress content condition provider.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content\Conditions;

/**
 * Registers built-in WordPress request conditions.
 */
final class WordPressConditionProvider {

	/**
	 * Register built-in WordPress conditions.
	 *
	 * @param ConditionRegistry $registry Condition registry.
	 *
	 * @return void
	 */
	public function register( ConditionRegistry $registry ): void {
		$conditions = array(
			'single'     => 'is_single',
			'page'       => 'is_page',
			'archive'    => 'is_archive',
			'category'   => 'is_category',
			'tag'        => 'is_tag',
			'search'     => 'is_search',
			'404'        => 'is_404',
			'front-page' => 'is_front_page',
			'home'       => 'is_home',
		);

		foreach ( $conditions as $id => $callback ) {
			$registry->register(
				new class( $id, $callback ) implements ConditionInterface {

					/**
					 * Condition identifier.
					 *
					 * @var string
					 */
					private string $id;

					/**
					 * WordPress conditional tag callback.
					 *
					 * @var callable-string
					 */
					private string $callback;

					/**
					 * Constructor.
					 *
					 * @param string          $id       Condition identifier.
					 * @param callable-string $callback WordPress conditional tag callback.
					 */
					public function __construct( string $id, string $callback ) {
						$this->id       = $id;
						$this->callback = $callback;
					}

					/**
					 * Get the condition identifier.
					 *
					 * @return string
					 */
					public function id(): string {
						return $this->id;
					}

					/**
					 * Determine whether the WordPress condition matches.
					 *
					 * @return bool
					 */
					public function matches(): bool {
						return function_exists( $this->callback ) && (bool) call_user_func( $this->callback );
					}
				}
			);
		}
	}
}

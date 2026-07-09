<?php
/**
 * Content rule matcher.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Matches a content rule against a content match and resolved context.
 */
final class ContentRuleMatcher {
	/**
	 * Match a rule against content and context.
	 *
	 * @param ContentRule  $rule             Content rule.
	 * @param ContentMatch $contentMatch     Content match.
	 * @param string       $resolvedContext  Resolved context string.
	 *
	 * @return ContentRuleMatch|null
	 */
	public function match(
		ContentRule $rule,
		ContentMatch $contentMatch,
		string $resolvedContext
	): ?ContentRuleMatch {
		if ( $rule->get_context() !== $resolvedContext ) {
			return null;
		}

		if ( 'active' !== strtolower( $rule->get_status() ) ) {
			return null;
		}

		$score             = 0;
		$conditions        = $rule->get_conditions();
		$matchedConditions = array();

		$score += 40;
		$score += 30;

		if ( $this->conditions_match( $conditions ) ) {
			$score            += 20;
			$matchedConditions = $conditions;
		}

		$score += $rule->get_priority();

		return new ContentRuleMatch(
			$rule,
			$contentMatch->get_content(),
			(float) $score,
			$matchedConditions,
			array(
				'resolved_context'    => $resolvedContext,
				'content_match_score' => $contentMatch->get_score(),
				'matched_contexts'    => $contentMatch->get_matched_contexts(),
			)
		);
	}

	/**
	 * Determine whether a simple condition payload is matched.
	 *
	 * @param array<string,mixed> $conditions Rule conditions.
	 *
	 * @return bool
	 */
	private function conditions_match( array $conditions ): bool {
		if ( array() === $conditions ) {
			return true;
		}

		foreach ( $conditions as $condition ) {
			if ( ! $this->condition_matches( $condition ) ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * Determine whether one simple condition value is matched.
	 *
	 * @param mixed $condition Condition value.
	 *
	 * @return bool
	 */
	private function condition_matches( mixed $condition ): bool {
		if ( is_array( $condition ) && array_key_exists( 'matched', $condition ) ) {
			return true === $condition['matched'];
		}

		if ( is_array( $condition ) && array_key_exists( 'match', $condition ) ) {
			return true === $condition['match'];
		}

		return (bool) $condition;
	}
}

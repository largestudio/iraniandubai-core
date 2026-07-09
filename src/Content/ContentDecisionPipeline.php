<?php
/**
 * Content decision pipeline.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Selects the best content rule match deterministically.
 */
final class ContentDecisionPipeline {
	/**
	 * Decide the best content rule match for a context.
	 *
	 * @param array<int,ContentRuleMatch> $ruleMatches Rule matches.
	 * @param string                      $context     Resolved context.
	 *
	 * @return ContentDecision
	 */
	public function decide( array $ruleMatches, string $context ): ContentDecision {
		$bestMatch = null;
		$bestIndex = null;

		foreach ( $ruleMatches as $index => $ruleMatch ) {
			if ( ! $ruleMatch instanceof ContentRuleMatch ) {
				continue;
			}

			if ( null === $bestMatch || $this->is_better_match( $ruleMatch, $bestMatch ) ) {
				$bestMatch = $ruleMatch;
				$bestIndex = $index;
			}
		}

		if ( null === $bestMatch ) {
			return new ContentDecision(
				null,
				null,
				$context,
				0.0,
				'no_rule_matches',
				array(
					'candidate_count' => count( $ruleMatches ),
				)
			);
		}

		return new ContentDecision(
			$this->create_selection( $bestMatch ),
			$bestMatch,
			$context,
			$bestMatch->getScore(),
			'rule_match_selected',
			array(
				'selected_index'    => $bestIndex,
				'content_priority' => $this->get_content_priority( $bestMatch ),
				'rule_priority'    => $this->get_rule_priority( $bestMatch ),
				'candidate_count'  => count( $ruleMatches ),
			)
		);
	}

	/**
	 * Determine whether a candidate outranks the current best match.
	 *
	 * @param ContentRuleMatch $candidate Candidate match.
	 * @param ContentRuleMatch $current   Current best match.
	 *
	 * @return bool
	 */
	private function is_better_match( ContentRuleMatch $candidate, ContentRuleMatch $current ): bool {
		if ( $candidate->getScore() !== $current->getScore() ) {
			return $candidate->getScore() > $current->getScore();
		}

		$candidateContentPriority = $this->get_content_priority( $candidate );
		$currentContentPriority   = $this->get_content_priority( $current );

		if ( $candidateContentPriority !== $currentContentPriority ) {
			return $candidateContentPriority > $currentContentPriority;
		}

		$candidateRulePriority = $this->get_rule_priority( $candidate );
		$currentRulePriority   = $this->get_rule_priority( $current );

		if ( $candidateRulePriority !== $currentRulePriority ) {
			return $candidateRulePriority > $currentRulePriority;
		}

		return false;
	}

	/**
	 * Create a content selection from a rule match when possible.
	 *
	 * @param ContentRuleMatch $ruleMatch Rule match.
	 *
	 * @return ContentSelection|null
	 */
	private function create_selection( ContentRuleMatch $ruleMatch ): ?ContentSelection {
		$content = $ruleMatch->getContent();

		if ( ! $content instanceof Content ) {
			return null;
		}

		return new ContentSelection(
			new ContentMatch(
				$content,
				(int) round( $ruleMatch->getScore() ),
				$this->get_matched_contexts( $ruleMatch )
			)
		);
	}

	/**
	 * Get content priority from a rule match.
	 *
	 * @param ContentRuleMatch $ruleMatch Rule match.
	 *
	 * @return int
	 */
	private function get_content_priority( ContentRuleMatch $ruleMatch ): int {
		$content = $ruleMatch->getContent();

		return $content instanceof Content ? $content->get_priority() : 0;
	}

	/**
	 * Get rule priority from a rule match.
	 *
	 * @param ContentRuleMatch $ruleMatch Rule match.
	 *
	 * @return int
	 */
	private function get_rule_priority( ContentRuleMatch $ruleMatch ): int {
		$rule = $ruleMatch->getRule();

		return $rule instanceof ContentRule ? $rule->get_priority() : 0;
	}

	/**
	 * Get matched contexts from rule match metadata.
	 *
	 * @param ContentRuleMatch $ruleMatch Rule match.
	 *
	 * @return array<int,string>
	 */
	private function get_matched_contexts( ContentRuleMatch $ruleMatch ): array {
		$context         = $ruleMatch->getContext();
		$matchedContexts = $context['matched_contexts'] ?? array();

		if ( ! is_array( $matchedContexts ) ) {
			return array();
		}

		return array_values(
			array_filter(
				$matchedContexts,
				static fn( mixed $matchedContext ): bool => is_string( $matchedContext )
			)
		);
	}
}

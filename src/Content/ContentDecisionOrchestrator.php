<?php
/**
 * Content decision orchestrator.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Coordinates rule matching and decision selection.
 */
final class ContentDecisionOrchestrator {
	/**
	 * Content rule matcher.
	 *
	 * @var ContentRuleMatcher
	 */
	private ContentRuleMatcher $matcher;

	/**
	 * Content decision pipeline.
	 *
	 * @var ContentDecisionPipeline
	 */
	private ContentDecisionPipeline $pipeline;

	/**
	 * Constructor.
	 *
	 * @param ContentRuleMatcher      $matcher  Content rule matcher.
	 * @param ContentDecisionPipeline $pipeline Content decision pipeline.
	 */
	public function __construct(
		ContentRuleMatcher $matcher,
		ContentDecisionPipeline $pipeline
	) {
		$this->matcher  = $matcher;
		$this->pipeline = $pipeline;
	}

	/**
	 * Decide the best content decision for rules, matches, and context.
	 *
	 * @param array<int,ContentRule>  $rules   Content rules.
	 * @param array<int,ContentMatch> $matches Content matches.
	 * @param string                  $context Resolved context.
	 *
	 * @return ContentDecision
	 */
	public function decide(
		array $rules,
		array $matches,
		string $context
	): ContentDecision {
		$ruleMatches = array();

		foreach ( $rules as $rule ) {
			if ( ! $rule instanceof ContentRule ) {
				continue;
			}

			foreach ( $matches as $match ) {
				if ( ! $match instanceof ContentMatch ) {
					continue;
				}

				$ruleMatch = $this->matcher->match( $rule, $match, $context );

				if ( $ruleMatch instanceof ContentRuleMatch ) {
					$ruleMatches[] = $ruleMatch;
				}
			}
		}

		return $this->pipeline->decide( $ruleMatches, $context );
	}
}

<?php
/**
 * Immutable content rule match value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Stores the result of matching a rule against content.
 */
final class ContentRuleMatch {
	/**
	 * Matched rule.
	 *
	 * @var mixed
	 */
	private readonly mixed $rule;

	/**
	 * Matched content.
	 *
	 * @var mixed
	 */
	private readonly mixed $content;

	/**
	 * Match score.
	 *
	 * @var float
	 */
	private readonly float $score;

	/**
	 * Conditions that matched.
	 *
	 * @var array<int|string,mixed>
	 */
	private readonly array $matchedConditions;

	/**
	 * Additional match context.
	 *
	 * @var array<int|string,mixed>
	 */
	private readonly array $context;

	/**
	 * Constructor.
	 *
	 * @param mixed                   $rule              Matched rule.
	 * @param mixed                   $content           Matched content.
	 * @param float                   $score             Match score.
	 * @param array<int|string,mixed> $matchedConditions Conditions that matched.
	 * @param array<int|string,mixed> $context           Additional match context.
	 */
	public function __construct(
		mixed $rule,
		mixed $content,
		float $score,
		array $matchedConditions = array(),
		array $context = array()
	) {
		$this->rule              = $rule;
		$this->content           = $content;
		$this->score             = $score;
		$this->matchedConditions = $matchedConditions;
		$this->context           = $context;
	}

	/**
	 * Get matched rule.
	 *
	 * @return mixed
	 */
	public function getRule(): mixed {
		return $this->rule;
	}

	/**
	 * Get matched content.
	 *
	 * @return mixed
	 */
	public function getContent(): mixed {
		return $this->content;
	}

	/**
	 * Get match score.
	 *
	 * @return float
	 */
	public function getScore(): float {
		return $this->score;
	}

	/**
	 * Get matched conditions.
	 *
	 * @return array<int|string,mixed>
	 */
	public function getMatchedConditions(): array {
		return $this->matchedConditions;
	}

	/**
	 * Get additional match context.
	 *
	 * @return array<int|string,mixed>
	 */
	public function getContext(): array {
		return $this->context;
	}
}

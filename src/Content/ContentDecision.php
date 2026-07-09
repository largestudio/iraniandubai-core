<?php
/**
 * Content decision value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Represents the final content decision result.
 */
final class ContentDecision {
	/**
	 * Selected content.
	 *
	 * @var ContentSelection|null
	 */
	private readonly ?ContentSelection $selection;

	/**
	 * Selected rule match.
	 *
	 * @var ContentRuleMatch|null
	 */
	private readonly ?ContentRuleMatch $ruleMatch;

	/**
	 * Resolved decision context.
	 *
	 * @var string
	 */
	private readonly string $context;

	/**
	 * Decision score.
	 *
	 * @var float
	 */
	private readonly float $score;

	/**
	 * Decision reason.
	 *
	 * @var string
	 */
	private readonly string $reason;

	/**
	 * Decision metadata.
	 *
	 * @var array<string,mixed>
	 */
	private readonly array $meta;

	/**
	 * Constructor.
	 *
	 * @param ContentSelection|null $selection Selected content.
	 * @param ContentRuleMatch|null $ruleMatch Selected rule match.
	 * @param string                $context   Resolved decision context.
	 * @param float                 $score     Decision score.
	 * @param string                $reason    Decision reason.
	 * @param array<string,mixed>   $meta      Decision metadata.
	 */
	public function __construct(
		?ContentSelection $selection,
		?ContentRuleMatch $ruleMatch,
		string $context,
		float $score,
		string $reason,
		array $meta = array()
	) {
		$this->selection = $selection;
		$this->ruleMatch = $ruleMatch;
		$this->context   = $context;
		$this->score     = $score;
		$this->reason    = $reason;
		$this->meta      = $meta;
	}

	/**
	 * Get selected content.
	 *
	 * @return ContentSelection|null
	 */
	public function get_selection(): ?ContentSelection {
		return $this->selection;
	}

	/**
	 * Get selected rule match.
	 *
	 * @return ContentRuleMatch|null
	 */
	public function get_rule_match(): ?ContentRuleMatch {
		return $this->ruleMatch;
	}

	/**
	 * Get resolved decision context.
	 *
	 * @return string
	 */
	public function get_context(): string {
		return $this->context;
	}

	/**
	 * Get decision score.
	 *
	 * @return float
	 */
	public function get_score(): float {
		return $this->score;
	}

	/**
	 * Get decision reason.
	 *
	 * @return string
	 */
	public function get_reason(): string {
		return $this->reason;
	}

	/**
	 * Get decision metadata.
	 *
	 * @return array<string,mixed>
	 */
	public function get_meta(): array {
		return $this->meta;
	}
}

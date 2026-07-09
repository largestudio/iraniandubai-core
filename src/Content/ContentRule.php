<?php
/**
 * Content rule value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Represents an immutable content selection rule.
 */
final class ContentRule {

	/**
	 * Rule identifier.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Related content identifier.
	 *
	 * @var string
	 */
	private string $content_id;

	/**
	 * Rule context.
	 *
	 * @var string
	 */
	private string $context;

	/**
	 * Rule status.
	 *
	 * @var string
	 */
	private string $status;

	/**
	 * Rule priority.
	 *
	 * @var int
	 */
	private int $priority;

	/**
	 * Rule conditions.
	 *
	 * @var array<string,mixed>
	 */
	private array $conditions;

	/**
	 * Rule metadata.
	 *
	 * @var array<string,mixed>
	 */
	private array $meta;

	/**
	 * Constructor.
	 *
	 * @param string              $id         Rule identifier.
	 * @param string              $content_id Related content identifier.
	 * @param string              $context    Rule context.
	 * @param string              $status     Rule status.
	 * @param int                 $priority   Rule priority.
	 * @param array<string,mixed> $conditions Rule conditions.
	 * @param array<string,mixed> $meta       Rule metadata.
	 */
	public function __construct(
		string $id,
		string $content_id,
		string $context,
		string $status,
		int $priority = 10,
		array $conditions = array(),
		array $meta = array()
	) {
		$this->id         = $id;
		$this->content_id = $content_id;
		$this->context    = $context;
		$this->status     = $status;
		$this->priority   = $priority;
		$this->conditions = $conditions;
		$this->meta       = $meta;
	}

	/**
	 * Get the rule identifier.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the related content identifier.
	 *
	 * @return string
	 */
	public function get_content_id(): string {
		return $this->content_id;
	}

	/**
	 * Get the rule context.
	 *
	 * @return string
	 */
	public function get_context(): string {
		return $this->context;
	}

	/**
	 * Get the rule status.
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get the rule priority.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * Get the rule conditions.
	 *
	 * @return array<string,mixed>
	 */
	public function get_conditions(): array {
		return $this->conditions;
	}

	/**
	 * Get the rule metadata.
	 *
	 * @return array<string,mixed>
	 */
	public function get_meta(): array {
		return $this->meta;
	}
}

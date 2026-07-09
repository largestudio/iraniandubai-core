<?php
/**
 * Content execution value object.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Represents the result of preparing a content decision for execution.
 */
final class ContentExecution {
	/**
	 * Source content decision.
	 *
	 * @var ContentDecision
	 */
	private readonly ContentDecision $decision;

	/**
	 * Execution status.
	 *
	 * @var string
	 */
	private readonly string $status;

	/**
	 * Execution context.
	 *
	 * @var string
	 */
	private readonly string $context;

	/**
	 * Execution output payload.
	 *
	 * @var mixed
	 */
	private readonly mixed $output;

	/**
	 * Execution metadata.
	 *
	 * @var array<string,mixed>
	 */
	private readonly array $meta;

	/**
	 * Constructor.
	 *
	 * @param ContentDecision    $decision Source content decision.
	 * @param string             $status   Execution status.
	 * @param string             $context  Execution context.
	 * @param mixed              $output   Execution output payload.
	 * @param array<string,mixed> $meta     Execution metadata.
	 */
	public function __construct(
		ContentDecision $decision,
		string $status,
		string $context,
		mixed $output = null,
		array $meta = array()
	) {
		$this->decision = $decision;
		$this->status   = $status;
		$this->context  = $context;
		$this->output   = $output;
		$this->meta     = $meta;
	}

	/**
	 * Get source content decision.
	 *
	 * @return ContentDecision
	 */
	public function get_decision(): ContentDecision {
		return $this->decision;
	}

	/**
	 * Get execution status.
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get execution context.
	 *
	 * @return string
	 */
	public function get_context(): string {
		return $this->context;
	}

	/**
	 * Get execution output payload.
	 *
	 * @return mixed
	 */
	public function get_output(): mixed {
		return $this->output;
	}

	/**
	 * Get execution metadata.
	 *
	 * @return array<string,mixed>
	 */
	public function get_meta(): array {
		return $this->meta;
	}
}

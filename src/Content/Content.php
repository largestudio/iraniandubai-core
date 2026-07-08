<?php
/**
 * Generic LSOS content entity.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

use IDB\Content\Contracts\ContentInterface;

/**
 * Represents an immutable LSOS content object.
 */
final class Content implements ContentInterface {

	/**
	 * Content identifier.
	 *
	 * @var string
	 */
	private string $id;

	/**
	 * Content type.
	 *
	 * @var string
	 */
	private string $type;

	/**
	 * Content title.
	 *
	 * @var string
	 */
	private string $title;

	/**
	 * Content status.
	 *
	 * @var string
	 */
	private string $status;

	/**
	 * Content priority.
	 *
	 * @var int
	 */
	private int $priority;

	/**
	 * Content metadata.
	 *
	 * @var array<string,mixed>
	 */
	private array $meta;

	/**
	 * Content display conditions.
	 *
	 * @var array<string,mixed>
	 */
	private array $conditions;

	/**
	 * Constructor.
	 *
	 * @param string              $id         Content identifier.
	 * @param string              $type       Content type.
	 * @param string              $title      Content title.
	 * @param string              $status     Content status.
	 * @param int                 $priority   Content priority.
	 * @param array<string,mixed> $meta       Content metadata.
	 * @param array<string,mixed> $conditions Content display conditions.
	 */
	public function __construct(
		string $id,
		string $type,
		string $title,
		string $status,
		int $priority = 10,
		array $meta = array(),
		array $conditions = array()
	) {
		$this->id         = $id;
		$this->type       = $type;
		$this->title      = $title;
		$this->status     = $status;
		$this->priority   = $priority;
		$this->meta       = $meta;
		$this->conditions = $conditions;
	}

	/**
	 * Get the stable content identifier.
	 *
	 * @return string
	 */
	public function get_id(): string {
		return $this->id;
	}

	/**
	 * Get the content type identifier.
	 *
	 * @return string
	 */
	public function get_type(): string {
		return $this->type;
	}

	/**
	 * Get the content context identifier.
	 *
	 * @return string
	 */
	public function get_context(): string {
		$context = $this->meta['context'] ?? '';

		return is_scalar( $context ) ? (string) $context : '';
	}

	/**
	 * Get the content title.
	 *
	 * @return string
	 */
	public function get_title(): string {
		return $this->title;
	}

	/**
	 * Get the content status.
	 *
	 * @return string
	 */
	public function get_status(): string {
		return $this->status;
	}

	/**
	 * Get the content priority.
	 *
	 * @return int
	 */
	public function get_priority(): int {
		return $this->priority;
	}

	/**
	 * Get content metadata.
	 *
	 * @return array<string,mixed>
	 */
	public function get_meta(): array {
		return $this->meta;
	}

	/**
	 * Get content display conditions.
	 *
	 * @return array<string,mixed>
	 */
	public function get_conditions(): array {
		return $this->conditions;
	}

	/**
	 * Get structured content data.
	 *
	 * @return array<string,mixed>
	 */
	public function get_data(): array {
		return array(
			'id'         => $this->id,
			'type'       => $this->type,
			'title'      => $this->title,
			'status'     => $this->status,
			'priority'   => $this->priority,
			'meta'       => $this->meta,
			'conditions' => $this->conditions,
		);
	}
}

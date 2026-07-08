<?php
/**
 * Content entity factory.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Creates immutable Content entities from normalized data.
 */
final class ContentFactory {

	/**
	 * Create a Content entity.
	 *
	 * @param string              $id         Content identifier.
	 * @param string              $type       Content type.
	 * @param string              $title      Content title.
	 * @param string              $status     Content status.
	 * @param int                 $priority   Content priority.
	 * @param array<string,mixed> $meta       Content metadata.
	 * @param array<string,mixed> $conditions Content display conditions.
	 *
	 * @return Content
	 */
	public function create(
		string $id,
		string $type = 'generic',
		string $title = '',
		string $status = 'draft',
		int $priority = 10,
		array $meta = array(),
		array $conditions = array()
	): Content {
		return new Content(
			$id,
			$type,
			$title,
			$status,
			$priority,
			$meta,
			$conditions
		);
	}

	/**
	 * Create a Content entity from an array payload.
	 *
	 * @param array<string,mixed> $data Content data.
	 *
	 * @return Content|null
	 */
	public function from_array( array $data ): ?Content {
		$id = isset( $data['id'] ) && is_scalar( $data['id'] ) ? (string) $data['id'] : '';

		if ( '' === $id ) {
			return null;
		}

		$type       = isset( $data['type'] ) && is_scalar( $data['type'] ) ? (string) $data['type'] : 'generic';
		$title      = isset( $data['title'] ) && is_scalar( $data['title'] ) ? (string) $data['title'] : '';
		$status     = isset( $data['status'] ) && is_scalar( $data['status'] ) ? (string) $data['status'] : 'draft';
		$priority   = isset( $data['priority'] ) ? (int) $data['priority'] : 10;
		$meta       = isset( $data['meta'] ) && is_array( $data['meta'] ) ? $data['meta'] : array();
		$conditions = isset( $data['conditions'] ) && is_array( $data['conditions'] ) ? $data['conditions'] : array();

		return $this->create(
			$id,
			$type,
			$title,
			$status,
			$priority,
			$meta,
			$conditions
		);
	}
}

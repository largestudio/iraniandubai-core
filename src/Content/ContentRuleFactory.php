<?php
/**
 * Content rule factory.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

/**
 * Creates immutable content rules from normalized data.
 */
final class ContentRuleFactory {

	/**
	 * Create a content rule.
	 *
	 * @param string              $id         Rule identifier.
	 * @param string              $content_id Related content identifier.
	 * @param string              $context    Rule context.
	 * @param string              $status     Rule status.
	 * @param int                 $priority   Rule priority.
	 * @param array<string,mixed> $conditions Rule conditions.
	 * @param array<string,mixed> $meta       Rule metadata.
	 *
	 * @return ContentRule
	 */
	public function create(
		string $id,
		string $content_id,
		string $context = '',
		string $status = 'draft',
		int $priority = 10,
		array $conditions = array(),
		array $meta = array()
	): ContentRule {
		return new ContentRule(
			$id,
			$content_id,
			$context,
			$status,
			$priority,
			$conditions,
			$meta
		);
	}

	/**
	 * Create a content rule from an array payload.
	 *
	 * @param array<string,mixed> $data Rule data.
	 *
	 * @return ContentRule|null
	 */
	public function from_array( array $data ): ?ContentRule {
		$id         = isset( $data['id'] ) && is_scalar( $data['id'] ) ? (string) $data['id'] : '';
		$content_id = isset( $data['content_id'] ) && is_scalar( $data['content_id'] ) ? (string) $data['content_id'] : '';

		if ( '' === $id || '' === $content_id ) {
			return null;
		}

		$context    = isset( $data['context'] ) && is_scalar( $data['context'] ) ? (string) $data['context'] : '';
		$status     = isset( $data['status'] ) && is_scalar( $data['status'] ) ? (string) $data['status'] : 'draft';
		$priority   = isset( $data['priority'] ) ? (int) $data['priority'] : 10;
		$conditions = isset( $data['conditions'] ) && is_array( $data['conditions'] ) ? $data['conditions'] : array();
		$meta       = isset( $data['meta'] ) && is_array( $data['meta'] ) ? $data['meta'] : array();

		return $this->create(
			$id,
			$content_id,
			$context,
			$status,
			$priority,
			$conditions,
			$meta
		);
	}
}

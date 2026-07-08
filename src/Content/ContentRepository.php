<?php
/**
 * Content repository foundation.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Manages future content source registrations without persistence.
 */
final class ContentRepository {

	/**
	 * Registered content sources.
	 *
	 * @var array<string,array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}>
	 */
	private array $sources = array();

	/**
	 * Register a future content source.
	 *
	 * @param string              $id       Source identifier.
	 * @param string              $label    Human-readable source label.
	 * @param callable|null       $callback Optional source callback.
	 * @param array<string,mixed> $meta     Optional source metadata.
	 *
	 * @return bool True when the source was registered.
	 */
	public function register_source(
		string $id,
		string $label,
		?callable $callback = null,
		array $meta = array()
	): bool {
		$id    = sanitize_key( $id );
		$label = trim( $label );

		if ( '' === $id || '' === $label ) {
			return false;
		}

		$this->sources[ $id ] = array(
			'id'       => $id,
			'label'    => $label,
			'callback' => $callback,
			'meta'     => $meta,
		);

		return true;
	}

	/**
	 * Check whether a content source is registered.
	 *
	 * @param string $id Source identifier.
	 *
	 * @return bool
	 */
	public function has_source( string $id ): bool {
		return isset( $this->sources[ sanitize_key( $id ) ] );
	}

	/**
	 * Get one registered content source.
	 *
	 * @param string $id Source identifier.
	 *
	 * @return array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}|null
	 */
	public function get_source( string $id ): ?array {
		$id = sanitize_key( $id );

		return $this->sources[ $id ] ?? null;
	}

	/**
	 * Get all registered content sources.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}>
	 */
	public function get_sources(): array {
		return $this->sources;
	}
}

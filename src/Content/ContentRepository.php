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
 * Manages future content registrations without persistence.
 */
final class ContentRepository {

	/**
	 * Content factory.
	 *
	 * @var ContentFactory
	 */
	private ContentFactory $factory;

	/**
	 * Registered content entries.
	 *
	 * @var array<string,array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}>
	 */
	private array $items = array();

	/**
	 * Constructor.
	 *
	 * @param ContentFactory|null $factory Optional content factory.
	 */
	public function __construct( ?ContentFactory $factory = null ) {
		$this->factory = $factory ?? new ContentFactory();
	}

	/**
	 * Register a future content entry.
	 *
	 * @param string              $id       Content identifier.
	 * @param string              $label    Human-readable content label.
	 * @param callable|null       $callback Optional content callback.
	 * @param array<string,mixed> $meta     Optional content metadata.
	 *
	 * @return bool True when the content entry was registered.
	 */
	public function register(
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

		$this->items[ $id ] = array(
			'id'       => $id,
			'label'    => $label,
			'callback' => $callback,
			'meta'     => $meta,
		);

		return true;
	}

	/**
	 * Check whether a content entry is registered.
	 *
	 * @param string $id Content identifier.
	 *
	 * @return bool
	 */
	public function has( string $id ): bool {
		return isset( $this->items[ sanitize_key( $id ) ] );
	}

	/**
	 * Get one registered content entry.
	 *
	 * @param string $id Content identifier.
	 *
	 * @return array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}|null
	 */
	public function get( string $id ): ?array {
		$id = sanitize_key( $id );

		return $this->items[ $id ] ?? null;
	}

	/**
	 * Get one registered content entry as a Content entity.
	 *
	 * @param string $id Content identifier.
	 *
	 * @return Content|null
	 */
	public function get_entity( string $id ): ?Content {
		$item = $this->get( $id );

		return null !== $item ? $this->make_entity( $item ) : null;
	}

	/**
	 * Get all registered content entries.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}>
	 */
	public function all(): array {
		return $this->items;
	}

	/**
	 * Get all registered content entries as Content entities.
	 *
	 * @return array<string,Content>
	 */
	public function all_entities(): array {
		$entities = array();

		foreach ( $this->items as $id => $item ) {
			$entity = $this->make_entity( $item );

			if ( null !== $entity ) {
				$entities[ $id ] = $entity;
			}
		}

		return $entities;
	}

	/**
	 * Remove a registered content entry.
	 *
	 * @param string $id Content identifier.
	 *
	 * @return bool True when an entry existed and was removed.
	 */
	public function remove( string $id ): bool {
		$id = sanitize_key( $id );

		if ( ! isset( $this->items[ $id ] ) ) {
			return false;
		}

		unset( $this->items[ $id ] );

		return true;
	}

	/**
	 * Get the content factory.
	 *
	 * @return ContentFactory
	 */
	public function get_factory(): ContentFactory {
		return $this->factory;
	}

	/**
	 * Create a Content entity from a registered item.
	 *
	 * @param array{id:string,label:string,callback:callable|null,meta:array<string,mixed>} $item Registered item.
	 *
	 * @return Content|null
	 */
	private function make_entity( array $item ): ?Content {
		$meta = $item['meta'];

		$data = array(
			'id'         => $item['id'],
			'type'       => isset( $meta['type'] ) && is_scalar( $meta['type'] ) ? (string) $meta['type'] : 'generic',
			'title'      => isset( $meta['title'] ) && is_scalar( $meta['title'] ) ? (string) $meta['title'] : $item['label'],
			'status'     => isset( $meta['status'] ) && is_scalar( $meta['status'] ) ? (string) $meta['status'] : 'draft',
			'priority'   => isset( $meta['priority'] ) ? (int) $meta['priority'] : 10,
			'meta'       => $meta,
			'conditions' => isset( $meta['conditions'] ) && is_array( $meta['conditions'] ) ? $meta['conditions'] : array(),
		);

		return $this->factory->from_array( $data );
	}

	/**
	 * Register a future content source.
	 *
	 * @deprecated 1.3.0 Use register() instead.
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
		return $this->register( $id, $label, $callback, $meta );
	}

	/**
	 * Check whether a content source is registered.
	 *
	 * @deprecated 1.3.0 Use has() instead.
	 *
	 * @param string $id Source identifier.
	 *
	 * @return bool
	 */
	public function has_source( string $id ): bool {
		return $this->has( $id );
	}

	/**
	 * Get one registered content source.
	 *
	 * @deprecated 1.3.0 Use get() instead.
	 *
	 * @param string $id Source identifier.
	 *
	 * @return array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}|null
	 */
	public function get_source( string $id ): ?array {
		return $this->get( $id );
	}

	/**
	 * Get all registered content sources.
	 *
	 * @deprecated 1.3.0 Use all() instead.
	 *
	 * @return array<string,array{id:string,label:string,callback:callable|null,meta:array<string,mixed>}>
	 */
	public function get_sources(): array {
		return $this->all();
	}
}

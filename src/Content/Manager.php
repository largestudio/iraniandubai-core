<?php
/**
 * Content Engine manager.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Content;

use IDB\Core\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Boots the LSOS Content Engine foundation.
 */
final class Manager implements ModuleInterface {

	/**
	 * Content repository.
	 *
	 * @var ContentRepository
	 */
	private ContentRepository $repository;

	/**
	 * Content resolver.
	 *
	 * @var ContentResolver
	 */
	private ContentResolver $resolver;

	/**
	 * Content renderer.
	 *
	 * @var ContentRenderer
	 */
	private ContentRenderer $renderer;

	/**
	 * Content conditions service.
	 *
	 * @var ContentConditions
	 */
	private ContentConditions $conditions;

	/**
	 * Constructor.
	 *
	 * @param ContentRepository|null $repository Optional repository.
	 * @param ContentResolver|null   $resolver   Optional resolver.
	 * @param ContentRenderer|null   $renderer   Optional renderer.
	 * @param ContentConditions|null $conditions Optional conditions service.
	 */
	public function __construct(
		?ContentRepository $repository = null,
		?ContentResolver $resolver = null,
		?ContentRenderer $renderer = null,
		?ContentConditions $conditions = null
	) {
		$this->repository = $repository ?? new ContentRepository();
		$this->resolver   = $resolver ?? new ContentResolver();
		$this->renderer   = $renderer ?? new ContentRenderer();
		$this->conditions = $conditions ?? new ContentConditions();
	}

	/**
	 * Register Content Engine hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->register_hooks();
	}

	/**
	 * Get the content repository.
	 *
	 * @return ContentRepository
	 */
	public function get_repository(): ContentRepository {
		return $this->repository;
	}

	/**
	 * Get the content resolver.
	 *
	 * @return ContentResolver
	 */
	public function get_resolver(): ContentResolver {
		return $this->resolver;
	}

	/**
	 * Get the content renderer.
	 *
	 * @return ContentRenderer
	 */
	public function get_renderer(): ContentRenderer {
		return $this->renderer;
	}

	/**
	 * Get the content conditions service.
	 *
	 * @return ContentConditions
	 */
	public function get_conditions(): ContentConditions {
		return $this->conditions;
	}

	/**
	 * Register future extension hooks.
	 *
	 * @return void
	 */
	private function register_hooks(): void {
		add_action( 'init', array( $this, 'register_content' ), 20 );
	}

	/**
	 * Allow future modules to register content entries.
	 *
	 * @return void
	 */
	public function register_content(): void {
		/**
		 * Fires when LSOS content entries can be registered.
		 *
		 * @param ContentRepository $repository Content repository.
		 * @param ContentResolver   $resolver   Content resolver.
		 * @param ContentRenderer   $renderer   Content renderer.
		 * @param ContentConditions $conditions Content conditions service.
		 * @param Manager           $manager    Content manager.
		 */
		do_action(
			'lsos/content/register',
			$this->repository,
			$this->resolver,
			$this->renderer,
			$this->conditions,
			$this
		);
	}
}

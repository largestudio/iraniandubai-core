<?php
/**
 * IranianDubai Core
 * Settings Manager
 *
 * @package IranianDubaiCore
 */

namespace IDB\Admin;

use IDB\Blog\Defaults;
use IDB\Core\ModuleInterface;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Registers and renders plugin settings.
 */
final class Settings implements ModuleInterface {
	private const PAGE_SLUG    = 'idb-core';
	private const NONCE_ACTION = 'idb_save_settings';
	private const NONCE_NAME   = 'idb_nonce';

	/**
	 * Option name.
	 *
	 * @var string
	 */
	private string $option_name = IDB_CORE_OPTION_NAME;

	/**
	 * Register Hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		add_action(
			'admin_menu',
			array(
				$this,
				'menu',
			)
		);

		add_action(
			'admin_init',
			array(
				$this,
				'save_settings',
			)
		);
	}

	/**
	 * Register Admin Menu.
	 *
	 * @return void
	 */
	public function menu(): void {
		add_menu_page(
			__( 'IranianDubai Core', 'iraniandubai-core' ),
			__( 'IranianDubai', 'iraniandubai-core' ),
			'manage_options',
			self::PAGE_SLUG,
			array(
				$this,
				'page',
			),
			'dashicons-layout',
			58
		);
	}

	/**
	 * Save Settings.
	 *
	 * @return void
	 */
	public function save_settings(): void {
		if ( empty( $_POST['idb_save_settings'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer(
			self::NONCE_ACTION,
			self::NONCE_NAME
		);

		$options = $this->sanitize_posted_options();

		update_option(
			$this->option_name,
			$options
		);

		wp_safe_redirect(
			add_query_arg(
				'saved',
				'1',
				menu_page_url(
					self::PAGE_SLUG,
					false
				)
			)
		);

		exit;
	}

	/**
	 * Plugin Options.
	 *
	 * @return array{posts_per_page:int,excerpt_length:int,columns:int}
	 */
	private function options(): array {
		return Defaults::settings();
	}

	/**
	 * Render Page.
	 *
	 * @return void
	 */
	public function page(): void {
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have permission to access this page.', 'iraniandubai-core' ) );
		}

		$options = $this->options();
		?>
		<div class="wrap">

			<h1><?php esc_html_e( 'IranianDubai Core', 'iraniandubai-core' ); ?></h1>

			<?php if ( isset( $_GET['saved'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['saved'] ) ) ) : ?>

				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'iraniandubai-core' ); ?></p>
				</div>

			<?php endif; ?>

			<form method="post">

				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>

				<table class="form-table">

					<tr>
						<th scope="row">
							<label for="posts_per_page">
								<?php esc_html_e( 'Posts per page', 'iraniandubai-core' ); ?>
							</label>
						</th>
						<td>
							<input
								id="posts_per_page"
								type="number"
								name="posts_per_page"
								value="<?php echo esc_attr( $options['posts_per_page'] ); ?>"
								min="<?php echo esc_attr( (string) Defaults::POSTS_PER_PAGE_MIN ); ?>"
								max="<?php echo esc_attr( (string) Defaults::POSTS_PER_PAGE_MAX ); ?>"
								step="1"
								class="small-text"
							/>
							<p class="description">
								<?php
								printf(
									/* translators: 1: Minimum value. 2: Maximum value. */
									esc_html__( 'Allowed range: %1$d-%2$d posts.', 'iraniandubai-core' ),
									absint( Defaults::POSTS_PER_PAGE_MIN ),
									absint( Defaults::POSTS_PER_PAGE_MAX )
								);
								?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="excerpt_length">
								<?php esc_html_e( 'Excerpt length', 'iraniandubai-core' ); ?>
							</label>
						</th>
						<td>
							<input
								id="excerpt_length"
								type="number"
								name="excerpt_length"
								value="<?php echo esc_attr( $options['excerpt_length'] ); ?>"
								min="<?php echo esc_attr( (string) Defaults::EXCERPT_LENGTH_MIN ); ?>"
								max="<?php echo esc_attr( (string) Defaults::EXCERPT_LENGTH_MAX ); ?>"
								step="1"
								class="small-text"
							/>
							<p class="description">
								<?php
								printf(
									/* translators: 1: Minimum value. 2: Maximum value. */
									esc_html__( 'Allowed range: %1$d-%2$d words.', 'iraniandubai-core' ),
									absint( Defaults::EXCERPT_LENGTH_MIN ),
									absint( Defaults::EXCERPT_LENGTH_MAX )
								);
								?>
							</p>
						</td>
					</tr>

					<tr>
						<th scope="row">
							<label for="columns">
								<?php esc_html_e( 'Default columns', 'iraniandubai-core' ); ?>
							</label>
						</th>
						<td>
							<select
								id="columns"
								name="columns"
							>
								<?php for ( $columns = Defaults::COLUMNS_MIN; $columns <= Defaults::COLUMNS_MAX; ++$columns ) : ?>
									<option value="<?php echo esc_attr( (string) $columns ); ?>" <?php selected( $options['columns'], $columns ); ?>>
										<?php echo esc_html( (string) $columns ); ?>
									</option>
								<?php endfor; ?>
							</select>
							<p class="description">
								<?php esc_html_e( 'Used when a shortcode or Elementor widget does not set its own column count.', 'iraniandubai-core' ); ?>
							</p>
						</td>
					</tr>

				</table>

				<p>
					<button
						type="submit"
						name="idb_save_settings"
						value="1"
						class="button button-primary"
					>
						<?php esc_html_e( 'Save Changes', 'iraniandubai-core' ); ?>
					</button>
				</p>

			</form>

		</div>

		<?php

	}

	/**
	 * Read and sanitize submitted settings.
	 *
	 * @return array{posts_per_page:int,excerpt_length:int,columns:int}
	 */
	private function sanitize_posted_options(): array {
		$posted = array(
			'posts_per_page' => Defaults::SETTINGS['posts_per_page'],
			'excerpt_length' => Defaults::SETTINGS['excerpt_length'],
			'columns'        => Defaults::SETTINGS['columns'],
		);

		foreach ( array_keys( $posted ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$posted[ $key ] = wp_unslash( $_POST[ $key ] );
			}
		}

		return Defaults::sanitize( $posted );
	}

}

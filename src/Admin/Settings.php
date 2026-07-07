<?php
/**
 * IranianDubai Core
 * Settings Manager
 *
 * @package IranianDubaiCore
 */

namespace IDB\Admin;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

final class Settings {

	/**
	 * Option Name
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
				'save',
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
			'IranianDubai',
			'IranianDubai',
			'manage_options',
			'idb-core',
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
	public function save(): void {

		if ( empty( $_POST['idb_save_settings'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		check_admin_referer(
			'idb_save_settings',
			'idb_nonce'
		);

		$options = $this->options();
				$options['posts_per_page'] = max(
			1,
			min(
				48,
				absint(
					$_POST['posts_per_page'] ?? 6
				)
			)
		);

		$options['excerpt_length'] = max(
			5,
			min(
				100,
				absint(
					$_POST['excerpt_length'] ?? 24
				)
			)
		);

		$options['columns'] = max(
			1,
			min(
				4,
				absint(
					$_POST['columns'] ?? 2
				)
			)
		);

		update_option(
			$this->option_name,
			$options
		);

		wp_safe_redirect(

			add_query_arg(
				'saved',
				'1',
				menu_page_url(
					'idb-core',
					false
				)
			)

		);

		exit;

	}

	/**
	 * Plugin Options.
	 *
	 * @return array
	 */
	private function options(): array {

		$options = get_option(
			$this->option_name,
			array()
		);

		if ( ! is_array( $options ) ) {
			$options = array();
		}

		return wp_parse_args(

			$options,

			array(
				'posts_per_page' => 6,
				'excerpt_length' => 24,
				'columns'        => 2,
			)

		);

	}

	/**
	 * Render Page.
	 *
	 * @return void
	 */
	public function page(): void {

		$options = $this->options();
		?>
				<div class="wrap">

			<h1>IranianDubai Core</h1>

			<?php if ( isset( $_GET['saved'] ) ) : ?>

				<div class="notice notice-success is-dismissible">

					<p>

						Settings saved successfully.

					</p>

				</div>

			<?php endif; ?>

			<form method="post">

				<?php wp_nonce_field( 'idb_save_settings', 'idb_nonce' ); ?>

				<table class="form-table">

					<tr>

						<th scope="row">

							<label for="posts_per_page">

								Posts Per Page

							</label>

						</th>

						<td>

							<input
								id="posts_per_page"
								type="number"
								name="posts_per_page"
								value="<?php echo esc_attr( $options['posts_per_page'] ); ?>"
								min="1"
								max="48"
								class="small-text"
							/>

						</td>

					</tr>

					<tr>

						<th scope="row">

							<label for="excerpt_length">

								Excerpt Length

							</label>

						</th>

						<td>

							<input
								id="excerpt_length"
								type="number"
								name="excerpt_length"
								value="<?php echo esc_attr( $options['excerpt_length'] ); ?>"
								min="5"
								max="100"
								class="small-text"
							/>

						</td>

					</tr>

					<tr>

						<th scope="row">

							<label for="columns">

								Default Columns

							</label>

						</th>

						<td>

							<select
								id="columns"
								name="columns"
							>

								<option value="1" <?php selected( $options['columns'], 1 ); ?>>1</option>

								<option value="2" <?php selected( $options['columns'], 2 ); ?>>2</option>

								<option value="3" <?php selected( $options['columns'], 3 ); ?>>3</option>

								<option value="4" <?php selected( $options['columns'], 4 ); ?>>4</option>

							</select>

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

						Save Changes

					</button>

				</p>

			</form>

		</div>

		<?php

	}

}
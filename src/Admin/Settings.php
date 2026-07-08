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
	private const PAGE_SLUG     = 'idb-core';
	private const NONCE_ACTION  = 'idb_save_settings';
	private const NONCE_NAME    = 'idb_nonce';
	private const EXPORT_ACTION = 'idb_export_settings';
	private const EXPORT_NONCE  = 'idb_export_nonce';
	private const IMPORT_ACTION = 'idb_import_settings';
	private const IMPORT_NONCE  = 'idb_import_nonce';

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
				'handle_settings_actions',
			)
		);

		add_filter(
			'gettext',
			array(
				$this,
				'translate_admin_text',
			),
			10,
			3
		);
	}

	/**
	 * Provide immediate Persian admin translations when language files are unavailable.
	 *
	 * @param string $translation Translated text.
	 * @param string $text        Original text.
	 * @param string $domain      Text domain.
	 *
	 * @return string
	 */
	public function translate_admin_text( string $translation, string $text, string $domain ): string {
		if ( 'iraniandubai-core' !== $domain || ! is_admin() || ! $this->is_persian_locale() ) {
			return $translation;
		}

		$translations = array(
			'IranianDubai Core' => 'هسته ایرانیان دبی',
			'IranianDubai' => 'ایرانیان دبی',
			'You do not have permission to access this page.' => 'شما اجازه دسترسی به این صفحه را ندارید.',
			'Settings saved successfully.' => 'تنظیمات با موفقیت ذخیره شد.',
			'General Settings' => 'تنظیمات عمومی',
			'Use these defaults when a shortcode or Elementor widget does not provide its own blog display values.' => 'وقتی شورت‌کد یا ابزارک المنتور مقدار اختصاصی برای نمایش بلاگ ندارد، از این پیش‌فرض‌ها استفاده می‌شود.',
			'Blog Settings' => 'تنظیمات بلاگ',
			'Posts Per Page' => 'تعداد نوشته در هر صفحه',
			'Number of posts displayed on each page. Allowed range: %1$d-%2$d posts.' => 'تعداد نوشته‌هایی که در هر صفحه نمایش داده می‌شود. بازه مجاز: %1$d تا %2$d نوشته.',
			'Query Settings' => 'تنظیمات کوئری',
			'Include Categories' => 'دسته‌های شامل',
			'Only show posts from these categories. Leave empty to include all categories.' => 'فقط نوشته‌های این دسته‌ها نمایش داده شود. برای نمایش همه دسته‌ها خالی بگذارید.',
			'Exclude Categories' => 'دسته‌های مستثنی',
			'Hide posts from these categories. Leave empty to exclude none.' => 'نوشته‌های این دسته‌ها مخفی شود. برای مستثنی نکردن هیچ دسته‌ای خالی بگذارید.',
			'Include Tags' => 'برچسب‌های شامل',
			'Only show posts with these tags. Leave empty to include all tags.' => 'فقط نوشته‌های دارای این برچسب‌ها نمایش داده شود. برای نمایش همه برچسب‌ها خالی بگذارید.',
			'Exclude Tags' => 'برچسب‌های مستثنی',
			'Hide posts with these tags. Leave empty to exclude none.' => 'نوشته‌های دارای این برچسب‌ها مخفی شود. برای مستثنی نکردن هیچ برچسبی خالی بگذارید.',
			'Author' => 'نویسنده',
			'All authors' => 'همه نویسنده‌ها',
			'Show posts from one author. Leave empty to include all authors.' => 'نوشته‌های یک نویسنده نمایش داده شود. برای نمایش همه نویسنده‌ها خالی بگذارید.',
			'Sticky Posts Mode' => 'حالت نوشته‌های سنجاق‌شده',
			'Include all posts' => 'نمایش همه نوشته‌ها',
			'Only sticky posts' => 'فقط نوشته‌های سنجاق‌شده',
			'Exclude sticky posts' => 'حذف نوشته‌های سنجاق‌شده',
			'Control how sticky posts are included in the blog query.' => 'نحوه حضور نوشته‌های سنجاق‌شده در کوئری بلاگ را کنترل کنید.',
			'Offset' => 'آفست',
			'Skip this many posts before showing results. Pagination is adjusted safely. Allowed range: 0-%d.' => 'این تعداد نوشته پیش از نمایش نتایج رد می‌شود. صفحه‌بندی به‌صورت امن تنظیم می‌شود. بازه مجاز: 0 تا %d.',
			'Order By' => 'مرتب‌سازی بر اساس',
			'Date' => 'تاریخ',
			'Modified Date' => 'تاریخ ویرایش',
			'Title' => 'عنوان',
			'Comment Count' => 'تعداد دیدگاه',
			'Random' => 'تصادفی',
			'Menu Order' => 'ترتیب منو',
			'Choose which post field controls sorting.' => 'انتخاب کنید مرتب‌سازی بر اساس کدام فیلد نوشته انجام شود.',
			'Order' => 'ترتیب',
			'Descending' => 'نزولی',
			'Ascending' => 'صعودی',
			'Choose ascending or descending order.' => 'ترتیب صعودی یا نزولی را انتخاب کنید.',
			'Display Settings' => 'تنظیمات نمایش',
			'Layout Style' => 'سبک چیدمان',
			'Choose the blog layout style used by shortcode and Elementor output.' => 'سبک چیدمان بلاگ برای خروجی شورت‌کد و المنتور را انتخاب کنید.',
			'Pagination Type' => 'نوع صفحه‌بندی',
			'Choose how visitors load additional blog posts.' => 'انتخاب کنید بازدیدکنندگان چگونه نوشته‌های بیشتر بلاگ را بارگذاری کنند.',
			'Pagination' => 'صفحه‌بندی',
			'Load More' => 'مشاهده بیشتر',
			'Infinite Scroll' => 'اسکرول بی‌نهایت',
			'Grid' => 'شبکه‌ای',
			'List' => 'لیستی',
			'Magazine' => 'مجله‌ای',
			'Minimal' => 'ساده',
			'Columns' => 'ستون‌ها',
			'Desktop columns for the blog grid when no shortcode or Elementor column value is set.' => 'تعداد ستون‌های دسکتاپ برای شبکه بلاگ، وقتی مقدار ستون در شورت‌کد یا المنتور تنظیم نشده باشد.',
			'Excerpt Length' => 'طول خلاصه',
			'Maximum words shown before Read More. Allowed range: %1$d-%2$d words.' => 'حداکثر تعداد کلمات پیش از ادامه مطلب. بازه مجاز: %1$d تا %2$d کلمه.',
			'Save Changes' => 'ذخیره تغییرات',
			'Shortcode' => 'شورت‌کد',
			'Basic Shortcode' => 'شورت‌کد اصلی',
			'Example' => 'نمونه',
			'Attribute' => 'ویژگی',
			'Default' => 'پیش‌فرض',
			'Description' => 'توضیح',
			'Elementor Help' => 'راهنمای المنتور',
			'Location' => 'محل نمایش',
			'Elementor editor, General widget category.' => 'ویرایشگر المنتور، دسته ابزارک‌های عمومی.',
			'Widget Name' => 'نام ابزارک',
			'IranianDubai Blog' => 'بلاگ ایرانیان دبی',
			'Required Settings' => 'تنظیمات ضروری',
			'No required settings. The widget uses the saved defaults until its controls are customized.' => 'تنظیم ضروری ندارد. ابزارک تا زمانی که کنترل‌های آن تغییر نکنند، از پیش‌فرض‌های ذخیره‌شده استفاده می‌کند.',
			'Example Usage' => 'نمونه استفاده',
			'Add IranianDubai Blog to a page, then adjust Columns, Excerpt, and Pagination in the Content tab. Query behavior comes from Query Settings in this settings page.' => 'ابزارک بلاگ ایرانیان دبی را به صفحه اضافه کنید، سپس ستون‌ها، خلاصه و صفحه‌بندی را در تب محتوا تنظیم کنید. رفتار کوئری از بخش تنظیمات کوئری در همین صفحه تنظیمات می‌آید.',
			'Import / Export Settings' => 'درون‌ریزی / برون‌بری تنظیمات',
			'Export Settings' => 'برون‌بری تنظیمات',
			'Export only IranianDubai Core plugin settings as a JSON file.' => 'فقط تنظیمات افزونه هسته ایرانیان دبی را به‌صورت فایل JSON برون‌بری می‌کند.',
			'Import Settings' => 'درون‌ریزی تنظیمات',
			'Import settings JSON' => 'JSON تنظیمات برای درون‌ریزی',
			'Paste a JSON export from IranianDubai Core. Imported values are validated and clamped to the allowed settings ranges.' => 'خروجی JSON هسته ایرانیان دبی را وارد کنید. مقدارهای درون‌ریزی‌شده اعتبارسنجی می‌شوند و در بازه مجاز تنظیمات قرار می‌گیرند.',
			'About' => 'درباره',
			'Plugin' => 'افزونه',
			'Version' => 'نسخه',
			'Purpose' => 'هدف',
			'Core blog, shortcode, Elementor, and settings tools for the IranianDubai website.' => 'ابزارهای اصلی بلاگ، شورت‌کد، المنتور و تنظیمات برای وب‌سایت ایرانیان دبی.',
			'All categories' => 'همه دسته‌ها',
			'Category slug or ID to show posts from one category.' => 'نامک یا شناسه دسته برای نمایش نوشته‌های یک دسته.',
			'Saved display setting' => 'تنظیم نمایش ذخیره‌شده',
			'Desktop columns for the blog grid.' => 'ستون‌های دسکتاپ برای شبکه بلاگ.',
			'Maximum excerpt words shown before Read More.' => 'حداکثر کلمات خلاصه پیش از ادامه مطلب.',
			'Post order. Supports ASC or DESC.' => 'ترتیب نوشته‌ها. از ASC یا DESC پشتیبانی می‌کند.',
			'Sort field. Supports date, title, modified, menu_order, rand, comment_count, or ID.' => 'فیلد مرتب‌سازی. از date، title، modified، menu_order، rand، comment_count یا ID پشتیبانی می‌کند.',
			'Enable or disable pagination.' => 'فعال یا غیرفعال کردن صفحه‌بندی.',
			'Current pagination page for direct shortcode use.' => 'صفحه فعلی صفحه‌بندی برای استفاده مستقیم در شورت‌کد.',
			'Saved blog setting' => 'تنظیم ذخیره‌شده بلاگ',
			'Saved query setting' => 'تنظیم ذخیره‌شده کوئری',
			'Alternative post count attribute.' => 'ویژگی جایگزین برای تعداد نوشته‌ها.',
			'Number of posts displayed on each page.' => 'تعداد نوشته‌هایی که در هر صفحه نمایش داده می‌شود.',
			'Settings export failed. Please try again.' => 'برون‌بری تنظیمات ناموفق بود. لطفا دوباره تلاش کنید.',
			'Import failed. Please paste a JSON settings export.' => 'درون‌ریزی ناموفق بود. لطفا خروجی JSON تنظیمات را وارد کنید.',
			'Import failed. The JSON does not contain valid IranianDubai Core settings.' => 'درون‌ریزی ناموفق بود. JSON شامل تنظیمات معتبر هسته ایرانیان دبی نیست.',
			'Settings imported successfully.' => 'تنظیمات با موفقیت درون‌ریزی شد.',
		);

		return $translations[ $text ] ?? $translation;
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
	public function handle_settings_actions(): void {
		if ( ! isset( $_POST['idb_settings_action'] ) ) {
			return;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		$action = sanitize_key( wp_unslash( $_POST['idb_settings_action'] ) );

		if ( 'save' === $action ) {
			$this->save_settings();
			return;
		}

		if ( 'export' === $action ) {
			$this->export_settings();
			return;
		}

		if ( 'import' === $action ) {
			$this->import_settings();
		}
	}

	/**
	 * Save settings from the admin form.
	 *
	 * @return void
	 */
	private function save_settings(): void {
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
	 * Export current plugin settings as JSON.
	 *
	 * @return void
	 */
	private function export_settings(): void {
		check_admin_referer(
			self::EXPORT_ACTION,
			self::EXPORT_NONCE
		);

		$payload = wp_json_encode(
			array(
				'plugin'   => 'iraniandubai-core',
				'version'  => IDB_CORE_VERSION,
				'settings' => $this->options(),
			),
			JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
		);

		if ( ! is_string( $payload ) ) {
			$this->redirect_with_notice( 'export_error' );
		}

		nocache_headers();
		header( 'Content-Type: application/json; charset=' . $this->get_blog_charset() );
		header(
			'Content-Disposition: attachment; filename=iraniandubai-core-settings-' . gmdate( 'Y-m-d' ) . '.json'
		);
		header( 'X-Content-Type-Options: nosniff' );

		echo $payload; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- JSON is generated by wp_json_encode.
		exit;
	}

	/**
	 * Import plugin settings from JSON.
	 *
	 * @return void
	 */
	private function import_settings(): void {
		check_admin_referer(
			self::IMPORT_ACTION,
			self::IMPORT_NONCE
		);

		$raw_json = isset( $_POST['idb_import_json'] )
			? trim( (string) wp_unslash( $_POST['idb_import_json'] ) )
			: '';

		if ( '' === $raw_json ) {
			$this->redirect_with_notice( 'import_empty' );
		}

		$decoded = json_decode( $raw_json, true, 512 );

		if ( JSON_ERROR_NONE !== json_last_error() || ! is_array( $decoded ) ) {
			$this->redirect_with_notice( 'import_invalid' );
		}

		$settings = isset( $decoded['settings'] ) && is_array( $decoded['settings'] ) ? $decoded['settings'] : $decoded;

		if ( ! $this->has_importable_settings( $settings ) ) {
			$this->redirect_with_notice( 'import_invalid' );
		}

		update_option(
			$this->option_name,
			Defaults::sanitize( $settings )
		);

		$this->redirect_with_notice( 'imported' );
	}

	/**
	 * Plugin Options.
	 *
	 * @return array<string,mixed>
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
		$example = wp_json_encode( array( 'settings' => $options ) );
		$shortcode_attributes = $this->get_shortcode_attribute_rows();

		if ( ! is_string( $example ) ) {
			$example = '';
		}
		?>
		<div class="wrap">

			<h1><?php esc_html_e( 'IranianDubai Core', 'iraniandubai-core' ); ?></h1>

			<?php if ( isset( $_GET['saved'] ) && '1' === sanitize_text_field( wp_unslash( $_GET['saved'] ) ) ) : ?>

				<div class="notice notice-success is-dismissible">
					<p><?php esc_html_e( 'Settings saved successfully.', 'iraniandubai-core' ); ?></p>
				</div>

			<?php endif; ?>

			<?php $this->render_import_export_notice(); ?>

			<h2><?php esc_html_e( 'General Settings', 'iraniandubai-core' ); ?></h2>
			<div class="notice notice-info inline">
				<p>
					<?php esc_html_e( 'Use these defaults when a shortcode or Elementor widget does not provide its own blog display values.', 'iraniandubai-core' ); ?>
				</p>
			</div>

			<form method="post">

				<?php wp_nonce_field( self::NONCE_ACTION, self::NONCE_NAME ); ?>
				<input type="hidden" name="idb_settings_action" value="save" />

				<h2><?php esc_html_e( 'Blog Settings', 'iraniandubai-core' ); ?></h2>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Blog Settings', 'iraniandubai-core' ); ?></legend>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="posts_per_page">
									<?php esc_html_e( 'Posts Per Page', 'iraniandubai-core' ); ?>
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
									aria-describedby="posts_per_page_description"
								/>
								<p id="posts_per_page_description" class="description">
									<?php
									printf(
										/* translators: 1: Minimum value. 2: Maximum value. */
										esc_html__( 'Number of posts displayed on each page. Allowed range: %1$d-%2$d posts.', 'iraniandubai-core' ),
										absint( Defaults::POSTS_PER_PAGE_MIN ),
										absint( Defaults::POSTS_PER_PAGE_MAX )
									);
									?>
								</p>
							</td>
						</tr>
					</table>
				</fieldset>

				<h2><?php esc_html_e( 'Query Settings', 'iraniandubai-core' ); ?></h2>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Query Settings', 'iraniandubai-core' ); ?></legend>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="include_categories">
									<?php esc_html_e( 'Include Categories', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="include_categories"
									name="include_categories[]"
									multiple
									aria-describedby="include_categories_description"
								>
									<?php foreach ( $this->get_term_options( 'category' ) as $term_id => $term_name ) : ?>
										<option value="<?php echo esc_attr( (string) $term_id ); ?>" <?php selected( in_array( $term_id, $options['include_categories'], true ) ); ?>>
											<?php echo esc_html( $term_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="include_categories_description" class="description">
									<?php esc_html_e( 'Only show posts from these categories. Leave empty to include all categories.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="exclude_categories">
									<?php esc_html_e( 'Exclude Categories', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="exclude_categories"
									name="exclude_categories[]"
									multiple
									aria-describedby="exclude_categories_description"
								>
									<?php foreach ( $this->get_term_options( 'category' ) as $term_id => $term_name ) : ?>
										<option value="<?php echo esc_attr( (string) $term_id ); ?>" <?php selected( in_array( $term_id, $options['exclude_categories'], true ) ); ?>>
											<?php echo esc_html( $term_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="exclude_categories_description" class="description">
									<?php esc_html_e( 'Hide posts from these categories. Leave empty to exclude none.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="include_tags">
									<?php esc_html_e( 'Include Tags', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="include_tags"
									name="include_tags[]"
									multiple
									aria-describedby="include_tags_description"
								>
									<?php foreach ( $this->get_term_options( 'post_tag' ) as $term_id => $term_name ) : ?>
										<option value="<?php echo esc_attr( (string) $term_id ); ?>" <?php selected( in_array( $term_id, $options['include_tags'], true ) ); ?>>
											<?php echo esc_html( $term_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="include_tags_description" class="description">
									<?php esc_html_e( 'Only show posts with these tags. Leave empty to include all tags.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="exclude_tags">
									<?php esc_html_e( 'Exclude Tags', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="exclude_tags"
									name="exclude_tags[]"
									multiple
									aria-describedby="exclude_tags_description"
								>
									<?php foreach ( $this->get_term_options( 'post_tag' ) as $term_id => $term_name ) : ?>
										<option value="<?php echo esc_attr( (string) $term_id ); ?>" <?php selected( in_array( $term_id, $options['exclude_tags'], true ) ); ?>>
											<?php echo esc_html( $term_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="exclude_tags_description" class="description">
									<?php esc_html_e( 'Hide posts with these tags. Leave empty to exclude none.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="author">
									<?php esc_html_e( 'Author', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="author"
									name="author"
									aria-describedby="author_description"
								>
									<option value="0"><?php esc_html_e( 'All authors', 'iraniandubai-core' ); ?></option>
									<?php foreach ( $this->get_author_options() as $author_id => $author_name ) : ?>
										<option value="<?php echo esc_attr( (string) $author_id ); ?>" <?php selected( $options['author'], $author_id ); ?>>
											<?php echo esc_html( $author_name ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="author_description" class="description">
									<?php esc_html_e( 'Show posts from one author. Leave empty to include all authors.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="sticky_posts_mode">
									<?php esc_html_e( 'Sticky Posts Mode', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="sticky_posts_mode"
									name="sticky_posts_mode"
									aria-describedby="sticky_posts_mode_description"
								>
									<?php foreach ( $this->get_sticky_mode_options() as $mode_value => $mode_label ) : ?>
										<option value="<?php echo esc_attr( $mode_value ); ?>" <?php selected( $options['sticky_posts_mode'], $mode_value ); ?>>
											<?php echo esc_html( $mode_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="sticky_posts_mode_description" class="description">
									<?php esc_html_e( 'Control how sticky posts are included in the blog query.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="offset">
									<?php esc_html_e( 'Offset', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<input
									id="offset"
									type="number"
									name="offset"
									value="<?php echo esc_attr( $options['offset'] ); ?>"
									min="0"
									max="<?php echo esc_attr( (string) Defaults::OFFSET_MAX ); ?>"
									step="1"
									class="small-text"
									aria-describedby="offset_description"
								/>
								<p id="offset_description" class="description">
									<?php
									printf(
										/* translators: %d: Maximum offset value. */
										esc_html__( 'Skip this many posts before showing results. Pagination is adjusted safely. Allowed range: 0-%d.', 'iraniandubai-core' ),
										absint( Defaults::OFFSET_MAX )
									);
									?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="orderby">
									<?php esc_html_e( 'Order By', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="orderby"
									name="orderby"
									aria-describedby="orderby_description"
								>
									<?php foreach ( $this->get_orderby_options() as $orderby_value => $orderby_label ) : ?>
										<option value="<?php echo esc_attr( $orderby_value ); ?>" <?php selected( $options['orderby'], $orderby_value ); ?>>
											<?php echo esc_html( $orderby_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="orderby_description" class="description">
									<?php esc_html_e( 'Choose which post field controls sorting.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="order">
									<?php esc_html_e( 'Order', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="order"
									name="order"
									aria-describedby="order_description"
								>
									<?php foreach ( $this->get_order_options() as $order_value => $order_label ) : ?>
										<option value="<?php echo esc_attr( $order_value ); ?>" <?php selected( $options['order'], $order_value ); ?>>
											<?php echo esc_html( $order_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="order_description" class="description">
									<?php esc_html_e( 'Choose ascending or descending order.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>
					</table>
				</fieldset>

				<h2><?php esc_html_e( 'Display Settings', 'iraniandubai-core' ); ?></h2>
				<fieldset>
					<legend class="screen-reader-text"><?php esc_html_e( 'Display Settings', 'iraniandubai-core' ); ?></legend>
					<table class="form-table" role="presentation">
						<tr>
							<th scope="row">
								<label for="layout">
									<?php esc_html_e( 'Layout Style', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="layout"
									name="layout"
									aria-describedby="layout_description"
								>
									<?php foreach ( $this->get_layout_options() as $layout_value => $layout_label ) : ?>
										<option value="<?php echo esc_attr( $layout_value ); ?>" <?php selected( $options['layout'], $layout_value ); ?>>
											<?php echo esc_html( $layout_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="layout_description" class="description">
									<?php esc_html_e( 'Choose the blog layout style used by shortcode and Elementor output.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="pagination_mode">
									<?php esc_html_e( 'Pagination Type', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="pagination_mode"
									name="pagination_mode"
									aria-describedby="pagination_mode_description"
								>
									<?php foreach ( $this->get_pagination_mode_options() as $mode_value => $mode_label ) : ?>
										<option value="<?php echo esc_attr( $mode_value ); ?>" <?php selected( $options['pagination_mode'], $mode_value ); ?>>
											<?php echo esc_html( $mode_label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
								<p id="pagination_mode_description" class="description">
									<?php esc_html_e( 'Choose how visitors load additional blog posts.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="columns">
									<?php esc_html_e( 'Columns', 'iraniandubai-core' ); ?>
								</label>
							</th>
							<td>
								<select
									id="columns"
									name="columns"
									aria-describedby="columns_description"
								>
									<?php for ( $columns = Defaults::COLUMNS_MIN; $columns <= Defaults::COLUMNS_MAX; ++$columns ) : ?>
										<option value="<?php echo esc_attr( (string) $columns ); ?>" <?php selected( $options['columns'], $columns ); ?>>
											<?php echo esc_html( (string) $columns ); ?>
										</option>
									<?php endfor; ?>
								</select>
								<p id="columns_description" class="description">
									<?php esc_html_e( 'Desktop columns for the blog grid when no shortcode or Elementor column value is set.', 'iraniandubai-core' ); ?>
								</p>
							</td>
						</tr>

						<tr>
							<th scope="row">
								<label for="excerpt_length">
									<?php esc_html_e( 'Excerpt Length', 'iraniandubai-core' ); ?>
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
									aria-describedby="excerpt_length_description"
								/>
								<p id="excerpt_length_description" class="description">
									<?php
									printf(
										/* translators: 1: Minimum value. 2: Maximum value. */
										esc_html__( 'Maximum words shown before Read More. Allowed range: %1$d-%2$d words.', 'iraniandubai-core' ),
										absint( Defaults::EXCERPT_LENGTH_MIN ),
										absint( Defaults::EXCERPT_LENGTH_MAX )
									);
									?>
								</p>
							</td>
						</tr>
					</table>
				</fieldset>

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

			<hr />

			<h2><?php esc_html_e( 'Shortcode', 'iraniandubai-core' ); ?></h2>

			<h3><?php esc_html_e( 'Basic Shortcode', 'iraniandubai-core' ); ?></h3>
			<p class="description">
				<code>[idb_blog]</code>
			</p>

			<h3><?php esc_html_e( 'Example', 'iraniandubai-core' ); ?></h3>
			<p class="description">
				<code>[idb_blog posts_per_page="9" columns="3"]</code>
			</p>

			<table class="widefat striped">
				<thead>
					<tr>
						<th scope="col"><?php esc_html_e( 'Attribute', 'iraniandubai-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Default', 'iraniandubai-core' ); ?></th>
						<th scope="col"><?php esc_html_e( 'Description', 'iraniandubai-core' ); ?></th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $shortcode_attributes as $attribute ) : ?>
						<tr>
							<th scope="row"><code><?php echo esc_html( $attribute['name'] ); ?></code></th>
							<td><?php echo esc_html( $attribute['default'] ); ?></td>
							<td><?php echo esc_html( $attribute['description'] ); ?></td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>

			<h2><?php esc_html_e( 'Elementor Help', 'iraniandubai-core' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Location', 'iraniandubai-core' ); ?></th>
						<td><?php esc_html_e( 'Elementor editor, General widget category.', 'iraniandubai-core' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Widget Name', 'iraniandubai-core' ); ?></th>
						<td><?php esc_html_e( 'IranianDubai Blog', 'iraniandubai-core' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Required Settings', 'iraniandubai-core' ); ?></th>
						<td><?php esc_html_e( 'No required settings. The widget uses the saved defaults until its controls are customized.', 'iraniandubai-core' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Example Usage', 'iraniandubai-core' ); ?></th>
						<td>
							<?php
							esc_html_e(
								'Add IranianDubai Blog to a page, then adjust Columns, Excerpt, and Pagination in the Content tab. Query behavior comes from Query Settings in this settings page.',
								'iraniandubai-core'
							);
							?>
						</td>
					</tr>
				</tbody>
			</table>

			<hr />

			<h2><?php esc_html_e( 'Import / Export Settings', 'iraniandubai-core' ); ?></h2>

			<h3><?php esc_html_e( 'Export Settings', 'iraniandubai-core' ); ?></h3>

			<form method="post">
				<?php wp_nonce_field( self::EXPORT_ACTION, self::EXPORT_NONCE ); ?>
				<input type="hidden" name="idb_settings_action" value="export" />
				<p>
					<?php esc_html_e( 'Export only IranianDubai Core plugin settings as a JSON file.', 'iraniandubai-core' ); ?>
				</p>
				<?php submit_button( __( 'Export Settings', 'iraniandubai-core' ), 'secondary', 'submit', false ); ?>
			</form>

			<h3><?php esc_html_e( 'Import Settings', 'iraniandubai-core' ); ?></h3>

			<form method="post">
				<?php wp_nonce_field( self::IMPORT_ACTION, self::IMPORT_NONCE ); ?>
				<input type="hidden" name="idb_settings_action" value="import" />
				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="idb_import_json">
								<?php esc_html_e( 'Import settings JSON', 'iraniandubai-core' ); ?>
							</label>
						</th>
						<td>
							<textarea
								id="idb_import_json"
								name="idb_import_json"
								rows="8"
								class="large-text code"
								placeholder="<?php echo esc_attr( $example ); ?>"
								aria-describedby="idb_import_json_description"
							></textarea>
							<p id="idb_import_json_description" class="description">
								<?php
								esc_html_e(
									'Paste a JSON export from IranianDubai Core. Imported values are validated and clamped to the allowed settings ranges.',
									'iraniandubai-core'
								);
								?>
							</p>
						</td>
					</tr>
				</table>
				<?php submit_button( __( 'Import Settings', 'iraniandubai-core' ), 'secondary', 'submit', false ); ?>
			</form>

			<hr />

			<h2><?php esc_html_e( 'About', 'iraniandubai-core' ); ?></h2>
			<table class="widefat striped">
				<tbody>
					<tr>
						<th scope="row"><?php esc_html_e( 'Plugin', 'iraniandubai-core' ); ?></th>
						<td><?php esc_html_e( 'IranianDubai Core', 'iraniandubai-core' ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Version', 'iraniandubai-core' ); ?></th>
						<td><?php echo esc_html( IDB_CORE_VERSION ); ?></td>
					</tr>
					<tr>
						<th scope="row"><?php esc_html_e( 'Purpose', 'iraniandubai-core' ); ?></th>
						<td><?php esc_html_e( 'Core blog, shortcode, Elementor, and settings tools for the IranianDubai website.', 'iraniandubai-core' ); ?></td>
					</tr>
				</tbody>
			</table>

		</div>

		<?php

	}

	/**
	 * Get layout select options.
	 *
	 * @return array<string,string>
	 */
	private function get_layout_options(): array {
		return array(
			'grid'     => __( 'Grid', 'iraniandubai-core' ),
			'list'     => __( 'List', 'iraniandubai-core' ),
			'magazine' => __( 'Magazine', 'iraniandubai-core' ),
			'minimal'  => __( 'Minimal', 'iraniandubai-core' ),
		);
	}

	/**
	 * Get frontend pagination mode select options.
	 *
	 * @return array<string,string>
	 */
	private function get_pagination_mode_options(): array {
		return array(
			'pagination'      => __( 'Pagination', 'iraniandubai-core' ),
			'load_more'       => __( 'Load More', 'iraniandubai-core' ),
			'infinite_scroll' => __( 'Infinite Scroll', 'iraniandubai-core' ),
		);
	}

	/**
	 * Get taxonomy term select options.
	 *
	 * @param string $taxonomy Taxonomy name.
	 *
	 * @return array<int,string>
	 */
	private function get_term_options( string $taxonomy ): array {
		$terms = get_terms(
			array(
				'hide_empty' => false,
				'taxonomy'   => $taxonomy,
			)
		);

		if ( ! is_array( $terms ) ) {
			return array();
		}

		$options = array();

		foreach ( $terms as $term ) {
			if ( $term instanceof \WP_Term ) {
				$options[ $term->term_id ] = $term->name;
			}
		}

		return $options;
	}

	/**
	 * Get author select options.
	 *
	 * @return array<int,string>
	 */
	private function get_author_options(): array {
		$users = get_users(
			array(
				'has_published_posts' => array( 'post' ),
				'orderby'             => 'display_name',
				'order'               => 'ASC',
			)
		);

		$options = array();

		foreach ( $users as $user ) {
			if ( $user instanceof \WP_User ) {
				$options[ $user->ID ] = $user->display_name;
			}
		}

		return $options;
	}

	/**
	 * Get sticky mode select options.
	 *
	 * @return array<string,string>
	 */
	private function get_sticky_mode_options(): array {
		return array(
			'include_all'    => __( 'Include all posts', 'iraniandubai-core' ),
			'only_sticky'    => __( 'Only sticky posts', 'iraniandubai-core' ),
			'exclude_sticky' => __( 'Exclude sticky posts', 'iraniandubai-core' ),
		);
	}

	/**
	 * Get orderby select options.
	 *
	 * @return array<string,string>
	 */
	private function get_orderby_options(): array {
		return array(
			'date'          => __( 'Date', 'iraniandubai-core' ),
			'modified'      => __( 'Modified Date', 'iraniandubai-core' ),
			'title'         => __( 'Title', 'iraniandubai-core' ),
			'comment_count' => __( 'Comment Count', 'iraniandubai-core' ),
			'rand'          => __( 'Random', 'iraniandubai-core' ),
			'menu_order'    => __( 'Menu Order', 'iraniandubai-core' ),
		);
	}

	/**
	 * Get order select options.
	 *
	 * @return array<string,string>
	 */
	private function get_order_options(): array {
		return array(
			'DESC' => __( 'Descending', 'iraniandubai-core' ),
			'ASC'  => __( 'Ascending', 'iraniandubai-core' ),
		);
	}

	/**
	 * Get supported shortcode attribute documentation rows.
	 *
	 * @return array<int,array{name:string,default:string,description:string}>
	 */
	private function get_shortcode_attribute_rows(): array {
		return array(
			array(
				'name'        => 'category',
				'default'     => __( 'All categories', 'iraniandubai-core' ),
				'description' => __( 'Category slug or ID to show posts from one category.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'columns',
				'default'     => __( 'Saved display setting', 'iraniandubai-core' ),
				'description' => __( 'Desktop columns for the blog grid.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'excerpt',
				'default'     => __( 'Saved display setting', 'iraniandubai-core' ),
				'description' => __( 'Maximum excerpt words shown before Read More.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'order',
				'default'     => __( 'Saved query setting', 'iraniandubai-core' ),
				'description' => __( 'Post order. Supports ASC or DESC.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'orderby',
				'default'     => __( 'Saved query setting', 'iraniandubai-core' ),
				'description' => __( 'Sort field. Supports date, title, modified, menu_order, rand, comment_count, or ID.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'pagination',
				'default'     => 'yes',
				'description' => __( 'Enable or disable pagination.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'paged',
				'default'     => '1',
				'description' => __( 'Current pagination page for direct shortcode use.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'posts',
				'default'     => __( 'Saved blog setting', 'iraniandubai-core' ),
				'description' => __( 'Alternative post count attribute.', 'iraniandubai-core' ),
			),
			array(
				'name'        => 'posts_per_page',
				'default'     => __( 'Saved blog setting', 'iraniandubai-core' ),
				'description' => __( 'Number of posts displayed on each page.', 'iraniandubai-core' ),
			),
		);
	}

	/**
	 * Read and sanitize submitted settings.
	 *
	 * @return array<string,mixed>
	 */
	private function sanitize_posted_options(): array {
		$posted = array(
			'posts_per_page'     => Defaults::SETTINGS['posts_per_page'],
			'excerpt_length'     => Defaults::SETTINGS['excerpt_length'],
			'columns'            => Defaults::SETTINGS['columns'],
			'layout'             => Defaults::SETTINGS['layout'],
			'pagination_mode'    => Defaults::SETTINGS['pagination_mode'],
			'include_categories' => Defaults::SETTINGS['include_categories'],
			'exclude_categories' => Defaults::SETTINGS['exclude_categories'],
			'include_tags'       => Defaults::SETTINGS['include_tags'],
			'exclude_tags'       => Defaults::SETTINGS['exclude_tags'],
			'author'             => Defaults::SETTINGS['author'],
			'sticky_posts_mode'  => Defaults::SETTINGS['sticky_posts_mode'],
			'offset'             => Defaults::SETTINGS['offset'],
			'orderby'            => Defaults::SETTINGS['orderby'],
			'order'              => Defaults::SETTINGS['order'],
		);

		foreach ( array_keys( $posted ) as $key ) {
			if ( isset( $_POST[ $key ] ) ) {
				$posted[ $key ] = wp_unslash( $_POST[ $key ] );
			}
		}

		return Defaults::sanitize( $posted );
	}

	/**
	 * Check whether decoded JSON contains at least one supported setting.
	 *
	 * @param array<string,mixed> $settings Decoded settings candidate.
	 *
	 * @return bool
	 */
	private function has_importable_settings( array $settings ): bool {
		foreach ( array_keys( Defaults::SETTINGS ) as $key ) {
			if ( array_key_exists( $key, $settings ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Redirect back to the settings page with an import/export notice.
	 *
	 * @param string $notice Notice key.
	 *
	 * @return void
	 */
	private function redirect_with_notice( string $notice ): void {
		wp_safe_redirect(
			add_query_arg(
				'idb_notice',
				sanitize_key( $notice ),
				menu_page_url(
					self::PAGE_SLUG,
					false
				)
			)
		);

		exit;
	}

	/**
	 * Render import/export admin notices.
	 *
	 * @return void
	 */
	private function render_import_export_notice(): void {
		if ( ! isset( $_GET['idb_notice'] ) ) {
			return;
		}

		$notice   = sanitize_key( wp_unslash( $_GET['idb_notice'] ) );
		$messages = array(
			'export_error'   => array(
				'error',
				__( 'Settings export failed. Please try again.', 'iraniandubai-core' ),
			),
			'import_empty'   => array(
				'warning',
				__( 'Import failed. Please paste a JSON settings export.', 'iraniandubai-core' ),
			),
			'import_invalid' => array(
				'error',
				__( 'Import failed. The JSON does not contain valid IranianDubai Core settings.', 'iraniandubai-core' ),
			),
			'imported'       => array(
				'success',
				__( 'Settings imported successfully.', 'iraniandubai-core' ),
			),
		);

		if ( ! isset( $messages[ $notice ] ) ) {
			return;
		}

		$type    = $messages[ $notice ][0];
		$message = $messages[ $notice ][1];
		?>
		<div class="notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo esc_html( $message ); ?></p>
		</div>
		<?php
	}

	/**
	 * Get a safe charset value for export headers.
	 *
	 * @return string
	 */
	private function get_blog_charset(): string {
		$charset = (string) get_option( 'blog_charset', 'UTF-8' );
		$charset = preg_replace( '/[^A-Za-z0-9_-]/', '', $charset );

		return is_string( $charset ) && '' !== $charset ? $charset : 'UTF-8';
	}

	/**
	 * Check whether the active admin locale is Persian.
	 *
	 * @return bool
	 */
	private function is_persian_locale(): bool {
		return str_starts_with( get_locale(), 'fa' ) || str_starts_with( determine_locale(), 'fa' );
	}

}

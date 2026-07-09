<?php
/**
 * Blog hero component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<header class="idb-blog__hero">
	<div class="idb-blog__hero-content">
		<p class="idb-blog__eyebrow"><?php esc_html_e( 'مجله ایرانیان دبی', 'iraniandubai-core' ); ?></p>
		<h1 class="idb-blog__heading"><?php esc_html_e( 'راهنمای زندگی و رشد ایرانیان در دبی', 'iraniandubai-core' ); ?></h1>
		<p class="idb-blog__intro"><?php esc_html_e( 'مطالب کاربردی، تازه‌ترین راهنماها و روایت‌های عملی برای زندگی، سرمایه‌گذاری و رشد در دبی.', 'iraniandubai-core' ); ?></p>

		<form
			class="idb-blog__search"
			action="<?php echo esc_url( $blog_renderer->get_search_url() ); ?>"
			method="get"
			role="search"
		>
			<?php if ( '' !== $selected_category ) : ?>
				<input type="hidden" name="idb_category" value="<?php echo esc_attr( $selected_category ); ?>" />
			<?php endif; ?>

			<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>">
				<?php esc_html_e( 'جستجو در مطالب', 'iraniandubai-core' ); ?>
			</label>
			<input
				id="<?php echo esc_attr( $search_id ); ?>"
				class="idb-blog__search-input"
				type="search"
				name="idb_search"
				value="<?php echo esc_attr( $selected_search ); ?>"
				placeholder="<?php esc_attr_e( 'جستجو در مقالات', 'iraniandubai-core' ); ?>"
			/>
			<button class="idb-blog__search-button" type="submit">
				<?php esc_html_e( 'جستجو', 'iraniandubai-core' ); ?>
			</button>
		</form>
	</div>
</header>

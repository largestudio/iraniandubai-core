<?php
/**
 * Blog sidebar component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( empty( $filter_categories ) ) {
	return;
}
?>

<aside class="idb-blog__sidebar" aria-label="<?php esc_attr_e( 'Blog sidebar', 'iraniandubai-core' ); ?>">
	<div class="idb-blog-sidebar-card">
		<h2 class="idb-blog-sidebar-card__title"><?php esc_html_e( 'دسته‌بندی‌ها', 'iraniandubai-core' ); ?></h2>
		<ul class="idb-blog-sidebar-card__list">
			<?php foreach ( $filter_categories as $filter_category ) : ?>
				<li>
					<a href="<?php echo esc_url( $blog_renderer->get_category_filter_url( $filter_category->slug ) ); ?>">
						<?php echo esc_html( $filter_category->name ); ?>
					</a>
				</li>
			<?php endforeach; ?>
		</ul>
	</div>
</aside>

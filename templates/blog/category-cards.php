<?php
/**
 * Blog category cards component.
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

<nav class="idb-blog__filters idb-blog__category-cards" aria-label="<?php esc_attr_e( 'Blog category filter', 'iraniandubai-core' ); ?>">
	<a class="idb-blog__filter-link <?php echo esc_attr( '' === $selected_category ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $blog_renderer->get_category_filter_url( '' ) ); ?>">
		<span class="idb-blog__filter-title"><?php esc_html_e( 'همه مطالب', 'iraniandubai-core' ); ?></span>
	</a>

	<?php foreach ( $filter_categories as $filter_category ) : ?>
		<a class="idb-blog__filter-link <?php echo esc_attr( $selected_category === $filter_category->slug ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $blog_renderer->get_category_filter_url( $filter_category->slug ) ); ?>">
			<span class="idb-blog__filter-title"><?php echo esc_html( $filter_category->name ); ?></span>
		</a>
	<?php endforeach; ?>
</nav>

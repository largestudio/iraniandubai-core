<?php
/**
 * Elementor Blog Grid template.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$component_path = static function ( string $component ): string {
	return IDB_CORE_PATH . 'templates/blog/' . $component . '.php';
};
?>

<section
	class="idb-blog idb-blog--elementor-grid idb-blog--layout-<?php echo esc_attr( (string) $layout ); ?> idb-blog--pagination-<?php echo esc_attr( $pagination_mode ); ?>"
	data-idb-blog
	data-idb-blog-atts="<?php echo esc_attr( $ajax_atts ); ?>"
	data-idb-blog-pagination-mode="<?php echo esc_attr( $pagination_mode ); ?>"
	dir="rtl"
	aria-label="<?php esc_attr_e( 'Blog posts', 'iraniandubai-core' ); ?>"
>
	<?php if ( $blog_query->have_posts() ) : ?>
		<main class="idb-blog__main">
			<?php include $component_path( 'grid' ); ?>
			<?php include $component_path( 'pagination' ); ?>
		</main>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- BlogRenderer returns escaped JSON-LD script markup.
		echo $blog_renderer->get_schema( $blog_query );
		?>
	<?php else : ?>
		<p class="idb-blog__empty"><?php esc_html_e( 'No posts found.', 'iraniandubai-core' ); ?></p>
	<?php endif; ?>
</section>

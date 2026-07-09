<?php
/**
 * Blog shortcode template.
 *
 * @package IranianDubaiCore
 *
 * @var WP_Query                  $blog_query    Blog posts query.
 * @var IDB\Frontend\BlogRenderer $blog_renderer Blog renderer.
 * @var array<string,mixed>       $blog_atts     Blog shortcode attributes.
 * @var int                       $blog_paged    Current pagination page.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if (
	! isset( $blog_query, $blog_renderer, $blog_atts )
	|| ! $blog_query instanceof WP_Query
	|| ! $blog_renderer instanceof IDB\Frontend\BlogRenderer
	|| ! is_array( $blog_atts )
) {
	return;
}

$selected_category = $blog_renderer->get_selected_category( $blog_atts );
$selected_search   = $blog_renderer->get_selected_search();
$filter_categories = $blog_renderer->get_filter_categories( $blog_atts );
$columns           = isset( $blog_atts['columns'] ) ? absint( $blog_atts['columns'] ) : 2;
$excerpt_length    = isset( $blog_atts['excerpt'] ) ? absint( $blog_atts['excerpt'] ) : 24;
$layout            = isset( $blog_atts['layout'] ) ? sanitize_html_class( (string) $blog_atts['layout'] ) : 'grid';
$pagination_mode   = sanitize_html_class( $blog_renderer->get_pagination_mode( $blog_atts ) );
$current_page      = isset( $blog_paged ) ? max( 1, absint( $blog_paged ) ) : $blog_renderer->get_current_page( $blog_atts );
$has_next_page     = $current_page < (int) $blog_query->max_num_pages;
$next_page_url     = $has_next_page ? $blog_renderer->get_page_url( $current_page + 1 ) : '';
$ajax_atts         = $blog_renderer->get_ajax_attributes( $blog_atts );
$search_id         = wp_unique_id( 'idb-blog-search-' );
$component_path    = static function ( string $component ): string {
	return IDB_CORE_PATH . 'templates/blog/' . $component . '.php';
};
?>

<section
	class="idb-blog idb-blog--layout-<?php echo esc_attr( $layout ); ?> idb-blog--pagination-<?php echo esc_attr( $pagination_mode ); ?>"
	data-idb-blog
	data-idb-blog-atts="<?php echo esc_attr( $ajax_atts ); ?>"
	data-idb-blog-pagination-mode="<?php echo esc_attr( $pagination_mode ); ?>"
	dir="rtl"
	aria-label="<?php esc_attr_e( 'Latest blog posts', 'iraniandubai-core' ); ?>"
>
	<?php include $component_path( 'hero' ); ?>
	<?php include $component_path( 'category-cards' ); ?>

	<?php if ( $blog_query->have_posts() ) : ?>
		<?php include $component_path( 'featured' ); ?>

		<div class="idb-blog__content-layout">
			<main class="idb-blog__main">
				<?php include $component_path( 'grid' ); ?>
				<?php include $component_path( 'pagination' ); ?>
			</main>

			<?php include $component_path( 'sidebar' ); ?>
		</div>

		<?php
		// phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- BlogRenderer returns escaped JSON-LD script markup.
		echo $blog_renderer->get_schema( $blog_query );
		?>
	<?php else : ?>
		<p class="idb-blog__empty"><?php esc_html_e( 'No posts found.', 'iraniandubai-core' ); ?></p>
	<?php endif; ?>

	<?php include $component_path( 'cta' ); ?>
</section>

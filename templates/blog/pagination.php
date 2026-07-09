<?php
/**
 * Blog pagination component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<?php if ( $blog_renderer->has_pagination( $blog_atts ) && $blog_query->max_num_pages > 1 && 'pagination' === $pagination_mode ) : ?>
	<nav class="idb-blog__pagination" aria-label="<?php esc_attr_e( 'Blog pagination', 'iraniandubai-core' ); ?>">
		<?php
		$big      = 999999999;
		$add_args = array();

		$pagination_args = array(
			'base'      => $blog_renderer->get_pagination_base( $big ),
			'format'    => $blog_renderer->get_pagination_format(),
			'total'     => $blog_query->max_num_pages,
			'current'   => $current_page,
			'prev_text' => '&lsaquo;',
			'next_text' => '&rsaquo;',
		);

		if ( '' !== $selected_category ) {
			$add_args['idb_category'] = $selected_category;
		}

		if ( '' !== $selected_search ) {
			$add_args['idb_search'] = $selected_search;
		}

		if ( ! empty( $add_args ) ) {
			$pagination_args['add_args'] = $add_args;
		}

		$pagination = paginate_links( $pagination_args );

		if ( is_string( $pagination ) ) {
			echo wp_kses_post( $pagination );
		}
		?>
	</nav>
<?php endif; ?>

<?php if ( $blog_renderer->has_pagination( $blog_atts ) && $has_next_page && 'load_more' === $pagination_mode ) : ?>
	<div class="idb-blog__load-more">
		<a
			class="idb-blog__load-more-button"
			href="<?php echo esc_url( $next_page_url ); ?>"
			data-idb-blog-load-more
		>
			<?php echo esc_html( $blog_renderer->get_load_more_text() ); ?>
		</a>
	</div>
<?php endif; ?>

<?php if ( $blog_renderer->has_pagination( $blog_atts ) && $has_next_page && 'infinite_scroll' === $pagination_mode ) : ?>
	<div class="idb-blog__infinite" data-idb-blog-infinite>
		<a
			class="idb-blog__infinite-link"
			href="<?php echo esc_url( $next_page_url ); ?>"
			data-idb-blog-infinite-link
		>
			<?php echo esc_html( $blog_renderer->get_load_more_text() ); ?>
		</a>
	</div>
<?php endif; ?>

<?php
/**
 * Blog shortcode template.
 *
 * @package IranianDubaiCore
 *
 * @var WP_Query                  $blog_query    Blog posts query.
 * @var IDB\Frontend\BlogRenderer $blog_renderer Blog renderer.
 * @var array<string,mixed>       $blog_atts     Blog shortcode attributes.
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
$filter_categories = $blog_renderer->get_filter_categories( $blog_atts );
$columns           = isset( $blog_atts['columns'] ) ? absint( $blog_atts['columns'] ) : 2;
$excerpt_length    = isset( $blog_atts['excerpt'] ) ? absint( $blog_atts['excerpt'] ) : 24;
?>

<section class="idb-blog" aria-label="<?php esc_attr_e( 'Latest blog posts', 'iraniandubai-core' ); ?>">
	<?php if ( ! empty( $filter_categories ) ) : ?>
		<nav class="idb-blog__filters" aria-label="<?php esc_attr_e( 'Blog category filter', 'iraniandubai-core' ); ?>">
			<a class="idb-blog__filter-link <?php echo esc_attr( '' === $selected_category ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $blog_renderer->get_category_filter_url( '' ) ); ?>">
				<?php esc_html_e( 'All', 'iraniandubai-core' ); ?>
			</a>

			<?php foreach ( $filter_categories as $filter_category ) : ?>
				<a class="idb-blog__filter-link <?php echo esc_attr( $selected_category === $filter_category->slug ? 'is-active' : '' ); ?>" href="<?php echo esc_url( $blog_renderer->get_category_filter_url( $filter_category->slug ) ); ?>">
					<?php echo esc_html( $filter_category->name ); ?>
				</a>
			<?php endforeach; ?>
		</nav>
	<?php endif; ?>

	<?php if ( $blog_query->have_posts() ) : ?>
		<div class="idb-blog__grid idb-blog__grid--columns-<?php echo esc_attr( (string) $columns ); ?>">
			<?php
			while ( $blog_query->have_posts() ) :
				$blog_query->the_post();

				$post_id      = get_the_ID();
				$permalink    = get_permalink( $post_id );
				$category     = $blog_renderer->get_category( $post_id );
				$reading_time = $blog_renderer->get_read_time( $post_id );
				?>
				<article <?php post_class( 'idb-blog-card' ); ?>>
					<a class="idb-blog-card__media" href="<?php echo esc_url( $permalink ); ?>" aria-label="<?php echo esc_attr( get_the_title() ); ?>">
						<?php echo wp_kses_post( $blog_renderer->get_image( $post_id ) ); ?>
					</a>

					<div class="idb-blog-card__content">
						<div class="idb-blog-card__meta">
							<?php if ( null !== $category ) : ?>
								<a class="idb-blog-card__category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
									<?php echo esc_html( $category->name ); ?>
								</a>
							<?php endif; ?>

							<span class="idb-blog-card__reading-time">
								<?php
								printf(
									/* translators: %d: Estimated reading time in minutes. */
									esc_html( _n( '%d min read', '%d min read', $reading_time, 'iraniandubai-core' ) ),
									absint( $reading_time )
								);
								?>
							</span>
						</div>

						<h2 class="idb-blog-card__title">
							<a href="<?php echo esc_url( $permalink ); ?>">
								<?php echo esc_html( get_the_title() ); ?>
							</a>
						</h2>

						<?php if ( $excerpt_length > 0 ) : ?>
							<div class="idb-blog-card__excerpt">
								<p><?php echo esc_html( $blog_renderer->get_excerpt( $post_id, $excerpt_length ) ); ?></p>
							</div>
						<?php endif; ?>

						<footer class="idb-blog-card__footer">
							<time class="idb-blog-card__date" datetime="<?php echo esc_attr( get_the_date( DATE_W3C ) ); ?>">
								<?php echo esc_html( get_the_date() ); ?>
							</time>

							<a class="idb-blog-card__button" href="<?php echo esc_url( $permalink ); ?>">
								<?php echo esc_html( html_entity_decode( '&#1575;&#1583;&#1575;&#1605;&#1607; &#1605;&#1591;&#1604;&#1576;', ENT_QUOTES, 'UTF-8' ) ); ?>
							</a>
						</footer>
					</div>
				</article>
			<?php endwhile; ?>
		</div>

		<?php if ( $blog_renderer->has_pagination( $blog_atts ) && $blog_query->max_num_pages > 1 ) : ?>
			<nav class="idb-blog__pagination" aria-label="<?php esc_attr_e( 'Blog pagination', 'iraniandubai-core' ); ?>">
				<?php
				$big = 999999999;

$pagination_args = array(
	'base'      => str_replace(
		$big,
		'%#%',
		esc_url( get_pagenum_link( $big ) )
	),
	'format'    => '?paged=%#%',
	'total'     => $blog_query->max_num_pages,
	'current'   => max( 1, get_query_var( 'paged' ) ),
	'prev_text' => '«',
	'next_text' => '»',
);

				if ( '' !== $selected_category ) {
					$pagination_args['add_args'] = array(
						'idb_category' => $selected_category,
					);
				}

				$pagination = paginate_links( $pagination_args );

				if ( is_string( $pagination ) ) {
					echo wp_kses_post( $pagination );
				}
				?>
			</nav>
		<?php endif; ?>
	<?php else : ?>
		<p class="idb-blog__empty"><?php esc_html_e( 'No posts found.', 'iraniandubai-core' ); ?></p>
	<?php endif; ?>
</section>

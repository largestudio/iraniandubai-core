<?php
/**
 * Blog grid component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="idb-blog__grid idb-blog__grid--columns-<?php echo esc_attr( (string) $columns ); ?>" data-idb-blog-grid>
	<?php
	$blog_index = 0;

	while ( $blog_query->have_posts() ) :
		$blog_query->the_post();

		$post_id      = get_the_ID();
		$post_title   = get_the_title();
		$title_id     = 'idb-blog-card-title-' . $post_id;
		$permalink    = get_permalink( $post_id );
		$category     = $blog_renderer->get_category( $post_id );
		$reading_time = $blog_renderer->get_read_time( $post_id );
		$is_priority  = 0 === $blog_index;
		++$blog_index;
		?>
		<article <?php post_class( 'idb-blog-card' ); ?> aria-labelledby="<?php echo esc_attr( $title_id ); ?>">
			<a
				class="idb-blog-card__media"
				href="<?php echo esc_url( $permalink ); ?>"
				aria-label="<?php echo esc_attr( sprintf(
					/* translators: %s: Post title. */
					__( 'Read %s', 'iraniandubai-core' ),
					$post_title
				) ); ?>"
			>
				<?php echo wp_kses_post( $blog_renderer->get_image( $post_id, $is_priority ) ); ?>
			</a>

			<div class="idb-blog-card__content">
				<div class="idb-blog-card__meta">
					<?php if ( null !== $category ) : ?>
						<a class="idb-blog-card__category" href="<?php echo esc_url( get_category_link( $category->term_id ) ); ?>">
							<?php echo esc_html( $category->name ); ?>
						</a>
					<?php endif; ?>

					<span class="idb-blog-card__reading-time">
						<?php echo esc_html( $blog_renderer->get_read_time_text( $reading_time ) ); ?>
					</span>
				</div>

				<h2 id="<?php echo esc_attr( $title_id ); ?>" class="idb-blog-card__title">
					<a href="<?php echo esc_url( $permalink ); ?>">
						<?php echo esc_html( $post_title ); ?>
					</a>
				</h2>

				<?php if ( $excerpt_length > 0 ) : ?>
					<div class="idb-blog-card__excerpt">
						<p><?php echo esc_html( $blog_renderer->get_excerpt( $post_id, $excerpt_length ) ); ?></p>
					</div>
				<?php endif; ?>

				<footer class="idb-blog-card__footer">
					<time
						class="idb-blog-card__date"
						datetime="<?php echo esc_attr( get_post_time( DATE_W3C, true, $post_id ) ); ?>"
						dir="<?php echo esc_attr( $blog_renderer->get_display_date_direction() ); ?>"
						style="unicode-bidi: isolate;"
					>
						<?php echo esc_html( $blog_renderer->get_display_date( $post_id ) ); ?>
					</time>

					<a
						class="idb-blog-card__button"
						href="<?php echo esc_url( $permalink ); ?>"
						aria-label="<?php echo esc_attr( sprintf(
							/* translators: %s: Post title. */
							__( 'Continue reading %s', 'iraniandubai-core' ),
							$post_title
						) ); ?>"
					>
						<?php echo esc_html( $blog_renderer->get_read_more_text() ); ?>
					</a>
				</footer>
			</div>
		</article>
	<?php endwhile; ?>
</div>

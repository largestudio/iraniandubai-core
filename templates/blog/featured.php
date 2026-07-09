<?php
/**
 * Blog featured section component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$featured_post = isset( $blog_query->posts[0] ) && $blog_query->posts[0] instanceof WP_Post ? $blog_query->posts[0] : null;

if ( null === $featured_post ) {
	return;
}

$featured_id       = (int) $featured_post->ID;
$featured_title    = get_the_title( $featured_id );
$featured_url      = get_permalink( $featured_id );
$featured_category = $blog_renderer->get_category( $featured_id );
?>

<section class="idb-blog__featured" aria-label="<?php esc_attr_e( 'Featured articles', 'iraniandubai-core' ); ?>">
	<div class="idb-blog__section-heading">
		<p class="idb-blog__section-kicker"><?php esc_html_e( 'پیشنهاد ویژه', 'iraniandubai-core' ); ?></p>
		<h2><?php esc_html_e( 'انتخاب سردبیر', 'iraniandubai-core' ); ?></h2>
	</div>

	<article class="idb-blog-featured-card">
		<a class="idb-blog-featured-card__media" href="<?php echo esc_url( $featured_url ); ?>">
			<?php echo wp_kses_post( $blog_renderer->get_image( $featured_id, true ) ); ?>
		</a>

		<div class="idb-blog-featured-card__content">
			<?php if ( null !== $featured_category ) : ?>
				<a class="idb-blog-card__category" href="<?php echo esc_url( get_category_link( $featured_category->term_id ) ); ?>">
					<?php echo esc_html( $featured_category->name ); ?>
				</a>
			<?php endif; ?>

			<h3 class="idb-blog-featured-card__title">
				<a href="<?php echo esc_url( $featured_url ); ?>"><?php echo esc_html( $featured_title ); ?></a>
			</h3>

			<p class="idb-blog-featured-card__excerpt"><?php echo esc_html( $blog_renderer->get_excerpt( $featured_id, $excerpt_length ) ); ?></p>
		</div>
	</article>
</section>

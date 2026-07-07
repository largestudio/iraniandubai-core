<?php
/**
 * Elementor Blog Renderer.
 *
 * @package IranianDubaiCore
 */

namespace IDB\Elementor;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Responsible for rendering blog cards inside Elementor.
 */
final class Renderer {

	/**
	 * Render widget.
	 *
	 * @param array $settings Widget settings.
	 *
	 * @return string
	 */
	public function render( array $settings ): string {

		$defaults = array(
			'posts'      => 6,
			'columns'    => 2,
			'category'   => '',
			'orderby'    => 'date',
			'order'      => 'DESC',
			'excerpt'    => 24,
			'pagination' => false,
		);

		$settings = wp_parse_args( $settings, $defaults );

		$query = new \WP_Query(
			array(
				'post_type'      => 'post',
				'post_status'    => 'publish',
				'posts_per_page' => absint( $settings['posts'] ),
				'orderby'        => sanitize_key( $settings['orderby'] ),
				'order'          => sanitize_text_field( $settings['order'] ),
			)
		);

		ob_start();

		?>

		<div class="idb-widget">

			<div class="idb-widget__grid">

		<?php

while ( $query->have_posts() ) :

	$query->the_post();

	$thumbnail = get_the_post_thumbnail_url( get_the_ID(), 'large' );

	?>

	<article class="idb-widget-card">

		<?php if ( $thumbnail ) : ?>

			<div class="idb-widget-card__media">

				<a href="<?php echo esc_url( get_permalink() ); ?>">

					<img
						src="<?php echo esc_url( $thumbnail ); ?>"
						alt="<?php echo esc_attr( get_the_title() ); ?>"
						loading="lazy"
					/>

				</a>

			</div>

		<?php endif; ?>

		<div class="idb-widget-card__content">

			<?php

			$category = get_the_category();

			if ( ! empty( $category ) ) :

				?>

				<span class="idb-widget-card__category">

					<?php echo esc_html( $category[0]->name ); ?>

				</span>

			<?php endif; ?>

			<h3 class="idb-widget-card__title">

				<a href="<?php echo esc_url( get_permalink() ); ?>">

					<?php the_title(); ?>

				</a>

			</h3>

			<div class="idb-widget-card__meta">

				<span>

					<?php echo esc_html( get_the_date() ); ?>

				</span>

			</div>

			<div class="idb-widget-card__excerpt">

				<?php

				echo esc_html(

					wp_trim_words(

						wp_strip_all_tags( get_the_excerpt() ),

						(int) $settings['excerpt']

					)

				);

				?>

			</div>

			<a
				class="idb-widget-card__button"
				href="<?php echo esc_url( get_permalink() ); ?>"
			>

				<?php esc_html_e( '&#1575;&#1583;&#1575;&#1605;&#1607; &#1605;&#1591;&#1604;&#1576;', 'iraniandubai-core' ); ?>

			</a>

		</div>

	</article>

	<?php

endwhile;

		wp_reset_postdata();

		?>

			</div>

		</div>

		<?php

		return (string) ob_get_clean();

	}
}
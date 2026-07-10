<?php
/**
 * Elementor Blog Hero template.
 *
 * @package IranianDubaiCore
 *
 * @var array<string,mixed>       $settings      Elementor widget settings.
 * @var IDB\Frontend\BlogRenderer $blog_renderer Blog renderer.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$title              = isset( $settings['title'] ) ? (string) $settings['title'] : '';
$description        = isset( $settings['description'] ) ? (string) $settings['description'] : '';
$search_placeholder = isset( $settings['search_placeholder'] ) ? (string) $settings['search_placeholder'] : '';
$search_button_text = isset( $settings['search_button_text'] ) ? (string) $settings['search_button_text'] : '';
$show_search        = 'yes' === ( $settings['show_search'] ?? 'yes' );
$image              = isset( $settings['image'] ) && is_array( $settings['image'] ) ? $settings['image'] : array();
$image_id           = isset( $image['id'] ) ? absint( $image['id'] ) : 0;
$image_url          = isset( $image['url'] ) ? esc_url_raw( (string) $image['url'] ) : '';
$search_id          = wp_unique_id( 'idb-elementor-blog-search-' );
$selected_search    = $blog_renderer->get_selected_search();
?>

<section class="idb-blog idb-blog--elementor-hero" dir="rtl">
	<header class="idb-blog__hero">
		<div class="idb-blog__hero-media" aria-hidden="true">
			<?php if ( $image_id > 0 ) : ?>
				<?php
				echo wp_kses_post(
					wp_get_attachment_image(
						$image_id,
						'large',
						false,
						array(
							'class'    => 'idb-blog-card__image',
							'loading'  => 'eager',
							'decoding' => 'async',
						)
					)
				);
				?>
			<?php elseif ( '' !== $image_url ) : ?>
				<img class="idb-blog-card__image" src="<?php echo esc_url( $image_url ); ?>" alt="" loading="eager" decoding="async" />
			<?php else : ?>
				<span class="idb-blog-card__image idb-blog-card__image--placeholder"></span>
			<?php endif; ?>
			<span class="idb-blog__hero-overlay" aria-hidden="true"></span>
		</div>

		<div class="idb-blog__hero-content">
			<?php if ( '' !== $title ) : ?>
				<h1 class="idb-blog__heading"><?php echo esc_html( $title ); ?></h1>
			<?php endif; ?>

			<?php if ( '' !== $description ) : ?>
				<p class="idb-blog__intro"><?php echo esc_html( $description ); ?></p>
			<?php endif; ?>

			<?php if ( $show_search ) : ?>
				<form
					class="idb-blog__search"
					action="<?php echo esc_url( $blog_renderer->get_search_url() ); ?>"
					method="get"
					role="search"
					data-idb-blog-search
				>
					<label class="screen-reader-text" for="<?php echo esc_attr( $search_id ); ?>">
						<?php esc_html_e( 'Search blog posts', 'iraniandubai-core' ); ?>
					</label>
					<input
						id="<?php echo esc_attr( $search_id ); ?>"
						class="idb-blog__search-input"
						type="search"
						name="idb_search"
						value="<?php echo esc_attr( $selected_search ); ?>"
						placeholder="<?php echo esc_attr( $search_placeholder ); ?>"
					/>
					<button class="idb-blog__search-button" type="submit">
						<?php echo esc_html( $search_button_text ); ?>
					</button>
				</form>
			<?php endif; ?>
		</div>
	</header>
</section>

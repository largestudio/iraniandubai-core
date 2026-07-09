<?php
/**
 * Blog CTA component.
 *
 * @package IranianDubaiCore
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<section class="idb-blog__cta" aria-label="<?php esc_attr_e( 'IranianDubai call to action', 'iraniandubai-core' ); ?>">
	<div>
		<p class="idb-blog__section-kicker"><?php esc_html_e( 'ایرانیان دبی', 'iraniandubai-core' ); ?></p>
		<h2><?php esc_html_e( 'برای قدم بعدی در دبی به راهنمایی عملی نیاز دارید؟', 'iraniandubai-core' ); ?></h2>
	</div>
	<a class="idb-blog__cta-button" href="<?php echo esc_url( home_url( '/' ) ); ?>">
		<?php esc_html_e( 'شروع کنید', 'iraniandubai-core' ); ?>
	</a>
</section>

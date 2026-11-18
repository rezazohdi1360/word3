<?php
/**
 * ردیف افقی محصولات. args: title, more_url, more_label, query (آرگومان‌های WP_Query).
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) || ! class_exists( 'WooCommerce' ) ) {
	return;
}

$title = isset( $args['title'] ) ? $args['title'] : '';
$query = isset( $args['query'] ) ? $args['query'] : array();

$q = new WP_Query(
	wp_parse_args(
		$query,
		array(
			'post_type'           => 'product',
			'post_status'         => 'publish',
			'posts_per_page'      => 10,
			'ignore_sticky_posts' => true,
			'no_found_rows'       => true,
		)
	)
);
if ( ! $q->have_posts() ) {
	return;
}
?>
<section class="ab-section ab-rail"><div class="ab-container">
	<div class="ab-section__head"><h2 class="ab-section__title"><?php echo esc_html( $title ); ?></h2>
	<?php if ( ! empty( $args['more_url'] ) ) : ?>
		<a class="ab-section__more" href="<?php echo esc_url( $args['more_url'] ); ?>"><?php echo esc_html( isset( $args['more_label'] ) ? $args['more_label'] : __( 'مشاهده همه', 'abed' ) ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a>
	<?php endif; ?>
	</div>
	<div class="ab-rail__track" data-ab-rail>
		<ul class="products ab-rail__list">
			<?php
			while ( $q->have_posts() ) {
				$q->the_post();
				global $product;
				$product = wc_get_product( get_the_ID() );
				get_template_part( 'template-parts/product-card' );
			}
			wp_reset_postdata();
			?>
		</ul>
		<button type="button" class="ab-rail__nav ab-rail__nav--prev" data-dir="1" aria-label="<?php esc_attr_e( 'قبلی', 'abed' ); ?>"><?php echo abed_icon( 'chevron-right' ); // phpcs:ignore ?></button>
		<button type="button" class="ab-rail__nav ab-rail__nav--next" data-dir="-1" aria-label="<?php esc_attr_e( 'بعدی', 'abed' ); ?>"><?php echo abed_icon( 'chevron-left' ); // phpcs:ignore ?></button>
	</div>
</div></section>

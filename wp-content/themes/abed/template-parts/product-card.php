<?php
/**
 * کارت محصول (ردیف‌ها و آرشیو). از $product سراسری یا args['product'] استفاده می‌کند.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
$p = isset( $args['product'] ) ? $args['product'] : $product;
if ( ! $p instanceof WC_Product ) {
	return;
}

$id    = $p->get_id();
$min   = abed_min_price( $p );
$max_q = $p->get_max_purchase_quantity();
$unit  = $p->get_meta( '_abed_unit' );
if ( ! $unit ) {
	$unit = $p->get_short_description() ? wp_trim_words( wp_strip_all_tags( $p->get_short_description() ), 6, '' ) : '';
}
$can_buy = $p->is_purchasable() && $p->is_in_stock() && $min > 0;
?>
<li <?php wc_product_class( 'ab-card', $p ); ?>>
	<a class="ab-card__media" href="<?php echo esc_url( $p->get_permalink() ); ?>" tabindex="-1" aria-hidden="true">
		<?php
		echo $p->get_image( 'woocommerce_thumbnail', array( 'class' => 'ab-card__img', 'loading' => 'lazy', 'sizes' => '(max-width: 600px) 40vw, (max-width: 1100px) 28vw, 260px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput
		?>
	</a>
	<div class="ab-card__body">
		<h3 class="ab-card__title"><a href="<?php echo esc_url( $p->get_permalink() ); ?>"><?php echo esc_html( $p->get_name() ); ?></a></h3>
		<?php if ( $unit ) : ?><p class="ab-card__unit"><?php echo esc_html( $unit ); ?></p><?php endif; ?>
		<div class="ab-price ab-price--card">
			<?php if ( $min > 0 ) : ?>
				<span class="ab-price__now"><?php if ( count( abed_get_tiers( $p ) ) > 1 ) : ?><small><?php esc_html_e( 'از', 'abed' ); ?></small> <?php endif; ?><?php echo wp_kses_post( abed_money( $min ) ); ?></span>
			<?php else : ?>
				<span class="ab-price__now ab-price__inq"><?php esc_html_e( 'استعلام قیمت', 'abed' ); ?></span>
			<?php endif; ?>
		</div>
		<?php abed_tiers_table( $p, 'card' ); ?>
	</div>
	<?php if ( $can_buy ) : ?>
	<form class="ab-card__buy" action="<?php echo esc_url( $p->get_permalink() ); ?>" method="post" data-ab-add>
		<div class="ab-qty ab-qty--sm" data-ab-qty>
			<button type="button" class="ab-qty__btn" data-step="1" aria-label="<?php esc_attr_e( 'افزایش تعداد', 'abed' ); ?>"><?php echo abed_icon( 'plus' ); // phpcs:ignore ?></button>
			<label class="screen-reader-text" for="ab-q-<?php echo esc_attr( $id ); ?>"><?php echo esc_html( sprintf( /* translators: %s: product */ __( 'تعداد %s', 'abed' ), $p->get_name() ) ); ?></label>
			<input class="ab-qty__input" type="number" inputmode="numeric" id="ab-q-<?php echo esc_attr( $id ); ?>" name="quantity" value="1" min="1"<?php echo $max_q > 0 ? ' max="' . esc_attr( $max_q ) . '"' : ''; ?> step="1">
			<button type="button" class="ab-qty__btn" data-step="-1" aria-label="<?php esc_attr_e( 'کاهش تعداد', 'abed' ); ?>"><?php echo abed_icon( 'minus' ); // phpcs:ignore ?></button>
		</div>
		<input type="hidden" name="add-to-cart" value="<?php echo esc_attr( $id ); ?>">
		<button type="submit" class="ab-btn ab-btn--primary ab-card__add" rel="nofollow" aria-label="<?php echo esc_attr( sprintf( /* translators: %s: product */ __( 'افزودن %s به لیست خرید', 'abed' ), $p->get_name() ) ); ?>"><?php echo abed_icon( 'bag-plus' ); // phpcs:ignore ?><span><?php esc_html_e( 'افزودن', 'abed' ); ?></span></button>
	</form>
	<?php else : ?>
	<div class="ab-card__buy"><a class="ab-btn ab-btn--ghost ab-card__add" href="<?php echo esc_url( $p->get_permalink() ); ?>"><?php echo $min > 0 ? esc_html__( 'ناموجود — مشاهده', 'abed' ) : esc_html__( 'استعلام قیمت', 'abed' ); ?></a></div>
	<?php endif; ?>
</li>

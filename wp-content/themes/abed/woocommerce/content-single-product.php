<?php
/**
 * صفحه تک‌محصول.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( ! $product instanceof WC_Product ) {
	return;
}

$id      = $product->get_id();
$tiers   = abed_get_tiers( $product );
$min     = abed_min_price( $product );
$max_q   = $product->get_max_purchase_quantity();
$gallery = array_merge( array( $product->get_image_id() ), $product->get_gallery_image_ids() );
$gallery = array_values( array_filter( $gallery ) );
$sales   = abed_contact()['phones'][0]['tel'];
$unit    = $product->get_meta( '_abed_unit' );
$can_buy = $product->is_purchasable() && $product->is_in_stock() && $min > 0;
$attrs   = $product->get_attributes();
?>
<div id="product-<?php echo esc_attr( $id ); ?>" <?php wc_product_class( 'ab-product', $product ); ?>>
	<?php abed_breadcrumbs(); ?>

	<div class="ab-pmain">
		<div class="ab-pmain__gallery">
			<div class="ab-gallery" data-ab-gallery>
				<div class="ab-gallery__main">
					<?php if ( $gallery ) : ?>
						<?php foreach ( $gallery as $i => $img_id ) : ?>
							<?php $full = wp_get_attachment_image_src( $img_id, 'full' ); ?>
							<a class="ab-gallery__slide" href="<?php echo esc_url( $full[0] ); ?>" data-ab-zoom data-w="<?php echo esc_attr( $full[1] ); ?>" data-h="<?php echo esc_attr( $full[2] ); ?>">
								<?php echo wp_get_attachment_image( $img_id, 'woocommerce_single', false, array( 'loading' => 0 === $i ? 'eager' : 'lazy', 'sizes' => '(max-width: 900px) 92vw, 520px' ) ); // phpcs:ignore WordPress.Security.EscapeOutput ?>
							</a>
						<?php endforeach; ?>
					<?php else : ?>
						<div class="ab-gallery--empty"><?php echo wp_kses_post( wc_placeholder_img( 'woocommerce_single' ) ); ?></div>
					<?php endif; ?>
				</div>
				<?php if ( count( $gallery ) > 1 ) : ?>
				<div class="ab-gallery__thumbs">
					<?php foreach ( $gallery as $img_id ) : ?>
						<button type="button" class="ab-gallery__thumb"><?php echo wp_get_attachment_image( $img_id, 'woocommerce_gallery_thumbnail' ); // phpcs:ignore WordPress.Security.EscapeOutput ?></button>
					<?php endforeach; ?>
				</div>
				<?php endif; ?>
			</div>
		</div>

		<div class="ab-pmain__info">
			<h1 class="ab-ptitle product_title entry-title"><?php the_title(); ?></h1>
			<?php if ( $product->get_short_description() ) : ?>
			<div class="ab-pkey">
				<p class="ab-pkey__title"><?php esc_html_e( 'ویژگی‌های اصلی', 'abed' ); ?></p>
				<?php echo wp_kses_post( wpautop( $product->get_short_description() ) ); ?>
				<a class="ab-pkey__all" href="#specs"><?php esc_html_e( 'همه مشخصات', 'abed' ); ?></a>
			</div>
			<?php endif; ?>
		</div>

		<div class="ab-pmain__buy summary entry-summary">
			<div class="ab-buybox" data-ab-buybox>
				<p class="ab-stock <?php echo $product->is_in_stock() ? 'is-in' : 'is-out'; ?>"><?php echo abed_icon( 'check' ); // phpcs:ignore ?><span><?php echo $product->is_in_stock() ? esc_html__( 'موجود در انبار', 'abed' ) : esc_html__( 'ناموجود', 'abed' ); ?></span></p>

				<div class="ab-price ab-price--full">
					<?php if ( $min > 0 ) : ?>
						<span class="ab-price__now"><?php if ( count( $tiers ) > 1 ) : ?><small><?php esc_html_e( 'از', 'abed' ); ?></small> <?php endif; ?><?php echo wp_kses_post( abed_money( $min ) ); ?></span>
						<?php if ( $unit ) : ?><span class="ab-price__unit"><?php echo esc_html( $unit ); ?></span><?php endif; ?>
					<?php else : ?>
						<span class="ab-price__now ab-price__inq"><?php esc_html_e( 'برای قیمت تماس بگیرید', 'abed' ); ?></span>
					<?php endif; ?>
				</div>
				<div class="ab-pricemeta"><span class="ab-updated"><?php echo abed_icon( 'clock' ); // phpcs:ignore ?><?php esc_html_e( 'قیمت به‌روز:', 'abed' ); ?> <time datetime="<?php echo esc_attr( get_the_modified_date( 'c' ) ); ?>"><?php echo esc_html( wp_date( 'j F Y', get_post_modified_time( 'U' ) ) ); ?></time></span></div>

				<?php if ( count( $tiers ) > 1 ) : ?>
				<div class="ab-buybox__tiers">
					<p class="ab-buybox__label"><?php echo abed_icon( 'layers' ); // phpcs:ignore ?> <?php esc_html_e( 'قیمت پلکانی — هرچه بیشتر، ارزان‌تر', 'abed' ); ?></p>
					<?php abed_tiers_table( $product, 'full' ); ?>
				</div>
				<?php endif; ?>

				<?php if ( $max_q > 0 ) : ?>
				<ul class="ab-limits">
					<li><?php echo abed_icon( 'plus' ); // phpcs:ignore ?><span><?php esc_html_e( 'حداکثر هر سفارش از سایت:', 'abed' ); ?> <strong><?php echo esc_html( abed_fa_digits( $max_q ) ); ?></strong></span></li>
					<li class="ab-limits__note"><?php esc_html_e( 'برای بیشتر از سقف با', 'abed' ); ?> <a href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php esc_html_e( 'واحد فروش', 'abed' ); ?></a> <?php esc_html_e( 'تماس بگیرید.', 'abed' ); ?></li>
				</ul>
				<?php endif; ?>

				<?php if ( $can_buy ) : ?>
				<form class="cart ab-addform" action="<?php echo esc_url( $product->get_permalink() ); ?>" method="post" enctype="multipart/form-data" data-ab-add data-ab-single>
					<div class="ab-addform__row">
						<div class="quantity ab-qty" data-ab-qty>
							<button type="button" class="ab-qty__btn" data-step="1" aria-label="<?php esc_attr_e( 'افزایش تعداد', 'abed' ); ?>"><?php echo abed_icon( 'plus' ); // phpcs:ignore ?></button>
							<label class="screen-reader-text" for="quantity_<?php echo esc_attr( $id ); ?>"><?php esc_html_e( 'تعداد', 'abed' ); ?></label>
							<input type="number" id="quantity_<?php echo esc_attr( $id ); ?>" class="input-text qty text ab-qty__input" name="quantity" value="1" min="1"<?php echo $max_q > 0 ? ' max="' . esc_attr( $max_q ) . '"' : ''; ?> step="1" inputmode="numeric" autocomplete="off">
							<button type="button" class="ab-qty__btn" data-step="-1" aria-label="<?php esc_attr_e( 'کاهش تعداد', 'abed' ); ?>"><?php echo abed_icon( 'minus' ); // phpcs:ignore ?></button>
						</div>
						<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $id ); ?>" class="single_add_to_cart_button ab-btn ab-btn--primary ab-btn--lg ab-addform__btn"><?php echo abed_icon( 'bag-plus' ); // phpcs:ignore ?><span><?php esc_html_e( 'افزودن به لیست خرید', 'abed' ); ?></span></button>
					</div>
					<p class="ab-addform__total" data-ab-linetotal hidden><?php esc_html_e( 'جمع:', 'abed' ); ?> <strong></strong> <small></small></p>
				</form>
				<?php else : ?>
				<a class="ab-btn ab-btn--gold ab-btn--lg" href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?> <span><?php esc_html_e( 'استعلام قیمت و موجودی', 'abed' ); ?></span></a>
				<?php endif; ?>

				<p class="ab-delivery"><?php echo abed_icon( 'truck' ); // phpcs:ignore ?><span><?php esc_html_e( 'ارسال از مشهد به سراسر ایران؛ تحویل معمولاً ۱ تا ۴ روز کاری', 'abed' ); ?></span></p>
			</div>

			<div class="ab-phelp"><div class="ab-phelp__text">
				<strong><?php esc_html_e( 'برای خرید عمده محصولات در خدمتیم', 'abed' ); ?></strong>
				<span><?php esc_html_e( 'مشاوره و راهنمایی صحیح، برای ما مهم‌تر از فروش محصول است.', 'abed' ); ?></span></div>
				<div class="ab-phelp__actions">
					<a class="ab-btn ab-btn--primary ab-btn--sm" href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( abed_tel_text( $sales ) ); ?></span></a>
					<a class="ab-btn ab-btn--ghost ab-btn--sm" href="<?php echo esc_url( abed_socials()['واتس‌اپ'] ); ?>" target="_blank" rel="noopener"><?php echo abed_icon( 'chat' ); // phpcs:ignore ?> <?php esc_html_e( 'واتس‌اپ', 'abed' ); ?></a>
				</div>
			</div>
		</div>
	</div>

	<div class="ab-pdetail">
		<div class="ab-pdetail__main">
			<?php if ( $product->get_description() ) : ?>
			<section class="ab-psec ab-psec--description" id="tab-description">
				<h2 class="ab-psec__title"><?php esc_html_e( 'معرفی محصول', 'abed' ); ?></h2>
				<div class="ab-prose"><?php echo wp_kses_post( apply_filters( 'the_content', $product->get_description() ) ); ?></div>
			</section>
			<?php endif; ?>
			<?php if ( comments_open() ) : ?>
			<section class="ab-psec ab-psec--reviews" id="tab-reviews">
				<h2 class="ab-psec__title"><?php esc_html_e( 'دیدگاه‌ها', 'abed' ); ?></h2>
				<?php comments_template(); ?>
			</section>
			<?php endif; ?>
		</div>

		<aside class="ab-pdetail__side">
			<section class="ab-specs" id="specs">
				<h2 class="ab-specs__title"><?php esc_html_e( 'مشخصات محصول', 'abed' ); ?></h2>
				<dl class="ab-specs__list">
					<?php if ( $product->get_sku() ) : ?><div><dt><?php esc_html_e( 'کد محصول', 'abed' ); ?></dt><dd><?php echo esc_html( $product->get_sku() ); ?></dd></div><?php endif; ?>
					<?php if ( $unit ) : ?><div><dt><?php esc_html_e( 'واحد فروش', 'abed' ); ?></dt><dd><?php echo esc_html( $unit ); ?></dd></div><?php endif; ?>
					<?php if ( $product->has_weight() ) : ?><div><dt><?php esc_html_e( 'وزن', 'abed' ); ?></dt><dd><?php echo esc_html( wc_format_weight( $product->get_weight() ) ); ?></dd></div><?php endif; ?>
					<?php foreach ( $attrs as $attr ) : ?>
						<?php
						if ( ! $attr->get_visible() ) {
							continue;
						}
						$vals = $attr->is_taxonomy() ? wc_get_product_terms( $id, $attr->get_name(), array( 'fields' => 'names' ) ) : $attr->get_options();
						?>
						<div><dt><?php echo esc_html( wc_attribute_label( $attr->get_name() ) ); ?></dt><dd><?php echo esc_html( implode( '، ', (array) $vals ) ); ?></dd></div>
					<?php endforeach; ?>
				</dl>
			</section>
		</aside>
	</div>

	<?php
	$related = wc_get_related_products( $id, 10 );
	if ( $related ) {
		get_template_part(
			'template-parts/rail',
			null,
			array(
				'title' => __( 'محصولات مرتبط', 'abed' ),
				'query' => array( 'post__in' => $related, 'orderby' => 'post__in' ),
			)
		);
	}
	?>
</div>

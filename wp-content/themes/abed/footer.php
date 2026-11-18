<?php
/**
 * فوتر قالب.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact = abed_contact();
$sales   = $contact['phones'][0]['tel'];
$has_wc  = class_exists( 'WooCommerce' );
$wa      = abed_socials()['واتس‌اپ'];
$bale    = abed_socials()['بله'];

$foot_cats = array();
if ( $has_wc ) {
	$t = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'number' => 8, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
	$foot_cats = is_wp_error( $t ) ? array() : $t;
}
?>
</main>

<footer class="ab-footer"><div class="ab-container ab-footer__grid">
	<section class="ab-footer__col ab-footer__about">
		<?php abed_logo( 'ab-logo--footer' ); ?>
		<p><?php echo esc_html( abed_mod( 'abed_footer_about' ) ); ?></p>
		<ul class="ab-socials">
			<?php foreach ( abed_socials() as $label => $url ) : ?>
				<?php if ( $url ) : ?><li><a href="<?php echo esc_url( $url ); ?>" target="_blank" rel="noopener"><?php echo abed_icon( 'chat' ); // phpcs:ignore ?><?php echo esc_html( $label ); ?></a></li><?php endif; ?>
			<?php endforeach; ?>
		</ul>
	</section>

	<?php if ( $foot_cats ) : ?>
	<section class="ab-footer__col">
		<h2 class="ab-footer__title"><?php esc_html_e( 'دسته‌بندی‌ها', 'abed' ); ?></h2>
		<ul class="ab-footer__links"><?php foreach ( $foot_cats as $c ) : ?><li><a href="<?php echo esc_url( get_term_link( $c ) ); ?>"><?php echo esc_html( $c->name ); ?></a></li><?php endforeach; ?></ul>
	</section>
	<?php endif; ?>

	<section class="ab-footer__col">
		<h2 class="ab-footer__title"><?php esc_html_e( 'دسترسی سریع', 'abed' ); ?></h2>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'footer',
				'container'      => false,
				'menu_class'     => 'ab-footer__links',
				'fallback_cb'    => 'abed_menu_fallback',
				'depth'          => 1,
			)
		);
		?>
	</section>

	<section class="ab-footer__col ab-footer__contact">
		<h2 class="ab-footer__title"><?php esc_html_e( 'ارتباط با ما', 'abed' ); ?></h2>
		<address>
			<p class="ab-footer__addr"><?php echo abed_icon( 'pin' ); // phpcs:ignore ?><span><?php echo esc_html( $contact['address'] ); ?></span></p>
			<ul class="ab-footer__phones">
				<?php foreach ( $contact['phones'] as $ph ) : ?>
					<?php if ( $ph['tel'] ) : ?><li><span><?php echo esc_html( $ph['label'] ); ?></span><a href="tel:<?php echo esc_attr( abed_tel_href( $ph['tel'] ) ); ?>" dir="ltr"><?php echo esc_html( abed_tel_text( $ph['tel'] ) ); ?></a></li><?php endif; ?>
				<?php endforeach; ?>
			</ul>
		</address>
		<?php if ( abed_mod( 'abed_enamad_img' ) ) : ?>
		<div class="ab-footer__trust"><a referrerpolicy="origin" target="_blank" rel="noopener" href="https://trustseal.enamad.ir/"><img loading="lazy" decoding="async" referrerpolicy="origin" src="<?php echo esc_url( abed_mod( 'abed_enamad_img' ) ); ?>" alt="<?php esc_attr_e( 'نماد اعتماد الکترونیکی', 'abed' ); ?>" width="125" height="136" style="cursor:pointer" code="<?php echo esc_attr( abed_mod( 'abed_enamad_code' ) ); ?>"></a></div>
		<?php endif; ?>
	</section>
</div>
<div class="ab-footer__bottom"><div class="ab-container">
	<p><?php echo esc_html( sprintf( /* translators: 1: site name */ __( 'تمامی حقوق برای %s محفوظ است.', 'abed' ), get_bloginfo( 'name' ) ) ); ?> © <?php echo esc_html( abed_fa_digits( wp_date( 'Y' ) ) ); ?></p>
</div></div></footer>

<nav class="ab-bnav" aria-label="<?php esc_attr_e( 'دسترسی سریع موبایل', 'abed' ); ?>">
	<a class="ab-bnav__i<?php echo is_front_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php echo abed_icon( 'home' ); // phpcs:ignore ?><span><?php esc_html_e( 'خانه', 'abed' ); ?></span></a>
	<button type="button" class="ab-bnav__i" data-ab-open="menu" aria-controls="ab-menu"><?php echo abed_icon( 'grid' ); // phpcs:ignore ?><span><?php esc_html_e( 'دسته‌ها', 'abed' ); ?></span></button>
	<button type="button" class="ab-bnav__i" data-ab-focus-search><?php echo abed_icon( 'search' ); // phpcs:ignore ?><span><?php esc_html_e( 'جستجو', 'abed' ); ?></span></button>
	<?php if ( $has_wc ) : ?>
	<a class="ab-bnav__i" href="<?php echo esc_url( wc_get_cart_url() ); ?>" data-ab-open="cart" aria-controls="ab-cart" role="button"><span class="ab-hact__icon"><?php echo abed_icon( 'bag' ); // phpcs:ignore ?><span class="ab-cartcount" data-n="<?php echo esc_attr( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?>"><?php echo esc_html( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?></span></span><span><?php esc_html_e( 'لیست خرید', 'abed' ); ?></span></a>
	<a class="ab-bnav__i<?php echo function_exists( 'is_account_page' ) && is_account_page() ? ' is-active' : ''; ?>" href="<?php echo esc_url( wc_get_page_permalink( 'myaccount' ) ); ?>"><?php echo abed_icon( 'user' ); // phpcs:ignore ?><span><?php esc_html_e( 'حساب', 'abed' ); ?></span></a>
	<?php endif; ?>
</nav>

<div class="ab-float ab-float--left" data-ab-float>
	<ul class="ab-float__menu" id="ab-float-menu">
		<li><button type="button" data-abed-consult-open="1"><?php echo abed_icon( 'headset' ); // phpcs:ignore ?><span><?php esc_html_e( 'درخواست مشاوره تلفنی', 'abed' ); ?></span></button></li>
		<?php if ( $bale ) : ?><li><a href="<?php echo esc_url( $bale ); ?>" target="_blank" rel="noopener"><?php echo abed_icon( 'chat' ); // phpcs:ignore ?><span><?php esc_html_e( 'پیام در بله', 'abed' ); ?></span></a></li><?php endif; ?>
		<li><a href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo abed_icon( 'chat' ); // phpcs:ignore ?><span><?php esc_html_e( 'پیام در واتس‌اپ', 'abed' ); ?></span></a></li>
		<li><a href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?><span><?php esc_html_e( 'تماس تلفنی', 'abed' ); ?></span></a></li>
	</ul>
	<button type="button" class="ab-float__btn" aria-expanded="false" aria-controls="ab-float-menu" aria-label="<?php esc_attr_e( 'گزینه‌های تماس', 'abed' ); ?>"><?php echo abed_icon( 'phone', 'ab-float__on' ); // phpcs:ignore ?><?php echo abed_icon( 'close', 'ab-float__off' ); // phpcs:ignore ?></button>
</div>

<?php if ( $has_wc ) : ?>
<div class="ab-drawer" id="ab-cart" data-ab-panel="cart" role="dialog" aria-modal="true" aria-labelledby="ab-cart-title" hidden>
	<div class="ab-drawer__head">
		<h2 id="ab-cart-title" class="ab-drawer__title"><?php echo abed_icon( 'bag' ); // phpcs:ignore ?> <?php esc_html_e( 'لیست خرید', 'abed' ); ?> <span class="ab-cartcount" data-n="0">0</span></h2>
		<button type="button" class="ab-iconbtn" data-ab-close aria-label="<?php esc_attr_e( 'بستن', 'abed' ); ?>"><?php echo abed_icon( 'close' ); // phpcs:ignore ?></button>
	</div>
	<div class="ab-drawer__body" data-ab-drawer-body>
		<div class="ab-drawer__empty"><?php echo abed_icon( 'bag' ); // phpcs:ignore ?><p><?php esc_html_e( 'لیست خرید شما خالی است.', 'abed' ); ?></p>
		<a class="ab-btn ab-btn--primary" href="<?php echo esc_url( wc_get_page_permalink( 'shop' ) ); ?>"><?php esc_html_e( 'مشاهده محصولات', 'abed' ); ?></a></div>
	</div>
	<div class="ab-drawer__foot" data-ab-drawer-foot></div>
</div>
<?php endif; ?>

<div class="ab-toast" data-ab-toast role="status" aria-live="polite" hidden></div>

<dialog class="abcs-modal" id="abcs-modal" aria-labelledby="abcs-title" dir="rtl"><div class="abcs-modal__box">
	<button type="button" class="abcs-close" data-abcs-close aria-label="<?php esc_attr_e( 'بستن', 'abed' ); ?>">×</button>
	<h2 id="abcs-title">☎️ <?php esc_html_e( 'درخواست مشاوره تلفنی', 'abed' ); ?></h2>
	<?php get_template_part( 'template-parts/consult-form', null, array( 'ctx' => 'modal' ) ); ?>
</div></dialog>

<?php wp_footer(); ?>
</body>
</html>

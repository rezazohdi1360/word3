<?php
/**
 * هدر قالب.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$contact = abed_contact();
$sales   = $contact['phones'][0]['tel'];
$ticker  = array_filter( array_map( 'trim', explode( "\n", (string) get_theme_mod( 'abed_ticker', '' ) ) ) );
$has_wc  = class_exists( 'WooCommerce' );

// دسته‌بندی‌های اصلی و زیردسته‌ها برای مگامنو و منوی موبایل.
$top_cats = array();
if ( $has_wc ) {
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'parent'     => 0,
			'hide_empty' => true,
			'exclude'    => array( (int) get_option( 'default_product_cat' ) ),
		)
	);
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$kids = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $t->term_id, 'hide_empty' => true ) );
			$top_cats[] = array(
				'term' => $t,
				'kids' => is_wp_error( $kids ) ? array() : $kids,
				'img'  => wp_get_attachment_image_url( (int) get_term_meta( $t->term_id, 'thumbnail_id', true ), 'thumbnail' ),
			);
		}
	}
}
?>
<!doctype html>
<html <?php language_attributes(); ?>>
<head>
<meta charset="<?php bloginfo( 'charset' ); ?>">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<a class="ab-skip" href="#ab-main"><?php esc_html_e( 'پرش به محتوای اصلی', 'abed' ); ?></a>

<div class="ab-topbar"><div class="ab-container ab-topbar__in">
	<?php if ( $ticker ) : ?>
	<div class="ab-ticker" aria-label="<?php esc_attr_e( 'اطلاعیه‌ها', 'abed' ); ?>"><ul class="ab-ticker__list">
		<?php foreach ( $ticker as $line ) : ?>
			<li><?php echo abed_icon( 'check' ); // phpcs:ignore ?><?php echo esc_html( $line ); ?></li>
		<?php endforeach; ?>
	</ul></div>
	<?php endif; ?>
	<a class="ab-topbar__phone" href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( abed_tel_text( $sales ) ); ?></span></a>
</div></div>

<header class="ab-header" data-ab-header><div class="ab-container ab-header__in">
	<button type="button" class="ab-iconbtn ab-header__menu" data-ab-open="menu" aria-controls="ab-menu" aria-expanded="false" aria-label="<?php esc_attr_e( 'باز کردن منو', 'abed' ); ?>"><?php echo abed_icon( 'menu' ); // phpcs:ignore ?></button>
	<?php abed_logo(); ?>
	<div class="ab-header__search"><?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'header' ) ); ?></div>
	<div class="ab-header__actions">
		<a class="ab-hact ab-hact--account" href="<?php echo esc_url( $has_wc ? wc_get_page_permalink( 'myaccount' ) : wp_login_url() ); ?>">
			<?php echo abed_icon( 'user' ); // phpcs:ignore ?>
			<span class="ab-hact__label"><?php echo is_user_logged_in() ? esc_html__( 'حساب من', 'abed' ) : esc_html__( 'ورود / ثبت‌نام', 'abed' ); ?></span>
		</a>
		<?php if ( $has_wc ) : ?>
		<a class="ab-hact ab-hact--cart" href="<?php echo esc_url( wc_get_cart_url() ); ?>" aria-label="<?php esc_attr_e( 'لیست خرید', 'abed' ); ?>" data-ab-open="cart" aria-controls="ab-cart" role="button">
			<span class="ab-hact__icon"><?php echo abed_icon( 'bag' ); // phpcs:ignore ?><span class="ab-cartcount" data-n="<?php echo esc_attr( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?>"><?php echo esc_html( WC()->cart ? WC()->cart->get_cart_contents_count() : 0 ); ?></span></span>
			<span class="ab-hact__label"><?php esc_html_e( 'لیست خرید', 'abed' ); ?><span class="ab-carttotal"></span></span>
		</a>
		<?php endif; ?>
	</div>
</div></header>

<nav class="ab-nav" aria-label="<?php esc_attr_e( 'منوی اصلی', 'abed' ); ?>"><div class="ab-container ab-nav__in">
	<?php if ( $top_cats ) : ?>
	<div class="ab-mega" data-ab-mega>
		<button type="button" class="ab-mega__btn" aria-expanded="false" aria-controls="ab-mega-panel"><?php echo abed_icon( 'grid' ); // phpcs:ignore ?> <?php esc_html_e( 'دسته‌بندی کالاها', 'abed' ); ?> <?php echo abed_icon( 'chevron-down', 'ab-mega__chev' ); // phpcs:ignore ?></button>
		<div class="ab-mega__panel" id="ab-mega-panel"><div class="ab-mega__grid">
			<?php foreach ( $top_cats as $c ) : ?>
			<div class="ab-mega__col">
				<a class="ab-mega__top" href="<?php echo esc_url( get_term_link( $c['term'] ) ); ?>">
					<?php if ( $c['img'] ) : ?><img class="ab-mega__img" src="<?php echo esc_url( $c['img'] ); ?>" loading="lazy" width="40" height="40" alt=""><?php endif; ?>
					<span><?php echo esc_html( $c['term']->name ); ?></span>
				</a>
				<?php if ( $c['kids'] ) : ?>
				<ul><?php foreach ( array_slice( $c['kids'], 0, 8 ) as $k ) : ?><li><a href="<?php echo esc_url( get_term_link( $k ) ); ?>"><?php echo esc_html( $k->name ); ?></a></li><?php endforeach; ?></ul>
				<?php endif; ?>
			</div>
			<?php endforeach; ?>
		</div></div>
	</div>
	<?php endif; ?>
	<?php
	wp_nav_menu(
		array(
			'theme_location' => 'primary',
			'container'      => false,
			'menu_class'     => 'ab-menu',
			'fallback_cb'    => 'abed_menu_fallback',
			'depth'          => 1,
		)
	);
	?>
</div></nav>

<div class="ab-offcanvas" id="ab-menu" data-ab-panel="menu" role="dialog" aria-modal="true" aria-label="<?php esc_attr_e( 'منو', 'abed' ); ?>" hidden>
	<div class="ab-offcanvas__head">
		<?php abed_logo( 'ab-logo--sm', false ); ?>
		<button type="button" class="ab-iconbtn" data-ab-close aria-label="<?php esc_attr_e( 'بستن منو', 'abed' ); ?>"><?php echo abed_icon( 'close' ); // phpcs:ignore ?></button>
	</div>
	<div class="ab-offcanvas__body">
		<?php if ( $top_cats ) : ?>
		<p class="ab-offcanvas__label" id="ab-menu-cats"><?php esc_html_e( 'دسته‌بندی کالاها', 'abed' ); ?></p>
		<ul class="ab-acc" aria-labelledby="ab-menu-cats">
			<?php foreach ( $top_cats as $c ) : ?>
			<li>
				<?php if ( $c['kids'] ) : ?>
				<details><summary><?php echo esc_html( $c['term']->name ); ?><?php echo abed_icon( 'chevron-down' ); // phpcs:ignore ?></summary>
					<ul>
						<li><a href="<?php echo esc_url( get_term_link( $c['term'] ) ); ?>"><?php echo esc_html( sprintf( /* translators: %s: category */ __( 'همه %s', 'abed' ), $c['term']->name ) ); ?></a></li>
						<?php foreach ( $c['kids'] as $k ) : ?><li><a href="<?php echo esc_url( get_term_link( $k ) ); ?>"><?php echo esc_html( $k->name ); ?></a></li><?php endforeach; ?>
					</ul>
				</details>
				<?php else : ?>
				<a href="<?php echo esc_url( get_term_link( $c['term'] ) ); ?>"><?php echo esc_html( $c['term']->name ); ?></a>
				<?php endif; ?>
			</li>
			<?php endforeach; ?>
		</ul>
		<?php endif; ?>
		<?php
		wp_nav_menu(
			array(
				'theme_location' => 'primary',
				'container'      => false,
				'menu_class'     => 'ab-offcanvas__links',
				'fallback_cb'    => false,
				'depth'          => 1,
			)
		);
		?>
	</div>
</div>

<main id="ab-main" class="ab-main" tabindex="-1">

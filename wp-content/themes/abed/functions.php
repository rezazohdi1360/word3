<?php
/**
 * قالب عابد — توابع اصلی.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ABED_VERSION', '1.0.0' );
define( 'ABED_DIR', get_template_directory() );
define( 'ABED_URI', get_template_directory_uri() );

require_once ABED_DIR . '/inc/helpers.php';
require_once ABED_DIR . '/inc/customizer.php';
require_once ABED_DIR . '/inc/woocommerce.php';
require_once ABED_DIR . '/inc/ajax.php';

/**
 * تنظیمات پایه قالب.
 */
function abed_setup() {
	load_theme_textdomain( 'abed', ABED_DIR . '/languages' );

	add_theme_support( 'title-tag' );
	add_theme_support( 'post-thumbnails' );
	add_theme_support( 'automatic-feed-links' );
	add_theme_support( 'responsive-embeds' );
	add_theme_support( 'wp-block-styles' );
	add_theme_support(
		'html5',
		array( 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script' )
	);
	add_theme_support(
		'custom-logo',
		array(
			'height'      => 56,
			'width'       => 56,
			'flex-height' => true,
			'flex-width'  => true,
		)
	);

	register_nav_menus(
		array(
			'primary' => __( 'منوی اصلی (زیر هدر)', 'abed' ),
			'footer'  => __( 'دسترسی سریع (فوتر)', 'abed' ),
		)
	);
}
add_action( 'after_setup_theme', 'abed_setup' );

/**
 * کلاس‌های بدنه مطابق CSS قالب.
 *
 * @param string[] $classes Classes.
 * @return string[]
 */
function abed_body_class( $classes ) {
	$classes[] = 'ab-footer-collapse';
	$classes[] = 'ab-has-bottom-nav';
	$classes[] = is_front_page() ? 'ab-ctx-home' : 'ab-ctx-page';
	return $classes;
}
add_filter( 'body_class', 'abed_body_class' );

/**
 * بارگذاری CSS و JS.
 */
function abed_assets() {
	$u = ABED_URI . '/assets/';
	$v = ABED_VERSION;

	wp_enqueue_style( 'abed-base', $u . 'css/base.css', array(), $v );
	wp_enqueue_style( 'abed-content', $u . 'css/content.css', array( 'abed-base' ), $v );
	wp_enqueue_style( 'abed-shop', $u . 'css/shop.css', array( 'abed-base' ), $v );
	wp_enqueue_style( 'abed-consult', $u . 'css/consult.css', array( 'abed-base' ), $v );

	if ( is_front_page() ) {
		wp_enqueue_style( 'abed-trend', $u . 'css/trend.css', array( 'abed-base' ), $v );
		wp_enqueue_style( 'abed-story-front', $u . 'css/story-front.css', array( 'abed-base' ), $v );
		wp_enqueue_style( 'abed-presale', $u . 'css/presale.css', array( 'abed-base' ), $v );
	}
	if ( function_exists( 'is_product' ) && is_product() ) {
		wp_enqueue_style( 'abed-product', $u . 'css/product.css', array( 'abed-shop' ), $v );
	}
	if ( ! is_front_page() ) {
		wp_enqueue_style( 'abed-article', $u . 'css/article.css', array( 'abed-content' ), $v );
	}

	wp_enqueue_script( 'abed', $u . 'js/abed.js', array(), $v, array( 'in_footer' => true, 'strategy' => 'defer' ) );
	wp_enqueue_script( 'abed-consult', $u . 'js/consult.js', array(), $v, array( 'in_footer' => true, 'strategy' => 'defer' ) );

	wp_add_inline_script( 'abed', 'var ABED=' . wp_json_encode( abed_js_config() ) . ';', 'before' );
	wp_add_inline_script(
		'abed-consult',
		'var ABCS=' . wp_json_encode(
			array(
				'ajax'    => admin_url( 'admin-ajax.php' ),
				'pid'     => (string) get_the_ID(),
				'product' => ( function_exists( 'is_product' ) && is_product() ) ? get_the_title() : '',
			)
		) . ';',
		'before'
	);
}
add_action( 'wp_enqueue_scripts', 'abed_assets' );

/**
 * تنظیمات جاوااسکریپت (window.ABED).
 *
 * @return array
 */
function abed_js_config() {
	$wc = class_exists( 'WooCommerce' );
	return array(
		'home'        => home_url( '/' ),
		'wcAjax'      => $wc ? '/?wc-ajax=%%endpoint%%' : '',
		'searchIndex' => $wc ? rest_url( 'abed/v1/search-index' ) : '',
		'cartUrl'     => $wc ? wc_get_cart_url() : '',
		'drawer'      => '1',
		'afterAdd'    => 'stay',
		'checkoutUrl' => $wc ? wc_get_checkout_url() : '',
		'sep'         => '.',
		'popular'     => array_values( array_filter( array_map( 'trim', explode( '،', (string) abed_mod( 'abed_popular_searches' ) ) ) ) ),
		'protect'     => array(
			'src'  => 0,
			'img'  => 0,
			'nosel' => 0,
			'name' => get_bloginfo( 'name' ),
		),
		'currency'    => 'تومان',
		'i18n'        => array(
			'added'     => 'به لیست خرید اضافه شد',
			'error'     => 'خطا در ارتباط. دوباره تلاش کنید.',
			'noResult'  => 'محصولی پیدا نشد',
			'allResult' => 'نمایش همه نتایج',
			'inquiry'   => 'استعلام قیمت',
		),
	);
}

/**
 * پیش‌بارگذاری فونت.
 */
function abed_preload_font() {
	printf(
		'<link rel="preload" href="%s" as="font" type="font/woff2" crossorigin>' . "\n",
		esc_url( ABED_URI . '/assets/fonts/IRANYekanXVFaNumVF.woff2' )
	);
}
add_action( 'wp_head', 'abed_preload_font', 1 );

/**
 * رنگ نوار مرورگر موبایل.
 */
function abed_theme_color() {
	echo '<meta name="theme-color" content="#283618">' . "\n";
}
add_action( 'wp_head', 'abed_theme_color', 2 );

/**
 * خروجی اسپرایت آیکون‌ها در پایان بدنه.
 */
function abed_print_sprite() {
	include ABED_DIR . '/inc/sprite.php';
}
add_action( 'wp_footer', 'abed_print_sprite', 5 );

/**
 * حذف استایل پیش‌فرض ووکامرس: استایل فروشگاه توسط قالب تأمین می‌شود.
 */
add_filter( 'woocommerce_enqueue_styles', '__return_empty_array' );

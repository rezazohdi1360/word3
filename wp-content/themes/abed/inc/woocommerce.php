<?php
/**
 * یکپارچگی با ووکامرس: قیمت پلکانی، کارت محصول، لیست خرید کشویی، شاخص جستجو.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * پشتیبانی ووکامرس.
 */
function abed_wc_setup() {
	add_theme_support( 'woocommerce' );
	add_theme_support( 'wc-product-gallery-zoom' );
	add_theme_support( 'wc-product-gallery-lightbox' );
	add_theme_support( 'wc-product-gallery-slider' );
}
add_action( 'after_setup_theme', 'abed_wc_setup' );

/**
 * قیمت‌های پلکانی یک محصول.
 *
 * منبع داده: متای `_abed_price_tiers` به‌صورت JSON مانند [[1,1058000],[10,1028000],[20,998000]]
 * (حداقل تعداد، قیمت واحد). با فیلتر `abed_price_tiers` می‌توان از افزونه یا منبع دیگری خواند.
 * اگر چیزی ثبت نشده باشد، فقط یک پله با قیمت خود محصول برگردانده می‌شود.
 *
 * @param WC_Product $product Product.
 * @return array[] آرایه‌ای از [min, price] مرتب‌شده بر اساس min.
 */
function abed_get_tiers( $product ) {
	$tiers = array();
	$raw   = get_post_meta( $product->get_id(), '_abed_price_tiers', true );
	if ( is_string( $raw ) && '' !== $raw ) {
		$raw = json_decode( $raw, true );
	}
	if ( is_array( $raw ) ) {
		foreach ( $raw as $row ) {
			if ( is_array( $row ) && isset( $row[0], $row[1] ) && (float) $row[1] > 0 ) {
				$tiers[] = array( max( 1, (int) $row[0] ), (float) $row[1] );
			}
		}
	}
	if ( ! $tiers && '' !== $product->get_price() ) {
		$tiers[] = array( 1, (float) $product->get_price() );
	}
	$tiers = apply_filters( 'abed_price_tiers', $tiers, $product );
	usort(
		$tiers,
		function ( $a, $b ) {
			return $a[0] <=> $b[0];
		}
	);
	return $tiers;
}

/**
 * قیمت با فرمت ووکامرس.
 *
 * @param float $price Price.
 * @return string
 */
function abed_money( $price ) {
	return wc_price( $price );
}

/**
 * جدول قیمت پلکانی.
 *
 * @param WC_Product $product Product.
 * @param string     $variant card|full.
 */
function abed_tiers_table( $product, $variant = 'card' ) {
	$tiers = abed_get_tiers( $product );
	if ( count( $tiers ) < 2 ) {
		return;
	}
	$n = count( $tiers );
	echo '<table class="ab-tiers ab-tiers--' . esc_attr( $variant ) . '" data-tiers="' . esc_attr( wp_json_encode( $tiers ) ) . '">';
	if ( 'full' === $variant ) {
		echo '<thead><tr><th scope="col">' . esc_html__( 'تعداد', 'abed' ) . '</th><th scope="col">' . esc_html__( 'قیمت واحد', 'abed' ) . '</th></tr></thead>';
	}
	echo '<tbody>';
	foreach ( $tiers as $i => $t ) {
		$label = ( $i + 1 < $n )
			? sprintf( /* translators: 1: from 2: to */ __( '%1$s تا %2$s', 'abed' ), $t[0], $tiers[ $i + 1 ][0] - 1 )
			: sprintf( /* translators: %s: from */ __( '%s به بالا', 'abed' ), $t[0] );
		printf(
			'<tr data-min="%1$d"%2$s><th scope="row">%3$s</th><td>%4$s</td></tr>',
			(int) $t[0],
			0 === $i ? ' class="is-active"' : '',
			esc_html( $label ),
			abed_money( $t[1] ) // phpcs:ignore WordPress.Security.EscapeOutput
		);
	}
	echo '</tbody></table>';
}

/**
 * کمترین قیمت پلکانی (برای نمایش «از …»).
 *
 * @param WC_Product $product Product.
 * @return float
 */
function abed_min_price( $product ) {
	$tiers = abed_get_tiers( $product );
	if ( ! $tiers ) {
		return 0.0;
	}
	return min( array_column( $tiers, 1 ) );
}

/**
 * قطعه‌های لیست خرید (کشو و شمارنده).
 *
 * @param array $fragments Fragments.
 * @return array
 */
function abed_cart_fragments( $fragments ) {
	if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
		return $fragments;
	}
	$count = WC()->cart->get_cart_contents_count();
	$total = WC()->cart->get_cart_subtotal();

	$fragments['.ab-cartcount'] = '<span class="ab-cartcount" data-n="' . esc_attr( $count ) . '">' . esc_html( $count ) . '</span>';
	$fragments['.ab-carttotal'] = '<span class="ab-carttotal">' . ( $count ? ' — ' . $total : '' ) . '</span>';

	ob_start();
	echo '<div class="ab-drawer__body" data-ab-drawer-body="">';
	if ( ! $count ) {
		echo '<div class="ab-drawer__empty">' . abed_icon( 'bag' ) . '<p>' . esc_html__( 'لیست خرید شما خالی است.', 'abed' ) . '</p>'; // phpcs:ignore
		echo '<a class="ab-btn ab-btn--primary" href="' . esc_url( wc_get_page_permalink( 'shop' ) ) . '">' . esc_html__( 'مشاهده محصولات', 'abed' ) . '</a></div>';
	} else {
		echo '<ul class="ab-mini">';
		foreach ( WC()->cart->get_cart() as $key => $item ) {
			$p = $item['data'];
			if ( ! $p || ! $p->exists() ) {
				continue;
			}
			$qty = (int) $item['quantity'];
			echo '<li class="ab-mini__item" data-key="' . esc_attr( $key ) . '">';
			echo '<a class="ab-mini__img" href="' . esc_url( $p->get_permalink() ) . '">' . $p->get_image( 'woocommerce_gallery_thumbnail' ) . '</a>'; // phpcs:ignore
			echo '<div><a class="ab-mini__name" href="' . esc_url( $p->get_permalink() ) . '">' . esc_html( $p->get_name() ) . '</a>';
			echo '<span class="ab-mini__unit">' . wp_kses_post( wc_price( (float) $p->get_price() ) ) . '</span>';
			echo '<div class="ab-mini__row"><div class="ab-qty ab-qty--sm" data-ab-qty="">';
			echo '<button type="button" class="ab-qty__btn" data-step="1" aria-label="' . esc_attr__( 'افزایش تعداد', 'abed' ) . '">' . abed_icon( 'plus' ) . '</button>'; // phpcs:ignore
			echo '<input class="ab-qty__input" type="number" inputmode="numeric" min="0" step="1" value="' . esc_attr( $qty ) . '" aria-label="' . esc_attr__( 'تعداد', 'abed' ) . '">';
			echo '<button type="button" class="ab-qty__btn" data-step="-1" aria-label="' . esc_attr__( 'کاهش تعداد', 'abed' ) . '">' . abed_icon( 'minus' ) . '</button></div>'; // phpcs:ignore
			echo '<span class="ab-mini__total">' . wp_kses_post( wc_price( (float) $p->get_price() * $qty ) ) . '</span></div></div>';
			echo '<button type="button" class="ab-iconbtn ab-mini__remove" data-ab-remove="" aria-label="' . esc_attr__( 'حذف', 'abed' ) . '">' . abed_icon( 'trash' ) . '</button>'; // phpcs:ignore
			echo '</li>';
		}
		echo '</ul>';
	}
	echo '</div>';
	$fragments['[data-ab-drawer-body]'] = ob_get_clean();

	ob_start();
	echo '<div class="ab-drawer__foot" data-ab-drawer-foot="">';
	if ( $count ) {
		echo '<div class="ab-drawer__sum"><span>' . esc_html__( 'جمع لیست خرید', 'abed' ) . '</span><strong>' . wp_kses_post( $total ) . '</strong></div>';
		echo '<div class="ab-drawer__actions"><a class="ab-btn ab-btn--ghost" href="' . esc_url( wc_get_cart_url() ) . '">' . esc_html__( 'مشاهده لیست', 'abed' ) . '</a>';
		echo '<a class="ab-btn ab-btn--primary" href="' . esc_url( wc_get_checkout_url() ) . '">' . esc_html__( 'ثبت سفارش', 'abed' ) . '</a></div>';
	}
	echo '</div>';
	$fragments['[data-ab-drawer-foot]'] = ob_get_clean();

	return $fragments;
}
add_filter( 'woocommerce_add_to_cart_fragments', 'abed_cart_fragments' );

/**
 * اندازهٔ تصاویر ووکامرس.
 */
add_filter(
	'woocommerce_get_image_size_thumbnail',
	function () {
		return array( 'width' => 300, 'height' => 300, 'crop' => 1 );
	}
);

/**
 * تعداد محصولات در هر صفحه آرشیو.
 */
add_filter(
	'loop_shop_per_page',
	function () {
		return 24;
	}
);

/**
 * حذف المان‌های پیش‌فرض ووکامرس که قالب خودش جایگزین کرده است.
 */
function abed_wc_cleanup() {
	remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10 );
	remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10 );
	remove_action( 'woocommerce_before_main_content', 'woocommerce_breadcrumb', 20 );
	remove_action( 'woocommerce_sidebar', 'woocommerce_get_sidebar', 10 );
}
add_action( 'init', 'abed_wc_cleanup' );

/**
 * شاخص جستجوی آنی برای JS: /wp-json/abed/v1/search-index
 */
function abed_register_rest() {
	register_rest_route(
		'abed/v1',
		'/search-index',
		array(
			'methods'             => 'GET',
			'permission_callback' => '__return_true',
			'callback'            => 'abed_search_index',
		)
	);
}
add_action( 'rest_api_init', 'abed_register_rest' );

/**
 * ساخت (و کش) شاخص جستجو.
 *
 * @return array
 */
function abed_search_index() {
	$cached = get_transient( 'abed_search_index' );
	if ( is_array( $cached ) ) {
		return $cached;
	}
	$home = trailingslashit( home_url( '/' ) );
	$rel  = function ( $url ) use ( $home ) {
		return 0 === strpos( $url, $home ) ? substr( $url, strlen( $home ) ) : $url;
	};

	$out = array( 'home' => $home, 'p' => array(), 'c' => array() );

	$ids = get_posts(
		array(
			'post_type'      => 'product',
			'post_status'    => 'publish',
			'posts_per_page' => 2000,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		)
	);
	foreach ( $ids as $id ) {
		$p = wc_get_product( $id );
		if ( ! $p ) {
			continue;
		}
		$cats  = wp_get_post_terms( $id, 'product_cat', array( 'fields' => 'names' ) );
		$tags  = wp_get_post_terms( $id, 'product_tag', array( 'fields' => 'names' ) );
		$thumb = get_the_post_thumbnail_url( $id, 'thumbnail' );
		$min   = abed_min_price( $p );
		$out['p'][] = array(
			't' => $p->get_name(),
			's' => (string) $p->get_sku(),
			'c' => is_wp_error( $cats ) ? '' : implode( ' ', $cats ),
			'k' => is_wp_error( $tags ) ? '' : implode( ' ', $tags ),
			'u' => $rel( get_permalink( $id ) ),
			'm' => $thumb ? $rel( $thumb ) : '',
			'n' => is_wp_error( $cats ) || ! $cats ? '' : $cats[0],
			'x' => $min > 0 ? 1 : 0,
			'p' => $min > 0 ? wp_strip_all_tags( html_entity_decode( wc_price( $min ) ) ) : '',
		);
	}

	$terms = get_terms( array( 'taxonomy' => 'product_cat', 'hide_empty' => true ) );
	if ( ! is_wp_error( $terms ) ) {
		foreach ( $terms as $t ) {
			$out['c'][] = array( 't' => $t->name, 'u' => $rel( get_term_link( $t ) ), 'n' => (int) $t->count );
		}
	}

	set_transient( 'abed_search_index', $out, HOUR_IN_SECONDS );
	return $out;
}

/**
 * پاک‌کردن کش شاخص با تغییر محصول.
 */
function abed_flush_search_index() {
	delete_transient( 'abed_search_index' );
}
add_action( 'save_post_product', 'abed_flush_search_index' );
add_action( 'deleted_post', 'abed_flush_search_index' );
add_action( 'edited_product_cat', 'abed_flush_search_index' );

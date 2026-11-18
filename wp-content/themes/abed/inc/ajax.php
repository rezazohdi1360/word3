<?php
/**
 * اندپوینت‌های AJAX: افزودن گروهی به لیست خرید، تغییر تعداد، درخواست مشاوره.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * پاسخ استاندارد لیست خرید (قطعه‌ها + هش).
 */
function abed_cart_response() {
	$data = array(
		'fragments' => apply_filters( 'woocommerce_add_to_cart_fragments', array() ),
		'cart_hash' => WC()->cart->get_cart_hash(),
	);
	wp_send_json( $data );
}

/**
 * افزودن گروهی: items[productId] = quantity
 */
function abed_ajax_bulk_add() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( null, 400 );
	}
	$items  = isset( $_POST['items'] ) && is_array( $_POST['items'] ) ? wp_unslash( $_POST['items'] ) : array(); // phpcs:ignore WordPress.Security.NonceVerification
	$added  = 0;
	$failed = array();

	foreach ( $items as $pid => $qty ) {
		$pid = absint( $pid );
		$qty = max( 0, (int) $qty );
		if ( ! $pid || ! $qty ) {
			continue;
		}
		$product = wc_get_product( $pid );
		if ( $product && $product->is_purchasable() && $product->is_in_stock() && WC()->cart->add_to_cart( $pid, $qty ) ) {
			++$added;
		} else {
			$failed[] = $product ? $product->get_name() : (string) $pid;
		}
	}
	WC()->cart->calculate_totals();

	header( 'X-Abed-Added: ' . $added );
	if ( $failed ) {
		header( 'X-Abed-Failed: ' . rawurlencode( implode( '، ', $failed ) ) );
	}
	abed_cart_response();
}
add_action( 'wc_ajax_abed_bulk_add', 'abed_ajax_bulk_add' );

/**
 * تغییر تعداد یک ردیف لیست خرید (۰ = حذف).
 */
function abed_ajax_set_qty() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( null, 400 );
	}
	$key = isset( $_POST['key'] ) ? sanitize_text_field( wp_unslash( $_POST['key'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification
	$qty = isset( $_POST['qty'] ) ? max( 0, (int) $_POST['qty'] ) : 0; // phpcs:ignore WordPress.Security.NonceVerification

	if ( $key && WC()->cart->get_cart_item( $key ) ) {
		if ( 0 === $qty ) {
			WC()->cart->remove_cart_item( $key );
		} else {
			WC()->cart->set_quantity( $key, $qty, true );
		}
		WC()->cart->calculate_totals();
	}
	abed_cart_response();
}
add_action( 'wc_ajax_abed_set_qty', 'abed_ajax_set_qty' );

/**
 * نوع نوشتهٔ «درخواست مشاوره» برای ذخیره فرم تماس.
 */
function abed_register_consult_cpt() {
	register_post_type(
		'abed_consult',
		array(
			'labels'          => array(
				'name'          => __( 'درخواست‌های مشاوره', 'abed' ),
				'singular_name' => __( 'درخواست مشاوره', 'abed' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-phone',
			'supports'        => array( 'title', 'editor' ),
			'capability_type' => 'post',
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'abed_register_consult_cpt' );

/**
 * ثبت فرم مشاوره تلفنی (admin-ajax).
 */
function abed_ajax_consult_submit() {
	// ربات‌ها فیلد honeypot را پر می‌کنند.
	if ( ! empty( $_POST['website'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
		wp_send_json_success( array( 'message' => 'ok' ) );
	}

	$name  = isset( $_POST['last_name'] ) ? sanitize_text_field( wp_unslash( $_POST['last_name'] ) ) : ''; // phpcs:ignore
	$phone = isset( $_POST['phone'] ) ? sanitize_text_field( wp_unslash( $_POST['phone'] ) ) : ''; // phpcs:ignore
	$need  = isset( $_POST['product_needed'] ) ? sanitize_text_field( wp_unslash( $_POST['product_needed'] ) ) : ''; // phpcs:ignore
	$when  = isset( $_POST['when'] ) ? sanitize_key( wp_unslash( $_POST['when'] ) ) : 'any'; // phpcs:ignore
	$page  = isset( $_POST['page_url'] ) ? esc_url_raw( wp_unslash( $_POST['page_url'] ) ) : ''; // phpcs:ignore

	$digits = strtr( $phone, array( '۰' => '0', '۱' => '1', '۲' => '2', '۳' => '3', '۴' => '4', '۵' => '5', '۶' => '6', '۷' => '7', '۸' => '8', '۹' => '9' ) );
	$digits = preg_replace( '/\D+/', '', $digits );

	if ( '' === $name || '' === $need || ! preg_match( '/^(0|98)?9\d{9}$/', $digits ) ) {
		wp_send_json_error( array( 'message' => 'لطفاً نام، شماره موبایل معتبر و کالای مورد نیاز را وارد کنید.' ), 422 );
	}

	$labels = array( 'any' => 'هر وقت', 'am' => 'صبح', 'pm' => 'عصر' );
	$body   = "نام: $name\nموبایل: $digits\nکالا: $need\nزمان تماس: " . ( $labels[ $when ] ?? $labels['any'] ) . "\nصفحه: $page";

	wp_insert_post(
		array(
			'post_type'    => 'abed_consult',
			'post_status'  => 'publish',
			'post_title'   => $name . ' — ' . $digits,
			'post_content' => $body,
		)
	);
	wp_mail( get_option( 'admin_email' ), 'درخواست مشاوره تلفنی — ' . $name, $body );

	wp_send_json_success( array( 'message' => 'درخواست شما ثبت شد؛ در اولین فرصت با شما تماس می‌گیریم.' ) );
}
add_action( 'wp_ajax_abed_consult_submit', 'abed_ajax_consult_submit' );
add_action( 'wp_ajax_nopriv_abed_consult_submit', 'abed_ajax_consult_submit' );

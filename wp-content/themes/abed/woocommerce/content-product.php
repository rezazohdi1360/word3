<?php
/**
 * کارت محصول در حلقه‌های ووکامرس (آرشیو فروشگاه، دسته‌ها، جستجو).
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;
if ( empty( $product ) || ! $product->is_visible() ) {
	return;
}

get_template_part( 'template-parts/product-card', null, array( 'product' => $product ) );

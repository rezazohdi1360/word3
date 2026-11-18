<?php
/**
 * فرم جستجوی آنی (هدر و بنر). پارامتر args['id'] یکی از header یا hero.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$id    = isset( $args['id'] ) ? sanitize_html_class( $args['id'] ) : 'header';
$input = 'ab-s-' . $id;
?>
<form class="ab-search ab-search--<?php echo esc_attr( $id ); ?>" role="search" method="get" action="<?php echo esc_url( home_url( '/' ) ); ?>" data-ab-search>
	<label class="screen-reader-text" for="<?php echo esc_attr( $input ); ?>"><?php esc_html_e( 'جستجوی محصولات', 'abed' ); ?></label>
	<input class="ab-search__input" type="search" id="<?php echo esc_attr( $input ); ?>" name="s" value="<?php echo esc_attr( get_search_query() ); ?>" placeholder="<?php esc_attr_e( 'جستجوی نام کالا یا کد محصول…', 'abed' ); ?>" autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="<?php echo esc_attr( $input ); ?>-res">
	<?php if ( class_exists( 'WooCommerce' ) ) : ?><input type="hidden" name="post_type" value="product"><?php endif; ?>
	<button class="ab-search__clear" type="button" data-ab-search-clear aria-label="<?php esc_attr_e( 'پاک کردن جستجو', 'abed' ); ?>"><?php echo abed_icon( 'close' ); // phpcs:ignore ?></button>
	<button class="ab-search__btn" type="submit" aria-label="<?php esc_attr_e( 'جستجو', 'abed' ); ?>"><?php echo abed_icon( 'search' ); // phpcs:ignore ?></button>
	<div class="ab-search__res" id="<?php echo esc_attr( $input ); ?>-res" role="listbox" aria-label="<?php esc_attr_e( 'نتایج جستجو', 'abed' ); ?>" hidden></div>
</form>

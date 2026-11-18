<?php
/**
 * قالب همهٔ صفحات ووکامرس: فروشگاه، دسته‌ها، تک‌محصول؛ و صفحه‌های سبد/پرداخت/حساب از طریق محتوای صفحه.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ab-container ab-wcmain">
<?php if ( is_singular( 'product' ) ) : ?>

	<div class="woocommerce-notices-wrapper"><?php wc_print_notices(); ?></div>
	<?php
	while ( have_posts() ) {
		the_post();
		wc_get_template_part( 'content', 'single-product' );
	}
	?>

<?php elseif ( is_shop() || is_product_taxonomy() || is_search() ) : ?>

	<div class="ab-archive" data-ab-archive>
		<?php abed_breadcrumbs(); ?>
		<header class="ab-archive__head">
			<div class="ab-archive__titlewrap"><h1 class="ab-archive__title"><?php woocommerce_page_title(); ?></h1></div>
			<?php
			$parent = is_product_category() ? get_queried_object_id() : 0;
			$subs   = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $parent, 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
			if ( ! is_wp_error( $subs ) && $subs && ! is_search() ) :
				?>
				<nav class="ab-chips" aria-label="<?php esc_attr_e( 'زیردسته‌ها', 'abed' ); ?>"><ul>
					<?php foreach ( $subs as $s ) : ?>
						<li><a class="ab-chip" href="<?php echo esc_url( get_term_link( $s ) ); ?>"><?php echo esc_html( $s->name ); ?> <span class="ab-chip__n"><?php echo esc_html( abed_fa_digits( $s->count ) ); ?></span></a></li>
					<?php endforeach; ?>
				</ul></nav>
			<?php endif; ?>
		</header>

		<div class="ab-archive__layout"><div class="ab-archive__main">
			<?php if ( woocommerce_product_loop() ) : ?>
				<div class="ab-toolbar">
					<p class="ab-toolbar__count"><?php woocommerce_result_count(); ?></p>
					<div class="ab-toolbar__sort"><?php woocommerce_catalog_ordering(); ?></div>
				</div>
				<ul class="products ab-grid">
					<?php
					while ( have_posts() ) {
						the_post();
						wc_get_template_part( 'content', 'product' );
					}
					?>
				</ul>
				<?php woocommerce_pagination(); ?>
			<?php else : ?>
				<p class="ab-empty"><?php esc_html_e( 'محصولی پیدا نشد.', 'abed' ); ?></p>
			<?php endif; ?>
			<?php if ( is_product_category() && term_description() ) : ?>
				<div class="ab-archive__about ab-prose"><?php echo wp_kses_post( term_description() ); ?></div>
			<?php endif; ?>
		</div></div>
	</div>

<?php else : ?>

	<?php woocommerce_content(); ?>

<?php endif; ?>
</div>
<?php
abed_cta();
get_footer();

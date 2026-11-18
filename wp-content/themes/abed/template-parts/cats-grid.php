<?php
/**
 * شبکه دسته‌بندی‌ها. args: title, more_url, more_label, terms (WP_Term[]).
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$terms = isset( $args['terms'] ) ? $args['terms'] : array();
if ( ! $terms ) {
	return;
}
?>
<section class="ab-section ab-cats"><div class="ab-container">
	<div class="ab-section__head"><h2 class="ab-section__title"><?php echo esc_html( $args['title'] ); ?></h2>
	<?php if ( ! empty( $args['more_url'] ) ) : ?>
		<a class="ab-section__more" href="<?php echo esc_url( $args['more_url'] ); ?>"><?php echo esc_html( isset( $args['more_label'] ) ? $args['more_label'] : __( 'همه محصولات', 'abed' ) ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a>
	<?php endif; ?>
	</div>
	<ul class="ab-cats__grid">
		<?php foreach ( $terms as $t ) : ?>
			<?php $img = wp_get_attachment_image( (int) get_term_meta( $t->term_id, 'thumbnail_id', true ), 'woocommerce_thumbnail', false, array( 'class' => 'attachment-woocommerce_thumbnail', 'loading' => 'lazy', 'alt' => '' ) ); ?>
			<li>
				<a class="ab-cat" href="<?php echo esc_url( get_term_link( $t ) ); ?>">
					<span class="ab-cat__img"><?php echo $img; // phpcs:ignore WordPress.Security.EscapeOutput ?></span>
					<span class="ab-cat__name"><?php echo esc_html( $t->name ); ?></span>
					<span class="ab-cat__n"><?php echo esc_html( sprintf( /* translators: %s: count */ __( '%s کالا', 'abed' ), abed_fa_digits( $t->count ) ) ); ?></span>
				</a>
			</li>
		<?php endforeach; ?>
	</ul>
</div></section>

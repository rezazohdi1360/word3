<?php
/**
 * قالب پیش‌فرض: آرشیو نوشته‌ها و نتایج جستجو.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ab-container ab-page">
	<?php abed_breadcrumbs(); ?>
	<header class="ab-archive__head"><h1 class="ab-archive__title">
		<?php
		if ( is_search() ) {
			/* translators: %s: query */
			printf( esc_html__( 'نتایج جستجو برای «%s»', 'abed' ), esc_html( get_search_query() ) );
		} elseif ( is_archive() ) {
			echo esc_html( wp_strip_all_tags( get_the_archive_title() ) );
		} else {
			esc_html_e( 'نوشته‌ها', 'abed' );
		}
		?>
	</h1></header>

	<?php if ( have_posts() ) : ?>
		<div class="ab-posts">
			<?php
			while ( have_posts() ) :
				the_post();
				?>
				<article id="post-<?php the_ID(); ?>" <?php post_class( 'ab-article' ); ?>>
					<?php if ( has_post_thumbnail() ) : ?><a class="ab-article__img" href="<?php the_permalink(); ?>"><?php the_post_thumbnail( 'large' ); ?></a><?php endif; ?>
					<header class="ab-article__head"><h2 class="ab-article__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2></header>
					<div class="ab-prose"><?php the_excerpt(); ?></div>
				</article>
			<?php endwhile; ?>
		</div>
		<?php the_posts_pagination( array( 'mid_size' => 1, 'prev_text' => __( 'قبلی', 'abed' ), 'next_text' => __( 'بعدی', 'abed' ) ) ); ?>
	<?php else : ?>
		<p class="ab-empty"><?php esc_html_e( 'موردی پیدا نشد.', 'abed' ); ?></p>
		<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'page' ) ); ?>
	<?php endif; ?>
</div>
<?php
get_footer();

<?php
/**
 * قالب برگه‌ها.
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
	<?php
	while ( have_posts() ) :
		the_post();
		?>
		<article id="post-<?php the_ID(); ?>" <?php post_class( 'ab-article' ); ?>>
			<header class="ab-article__head"><h1 class="ab-article__title"><?php the_title(); ?></h1></header>
			<div class="ab-prose ab-article__body"><?php the_content(); ?></div>
		</article>
		<?php
		if ( comments_open() || get_comments_number() ) {
			comments_template();
		}
	endwhile;
	?>
</div>
<?php
abed_cta();
get_footer();

<?php
/**
 * صفحه ۴۰۴.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>
<div class="ab-container ab-page">
	<article class="ab-article">
		<header class="ab-article__head"><h1 class="ab-article__title"><?php esc_html_e( 'صفحه پیدا نشد', 'abed' ); ?></h1></header>
		<div class="ab-prose ab-article__body">
			<p><?php esc_html_e( 'آدرس واردشده وجود ندارد یا جابه‌جا شده است. می‌توانید محصول مورد نظرتان را جستجو کنید.', 'abed' ); ?></p>
			<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'page' ) ); ?>
			<p><a class="ab-btn ab-btn--primary" href="<?php echo esc_url( home_url( '/' ) ); ?>"><?php esc_html_e( 'بازگشت به صفحه اصلی', 'abed' ); ?></a></p>
		</div>
	</article>
</div>
<?php
get_footer();

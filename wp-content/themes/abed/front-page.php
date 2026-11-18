<?php
/**
 * صفحه اصلی: بنر، دسته‌بندی‌ها، ردیف‌های محصول، مراحل خرید، مشاوره، سؤالات و فروشگاه.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$has_wc   = class_exists( 'WooCommerce' );
$shop_url = $has_wc ? wc_get_page_permalink( 'shop' ) : home_url( '/' );
$contact  = abed_contact();
$sales    = $contact['phones'][0]['tel'];

$top_cats = array();
if ( $has_wc ) {
	$t = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => 0, 'hide_empty' => true, 'exclude' => array( (int) get_option( 'default_product_cat' ) ) ) );
	$top_cats = is_wp_error( $t ) ? array() : $t;
}
?>
<div class="ab-home">

	<section class="ab-hero ab-hero--panel" style="--hp-x:13%;--hp-w:25%;--hp-ar:1616 / 656"><div class="ab-container"><div class="ab-hero__frame">
		<picture class="ab-hero__picture">
			<source media="(max-width: 1099px)" srcset="<?php echo esc_url( abed_img( 'Abed-First-Banner-mob.webp' ) ); ?>" sizes="100vw">
			<img width="1616" height="656" src="<?php echo esc_url( abed_img( 'Abed-First-Banner.webp' ) ); ?>" class="ab-hero__pimg" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="eager" fetchpriority="high" decoding="async">
		</picture>
		<div class="ab-hero__text">
			<p class="ab-hero__eyebrow"><?php echo abed_icon( 'leaf' ); // phpcs:ignore ?> <?php echo esc_html( get_theme_mod( 'abed_hero_eyebrow', 'فروشگاه عابد' ) ); ?></p>
			<h1 class="ab-hero__title"><?php echo esc_html( get_theme_mod( 'abed_hero_title', 'پخش عمده ادویه، گیاهان دارویی و محصولات عطاری' ) ); ?></h1>
			<p class="ab-hero__lead"><?php echo esc_html( get_theme_mod( 'abed_hero_lead', 'قیمت‌های روز، تخفیف پلکانی برای خرید عمده و ارسال به سراسر کشور' ) ); ?></p>
			<?php get_template_part( 'template-parts/search-form', null, array( 'id' => 'hero' ) ); ?>
			<div class="ab-hero__actions">
				<a class="ab-btn ab-btn--primary" href="<?php echo esc_url( $shop_url ); ?>"><?php esc_html_e( 'مشاهده همه محصولات', 'abed' ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a>
				<a class="ab-btn ab-btn--ghost" href="<?php echo esc_url( home_url( '/price-list/' ) ); ?>"><?php echo abed_icon( 'list' ); // phpcs:ignore ?> <?php esc_html_e( 'لیست قیمت امروز', 'abed' ); ?></a>
			</div>
		</div></div>
		<ul class="ab-usp">
			<li><?php echo abed_icon( 'clock' ); // phpcs:ignore ?><div><strong><?php esc_html_e( 'قیمت روز', 'abed' ); ?></strong><span><?php esc_html_e( 'به‌روزرسانی روزانه قیمت‌ها', 'abed' ); ?></span></div></li>
			<li><?php echo abed_icon( 'layers' ); // phpcs:ignore ?><div><strong><?php esc_html_e( 'تخفیف پلکانی', 'abed' ); ?></strong><span><?php esc_html_e( 'هرچه بیشتر، ارزان‌تر', 'abed' ); ?></span></div></li>
			<li><?php echo abed_icon( 'truck' ); // phpcs:ignore ?><div><strong><?php esc_html_e( 'ارسال سراسری', 'abed' ); ?></strong><span><?php esc_html_e( 'پست، تیپاکس، ماهکس، باربری', 'abed' ); ?></span></div></li>
			<li><?php echo abed_icon( 'shield' ); // phpcs:ignore ?><div><strong><?php esc_html_e( 'تأیید سریع', 'abed' ); ?></strong><span><?php esc_html_e( 'تأیید قیمت و موجودی در ۳۰ ثانیه', 'abed' ); ?></span></div></li>
		</ul>
	</div></section>

	<?php
	get_template_part( 'template-parts/cats-grid', null, array( 'title' => __( 'دسته‌بندی محصولات', 'abed' ), 'more_url' => $shop_url, 'terms' => $top_cats ) );

	// پرفروش‌ترین‌ها.
	get_template_part(
		'template-parts/rail',
		null,
		array(
			'title'    => __( 'پرفروش‌ترین‌ها', 'abed' ),
			'more_url' => $shop_url,
			'query'    => array( 'meta_key' => 'total_sales', 'orderby' => 'meta_value_num', 'order' => 'DESC' ), // phpcs:ignore WordPress.DB.SlowDBQuery
		)
	);

	// زیردسته‌های سه دسته‌ی اول: «انواع …».
	foreach ( array_slice( $top_cats, 0, 3 ) as $cat ) {
		$kids = get_terms( array( 'taxonomy' => 'product_cat', 'parent' => $cat->term_id, 'hide_empty' => true ) );
		if ( ! is_wp_error( $kids ) && $kids ) {
			get_template_part(
				'template-parts/cats-grid',
				null,
				array(
					'title'      => sprintf( /* translators: %s: category */ __( 'انواع %s', 'abed' ), $cat->name ),
					'more_url'   => get_term_link( $cat ),
					'more_label' => sprintf( /* translators: %s: category */ __( 'همه %s', 'abed' ), $cat->name ),
					'terms'      => $kids,
				)
			);
		}
	}
	?>

	<section class="ab-section ab-trust"><div class="ab-container"><div class="ab-trust__in">
		<picture class="ab-trust__bg">
			<img src="<?php echo esc_url( abed_img( 'Abed-Last-Banner-mob.webp' ) ); ?>" width="1616" height="656" alt="" loading="lazy" decoding="async">
		</picture>
		<div class="ab-trust__text">
			<h2 class="ab-trust__title"><?php esc_html_e( 'اعتبار ما شمایید', 'abed' ); ?></h2>
			<p class="ab-trust__lead"><?php echo wp_kses( get_theme_mod( 'abed_trust_lead', "۶۰ سال،<br>یا ۸۰ سال!<br>نظر شما<br>مهم‌تر از این اعداد است…" ), array( 'br' => array() ) ); ?></p>
			<a class="ab-btn ab-btn--primary" href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>"><?php esc_html_e( 'درباره ما', 'abed' ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a>
		</div>
	</div></div></section>

	<section class="ab-section ab-steps ab-steps--video"><div class="ab-container">
		<div class="ab-section__head"><h2 class="ab-section__title"><?php esc_html_e( 'خرید عمده در ۴ قدم', 'abed' ); ?></h2>
		<a class="ab-section__more" href="<?php echo esc_url( home_url( '/buying-guide/' ) ); ?>"><?php esc_html_e( 'راهنمای کامل خرید', 'abed' ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a></div>
		<div class="ab-steps__wrap">
			<ol class="ab-steps__list">
				<li class="ab-step"><span class="ab-step__n">۱</span><?php echo abed_icon( 'search', 'ab-step__icon' ); // phpcs:ignore ?><h3 class="ab-step__title"><?php esc_html_e( 'انتخاب کالا', 'abed' ); ?></h3><p class="ab-step__text"><?php esc_html_e( 'از جستجو، دسته‌ها یا نمای «سفارش سریع» کالاها و تعداد را انتخاب کنید.', 'abed' ); ?></p></li>
				<li class="ab-step"><span class="ab-step__n">۲</span><?php echo abed_icon( 'bag', 'ab-step__icon' ); // phpcs:ignore ?><h3 class="ab-step__title"><?php esc_html_e( 'ثبت سفارش', 'abed' ); ?></h3><p class="ab-step__text"><?php esc_html_e( 'قیمت پلکانی خودکار اعمال می‌شود؛ مبلغ نهایی را پیش از ثبت می‌بینید.', 'abed' ); ?></p></li>
				<li class="ab-step"><span class="ab-step__n">۳</span><?php echo abed_icon( 'shield', 'ab-step__icon' ); // phpcs:ignore ?><h3 class="ab-step__title"><?php esc_html_e( 'تأیید قیمت و موجودی', 'abed' ); ?></h3><p class="ab-step__text"><?php esc_html_e( 'قیمت و موجودی سفارش شما در کوتاه‌ترین زمان تأیید می‌شود.', 'abed' ); ?></p></li>
				<li class="ab-step"><span class="ab-step__n">۴</span><?php echo abed_icon( 'truck', 'ab-step__icon' ); // phpcs:ignore ?><h3 class="ab-step__title"><?php esc_html_e( 'ارسال', 'abed' ); ?></h3><p class="ab-step__text"><?php esc_html_e( 'با پست، تیپاکس، ماهکس یا باربری به سراسر کشور ارسال می‌شود.', 'abed' ); ?></p></li>
			</ol>
		</div>
	</div></section>

	<section class="ab-section ab-section--consult" id="consult"><div class="ab-container"><div class="ab-consult">
		<div class="ab-consult__text">
			<h2 class="ab-consult__title"><?php esc_html_e( 'برای خرید عمده مشاوره بگیرید', 'abed' ); ?></h2>
			<p><?php esc_html_e( 'مشاوره و راهنمایی صحیح، برای ما مهم‌تر از فروش محصول است.', 'abed' ); ?></p>
			<a class="ab-btn ab-btn--gold" href="tel:<?php echo esc_attr( abed_tel_href( $sales ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?><span dir="ltr"><?php echo esc_html( abed_tel_text( $sales ) ); ?></span></a>
		</div>
		<div class="ab-consult__form"><div class="abcs-inline"><?php get_template_part( 'template-parts/consult-form', null, array( 'ctx' => 'inline' ) ); ?></div></div>
	</div></div></section>

	<?php
	// پیشنهاد عابد: محصولات ویژه.
	get_template_part(
		'template-parts/rail',
		null,
		array(
			'title'    => __( 'پیشنهاد عابد', 'abed' ),
			'more_url' => $shop_url,
			'query'    => array(
				'tax_query' => array( // phpcs:ignore WordPress.DB.SlowDBQuery
					array(
						'taxonomy' => 'product_visibility',
						'field'    => 'name',
						'terms'    => 'featured',
					),
				),
			),
		)
	);

	// تازه‌ترین قیمت‌ها: آخرین محصولات به‌روزشده.
	get_template_part(
		'template-parts/rail',
		null,
		array(
			'title'    => __( 'تازه‌ترین قیمت‌ها', 'abed' ),
			'more_url' => $shop_url,
			'query'    => array( 'orderby' => 'modified', 'order' => 'DESC' ),
		)
	);
	?>

	<section class="ab-section ab-faq"><div class="ab-container">
		<div class="ab-section__head"><h2 class="ab-section__title"><?php esc_html_e( 'سؤالات پرتکرار خرید عمده', 'abed' ); ?></h2>
		<a class="ab-section__more" href="<?php echo esc_url( home_url( '/faq/' ) ); ?>"><?php esc_html_e( 'همه سؤالات', 'abed' ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a></div>
		<div class="ab-faq__list">
			<?php
			$faq = array(
				array( 'قیمت‌های سایت عمده است؟', 'بله؛ فروشگاه برای همکاری با عطاری‌ها، رستوران‌ها، فروشگاه‌ها و تولیدکنندگان راه‌اندازی شده و قیمت‌ها عمده است. برای مقدار بیشتر، قیمت پلکانی ارزان‌تر هم در صفحه هر کالا آمده است.' ),
				array( 'کمترین مقدار قابل خرید چقدر است؟', 'برای بیشتر محصولات یک کیلوگرم؛ در بعضی محصولات مثل جوش شیرین یک کیسه (۲۵ کیلویی) که در صفحه همان کالا نوشته شده است.' ),
				array( 'ارسال سفارش چطور انجام می‌شود؟', 'بنا به درخواست شما با پست، تیپاکس یا باربری به شهرستان‌ها و با پیک یا خودروی مجموعه در مشهد. سفارش‌ها معمولاً ظرف ۳ تا ۲۴ ساعت آماده ارسال می‌شوند.' ),
				array( 'تسویه فاکتور چگونه است؟', 'پرداخت از درگاه اینترنتی سایت، کارت به کارت، حواله پایا و ساتنا یا کارتخوان در مشهد. با همکاران دائمی به صورت اعتباری هم کار می‌کنیم.' ),
				array( 'می‌شود کالا را قلم بخرم و آسیاب‌شده تحویل بگیرم؟', 'بله؛ آسیاب به خواست مشتری از خدمات ماست. هنگام تأیید فاکتور، آسیاب هر قلم را اطلاع دهید.' ),
				array( 'اگر کیفیت کالا با انتظارم فرق داشت چه کنم؟', 'به محض رسیدن کالا، اگر کوچک‌ترین نارضایتی داشتید سریع به ما اطلاع دهید تا مرجوعی و بازگشت وجه انجام شود.' ),
			);
			foreach ( $faq as $i => $row ) :
				?>
				<details class="ab-faq__it"<?php echo 0 === $i ? ' open' : ''; ?>>
					<summary><span><?php echo esc_html( $row[0] ); ?></span><?php echo abed_icon( 'chevron-down' ); // phpcs:ignore ?></summary>
					<p><?php echo esc_html( $row[1] ); ?></p>
				</details>
			<?php endforeach; ?>
		</div>
	</div></section>

	<section class="ab-section ab-store"><div class="ab-container ab-store__in">
		<?php if ( get_theme_mod( 'abed_store_image' ) ) : ?>
		<figure class="ab-store__img"><img src="<?php echo esc_url( get_theme_mod( 'abed_store_image' ) ); ?>" width="1366" height="768" alt="<?php echo esc_attr( get_bloginfo( 'name' ) ); ?>" loading="lazy" decoding="async"></figure>
		<?php endif; ?>
		<div class="ab-store__text">
			<h2 class="ab-store__title"><?php echo esc_html( sprintf( /* translators: %s: site */ __( '%s در مشهد', 'abed' ), get_bloginfo( 'name' ) ) ); ?></h2>
			<p class="ab-store__lead"><?php esc_html_e( 'از نزدیک ببینید، بچشید و انتخاب کنید؛ یا با واحدهای فروش تماس بگیرید.', 'abed' ); ?></p>
			<p class="ab-store__addr"><?php echo abed_icon( 'pin' ); // phpcs:ignore ?><span><?php echo esc_html( $contact['address'] ); ?></span></p>
			<ul class="ab-store__phones">
				<?php foreach ( $contact['phones'] as $ph ) : ?>
					<?php if ( $ph['tel'] ) : ?><li><span><?php echo esc_html( $ph['label'] ); ?></span><a class="ab-btn ab-btn--ghost ab-btn--sm" href="tel:<?php echo esc_attr( abed_tel_href( $ph['tel'] ) ); ?>" dir="ltr"><?php echo esc_html( abed_tel_text( $ph['tel'] ) ); ?></a></li><?php endif; ?>
				<?php endforeach; ?>
			</ul>
			<p><a class="ab-btn ab-btn--primary" href="<?php echo esc_url( home_url( '/contact/' ) ); ?>"><?php esc_html_e( 'مسیر و ساعت کاری', 'abed' ); ?> <?php echo abed_icon( 'arrow-left' ); // phpcs:ignore ?></a></p>
		</div>
	</div></section>

</div>
<?php
get_footer();

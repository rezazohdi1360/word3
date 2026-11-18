<?php
/**
 * توابع کمکی قالب.
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * آیکون SVG از اسپرایت قالب.
 *
 * @param string $name  نام آیکون بدون پیشوند «i-».
 * @param string $class کلاس اضافه.
 * @return string
 */
function abed_icon( $name, $class = '' ) {
	return sprintf(
		'<svg class="ab-i %1$s" aria-hidden="true" focusable="false"><use href="#i-%2$s"></use></svg>',
		esc_attr( $class ),
		esc_attr( $name )
	);
}

/**
 * آدرس یک فایل در پوشه assets/img.
 *
 * @param string $file File name.
 * @return string
 */
function abed_img( $file ) {
	return ABED_URI . '/assets/img/' . ltrim( $file, '/' );
}

/**
 * تبدیل ارقام لاتین به فارسی.
 *
 * @param string|int $s Text.
 * @return string
 */
function abed_fa_digits( $s ) {
	return strtr( (string) $s, array( '0' => '۰', '1' => '۱', '2' => '۲', '3' => '۳', '4' => '۴', '5' => '۵', '6' => '۶', '7' => '۷', '8' => '۸', '9' => '۹' ) );
}

/**
 * اطلاعات تماس فروشگاه (قابل تغییر در سفارشی‌سازی).
 *
 * @return array
 */
function abed_contact() {
	return array(
		'address' => get_theme_mod( 'abed_address', 'ایران، مشهد، مصلی ۲، مقابل زینبیون 6، پلاک ۶۵' ),
		'phones'  => array(
			array( 'label' => 'واحد فروش', 'tel' => get_theme_mod( 'abed_phone_sales', '09151002298' ) ),
			array( 'label' => 'واحد اداری', 'tel' => get_theme_mod( 'abed_phone_office', '09150222298' ) ),
			array( 'label' => 'حسابداری', 'tel' => get_theme_mod( 'abed_phone_accounting', '09150552298' ) ),
			array( 'label' => 'ارتباط با مشتری', 'tel' => get_theme_mod( 'abed_phone_support', '09150992298' ) ),
		),
	);
}

/**
 * شماره موبایل ایران به قالب بین‌المللی برای لینک tel:.
 *
 * @param string $tel Number.
 * @return string
 */
function abed_tel_href( $tel ) {
	$tel = preg_replace( '/\D+/', '', (string) $tel );
	if ( 0 === strpos( $tel, '0' ) ) {
		$tel = '98' . substr( $tel, 1 );
	}
	return '+' . $tel;
}

/**
 * شماره تلفن با ارقام فارسی (جهت LTR برای نمایش درست).
 *
 * @param string $tel Number.
 * @return string
 */
function abed_tel_text( $tel ) {
	return abed_fa_digits( $tel );
}

/**
 * شبکه‌های اجتماعی فوتر.
 *
 * @return array label => url
 */
function abed_socials() {
	$wa = preg_replace( '/\D+/', '', abed_tel_href( get_theme_mod( 'abed_phone_sales', '09151002298' ) ) );
	return array(
		'واتس‌اپ'    => 'https://wa.me/' . $wa,
		'بله'        => get_theme_mod( 'abed_social_bale', 'https://ble.ir/abedherb' ),
		'ایتا'       => get_theme_mod( 'abed_social_eitaa', 'https://eitaa.com/abedherb' ),
		'سروش پلاس'  => get_theme_mod( 'abed_social_splus', 'https://splus.ir/abedherb' ),
		'روبیکا'     => get_theme_mod( 'abed_social_rubika', 'https://rubika.ir/abedherb' ),
	);
}

/**
 * لوگوی سایت: لوگوی سفارشی وردپرس یا لوگوی پیش‌فرض قالب.
 *
 * @param string $class Extra class on <a>.
 * @param bool   $tagline Show tagline.
 */
function abed_logo( $class = '', $tagline = true ) {
	$logo_id = get_theme_mod( 'custom_logo' );
	$img     = $logo_id
		? wp_get_attachment_image( $logo_id, array( 56, 56 ), false, array( 'class' => 'ab-logo__img', 'alt' => get_bloginfo( 'name' ) ) )
		: '<img class="ab-logo__img" src="' . esc_url( abed_img( 'Abedherb-logo.svg' ) ) . '" alt="' . esc_attr( get_bloginfo( 'name' ) ) . '" width="56" height="56" decoding="async">';
	$tag     = get_theme_mod( 'abed_tagline', 'پخش عمده عطاری و ادویه' );

	echo '<a class="ab-logo ' . esc_attr( $class ) . '" href="' . esc_url( home_url( '/' ) ) . '" rel="home">';
	echo $img; // phpcs:ignore WordPress.Security.EscapeOutput
	echo '<span class="ab-logo__name"><strong>' . esc_html( get_bloginfo( 'name' ) ) . '</strong>';
	if ( $tagline && $tag ) {
		echo '<small>' . esc_html( $tag ) . '</small>';
	}
	echo '</span></a>';
}

/**
 * مسیر صفحه (breadcrumb).
 */
function abed_breadcrumbs() {
	$items = array( array( __( 'خانه', 'abed' ), home_url( '/' ) ) );

	if ( function_exists( 'is_woocommerce' ) && is_woocommerce() && function_exists( 'wc_get_page_id' ) ) {
		$shop = wc_get_page_id( 'shop' );
		if ( is_product() ) {
			$terms = get_the_terms( get_the_ID(), 'product_cat' );
			if ( $terms && ! is_wp_error( $terms ) ) {
				$term = $terms[0];
				$chain = array_reverse( get_ancestors( $term->term_id, 'product_cat', 'taxonomy' ) );
				foreach ( $chain as $tid ) {
					$t = get_term( $tid, 'product_cat' );
					$items[] = array( $t->name, get_term_link( $t ) );
				}
				$items[] = array( $term->name, get_term_link( $term ) );
			}
			$items[] = array( get_the_title(), '' );
		} elseif ( is_product_taxonomy() ) {
			if ( $shop > 0 ) {
				$items[] = array( get_the_title( $shop ), get_permalink( $shop ) );
			}
			$items[] = array( single_term_title( '', false ), '' );
		} else {
			$items[] = array( $shop > 0 ? get_the_title( $shop ) : __( 'فروشگاه', 'abed' ), '' );
		}
	} elseif ( is_singular() ) {
		$items[] = array( get_the_title(), '' );
	} elseif ( is_search() ) {
		$items[] = array( __( 'نتایج جستجو', 'abed' ), '' );
	} elseif ( is_archive() ) {
		$items[] = array( wp_strip_all_tags( get_the_archive_title() ), '' );
	} else {
		return;
	}

	echo '<nav class="ab-crumbs" aria-label="' . esc_attr__( 'مسیر صفحه', 'abed' ) . '"><ol>';
	foreach ( $items as $it ) {
		if ( $it[1] ) {
			echo '<li><a href="' . esc_url( $it[1] ) . '">' . esc_html( $it[0] ) . '</a></li>';
		} else {
			echo '<li>' . esc_html( $it[0] ) . '</li>';
		}
	}
	echo '</ol></nav>';
}

/**
 * بخش دعوت به تماس (CTA) پایین صفحه‌ها.
 */
function abed_cta() {
	$c   = abed_contact();
	$tel = $c['phones'][0]['tel'];
	$wa  = abed_socials()['واتس‌اپ'];
	?>
	<section class="ab-section"><div class="ab-container"><div class="ab-cta">
		<div class="ab-cta__text">
			<h2 class="ab-cta__title"><?php esc_html_e( 'سفارش عمده یا استعلام قیمت ویژه دارید؟', 'abed' ); ?></h2>
			<p><?php esc_html_e( 'کارشناسان فروش برای سفارش‌های حجمی، کالای ناموجود و قیمت همکاری راهنمایی‌تان می‌کنند.', 'abed' ); ?></p>
		</div>
		<div class="ab-cta__actions">
			<a class="ab-btn ab-btn--gold ab-btn--lg" href="tel:<?php echo esc_attr( abed_tel_href( $tel ) ); ?>"><?php echo abed_icon( 'phone' ); // phpcs:ignore ?> <span dir="ltr"><?php echo esc_html( abed_tel_text( $tel ) ); ?></span></a>
			<a class="ab-btn ab-btn--light ab-btn--lg" href="<?php echo esc_url( $wa ); ?>" target="_blank" rel="noopener"><?php echo abed_icon( 'chat' ); // phpcs:ignore ?> <?php esc_html_e( 'پیام در واتس‌اپ', 'abed' ); ?></a>
		</div>
	</div></div></section>
	<?php
}

/**
 * منوی پیش‌فرض وقتی منویی در پیشخوان تعیین نشده است.
 */
function abed_menu_fallback() {
	$links = array(
		array( __( 'خانه', 'abed' ), home_url( '/' ) ),
	);
	if ( function_exists( 'wc_get_page_permalink' ) ) {
		$links[] = array( __( 'فروشگاه', 'abed' ), wc_get_page_permalink( 'shop' ) );
		$links[] = array( __( 'پیگیری سفارش', 'abed' ), home_url( '/order-tracking/' ) );
	}
	$links[] = array( __( 'لیست قیمت', 'abed' ), home_url( '/price-list/' ) );
	$links[] = array( __( 'راهنمای خرید', 'abed' ), home_url( '/buying-guide/' ) );
	$links[] = array( __( 'سؤالات متداول', 'abed' ), home_url( '/faq/' ) );
	$links[] = array( __( 'درباره ما', 'abed' ), home_url( '/about-us/' ) );
	$links[] = array( __( 'تماس با ما', 'abed' ), home_url( '/contact/' ) );

	echo '<ul class="ab-menu ab-footer__links">';
	foreach ( $links as $l ) {
		echo '<li><a href="' . esc_url( $l[1] ) . '">' . esc_html( $l[0] ) . '</a></li>';
	}
	echo '</ul>';
}

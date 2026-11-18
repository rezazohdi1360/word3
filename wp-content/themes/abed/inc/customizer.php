<?php
/**
 * تنظیمات سفارشی‌سازی (Customizer).
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * @param WP_Customize_Manager $wp_customize Manager.
 */
function abed_customize_register( $wp_customize ) {
	$wp_customize->add_section(
		'abed_site',
		array(
			'title'    => __( 'تنظیمات قالب عابد', 'abed' ),
			'priority' => 30,
		)
	);

	$fields = array(
		'abed_tagline'          => array( 'زیرعنوان کنار لوگو', 'پخش عمده عطاری و ادویه', 'text' ),
		'abed_ticker'           => array( 'اطلاعیه‌های نوار بالا (هر خط یک مورد)', "تأیید لحظه‌ای قیمت و موجودی در ۳۰ ثانیه\nقیمت‌ها به‌صورت روزانه به‌روزرسانی می‌شوند\nامکان پرداخت اینترنتی از درگاه به‌پرداخت ملت", 'textarea' ),
		'abed_hero_eyebrow'     => array( 'بالاعنوان بنر صفحه اصلی', 'فروشگاه عابد', 'text' ),
		'abed_hero_title'       => array( 'عنوان بنر صفحه اصلی', 'پخش عمده ادویه، گیاهان دارویی و محصولات عطاری', 'text' ),
		'abed_hero_lead'        => array( 'توضیح بنر صفحه اصلی', 'قیمت‌های روز، تخفیف پلکانی برای خرید عمده و ارسال به سراسر کشور', 'text' ),
		'abed_footer_about'     => array( 'متن درباره ما در فوتر', 'فروشگاه عابد با بیش از ۶۰ سال سابقه خانوادگی در زمینه محصولات عطاری، شامل گیاهان دارویی، ادویه‌جات و انواع بذر، در خدمت همکاران محترم است.', 'textarea' ),
		'abed_address'          => array( 'نشانی', 'ایران، مشهد، مصلی ۲، مقابل زینبیون 6، پلاک ۶۵', 'text' ),
		'abed_phone_sales'      => array( 'تلفن واحد فروش', '09151002298', 'text' ),
		'abed_phone_office'     => array( 'تلفن واحد اداری', '09150222298', 'text' ),
		'abed_phone_accounting' => array( 'تلفن حسابداری', '09150552298', 'text' ),
		'abed_phone_support'    => array( 'تلفن ارتباط با مشتری', '09150992298', 'text' ),
		'abed_social_bale'      => array( 'لینک بله', 'https://ble.ir/abedherb', 'url' ),
		'abed_social_eitaa'     => array( 'لینک ایتا', 'https://eitaa.com/abedherb', 'url' ),
		'abed_social_splus'     => array( 'لینک سروش پلاس', 'https://splus.ir/abedherb', 'url' ),
		'abed_social_rubika'    => array( 'لینک روبیکا', 'https://rubika.ir/abedherb', 'url' ),
		'abed_popular_searches' => array( 'جستجوهای پرتکرار (با ویرگول فارسی «،» جدا کنید)', 'فلفل سیاه،زردچوبه،جوش شیرین،فلفل قرمز،هل اکبر', 'text' ),
		'abed_enamad_code'      => array( 'کد نماد اعتماد (code در تگ enamad)', '', 'text' ),
		'abed_enamad_img'       => array( 'آدرس تصویر نماد اعتماد', '', 'url' ),
	);

	foreach ( $fields as $id => $f ) {
		$wp_customize->add_setting(
			$id,
			array(
				'default'           => $f[1],
				'sanitize_callback' => 'textarea' === $f[2] ? 'sanitize_textarea_field' : ( 'url' === $f[2] ? 'esc_url_raw' : 'sanitize_text_field' ),
			)
		);
		$wp_customize->add_control(
			$id,
			array(
				'label'   => $f[0],
				'section' => 'abed_site',
				'type'    => $f[2],
			)
		);
	}
}
add_action( 'customize_register', 'abed_customize_register' );

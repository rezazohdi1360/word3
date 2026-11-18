<?php
/**
 * فرم درخواست مشاوره تلفنی (برای مودال و بخش صفحه اصلی). args['ctx'] = modal|inline
 *
 * @package abed
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$ctx = isset( $args['ctx'] ) ? sanitize_key( $args['ctx'] ) : 'inline';
?>
<form class="abcs-form" data-abcs-form novalidate>
	<label><span><?php esc_html_e( 'نام شما', 'abed' ); ?></span><input name="last_name" autocomplete="name" required maxlength="80"></label>
	<label><span><?php esc_html_e( 'شماره موبایل', 'abed' ); ?></span><input name="phone" type="tel" inputmode="tel" autocomplete="tel" dir="ltr" required maxlength="20" placeholder="09…"></label>
	<label><span><?php esc_html_e( 'چه کالایی لازم دارید؟', 'abed' ); ?></span><input name="product_needed" required maxlength="150" placeholder="<?php esc_attr_e( 'مثلاً زردچوبه قلم، ۵۰ کیلو', 'abed' ); ?>"></label>
	<fieldset class="abcs-when">
		<legend><?php esc_html_e( 'چه زمانی تماس بگیریم؟', 'abed' ); ?></legend>
		<label><input type="radio" name="when" value="any" checked><span><?php esc_html_e( 'هر وقت', 'abed' ); ?></span></label>
		<label><input type="radio" name="when" value="am"><span><?php esc_html_e( 'صبح', 'abed' ); ?></span></label>
		<label><input type="radio" name="when" value="pm"><span><?php esc_html_e( 'عصر', 'abed' ); ?></span></label>
	</fieldset>
	<input type="text" name="website" class="abcs-hp" tabindex="-1" autocomplete="off" aria-hidden="true">
	<input type="hidden" name="ctx" value="<?php echo esc_attr( $ctx ); ?>">
	<button type="submit" class="abcs-submit"><?php esc_html_e( 'ثبت درخواست تماس', 'abed' ); ?></button>
	<p class="abcs-note"><?php esc_html_e( 'روزهای کاری، ساعت ۹ تا ۱۶', 'abed' ); ?></p>
	<p class="abcs-msg" role="status" aria-live="polite"></p>
</form>

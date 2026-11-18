<?php
/**
 * Plugin Name: Abed Demo Importer (اطلاعات نمونه عابد)
 * Description: دسته‌ها، ۷۵ محصول نمونه با قیمت پلکانی و عکس، صفحه‌های ثابت و منوی سایت عابد را با یک کلیک در سایت می‌ریزد تا قالب «abed» دقیقاً مثل سایت اصلی دیده شود. به ووکامرس نیاز دارد.
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Text Domain: abed-demo
 *
 * @package abed-demo-importer
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'ABED_DEMO_DIR', plugin_dir_path( __FILE__ ) );

/**
 * صفحه‌ی ابزار در پیشخوان.
 */
function abed_demo_menu() {
	add_management_page( 'اطلاعات نمونه عابد', 'اطلاعات نمونه عابد', 'manage_options', 'abed-demo', 'abed_demo_page' );
}
add_action( 'admin_menu', 'abed_demo_menu' );

/**
 * محتوای صفحه.
 */
function abed_demo_page() {
	$wc    = class_exists( 'WooCommerce' );
	$nonce = wp_create_nonce( 'abed_demo' );
	$total = count( abed_demo_data( 'products' ) );
	?>
	<div class="wrap" dir="rtl">
		<h1>وارد کردن اطلاعات نمونه عابد</h1>
		<?php if ( ! $wc ) : ?>
			<div class="notice notice-error"><p>ابتدا افزونه‌ی <strong>ووکامرس</strong> را نصب و فعال کنید، سپس به این صفحه برگردید.</p></div>
		<?php else : ?>
			<p>این ابزار ۱۲ دسته‌ی اصلی با زیردسته‌ها، <?php echo esc_html( $total ); ?> محصول (با قیمت پلکانی، مشخصات و عکس)، صفحه‌های درباره ما، تماس، سؤالات متداول، راهنمای خرید، سیاست مرجوعی و خدمات، و منوی سایت را می‌سازد و صفحه‌ی اصلی را روی قالب عابد تنظیم می‌کند.</p>
			<p>اگر دوباره اجرا شود، موارد تکراری ساخته نمی‌شوند. مدت کار حدود یک تا دو دقیقه است؛ تا پایان کار صفحه را نبندید.</p>
			<p><button class="button button-primary button-hero" id="abed-demo-go">شروع وارد کردن اطلاعات</button></p>
			<div id="abed-demo-bar" style="display:none;max-width:520px;height:18px;background:#e0e0e0;border-radius:9px;overflow:hidden"><div id="abed-demo-fill" style="height:100%;width:0;background:#283618;transition:width .3s"></div></div>
			<p id="abed-demo-msg" style="font-weight:600"></p>
			<script>
			(function(){
				var btn=document.getElementById('abed-demo-go'),bar=document.getElementById('abed-demo-bar'),fill=document.getElementById('abed-demo-fill'),msg=document.getElementById('abed-demo-msg');
				var total=<?php echo (int) $total; ?>,limit=4;
				function call(step,offset){
					var fd=new FormData();fd.append('action','abed_demo_run');fd.append('nonce','<?php echo esc_js( $nonce ); ?>');fd.append('step',step);fd.append('offset',offset);
					return fetch(ajaxurl,{method:'POST',body:fd,credentials:'same-origin'}).then(function(r){return r.json();});
				}
				function fail(e){msg.textContent='خطا: '+(e&&e.data&&e.data.message?e.data.message:'پاسخ نامعتبر از سرور. دوباره تلاش کنید؛ موارد انجام‌شده تکرار نمی‌شوند.');btn.disabled=false;}
				function products(offset){
					msg.textContent='در حال ساخت محصولات… ('+Math.min(offset,total)+' از '+total+')';
					fill.style.width=(10+Math.min(offset,total)/total*88)+'%';
					if(offset>=total){return finish();}
					call('products',offset).then(function(r){if(!r.success){return fail(r);}products(offset+limit);}).catch(fail);
				}
				function finish(){
					call('finish',0).then(function(r){
						if(!r.success){return fail(r);}
						fill.style.width='100%';msg.innerHTML='تمام شد. <a href="'+r.data.home+'" target="_blank">مشاهده سایت</a>';btn.disabled=false;
					}).catch(fail);
				}
				btn.addEventListener('click',function(){
					btn.disabled=true;bar.style.display='block';fill.style.width='5%';msg.textContent='در حال ساخت دسته‌ها، صفحه‌ها و منو…';
					call('setup',0).then(function(r){if(!r.success){return fail(r);}products(0);}).catch(fail);
				});
			})();
			</script>
		<?php endif; ?>
	</div>
	<?php
}

/**
 * خواندن فایل داده.
 *
 * @param string $name File name without extension.
 * @return array
 */
function abed_demo_data( $name ) {
	$file = ABED_DEMO_DIR . 'data/' . $name . '.json';
	if ( ! file_exists( $file ) ) {
		return array();
	}
	$d = json_decode( file_get_contents( $file ), true ); // phpcs:ignore WordPress.WP.AlternativeFunctions
	return is_array( $d ) ? $d : array();
}

/**
 * ساخت بازگشتی دسته‌ها.
 *
 * @param array $nodes  Tree.
 * @param int   $parent Parent term id.
 * @param array $path   Names so far.
 * @param array $map    Result map (path string => term id).
 */
function abed_demo_make_cats( $nodes, $parent, $path, &$map ) {
	foreach ( $nodes as $n ) {
		$name = $n['name'];
		$term = term_exists( $name, 'product_cat', $parent );
		if ( ! $term ) {
			$term = wp_insert_term( $name, 'product_cat', array( 'parent' => $parent ) );
		}
		if ( is_wp_error( $term ) ) {
			continue;
		}
		$id                                = (int) ( is_array( $term ) ? $term['term_id'] : $term );
		$p                                 = array_merge( $path, array( $name ) );
		$map[ implode( '|', $p ) ]         = $id;
		if ( ! empty( $n['children'] ) ) {
			abed_demo_make_cats( $n['children'], $id, $p, $map );
		}
	}
}

/**
 * وارد کردن یک فایل تصویر از پوشه‌ی افزونه به کتابخانه‌ی رسانه.
 *
 * @param string $file    File name inside images/.
 * @param int    $post_id Parent post.
 * @param string $title   Attachment title.
 * @return int Attachment id or 0.
 */
function abed_demo_attach( $file, $post_id, $title ) {
	$src = ABED_DEMO_DIR . 'images/' . basename( $file );
	if ( ! file_exists( $src ) ) {
		return 0;
	}
	require_once ABSPATH . 'wp-admin/includes/file.php';
	require_once ABSPATH . 'wp-admin/includes/media.php';
	require_once ABSPATH . 'wp-admin/includes/image.php';

	$tmp = wp_tempnam( $file );
	if ( ! copy( $src, $tmp ) ) {
		return 0;
	}
	$id = media_handle_sideload(
		array( 'name' => basename( $file ), 'tmp_name' => $tmp ),
		$post_id,
		$title
	);
	if ( is_wp_error( $id ) ) {
		if ( file_exists( $tmp ) ) {
			wp_delete_file( $tmp );
		}
		return 0;
	}
	return (int) $id;
}

/**
 * گام نخست: دسته‌ها، صفحه‌ها، منو، تنظیمات.
 */
function abed_demo_setup() {
	// دسته‌ها.
	$map = array();
	abed_demo_make_cats( abed_demo_data( 'categories' ), 0, array(), $map );
	update_option( 'abed_demo_cat_map', $map, false );

	// صفحه‌ها.
	$page_ids = array();
	foreach ( abed_demo_data( 'pages' ) as $pg ) {
		$existing = get_page_by_path( $pg['slug'] );
		if ( $existing ) {
			$page_ids[ $pg['slug'] ] = $existing->ID;
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'page',
				'post_status'  => 'publish',
				'post_title'   => $pg['title'],
				'post_name'    => $pg['slug'],
				'post_content' => wp_kses_post( $pg['content'] ),
			)
		);
		if ( $id && ! is_wp_error( $id ) ) {
			$page_ids[ $pg['slug'] ] = $id;
		}
	}

	// صفحه‌ی اصلی (محتوای آن را front-page.php قالب می‌سازد).
	$home = get_page_by_path( 'home-abed' );
	if ( ! $home ) {
		$hid = wp_insert_post( array( 'post_type' => 'page', 'post_status' => 'publish', 'post_title' => 'صفحه اصلی', 'post_name' => 'home-abed' ) );
	} else {
		$hid = $home->ID;
	}
	if ( $hid && ! is_wp_error( $hid ) ) {
		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $hid );
	}

	// منو.
	$menu_name = 'منوی اصلی عابد';
	$menu      = wp_get_nav_menu_object( $menu_name );
	if ( ! $menu ) {
		$mid = wp_create_nav_menu( $menu_name );
		if ( ! is_wp_error( $mid ) ) {
			wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => 'خانه', 'menu-item-url' => home_url( '/' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
			wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => 'محصولات فروشگاه عابد', 'menu-item-url' => function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : home_url( '/shop/' ), 'menu-item-status' => 'publish', 'menu-item-type' => 'custom' ) );
			foreach ( array( 'abed-services' => 'خدمات عابد', 'faq' => 'سوالات متداول', 'buying-guide' => 'راهنمای خرید', 'about-us' => 'درباره ما', 'contact' => 'تماس با ما' ) as $slug => $title ) {
				if ( ! empty( $page_ids[ $slug ] ) ) {
					wp_update_nav_menu_item( $mid, 0, array( 'menu-item-title' => $title, 'menu-item-object' => 'page', 'menu-item-object-id' => $page_ids[ $slug ], 'menu-item-type' => 'post_type', 'menu-item-status' => 'publish' ) );
				}
			}
			$locs            = get_theme_mod( 'nav_menu_locations', array() );
			$locs['primary'] = $mid;
			$locs['footer']  = $mid;
			set_theme_mod( 'nav_menu_locations', $locs );
		}
	}

	delete_transient( 'abed_search_index' );
}

/**
 * گام محصولات: چند محصول در هر درخواست.
 *
 * @param int $offset Start index.
 * @param int $limit  Batch size.
 */
function abed_demo_products( $offset, $limit ) {
	$all = abed_demo_data( 'products' );
	$map = get_option( 'abed_demo_cat_map', array() );

	foreach ( array_slice( $all, $offset, $limit ) as $i => $p ) {
		$key = md5( $p['title'] );
		$dup = get_posts( array( 'post_type' => 'product', 'post_status' => 'any', 'meta_key' => '_abed_demo_key', 'meta_value' => $key, 'fields' => 'ids', 'numberposts' => 1 ) ); // phpcs:ignore WordPress.DB.SlowDBQuery
		if ( $dup ) {
			continue;
		}
		$id = wp_insert_post(
			array(
				'post_type'    => 'product',
				'post_status'  => 'publish',
				'post_title'   => $p['title'],
				'post_content' => wp_kses_post( $p['desc'] ),
				'post_excerpt' => wp_kses_post( $p['short'] ),
			)
		);
		if ( ! $id || is_wp_error( $id ) ) {
			continue;
		}
		$price = ! empty( $p['tiers'] ) ? (float) $p['tiers'][0][1] : '';

		wp_set_object_terms( $id, 'simple', 'product_type' );
		update_post_meta( $id, '_abed_demo_key', $key );
		update_post_meta( $id, '_regular_price', $price );
		update_post_meta( $id, '_price', $price );
		update_post_meta( $id, '_sku', $p['sku'] );
		update_post_meta( $id, '_stock_status', 'instock' );
		update_post_meta( $id, '_manage_stock', 'no' );
		update_post_meta( $id, '_visibility', 'visible' );
		update_post_meta( $id, 'total_sales', max( 0, 200 - ( $offset + $i ) * 2 ) );
		update_post_meta( $id, '_abed_price_tiers', wp_json_encode( $p['tiers'] ) );
		update_post_meta( $id, '_abed_unit', $p['unit'] );

		// دسته‌ها: همه‌ی پله‌های مسیر.
		$term_ids = array();
		$path     = array();
		foreach ( $p['cats'] as $name ) {
			$path[] = $name;
			$k      = implode( '|', $path );
			if ( isset( $map[ $k ] ) ) {
				$term_ids[] = (int) $map[ $k ];
			}
		}
		if ( $term_ids ) {
			wp_set_object_terms( $id, $term_ids, 'product_cat' );
		}

		// مشخصات به‌صورت ویژگی‌های سفارشی.
		$attrs = array();
		foreach ( $p['specs'] as $pos => $sp ) {
			$attrs[ sanitize_title( $sp[0] ) . '-' . $pos ] = array(
				'name'         => $sp[0],
				'value'        => $sp[1],
				'position'     => $pos,
				'is_visible'   => 1,
				'is_variation' => 0,
				'is_taxonomy'  => 0,
			);
		}
		update_post_meta( $id, '_product_attributes', $attrs );

		// تصاویر.
		$gallery = array();
		foreach ( $p['images'] as $n => $file ) {
			$att = abed_demo_attach( $file, $id, $p['title'] );
			if ( ! $att ) {
				continue;
			}
			if ( 0 === count( $gallery ) && ! has_post_thumbnail( $id ) ) {
				set_post_thumbnail( $id, $att );
			} else {
				$gallery[] = $att;
			}
		}
		if ( $gallery ) {
			update_post_meta( $id, '_product_image_gallery', implode( ',', $gallery ) );
		}
	}
}

/**
 * پایان کار: پاک‌سازی کش‌ها.
 */
function abed_demo_finish() {
	delete_transient( 'abed_search_index' );
	if ( function_exists( 'wc_delete_product_transients' ) ) {
		wc_delete_product_transients( 0 );
	}
	flush_rewrite_rules();
}

/**
 * نقطه‌ی AJAX.
 */
function abed_demo_ajax() {
	if ( ! current_user_can( 'manage_options' ) || ! wp_verify_nonce( isset( $_POST['nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['nonce'] ) ) : '', 'abed_demo' ) ) {
		wp_send_json_error( array( 'message' => 'دسترسی مجاز نیست.' ), 403 );
	}
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error( array( 'message' => 'ووکامرس فعال نیست.' ), 400 );
	}
	// phpcs:disable WordPress.Security.NonceVerification
	$step   = isset( $_POST['step'] ) ? sanitize_key( wp_unslash( $_POST['step'] ) ) : '';
	$offset = isset( $_POST['offset'] ) ? absint( $_POST['offset'] ) : 0;
	// phpcs:enable
	@set_time_limit( 120 ); // phpcs:ignore

	if ( 'setup' === $step ) {
		abed_demo_setup();
		wp_send_json_success();
	}
	if ( 'products' === $step ) {
		abed_demo_products( $offset, 4 );
		wp_send_json_success();
	}
	if ( 'finish' === $step ) {
		abed_demo_finish();
		wp_send_json_success( array( 'home' => home_url( '/' ) ) );
	}
	wp_send_json_error( array( 'message' => 'گام نامعتبر.' ), 400 );
}
add_action( 'wp_ajax_abed_demo_run', 'abed_demo_ajax' );

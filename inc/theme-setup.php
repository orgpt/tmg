<?php
/**
 * Theme setup and assets.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function setup(): void {
	load_theme_textdomain('tmg-rentals', TMG_RENTALS_PATH . '/languages');

	add_theme_support('title-tag');
	add_theme_support('post-thumbnails');
	add_theme_support('html5', array('search-form', 'comment-form', 'comment-list', 'gallery', 'caption', 'style', 'script'));
	add_theme_support('custom-logo');
	add_theme_support('automatic-feed-links');
	add_theme_support('align-wide');

	register_nav_menus(
		array(
			'primary' => __('القائمة الرئيسية', 'tmg-rentals'),
		)
	);
}
add_action('after_setup_theme', __NAMESPACE__ . '\\setup');

function enqueue_assets(): void {
	$google_settings = function_exists(__NAMESPACE__ . '\\get_google_auth_settings') ? get_google_auth_settings() : array('client_id' => '');

	wp_enqueue_style('tmg-rentals-fonts', 'https://fonts.googleapis.com/css2?family=Cairo:wght@400;500;600;700;800;900&display=swap', array(), null);
	wp_enqueue_style('tmg-rentals-style', get_stylesheet_uri(), array(), TMG_RENTALS_VERSION);
	wp_enqueue_style('tmg-rentals-theme', TMG_RENTALS_URL . '/assets/css/theme.css', array('tmg-rentals-fonts', 'tmg-rentals-style'), TMG_RENTALS_VERSION);
	wp_enqueue_script('tmg-rentals-theme', TMG_RENTALS_URL . '/assets/js/theme.js', array(), TMG_RENTALS_VERSION, true);

	if (! empty($google_settings['client_id'])) {
		wp_enqueue_script('google-identity-services', 'https://accounts.google.com/gsi/client', array(), null, true);
	}

	wp_localize_script(
		'tmg-rentals-theme',
		'tmgRentals',
		array(
			'nextLabel'      => __('التالي', 'tmg-rentals'),
			'prevLabel'      => __('السابق', 'tmg-rentals'),
			'ajaxUrl'        => admin_url('admin-ajax.php'),
			'googleClientId' => $google_settings['client_id'] ?? '',
			'googleNonce'    => wp_create_nonce('tmg_google_auth'),
			'authRedirect'   => home_url('/add-property/'),
			'googleError'    => __('تعذر تسجيل الدخول عبر Google حالياً.', 'tmg-rentals'),
		)
	);
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');

function add_body_classes(array $classes): array {
	$classes[] = 'is-rtl';

	if (is_front_page()) {
		$classes[] = 'home-screen';
	}

	if (is_singular('properties')) {
		$classes[] = 'single-property-screen';
	}

	if ((bool) get_query_var('tmg_auth')) {
		$classes[] = 'auth-screen';
	}

	return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_body_classes');

function force_rtl_language_attributes(string $output): string {
	if (is_admin()) {
		return $output;
	}

	$output = preg_replace('/\sdir=("|\')(rtl|ltr)\1/i', '', $output) ?? $output;
	$output = preg_replace('/\slang=("|\')[^"\']+\1/i', '', $output) ?? $output;

	return trim($output . ' lang="ar" dir="rtl"');
}
add_filter('language_attributes', __NAMESPACE__ . '\\force_rtl_language_attributes');

function fallback_menu(): void {
	echo '<ul class="tmg-nav__menu">';
	echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('الرئيسية', 'tmg-rentals') . '</a></li>';
	echo '<li><a href="' . esc_url(get_post_type_archive_link('properties')) . '">' . esc_html__('العقارات', 'tmg-rentals') . '</a></li>';

	if (is_user_logged_in()) {
		echo '<li><a href="' . esc_url(home_url('/add-property/')) . '">' . esc_html__('أضف عقارك', 'tmg-rentals') . '</a></li>';
	} else {
		echo '<li><a href="' . esc_url(home_url('/auth/?mode=login')) . '">' . esc_html__('الدخول', 'tmg-rentals') . '</a></li>';
	}

	echo '</ul>';
}

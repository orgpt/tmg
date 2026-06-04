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
	wp_enqueue_style('tmg-rentals-style', get_stylesheet_uri(), array(), TMG_RENTALS_VERSION);
	wp_enqueue_style('tmg-rentals-theme', TMG_RENTALS_URL . '/assets/css/theme.css', array('tmg-rentals-style'), TMG_RENTALS_VERSION);
	wp_enqueue_script('tmg-rentals-theme', TMG_RENTALS_URL . '/assets/js/theme.js', array(), TMG_RENTALS_VERSION, true);

	wp_localize_script(
		'tmg-rentals-theme',
		'tmgRentals',
		array(
			'nextLabel' => __('التالي', 'tmg-rentals'),
			'prevLabel' => __('السابق', 'tmg-rentals'),
		)
	);
}
add_action('wp_enqueue_scripts', __NAMESPACE__ . '\\enqueue_assets');

function add_body_classes(array $classes): array {
	$classes[] = is_rtl() ? 'is-rtl' : 'is-ltr';

	if (is_front_page()) {
		$classes[] = 'home-screen';
	}

	if (is_singular('properties')) {
		$classes[] = 'single-property-screen';
	}

	return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\add_body_classes');

function fallback_menu(): void {
	echo '<ul class="tmg-nav__menu">';
	echo '<li><a href="' . esc_url(home_url('/')) . '">' . esc_html__('الرئيسية', 'tmg-rentals') . '</a></li>';
	echo '<li><a href="' . esc_url(get_post_type_archive_link('properties')) . '">' . esc_html__('العقارات', 'tmg-rentals') . '</a></li>';
	echo '<li><a href="' . esc_url(home_url('/add-property')) . '">' . esc_html__('أضف إعلانك', 'tmg-rentals') . '</a></li>';
	echo '</ul>';
}

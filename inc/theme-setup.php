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
	$is_add_property = function_exists(__NAMESPACE__ . '\\is_add_property_request') && is_add_property_request();

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

function add_resource_hints(array $urls, string $relation_type): array {
	if ($relation_type === 'preconnect') {
		$urls[] = 'https://fonts.googleapis.com';
		$urls[] = array(
			'href'        => 'https://fonts.gstatic.com',
			'crossorigin' => 'anonymous',
		);
	}

	return $urls;
}
add_filter('wp_resource_hints', __NAMESPACE__ . '\\add_resource_hints', 10, 2);

function preload_theme_stylesheet(string $html, string $handle, string $href, string $media): string {
	if ($handle !== 'tmg-rentals-theme') {
		return $html;
	}

	$preload = '<link rel="preload" as="style" href="' . esc_url($href) . '">';

	return $preload . $html;
}
add_filter('style_loader_tag', __NAMESPACE__ . '\\preload_theme_stylesheet', 10, 4);

function print_critical_styles(): void {
	if (is_admin()) {
		return;
	}
	?>
	<style id="tmg-critical-css">
		:root {
			--tmg-bg:#f9f9f6;
			--tmg-surface:#ffffff;
			--tmg-text:#1e293b;
			--tmg-border:rgba(30,41,59,.1);
			--tmg-shadow:0 20px 50px rgba(30,41,59,.08);
		}
		html { direction:rtl; background:var(--tmg-bg); }
		body {
			margin:0;
			font-family:"Cairo",Tahoma,"Segoe UI",sans-serif;
			background:
				radial-gradient(circle at top right, rgba(212,175,55,.12), transparent 28%),
				radial-gradient(circle at top left, rgba(25,135,84,.06), transparent 24%),
				linear-gradient(180deg, #fcfcfa 0%, #f3f6f1 100%);
			color:var(--tmg-text);
			line-height:1.7;
		}
		.tmg-container { width:min(1180px, calc(100% - 2rem)); margin:0 auto; }
		.tmg-site-header {
			position:sticky;
			top:0;
			z-index:20;
			background:rgba(255,255,255,.92);
			border-bottom:1px solid var(--tmg-border);
		}
		.tmg-site-header__inner {
			display:flex;
			flex-direction:row-reverse;
			align-items:center;
			justify-content:space-between;
			gap:1rem;
			padding:1rem 0;
		}
		.tmg-brand__logo { display:block; width:clamp(180px,17vw,260px); height:auto; }
		.tmg-header-actions { display:flex; align-items:center; gap:.9rem; }
		.tmg-section { padding:2rem 0 5rem; }
		.tmg-hero, .tmg-card {
			background:var(--tmg-surface);
			border:1px solid var(--tmg-border);
			border-radius:24px;
			box-shadow:var(--tmg-shadow);
		}
		.tmg-hero { padding:2rem; margin-bottom:2rem; }
	</style>
	<?php
}
add_action('wp_head', __NAMESPACE__ . '\\print_critical_styles', 1);

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

	if ((bool) get_query_var('tmg_agent_dashboard')) {
		$classes[] = 'agent-dashboard-screen';
	}

	if ((bool) get_query_var('tmg_property_payment')) {
		$classes[] = 'property-payment-screen';
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

function disable_frontend_admin_bar(): bool {
	return false;
}
add_filter('show_admin_bar', __NAMESPACE__ . '\\disable_frontend_admin_bar');

function ensure_frontend_upload_role_caps(): void {
	$roles = array('subscriber', 'contributor', 'author', 'editor');

	foreach ($roles as $role_name) {
		$role = get_role($role_name);

		if (! $role || $role->has_cap('upload_files')) {
			continue;
		}

		$role->add_cap('upload_files');
	}
}
add_action('init', __NAMESPACE__ . '\\ensure_frontend_upload_role_caps');

function allow_frontend_media_uploads(array $allcaps, array $caps, array $args, \WP_User $user): array {
	if (! $user->exists()) {
		return $allcaps;
	}

	// Front-end property submission and profile uploads need Media Library access
	// even for subscriber-like accounts.
	$allcaps['upload_files'] = true;

	return $allcaps;
}
add_filter('user_has_cap', __NAMESPACE__ . '\\allow_frontend_media_uploads', 10, 4);

function limit_frontend_attachment_queries(array $query): array {
	if (! is_user_logged_in() || current_user_can('manage_options')) {
		return $query;
	}

	$query['author'] = get_current_user_id();

	return $query;
}
add_filter('ajax_query_attachments_args', __NAMESPACE__ . '\\limit_frontend_attachment_queries');

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

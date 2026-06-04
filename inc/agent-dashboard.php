<?php
/**
 * Frontend agent dashboard.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_agent_dashboard_route(): void {
	add_rewrite_rule('^agent-dashboard/?$', 'index.php?tmg_agent_dashboard=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_agent_dashboard_route');

function register_agent_dashboard_query_vars(array $vars): array {
	$vars[] = 'tmg_agent_dashboard';

	return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\register_agent_dashboard_query_vars');

function is_agent_dashboard_request(): bool {
	$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ($home_path !== '' && str_starts_with($request_path, $home_path . '/')) {
		$request_path = substr($request_path, strlen($home_path) + 1);
	}

	return $request_path === 'agent-dashboard';
}

function maybe_use_agent_dashboard_template(string $template): string {
	if (! ((bool) get_query_var('tmg_agent_dashboard') || is_agent_dashboard_request())) {
		return $template;
	}

	$custom_template = TMG_RENTALS_PATH . '/template-agent-dashboard.php';

	if (! file_exists($custom_template)) {
		return $template;
	}

	status_header(200);
	global $wp_query;
	if ($wp_query instanceof \WP_Query) {
		$wp_query->is_404      = false;
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
	}

	return $custom_template;
}
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_agent_dashboard_template', 99);

function maybe_fix_agent_dashboard_title(array $parts): array {
	if ((bool) get_query_var('tmg_agent_dashboard') || is_agent_dashboard_request()) {
		$parts['title'] = __('لوحة الوكيل', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_agent_dashboard_title');

function maybe_fix_agent_dashboard_document_title(string $title): string {
	if ((bool) get_query_var('tmg_agent_dashboard') || is_agent_dashboard_request()) {
		return __('لوحة الوكيل - TMG Rentals', 'tmg-rentals');
	}

	return $title;
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\\maybe_fix_agent_dashboard_document_title', 99);

function get_agent_properties_query_args(int $user_id, array $extra = array()): array {
	$args = array(
		'post_type'      => 'properties',
		'post_status'    => array('publish', 'pending', 'draft'),
		'author'         => $user_id,
		'posts_per_page' => -1,
		'orderby'        => 'date',
		'order'          => 'DESC',
	);

	return array_merge($args, $extra);
}

function get_agent_dashboard_stats(int $user_id): array {
	$all_query = new \WP_Query(
		get_agent_properties_query_args(
			$user_id,
			array(
				'fields' => 'ids',
			)
		)
	);

	$published_query = new \WP_Query(
		get_agent_properties_query_args(
			$user_id,
			array(
				'post_status' => array('publish'),
				'fields'      => 'ids',
			)
		)
	);

	$rented_query = new \WP_Query(
		get_agent_properties_query_args(
			$user_id,
			array(
				'fields'     => 'ids',
				'meta_query' => array(
					array(
						'key'   => 'tmg_property_status',
						'value' => 'rented',
					),
				),
			)
		)
	);

	$pending_query = new \WP_Query(
		get_agent_properties_query_args(
			$user_id,
			array(
				'post_status' => array('pending'),
				'fields'      => 'ids',
			)
		)
	);

	$subscription = get_user_subscription($user_id);
	$usage        = get_user_subscription_usage($user_id);

	return array(
		'total'               => (int) $all_query->found_posts,
		'published'           => (int) $published_query->found_posts,
		'rented'              => (int) $rented_query->found_posts,
		'pending'             => (int) $pending_query->found_posts,
		'subscription_status' => $subscription['status'] ?: 'inactive',
		'package_title'       => $subscription['package_title'] ?: __('بدون باقة', 'tmg-rentals'),
		'usage'               => $usage,
	);
}

function get_agent_dashboard_reports(int $user_id): array {
	$posts = get_posts(
		get_agent_properties_query_args(
			$user_id,
			array(
				'posts_per_page' => -1,
			)
		)
	);

	$projects = array();
	$rental_types = array();

	foreach ($posts as $post) {
		$project = get_primary_project_label($post->ID);
		$rental  = get_primary_rental_type_label($post->ID);

		if ($project !== '') {
			$projects[$project] = ($projects[$project] ?? 0) + 1;
		}

		if ($rental !== '') {
			$rental_types[$rental] = ($rental_types[$rental] ?? 0) + 1;
		}
	}

	arsort($projects);
	arsort($rental_types);

	return array(
		'projects'     => $projects,
		'rental_types' => $rental_types,
	);
}

function get_agent_dashboard_listings(int $user_id): array {
	return get_posts(
		get_agent_properties_query_args(
			$user_id,
			array(
				'posts_per_page' => 20,
			)
		)
	);
}

function handle_agent_dashboard_actions(): void {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['tmg_dashboard_action'])) {
		return;
	}

	$action = sanitize_text_field(wp_unslash($_POST['tmg_dashboard_action']));

	if ($action !== 'toggle_property_status') {
		return;
	}

	if (! is_user_logged_in()) {
		wp_safe_redirect(home_url('/auth/?mode=login'));
		exit;
	}

	if (! isset($_POST['tmg_dashboard_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tmg_dashboard_nonce'])), 'tmg_dashboard_action')) {
		return;
	}

	$user_id    = get_current_user_id();
	$property_id = (int) ($_POST['property_id'] ?? 0);
	$property    = get_post($property_id);

	if (! $property instanceof \WP_Post || $property->post_type !== 'properties' || (int) $property->post_author !== $user_id) {
		wp_safe_redirect(home_url('/agent-dashboard/?notice=invalid_property'));
		exit;
	}

	$current_status = get_property_availability_status($property_id);
	$new_status     = $current_status === 'rented' ? 'available' : 'rented';

	update_post_meta($property_id, 'tmg_property_status', $new_status);

	wp_safe_redirect(home_url('/agent-dashboard/?notice=' . ($new_status === 'rented' ? 'marked_rented' : 'marked_available')));
	exit;
}
add_action('init', __NAMESPACE__ . '\\handle_agent_dashboard_actions');

function get_agent_dashboard_notice(): array {
	$notice = sanitize_text_field(wp_unslash($_GET['notice'] ?? ''));

	$messages = array(
		'marked_rented'    => array('type' => 'success', 'message' => __('تم تعليم العقار بأنه تم التأجير وإخفاؤه من البحث.', 'tmg-rentals')),
		'marked_available' => array('type' => 'success', 'message' => __('تمت إعادة العقار إلى حالة متاح الآن.', 'tmg-rentals')),
		'invalid_property' => array('type' => 'error', 'message' => __('تعذر تنفيذ الإجراء على هذا العقار.', 'tmg-rentals')),
	);

	return $messages[$notice] ?? array();
}

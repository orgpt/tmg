<?php
/**
 * Property payment flow for standard users.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_property_payment_route(): void {
	add_rewrite_rule('^property-payment/?$', 'index.php?tmg_property_payment=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_property_payment_route');

function register_property_payment_query_vars(array $vars): array {
	$vars[] = 'tmg_property_payment';

	return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\register_property_payment_query_vars');

function is_property_payment_request(): bool {
	$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ($home_path !== '' && str_starts_with($request_path, $home_path . '/')) {
		$request_path = substr($request_path, strlen($home_path) + 1);
	}

	return $request_path === 'property-payment';
}

function maybe_use_property_payment_template(string $template): string {
	if (! ((bool) get_query_var('tmg_property_payment') || is_property_payment_request())) {
		return $template;
	}

	$custom_template = TMG_RENTALS_PATH . '/template-property-payment.php';

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
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_property_payment_template', 99);

function maybe_fix_property_payment_title(array $parts): array {
	if ((bool) get_query_var('tmg_property_payment') || is_property_payment_request()) {
		$parts['title'] = __('إتمام الدفع', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_property_payment_title');

function maybe_fix_property_payment_document_title(string $title): string {
	if ((bool) get_query_var('tmg_property_payment') || is_property_payment_request()) {
		return __('إتمام الدفع - TMG Rentals', 'tmg-rentals');
	}

	return $title;
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\\maybe_fix_property_payment_document_title', 99);

function get_property_payment_status(int $post_id): string {
	$status = (string) get_post_meta($post_id, 'tmg_payment_status', true);

	return $status !== '' ? $status : 'not_required';
}

function is_property_waiting_payment(int $post_id): bool {
	return get_property_payment_status($post_id) === 'awaiting_payment';
}

function get_user_pending_payment_properties(int $user_id): array {
	return get_posts(
		array(
			'post_type'      => 'properties',
			'post_status'    => array('draft'),
			'author'         => $user_id,
			'posts_per_page' => -1,
			'orderby'        => 'date',
			'order'          => 'DESC',
			'meta_query'     => array(
				array(
					'key'   => 'tmg_payment_status',
					'value' => 'awaiting_payment',
				),
			),
		)
	);
}

function get_property_payment_notice(): array {
	$notice = sanitize_text_field(wp_unslash($_GET['payment_notice'] ?? ''));

	$messages = array(
		'paid'       => array('type' => 'success', 'message' => __('تم إرسال بيانات الدفع بنجاح وسيتم مراجعة الإعلان قبل النشر.', 'tmg-rentals')),
		'invalid'    => array('type' => 'error', 'message' => __('تعذر الوصول إلى هذا الإعلان أو لا تملك صلاحية الدفع له.', 'tmg-rentals')),
		'incomplete' => array('type' => 'error', 'message' => __('يرجى استكمال طريقة الدفع ومرجع العملية.', 'tmg-rentals')),
	);

	return $messages[$notice] ?? array();
}

function handle_property_payment_submission(): void {
	if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['tmg_property_payment_action'])) {
		return;
	}

	if (! is_user_logged_in()) {
		wp_safe_redirect(home_url('/auth/?mode=login'));
		exit;
	}

	if (! isset($_POST['tmg_property_payment_nonce']) || ! wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['tmg_property_payment_nonce'])), 'tmg_property_payment')) {
		return;
	}

	$property_id        = (int) ($_POST['property_id'] ?? 0);
	$payment_method     = sanitize_text_field(wp_unslash($_POST['payment_method'] ?? ''));
	$payment_reference  = sanitize_text_field(wp_unslash($_POST['payment_reference'] ?? ''));
	$payment_note       = sanitize_textarea_field(wp_unslash($_POST['payment_note'] ?? ''));
	$property           = get_post($property_id);
	$user_id            = get_current_user_id();

	if (! $property instanceof \WP_Post || $property->post_type !== 'properties' || (int) $property->post_author !== $user_id || ! is_property_waiting_payment($property_id)) {
		wp_safe_redirect(home_url('/property-payment/?payment_notice=invalid'));
		exit;
	}

	if ($payment_method === '' || $payment_reference === '') {
		wp_safe_redirect(home_url('/property-payment/?property_id=' . $property_id . '&payment_notice=incomplete'));
		exit;
	}

	update_post_meta($property_id, 'submission_payment_method', $payment_method);
	update_post_meta($property_id, 'submission_payment_reference', $payment_reference);
	update_post_meta($property_id, 'submission_payment_note', $payment_note);
	update_post_meta($property_id, 'tmg_payment_status', 'payment_submitted');
	update_post_meta($property_id, 'tmg_paid_at', current_time('mysql'));

	wp_update_post(
		array(
			'ID'          => $property_id,
			'post_status' => 'pending',
		)
	);

	wp_safe_redirect(home_url('/property-payment/?property_id=' . $property_id . '&payment_notice=paid'));
	exit;
}
add_action('init', __NAMESPACE__ . '\\handle_property_payment_submission');

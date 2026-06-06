<?php
/**
 * Frontend property submission.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function is_add_property_request(): bool {
	$request_uri  = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path    = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

	if ($home_path !== '' && str_starts_with($request_path, $home_path . '/')) {
		$request_path = substr($request_path, strlen($home_path) + 1);
	}

	return $request_path === 'add-property';
}

function get_payment_method_choices(): array {
	$settings = get_payment_settings();
	$choices  = array();

	foreach ($settings['methods'] as $key => $method) {
		$choices[$key] = $method['label'];
	}

	if (empty($choices)) {
		$choices = array(
			'instapay'      => __('InstaPay', 'tmg-rentals'),
			'vodafone_cash' => __('Vodafone Cash', 'tmg-rentals'),
			'fawaterk'      => __('Visa / فواتيرك', 'tmg-rentals'),
		);
	}

	return $choices;
}

function get_taxonomy_term_choices(string $taxonomy): array {
	$choices = array();
	$terms   = get_terms(
		array(
			'taxonomy'   => $taxonomy,
			'hide_empty' => false,
		)
	);

	if (is_wp_error($terms) || empty($terms)) {
		return $choices;
	}

	foreach ($terms as $term) {
		$choices[(string) $term->term_id] = $term->name;
	}

	return $choices;
}

function load_submission_project_field(array $field): array {
	$field['choices'] = get_taxonomy_term_choices('tmg_projects');

	return $field;
}
add_filter('acf/load_field/key=field_submission_project', __NAMESPACE__ . '\\load_submission_project_field');

function load_submission_rental_type_field(array $field): array {
	$field['choices'] = get_taxonomy_term_choices('rental_types');

	return $field;
}
add_filter('acf/load_field/key=field_submission_rental_type', __NAMESPACE__ . '\\load_submission_rental_type_field');

function register_add_property_route(): void {
	add_rewrite_rule('^add-property/?$', 'index.php?tmg_add_property=1', 'top');
}
add_action('init', __NAMESPACE__ . '\\register_add_property_route');

function register_add_property_query_var(array $vars): array {
	$vars[] = 'tmg_add_property';

	return $vars;
}
add_filter('query_vars', __NAMESPACE__ . '\\register_add_property_query_var');

function maybe_use_add_property_template(string $template): string {
	$is_route = (bool) get_query_var('tmg_add_property') || is_page('add-property') || is_add_property_request();

	if (! $is_route) {
		return $template;
	}

	$custom_template = TMG_RENTALS_PATH . '/template-add-property.php';

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
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_add_property_template', 99);

function normalize_add_property_query(): void {
	if (! ((bool) get_query_var('tmg_add_property') || is_add_property_request())) {
		return;
	}

	global $wp_query;

	if ($wp_query instanceof \WP_Query) {
		$wp_query->is_404      = false;
		$wp_query->is_page     = true;
		$wp_query->is_singular = true;
		$wp_query->is_home     = false;
		$wp_query->is_archive  = false;
	}
}
add_action('template_redirect', __NAMESPACE__ . '\\normalize_add_property_query', 1);

function maybe_fix_add_property_document_title(array $parts): array {
	if ((bool) get_query_var('tmg_add_property') || is_add_property_request()) {
		$parts['title'] = __('أضف عقارك', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_add_property_document_title');

function maybe_fix_add_property_title_text(string $title): string {
	if ((bool) get_query_var('tmg_add_property') || is_add_property_request()) {
		return __('أضف عقارك - TMG Rentals', 'tmg-rentals');
	}

	return $title;
}
add_filter('pre_get_document_title', __NAMESPACE__ . '\\maybe_fix_add_property_title_text', 99);

function maybe_fix_add_property_body_classes(array $classes): array {
	if ((bool) get_query_var('tmg_add_property') || is_add_property_request()) {
		$classes[] = 'page-template-add-property';
	}

	return $classes;
}
add_filter('body_class', __NAMESPACE__ . '\\maybe_fix_add_property_body_classes');

function handle_property_submission($post_id) {
	if ($post_id !== 'new_property_submission') {
		return $post_id;
	}

	$user_id = get_current_user_id();

	if (! $user_id) {
		wp_die(esc_html__('يجب تسجيل الدخول قبل إرسال العقار.', 'tmg-rentals'));
	}

	$permission = can_user_publish_property($user_id);

	if (! $permission['allowed']) {
		wp_die(esc_html($permission['message']));
	}

	$new_post = array(
		'post_type'    => 'properties',
		'post_status'  => 'pending',
		'post_title'   => sanitize_text_field(wp_unslash($_POST['acf']['field_submission_title'] ?? __('عقار جديد', 'tmg-rentals'))),
		'post_content' => wp_kses_post(wp_unslash($_POST['acf']['field_submission_description'] ?? '')),
		'post_author'  => $user_id,
	);

	$post_id = wp_insert_post($new_post);

	if (! empty($_POST['acf']['field_submission_project'])) {
		wp_set_object_terms($post_id, array((int) $_POST['acf']['field_submission_project']), 'tmg_projects', false);
	}

	if (! empty($_POST['acf']['field_submission_rental_type'])) {
		wp_set_object_terms($post_id, array((int) $_POST['acf']['field_submission_rental_type']), 'rental_types', false);
	}

	return $post_id;
}
add_filter('acf/pre_save_post', __NAMESPACE__ . '\\handle_property_submission');

function register_submission_form_fields(): void {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_tmg_frontend_submission',
			'title' => __('نموذج إضافة عقار', 'tmg-rentals'),
			'fields' => array(
				array(
					'key'      => 'field_submission_title',
					'label'    => __('عنوان الإعلان', 'tmg-rentals'),
					'name'     => 'submission_title',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_description',
					'label'    => __('وصف العقار', 'tmg-rentals'),
					'name'     => 'submission_description',
					'type'     => 'textarea',
					'rows'     => 6,
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_project',
					'label'    => __('المشروع', 'tmg-rentals'),
					'name'     => 'submission_project',
					'type'     => 'select',
					'choices'  => array(),
					'ui'       => 1,
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_rental_type',
					'label'    => __('نوع الإيجار', 'tmg-rentals'),
					'name'     => 'submission_rental_type',
					'type'     => 'select',
					'choices'  => array(),
					'ui'       => 1,
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_payment_method',
					'label'    => __('طريقة الدفع', 'tmg-rentals'),
					'name'     => 'submission_payment_method',
					'type'     => 'select',
					'choices'  => get_payment_method_choices(),
					'ui'       => 1,
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_payment_reference',
					'label'    => __('رقم العملية / مرجع الدفع', 'tmg-rentals'),
					'name'     => 'submission_payment_reference',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_submission_payment_note',
					'label' => __('ملاحظات الدفع', 'tmg-rentals'),
					'name'  => 'submission_payment_note',
					'type'  => 'textarea',
					'rows'  => 3,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'page_template',
						'operator' => '==',
						'value'    => 'template-add-property.php',
					),
				),
			),
			'active' => true,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_submission_form_fields');

function maybe_hide_payment_fields_for_agents($field) {
	if (! is_user_logged_in()) {
		return $field;
	}

	if (get_user_account_type(get_current_user_id()) !== 'agent') {
		return $field;
	}

	return false;
}
add_filter('acf/prepare_field/key=field_submission_payment_method', __NAMESPACE__ . '\\maybe_hide_payment_fields_for_agents');
add_filter('acf/prepare_field/key=field_submission_payment_reference', __NAMESPACE__ . '\\maybe_hide_payment_fields_for_agents');
add_filter('acf/prepare_field/key=field_submission_payment_note', __NAMESPACE__ . '\\maybe_hide_payment_fields_for_agents');

function validate_submission_payment_fields($valid, $value, $field, $input) {
	if ($valid !== true) {
		return $valid;
	}

	if (! is_user_logged_in()) {
		return $valid;
	}

	if (get_user_account_type(get_current_user_id()) === 'agent') {
		return true;
	}

	if (in_array($field['key'] ?? '', array('field_submission_payment_method', 'field_submission_payment_reference'), true) && trim((string) $value) === '') {
		return __('يرجى استكمال بيانات الدفع قبل إرسال الإعلان.', 'tmg-rentals');
	}

	return $valid;
}
add_filter('acf/validate_value/key=field_submission_payment_method', __NAMESPACE__ . '\\validate_submission_payment_fields', 10, 4);
add_filter('acf/validate_value/key=field_submission_payment_reference', __NAMESPACE__ . '\\validate_submission_payment_fields', 10, 4);

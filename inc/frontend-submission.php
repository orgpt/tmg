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
	$request_uri = sanitize_text_field(wp_unslash($_SERVER['REQUEST_URI'] ?? ''));
	$request_path = trim((string) wp_parse_url($request_uri, PHP_URL_PATH), '/');
	$home_path = trim((string) wp_parse_url(home_url('/'), PHP_URL_PATH), '/');

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

	if ($is_route) {
		$custom_template = TMG_RENTALS_PATH . '/template-add-property.php';

		if (file_exists($custom_template)) {
			status_header(200);
			global $wp_query;
			if ($wp_query instanceof \WP_Query) {
				$wp_query->is_404 = false;
			}

			return $custom_template;
		}
	}

	return $template;
}
add_filter('template_include', __NAMESPACE__ . '\\maybe_use_add_property_template', 99);

function maybe_fix_add_property_document_title(array $parts): array {
	if ((bool) get_query_var('tmg_add_property') || is_add_property_request()) {
		$parts['title'] = __('أضف عقارك', 'tmg-rentals');
	}

	return $parts;
}
add_filter('document_title_parts', __NAMESPACE__ . '\\maybe_fix_add_property_document_title');

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

	$new_post = array(
		'post_type'    => 'properties',
		'post_status'  => 'pending',
		'post_title'   => sanitize_text_field(wp_unslash($_POST['acf']['field_submission_title'] ?? __('عقار جديد', 'tmg-rentals'))),
		'post_content' => wp_kses_post(wp_unslash($_POST['acf']['field_submission_description'] ?? '')),
		'post_author'  => get_current_user_id() ?: 0,
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

	$project_choices = array();
	foreach (get_terms(array('taxonomy' => 'tmg_projects', 'hide_empty' => false)) as $term) {
		$project_choices[$term->term_id] = $term->name;
	}

	$rental_choices = array();
	foreach (get_terms(array('taxonomy' => 'rental_types', 'hide_empty' => false)) as $term) {
		$rental_choices[$term->term_id] = $term->name;
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
					'choices'  => $project_choices,
					'ui'       => 1,
					'required' => 1,
				),
				array(
					'key'      => 'field_submission_rental_type',
					'label'    => __('نوع الإيجار', 'tmg-rentals'),
					'name'     => 'submission_rental_type',
					'type'     => 'select',
					'choices'  => $rental_choices,
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

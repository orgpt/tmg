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

function handle_property_submission($post_id) {
	if ($post_id !== 'new_property_submission') {
		return $post_id;
	}

	$new_post = array(
		'post_type'   => 'properties',
		'post_status' => 'pending',
		'post_title'  => sanitize_text_field(wp_unslash($_POST['acf']['field_submission_title'] ?? __('عقار جديد', 'tmg-rentals'))),
		'post_content'=> wp_kses_post(wp_unslash($_POST['acf']['field_submission_description'] ?? '')),
		'post_author' => get_current_user_id() ?: 0,
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
			'key' => 'group_tmg_frontend_submission',
			'title' => __('نموذج إضافة عقار', 'tmg-rentals'),
			'fields' => array(
				array(
					'key' => 'field_submission_title',
					'label' => __('عنوان الإعلان', 'tmg-rentals'),
					'name' => 'submission_title',
					'type' => 'text',
					'required' => 1,
				),
				array(
					'key' => 'field_submission_description',
					'label' => __('وصف العقار', 'tmg-rentals'),
					'name' => 'submission_description',
					'type' => 'textarea',
					'rows' => 6,
					'required' => 1,
				),
				array(
					'key' => 'field_submission_project',
					'label' => __('المشروع', 'tmg-rentals'),
					'name' => 'submission_project',
					'type' => 'select',
					'choices' => $project_choices,
					'ui' => 1,
					'required' => 1,
				),
				array(
					'key' => 'field_submission_rental_type',
					'label' => __('نوع الإيجار', 'tmg-rentals'),
					'name' => 'submission_rental_type',
					'type' => 'select',
					'choices' => $rental_choices,
					'ui' => 1,
					'required' => 1,
				),
			),
			'location' => array(
				array(
					array(
						'param' => 'page_template',
						'operator' => '==',
						'value' => 'template-add-property.php',
					),
				),
			),
			'active' => true,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_submission_form_fields');

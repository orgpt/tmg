<?php
/**
 * ACF field registration.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_property_fields(): void {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_tmg_property_details',
			'title' => __('بيانات العقار', 'tmg-rentals'),
			'fields' => array(
				array(
					'key'      => 'field_property_price',
					'label'    => __('سعر الإيجار', 'tmg-rentals'),
					'name'     => 'property_price',
					'type'     => 'number',
					'min'      => 0,
					'step'     => 100,
					'required' => 1,
					'append'   => __('جنيه / شهر', 'tmg-rentals'),
				),
				array(
					'key'   => 'field_property_bedrooms',
					'label' => __('عدد الغرف', 'tmg-rentals'),
					'name'  => 'property_bedrooms',
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
				),
				array(
					'key'   => 'field_property_bathrooms',
					'label' => __('عدد الحمامات', 'tmg-rentals'),
					'name'  => 'property_bathrooms',
					'type'  => 'number',
					'min'   => 0,
					'step'  => 1,
				),
				array(
					'key'    => 'field_property_area',
					'label'  => __('المساحة', 'tmg-rentals'),
					'name'   => 'property_area',
					'type'   => 'number',
					'min'    => 0,
					'append' => __('م²', 'tmg-rentals'),
				),
				array(
					'key'   => 'field_property_floor',
					'label' => __('الدور', 'tmg-rentals'),
					'name'  => 'property_floor',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_project_group',
					'label' => __('المجموعة / Bhabits', 'tmg-rentals'),
					'name'  => 'project_group',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_building_number',
					'label' => __('رقم العمارة', 'tmg-rentals'),
					'name'  => 'building_number',
					'type'  => 'text',
				),
				array(
					'key'   => 'field_property_model',
					'label' => __('النموذج', 'tmg-rentals'),
					'name'  => 'property_model',
					'type'  => 'text',
				),
				array(
					'key'      => 'field_owner_name',
					'label'    => __('اسم المالك', 'tmg-rentals'),
					'name'     => 'owner_name',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'      => 'field_whatsapp_number',
					'label'    => __('رقم واتساب', 'tmg-rentals'),
					'name'     => 'whatsapp_number',
					'type'     => 'text',
					'required' => 1,
				),
				array(
					'key'   => 'field_phone_number',
					'label' => __('رقم الهاتف', 'tmg-rentals'),
					'name'  => 'phone_number',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_property_gallery',
					'label'         => __('معرض الصور', 'tmg-rentals'),
					'name'          => 'property_gallery',
					'type'          => 'gallery',
					'preview_size'  => 'medium',
					'insert'        => 'append',
					'library'       => 'all',
					'min'           => 1,
					'return_format' => 'array',
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'post_type',
						'operator' => '==',
						'value'    => 'properties',
					),
				),
			),
			'position'     => 'normal',
			'style'        => 'seamless',
			'active'       => true,
			'show_in_rest' => 1,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_property_fields');

function register_theme_options_page(): void {
	if (! function_exists('acf_add_options_page')) {
		return;
	}

	acf_add_options_page(
		array(
			'page_title' => __('إعدادات TMG Rentals', 'tmg-rentals'),
			'menu_title' => __('إعدادات TMG Rentals', 'tmg-rentals'),
			'menu_slug'  => 'tmg-rentals-settings',
			'capability' => 'manage_options',
			'redirect'   => false,
			'position'   => 61,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_theme_options_page');

function register_payment_settings_fields(): void {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_tmg_payment_settings',
			'title' => __('رسوم الإعلانات ووسائل الدفع', 'tmg-rentals'),
			'fields' => array(
				array(
					'key'    => 'field_listing_submission_fee',
					'label'  => __('رسوم إضافة العقار', 'tmg-rentals'),
					'name'   => 'listing_submission_fee',
					'type'   => 'number',
					'min'    => 0,
					'step'   => 1,
					'append' => __('ج.م', 'tmg-rentals'),
				),
				array(
					'key'   => 'field_listing_fee_note',
					'label' => __('ملاحظة الرسوم', 'tmg-rentals'),
					'name'  => 'listing_fee_note',
					'type'  => 'textarea',
					'rows'  => 3,
				),
				array(
					'key'           => 'field_enable_instapay',
					'label'         => __('تفعيل InstaPay', 'tmg-rentals'),
					'name'          => 'enable_instapay',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'   => 'field_instapay_account',
					'label' => __('معرف InstaPay', 'tmg-rentals'),
					'name'  => 'instapay_account',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_enable_vodafone_cash',
					'label'         => __('تفعيل Vodafone Cash', 'tmg-rentals'),
					'name'          => 'enable_vodafone_cash',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'   => 'field_vodafone_cash_number',
					'label' => __('رقم Vodafone Cash', 'tmg-rentals'),
					'name'  => 'vodafone_cash_number',
					'type'  => 'text',
				),
				array(
					'key'           => 'field_enable_fawaterk',
					'label'         => __('تفعيل فواتيرك', 'tmg-rentals'),
					'name'          => 'enable_fawaterk',
					'type'          => 'true_false',
					'ui'            => 1,
					'default_value' => 1,
				),
				array(
					'key'   => 'field_fawaterk_payment_url',
					'label' => __('رابط الدفع عبر فواتيرك', 'tmg-rentals'),
					'name'  => 'fawaterk_payment_url',
					'type'  => 'url',
				),
				array(
					'key'   => 'field_fawaterk_note',
					'label' => __('ملاحظة فواتيرك', 'tmg-rentals'),
					'name'  => 'fawaterk_note',
					'type'  => 'textarea',
					'rows'  => 3,
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tmg-rentals-settings',
					),
				),
			),
			'style'  => 'seamless',
			'active' => true,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_payment_settings_fields');

function register_auth_settings_fields(): void {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_tmg_auth_settings',
			'title' => __('إعدادات التسجيل والدخول', 'tmg-rentals'),
			'fields' => array(
				array(
					'key'          => 'field_google_client_id',
					'label'        => __('Google Client ID', 'tmg-rentals'),
					'name'         => 'google_client_id',
					'type'         => 'text',
					'instructions' => __('أدخل Web Client ID من Google Cloud Console لتفعيل تسجيل الدخول عبر Google.', 'tmg-rentals'),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tmg-rentals-settings',
					),
				),
			),
			'style'  => 'seamless',
			'active' => true,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_auth_settings_fields');

function register_agent_packages_fields(): void {
	if (! function_exists('acf_add_local_field_group')) {
		return;
	}

	acf_add_local_field_group(
		array(
			'key'   => 'group_tmg_agent_packages',
			'title' => __('باقات الوكلاء', 'tmg-rentals'),
			'fields' => array(
				array(
					'key'          => 'field_agent_packages',
					'label'        => __('الباقات', 'tmg-rentals'),
					'name'         => 'agent_packages',
					'type'         => 'repeater',
					'button_label' => __('إضافة باقة', 'tmg-rentals'),
					'layout'       => 'row',
					'sub_fields'   => array(
						array(
							'key'   => 'field_package_key',
							'label' => __('مفتاح الباقة', 'tmg-rentals'),
							'name'  => 'package_key',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_package_title',
							'label' => __('اسم الباقة', 'tmg-rentals'),
							'name'  => 'package_title',
							'type'  => 'text',
						),
						array(
							'key'    => 'field_package_price',
							'label'  => __('السعر', 'tmg-rentals'),
							'name'   => 'package_price',
							'type'   => 'number',
							'append' => __('ج.م', 'tmg-rentals'),
						),
						array(
							'key'     => 'field_package_period',
							'label'   => __('الدورية', 'tmg-rentals'),
							'name'    => 'package_period',
							'type'    => 'select',
							'choices' => array(
								'weekly'  => __('أسبوعي', 'tmg-rentals'),
								'monthly' => __('شهري', 'tmg-rentals'),
							),
						),
						array(
							'key'   => 'field_package_listing_limit',
							'label' => __('عدد العقارات المسموح بها', 'tmg-rentals'),
							'name'  => 'package_listing_limit',
							'type'  => 'number',
						),
						array(
							'key'   => 'field_package_badge',
							'label' => __('شارة الباقة', 'tmg-rentals'),
							'name'  => 'package_badge',
							'type'  => 'text',
						),
						array(
							'key'   => 'field_package_description',
							'label' => __('وصف الباقة', 'tmg-rentals'),
							'name'  => 'package_description',
							'type'  => 'textarea',
							'rows'  => 3,
						),
					),
				),
			),
			'location' => array(
				array(
					array(
						'param'    => 'options_page',
						'operator' => '==',
						'value'    => 'tmg-rentals-settings',
					),
				),
			),
			'style'  => 'seamless',
			'active' => true,
		)
	);
}
add_action('acf/init', __NAMESPACE__ . '\\register_agent_packages_fields');

<?php
/**
 * Template helpers.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function get_property_meta(int $post_id): array {
	return array(
		'price'      => (string) get_field('property_price', $post_id),
		'bedrooms'   => (string) get_field('property_bedrooms', $post_id),
		'bathrooms'  => (string) get_field('property_bathrooms', $post_id),
		'area'       => (string) get_field('property_area', $post_id),
		'floor'      => (string) get_field('property_floor', $post_id),
		'group'      => (string) get_field('project_group', $post_id),
		'building'   => (string) get_field('building_number', $post_id),
		'model'      => (string) get_field('property_model', $post_id),
		'owner_name' => (string) get_field('owner_name', $post_id),
		'whatsapp'   => (string) get_field('whatsapp_number', $post_id),
		'phone'      => (string) get_field('phone_number', $post_id),
		'gallery'    => get_field('property_gallery', $post_id),
	);
}

function get_property_availability_status(int $post_id): string {
	$status = (string) get_post_meta($post_id, 'tmg_property_status', true);

	return in_array($status, array('available', 'rented'), true) ? $status : 'available';
}

function get_property_availability_label(int $post_id): string {
	return get_property_availability_status($post_id) === 'rented'
		? __('تم التأجير', 'tmg-rentals')
		: __('متاح الآن', 'tmg-rentals');
}

function get_agent_profile_summary(int $user_id): array {
	$user = get_userdata($user_id);

	return array(
		'name'      => $user instanceof \WP_User ? $user->display_name : '',
		'email'     => $user instanceof \WP_User ? $user->user_email : '',
		'phone'     => (string) get_user_meta($user_id, 'tmg_agent_phone', true),
		'image_id'  => (int) get_user_meta($user_id, 'tmg_agent_profile_image_id', true),
		'image_url' => (string) get_user_meta($user_id, 'tmg_agent_profile_image_url', true),
	);
}

function format_price(string $price): string {
	if ($price === '') {
		return __('السعر عند الطلب', 'tmg-rentals');
	}

	return number_format_i18n((float) $price) . ' ' . __('ج.م / شهر', 'tmg-rentals');
}

function sanitize_phone(string $phone): string {
	return preg_replace('/[^\d+]/', '', $phone) ?? '';
}

function get_primary_project_label(int $post_id): string {
	$terms = get_the_terms($post_id, 'tmg_projects');

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	return $terms[0]->name;
}

function get_primary_rental_type_label(int $post_id): string {
	$terms = get_the_terms($post_id, 'rental_types');

	if (empty($terms) || is_wp_error($terms)) {
		return '';
	}

	return $terms[0]->name;
}

function get_payment_settings(): array {
	$settings = array(
		'fee'              => (float) get_field('listing_submission_fee', 'option'),
		'fee_note'         => (string) get_field('listing_fee_note', 'option'),
		'instapay_enabled' => (bool) get_field('enable_instapay', 'option'),
		'instapay_account' => (string) get_field('instapay_account', 'option'),
		'vodafone_enabled' => (bool) get_field('enable_vodafone_cash', 'option'),
		'vodafone_number'  => (string) get_field('vodafone_cash_number', 'option'),
		'fawaterk_enabled' => (bool) get_field('enable_fawaterk', 'option'),
		'fawaterk_url'     => (string) get_field('fawaterk_payment_url', 'option'),
		'fawaterk_note'    => (string) get_field('fawaterk_note', 'option'),
	);

	$methods = array();

	if ($settings['instapay_enabled'] && $settings['instapay_account'] !== '') {
		$methods['instapay'] = array(
			'label'   => __('InstaPay', 'tmg-rentals'),
			'details' => $settings['instapay_account'],
		);
	}

	if ($settings['vodafone_enabled'] && $settings['vodafone_number'] !== '') {
		$methods['vodafone_cash'] = array(
			'label'   => __('Vodafone Cash', 'tmg-rentals'),
			'details' => $settings['vodafone_number'],
		);
	}

	if ($settings['fawaterk_enabled'] && $settings['fawaterk_url'] !== '') {
		$methods['fawaterk'] = array(
			'label'   => __('Visa / بطاقة عبر فواتيرك', 'tmg-rentals'),
			'details' => $settings['fawaterk_url'],
			'note'    => $settings['fawaterk_note'],
		);
	}

	$settings['methods'] = $methods;

	return $settings;
}

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


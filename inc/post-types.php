<?php
/**
 * Post types and taxonomies.
 *
 * @package TMG_Rentals
 */

namespace TMG_Rentals;

if (! defined('ABSPATH')) {
	exit;
}

function register_properties_cpt(): void {
	$labels = array(
		'name'                  => __('العقارات', 'tmg-rentals'),
		'singular_name'         => __('عقار', 'tmg-rentals'),
		'menu_name'             => __('العقارات', 'tmg-rentals'),
		'name_admin_bar'        => __('عقار', 'tmg-rentals'),
		'add_new'               => __('إضافة جديد', 'tmg-rentals'),
		'add_new_item'          => __('إضافة عقار جديد', 'tmg-rentals'),
		'new_item'              => __('عقار جديد', 'tmg-rentals'),
		'edit_item'             => __('تعديل العقار', 'tmg-rentals'),
		'view_item'             => __('عرض العقار', 'tmg-rentals'),
		'all_items'             => __('كل العقارات', 'tmg-rentals'),
		'search_items'          => __('بحث في العقارات', 'tmg-rentals'),
		'not_found'             => __('لم يتم العثور على عقارات.', 'tmg-rentals'),
		'not_found_in_trash'    => __('لا توجد عقارات في سلة المهملات.', 'tmg-rentals'),
		'featured_image'        => __('الصورة الرئيسية', 'tmg-rentals'),
		'set_featured_image'    => __('تعيين الصورة الرئيسية', 'tmg-rentals'),
		'remove_featured_image' => __('إزالة الصورة الرئيسية', 'tmg-rentals'),
		'use_featured_image'    => __('استخدام كصورة رئيسية', 'tmg-rentals'),
	);

	register_post_type(
		'properties',
		array(
			'labels'             => $labels,
			'public'             => true,
			'show_in_rest'       => true,
			'menu_icon'          => 'dashicons-building',
			'has_archive'        => true,
			'rewrite'            => array('slug' => 'properties'),
			'supports'           => array('title', 'editor', 'thumbnail', 'excerpt', 'author'),
			'menu_position'      => 5,
			'publicly_queryable' => true,
		)
	);
}
add_action('init', __NAMESPACE__ . '\\register_properties_cpt');

function register_properties_taxonomies(): void {
	register_taxonomy(
		'tmg_projects',
		array('properties'),
		array(
			'labels'            => array(
				'name'          => __('المشاريع', 'tmg-rentals'),
				'singular_name' => __('مشروع', 'tmg-rentals'),
				'search_items'  => __('بحث في المشاريع', 'tmg-rentals'),
				'all_items'     => __('كل المشاريع', 'tmg-rentals'),
				'edit_item'     => __('تعديل المشروع', 'tmg-rentals'),
				'update_item'   => __('تحديث المشروع', 'tmg-rentals'),
				'add_new_item'  => __('إضافة مشروع', 'tmg-rentals'),
				'new_item_name' => __('اسم المشروع الجديد', 'tmg-rentals'),
				'menu_name'     => __('المشاريع', 'tmg-rentals'),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array('slug' => 'tmg-project'),
		)
	);

	register_taxonomy(
		'rental_types',
		array('properties'),
		array(
			'labels'            => array(
				'name'          => __('أنواع الإيجار', 'tmg-rentals'),
				'singular_name' => __('نوع إيجار', 'tmg-rentals'),
				'search_items'  => __('بحث في أنواع الإيجار', 'tmg-rentals'),
				'all_items'     => __('كل أنواع الإيجار', 'tmg-rentals'),
				'edit_item'     => __('تعديل نوع الإيجار', 'tmg-rentals'),
				'update_item'   => __('تحديث نوع الإيجار', 'tmg-rentals'),
				'add_new_item'  => __('إضافة نوع إيجار', 'tmg-rentals'),
				'new_item_name' => __('اسم نوع الإيجار', 'tmg-rentals'),
				'menu_name'     => __('أنواع الإيجار', 'tmg-rentals'),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array('slug' => 'rental-type'),
		)
	);
}
add_action('init', __NAMESPACE__ . '\\register_properties_taxonomies');

function filter_properties_archive(\WP_Query $query): void {
	if (is_admin() || ! $query->is_main_query() || ! is_post_type_archive('properties')) {
		return;
	}

	$tax_query  = array();
	$meta_query = array();

	if (! empty($_GET['project'])) {
		$tax_query[] = array(
			'taxonomy' => 'tmg_projects',
			'field'    => 'slug',
			'terms'    => sanitize_text_field(wp_unslash($_GET['project'])),
		);
	}

	if (! empty($_GET['rental_type'])) {
		$tax_query[] = array(
			'taxonomy' => 'rental_types',
			'field'    => 'slug',
			'terms'    => sanitize_text_field(wp_unslash($_GET['rental_type'])),
		);
	}

	if (! empty($_GET['price_min'])) {
		$meta_query[] = array(
			'key'     => 'property_price',
			'value'   => (int) $_GET['price_min'],
			'type'    => 'NUMERIC',
			'compare' => '>=',
		);
	}

	if (! empty($_GET['price_max'])) {
		$meta_query[] = array(
			'key'     => 'property_price',
			'value'   => (int) $_GET['price_max'],
			'type'    => 'NUMERIC',
			'compare' => '<=',
		);
	}

	if (! empty($_GET['area_min'])) {
		$meta_query[] = array(
			'key'     => 'property_area',
			'value'   => (int) $_GET['area_min'],
			'type'    => 'NUMERIC',
			'compare' => '>=',
		);
	}

	if (! empty($_GET['area_max'])) {
		$meta_query[] = array(
			'key'     => 'property_area',
			'value'   => (int) $_GET['area_max'],
			'type'    => 'NUMERIC',
			'compare' => '<=',
		);
	}

	if (! empty($_GET['bedrooms'])) {
		$meta_query[] = array(
			'key'     => 'property_bedrooms',
			'value'   => (int) $_GET['bedrooms'],
			'type'    => 'NUMERIC',
			'compare' => '=',
		);
	}

	if (! empty($_GET['bathrooms'])) {
		$meta_query[] = array(
			'key'     => 'property_bathrooms',
			'value'   => (int) $_GET['bathrooms'],
			'type'    => 'NUMERIC',
			'compare' => '=',
		);
	}

	if (! empty($_GET['group'])) {
		$meta_query[] = array(
			'key'     => 'project_group',
			'value'   => sanitize_text_field(wp_unslash($_GET['group'])),
			'compare' => 'LIKE',
		);
	}

	if (! empty($_GET['model'])) {
		$meta_query[] = array(
			'key'     => 'property_model',
			'value'   => sanitize_text_field(wp_unslash($_GET['model'])),
			'compare' => 'LIKE',
		);
	}

	if (! empty($tax_query)) {
		if (count($tax_query) > 1) {
			$tax_query['relation'] = 'AND';
		}
		$query->set('tax_query', $tax_query);
	}

	if (! empty($meta_query)) {
		if (count($meta_query) > 1) {
			$meta_query['relation'] = 'AND';
		}
		$query->set('meta_query', $meta_query);
	}

	if (! empty($_GET['q'])) {
		$query->set('s', sanitize_text_field(wp_unslash($_GET['q'])));
	}

	$query->set('posts_per_page', 12);
}
add_action('pre_get_posts', __NAMESPACE__ . '\\filter_properties_archive');

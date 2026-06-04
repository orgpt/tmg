<?php
/**
 * Template Name: Add Property
 *
 * @package TMG_Rentals
 */

if (function_exists('acf_form_head')) {
	acf_form_head();
}

get_header();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('إضافة إعلان جديد', 'tmg-rentals'); ?></span>
				<h1><?php the_title(); ?></h1>
				<p><?php esc_html_e('أرسل بيانات وحدتك من الواجهة الأمامية، وسيتم مراجعتها من الإدارة قبل النشر على المنصة.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<div class="tmg-card tmg-card--soft">
			<?php
			if (function_exists('acf_form')) {
				acf_form(
					array(
						'post_id'      => 'new_property_submission',
						'field_groups' => array('group_tmg_frontend_submission', 'group_tmg_property_details'),
						'new_post'     => array(
							'post_type'   => 'properties',
							'post_status' => 'pending',
						),
						'submit_value' => __('إرسال العقار للمراجعة', 'tmg-rentals'),
						'updated_message' => __('تم استلام الإعلان بنجاح وسيتم مراجعته قبل النشر.', 'tmg-rentals'),
						'html_submit_button' => '<button class="tmg-button tmg-button--primary" type="submit">%s</button>',
						'return' => add_query_arg('submission', 'success', get_permalink()),
						'uploader' => 'basic',
					)
				);
			} else {
				echo '<p>' . esc_html__('يرجى تفعيل ACF Pro لاستخدام نموذج إضافة العقار.', 'tmg-rentals') . '</p>';
			}
			?>
		</div>
	</div>
</main>
<?php
get_footer();

<?php
/**
 * Template Name: Add Property
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\get_payment_settings;

if (function_exists('acf_form_head')) {
	acf_form_head();
}

get_header();

$payment_settings = get_payment_settings();
$submission_fee   = $payment_settings['fee'];
$page_title       = get_the_title() ?: __('أضف عقارك', 'tmg-rentals');
$return_url       = home_url('/add-property/');
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('إضافة إعلان جديد', 'tmg-rentals'); ?></span>
				<h1><?php echo esc_html($page_title); ?></h1>
				<p><?php esc_html_e('أرسل بيانات وحدتك من الواجهة الأمامية، وسيتم مراجعتها من الإدارة قبل النشر على المنصة.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<div class="tmg-payment-panel">
			<div class="tmg-card tmg-card--soft">
				<h2><?php esc_html_e('رسوم نشر الإعلان', 'tmg-rentals'); ?></h2>
				<p class="tmg-payment-fee">
					<?php
					echo esc_html(
						$submission_fee > 0
							? number_format_i18n($submission_fee) . ' ' . __('ج.م', 'tmg-rentals')
							: __('مجاناً حالياً', 'tmg-rentals')
					);
					?>
				</p>
				<?php if (! empty($payment_settings['fee_note'])) : ?>
					<p class="tmg-entry"><?php echo esc_html($payment_settings['fee_note']); ?></p>
				<?php endif; ?>
			</div>

			<div class="tmg-card">
				<h2><?php esc_html_e('وسائل الدفع المتاحة', 'tmg-rentals'); ?></h2>
				<?php if (! empty($payment_settings['methods'])) : ?>
					<div class="tmg-payment-methods">
						<?php foreach ($payment_settings['methods'] as $key => $method) : ?>
							<div class="tmg-payment-method">
								<h3><?php echo esc_html($method['label']); ?></h3>
								<?php if ('fawaterk' === $key) : ?>
									<p><a class="tmg-button tmg-button--ghost" href="<?php echo esc_url($method['details']); ?>" target="_blank" rel="noopener"><?php esc_html_e('افتح رابط الدفع', 'tmg-rentals'); ?></a></p>
								<?php else : ?>
									<p dir="ltr"><?php echo esc_html($method['details']); ?></p>
								<?php endif; ?>
								<?php if (! empty($method['note'])) : ?>
									<p class="tmg-entry"><?php echo esc_html($method['note']); ?></p>
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<p class="tmg-entry"><?php esc_html_e('بعد الدفع، أكمل النموذج وأدخل رقم العملية أو مرجع الدفع ليتم مراجعة الإعلان.', 'tmg-rentals'); ?></p>
				<?php else : ?>
					<p><?php esc_html_e('لم يتم إعداد وسائل الدفع بعد من لوحة التحكم.', 'tmg-rentals'); ?></p>
				<?php endif; ?>
			</div>
		</div>

		<div class="tmg-card tmg-card--soft">
			<?php
			if (function_exists('acf_form')) {
				acf_form(
					array(
						'post_id'             => 'new_property_submission',
						'field_groups'        => array('group_tmg_frontend_submission', 'group_tmg_property_details'),
						'new_post'            => array(
							'post_type'   => 'properties',
							'post_status' => 'pending',
						),
						'submit_value'        => __('إرسال العقار للمراجعة', 'tmg-rentals'),
						'updated_message'     => __('تم استلام الإعلان بنجاح وسيتم مراجعته قبل النشر.', 'tmg-rentals'),
						'html_submit_button'  => '<button class="tmg-button tmg-button--primary" type="submit">%s</button>',
						'return'              => add_query_arg('submission', 'success', $return_url),
						'uploader'            => 'basic',
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

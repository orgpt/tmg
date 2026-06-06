<?php
/**
 * Property payment template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\get_payment_settings;
use function TMG_Rentals\get_property_payment_notice;
use function TMG_Rentals\get_property_payment_status;
use function TMG_Rentals\get_property_meta;

if (! is_user_logged_in()) {
	wp_safe_redirect(home_url('/auth/?mode=login'));
	exit;
}

$property_id       = (int) ($_GET['property_id'] ?? 0);
$property          = $property_id > 0 ? get_post($property_id) : null;
$payment_settings  = get_payment_settings();
$submission_fee    = $payment_settings['fee'];
$notice            = get_property_payment_notice();
$is_valid_property = $property instanceof \WP_Post && $property->post_type === 'properties' && (int) $property->post_author === get_current_user_id();
$payment_status    = $is_valid_property ? get_property_payment_status($property_id) : '';
$meta              = $is_valid_property ? get_property_meta($property_id) : array();

get_header();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('الخطوة التالية', 'tmg-rentals'); ?></span>
				<h1><?php esc_html_e('إتمام الدفع وإرسال الإعلان للمراجعة', 'tmg-rentals'); ?></h1>
				<p><?php esc_html_e('تم حفظ بيانات العقار في حسابك. أكمل الدفع الآن أو ارجع لاحقًا وسيظل الإعلان محفوظًا بانتظار الدفع.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<?php if (! empty($notice['message'])) : ?>
			<div class="tmg-auth-notice tmg-auth-notice--<?php echo esc_attr($notice['type']); ?>">
				<?php echo esc_html($notice['message']); ?>
			</div>
		<?php endif; ?>

		<?php if (! $is_valid_property) : ?>
			<div class="tmg-empty-state">
				<h2><?php esc_html_e('تعذر العثور على الإعلان', 'tmg-rentals'); ?></h2>
				<p><?php esc_html_e('اختر إعلانًا صالحًا من حسابك ثم أكمل الدفع من الرابط المخصص له.', 'tmg-rentals'); ?></p>
			</div>
		<?php else : ?>
			<div class="tmg-payment-panel">
				<div class="tmg-card tmg-card--soft">
					<h2><?php esc_html_e('بيانات الإعلان', 'tmg-rentals'); ?></h2>
					<div class="tmg-specs-grid">
						<div><span><?php esc_html_e('العنوان', 'tmg-rentals'); ?></span><strong><?php echo esc_html(get_the_title($property)); ?></strong></div>
						<div><span><?php esc_html_e('السعر', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) ($meta['price'] ?: __('غير محدد', 'tmg-rentals'))); ?></strong></div>
						<div><span><?php esc_html_e('الحالة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($payment_status === 'payment_submitted' ? __('تم إرسال الدفع', 'tmg-rentals') : __('بانتظار الدفع', 'tmg-rentals')); ?></strong></div>
						<div><span><?php esc_html_e('رسوم النشر', 'tmg-rentals'); ?></span><strong><?php echo esc_html(number_format_i18n($submission_fee) . ' ' . __('ج.م', 'tmg-rentals')); ?></strong></div>
					</div>
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
										<?php if ('instapay' === $key) : ?>
											<div class="tmg-payment-qr">
												<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/instapay.jpeg'); ?>" alt="<?php esc_attr_e('InstaPay QR', 'tmg-rentals'); ?>" loading="lazy">
											</div>
										<?php endif; ?>
									<?php endif; ?>
									<?php if (! empty($method['note'])) : ?>
										<p class="tmg-entry"><?php echo esc_html($method['note']); ?></p>
									<?php endif; ?>
								</div>
							<?php endforeach; ?>
						</div>
					<?php endif; ?>
				</div>
			</div>

			<div class="tmg-card">
				<?php if ($payment_status === 'payment_submitted') : ?>
					<h2><?php esc_html_e('تم استلام بيانات الدفع', 'tmg-rentals'); ?></h2>
					<p><?php esc_html_e('إعلانك الآن قيد المراجعة من الإدارة. لا تحتاج إلى أي خطوة إضافية حاليًا.', 'tmg-rentals'); ?></p>
					<p><a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(home_url('/add-property/')); ?>"><?php esc_html_e('العودة إلى حسابي', 'tmg-rentals'); ?></a></p>
				<?php else : ?>
					<h2><?php esc_html_e('أدخل بيانات الدفع', 'tmg-rentals'); ?></h2>
					<form class="tmg-auth-form" method="post" action="<?php echo esc_url(home_url('/property-payment/')); ?>">
						<input type="hidden" name="tmg_property_payment_action" value="submit_payment">
						<input type="hidden" name="property_id" value="<?php echo esc_attr((string) $property_id); ?>">
						<?php wp_nonce_field('tmg_property_payment', 'tmg_property_payment_nonce'); ?>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('طريقة الدفع', 'tmg-rentals'); ?></span>
							<select name="payment_method" required>
								<option value=""><?php esc_html_e('حدد الطريقة', 'tmg-rentals'); ?></option>
								<?php foreach ($payment_settings['methods'] as $key => $method) : ?>
									<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($method['label']); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('رقم العملية / مرجع الدفع', 'tmg-rentals'); ?></span>
							<input type="text" name="payment_reference" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('ملاحظات الدفع', 'tmg-rentals'); ?></span>
							<textarea name="payment_note" rows="4"></textarea>
						</label>
						<button class="tmg-button tmg-button--primary" type="submit"><?php esc_html_e('تأكيد الدفع وإرسال الإعلان', 'tmg-rentals'); ?></button>
					</form>
				<?php endif; ?>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();

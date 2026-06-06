<?php
/**
 * Template Name: Add Property
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\can_user_publish_property;
use function TMG_Rentals\get_payment_settings;
use function TMG_Rentals\get_user_account_type;
use function TMG_Rentals\get_user_subscription_usage;

if (function_exists('acf_form_head')) {
	acf_form_head();
}

get_header();

$user_id            = get_current_user_id();
$is_logged_in       = is_user_logged_in();
$account_type       = $is_logged_in ? get_user_account_type($user_id) : 'owner';
$is_agent           = $account_type === 'agent';
$publish_permission = $is_logged_in ? can_user_publish_property($user_id) : array('allowed' => false, 'message' => __('يرجى تسجيل الدخول أولاً.', 'tmg-rentals'));
$usage              = $is_logged_in ? get_user_subscription_usage($user_id) : array('used' => 0, 'limit' => 0, 'start' => '', 'end' => '');
$payment_settings   = get_payment_settings();
$submission_fee     = $payment_settings['fee'];
$page_title         = get_the_title() ?: __('أضف عقارك', 'tmg-rentals');
$return_url         = home_url('/add-property/');
$remaining_balance  = max(0, (int) $usage['limit'] - (int) $usage['used']);
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('إضافة إعلان جديد', 'tmg-rentals'); ?></span>
				<h1><?php echo esc_html($page_title); ?></h1>
				<?php if ($is_agent) : ?>
					<p><?php esc_html_e('هذه الصفحة مخصصة للوكلاء المشتركين. كل إعلان يتم إرساله هنا يستهلك عقارًا واحدًا من رصيد الباقة الحالية، ثم يذهب للمراجعة قبل النشر.', 'tmg-rentals'); ?></p>
				<?php else : ?>
					<p><?php esc_html_e('هذه الصفحة مخصصة للحسابات العادية لإضافة عقار واحد فقط برسوم النشر الحالية. إذا كنت تريد نشر عدة عقارات، اشترك في باقة وكيل.', 'tmg-rentals'); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<div class="tmg-payment-panel">
			<div class="tmg-card tmg-card--soft">
				<?php if ($is_agent) : ?>
					<h2><?php esc_html_e('الرصيد الحالي من الباقة', 'tmg-rentals'); ?></h2>
					<p class="tmg-payment-fee"><?php echo esc_html((string) $remaining_balance); ?></p>
					<p class="tmg-entry">
						<?php
						echo esc_html(
							sprintf(
								__('المستخدم حالياً: %1$d من %2$d عقار في الدورة الحالية.', 'tmg-rentals'),
								(int) $usage['used'],
								(int) $usage['limit']
							)
						);
						?>
					</p>
				<?php else : ?>
					<h2><?php esc_html_e('رسوم نشر الإعلان', 'tmg-rentals'); ?></h2>
					<p class="tmg-payment-fee">
						<?php
						echo esc_html(
							$submission_fee > 0
								? number_format_i18n($submission_fee) . ' ' . __('ج.م', 'tmg-rentals')
								: __('مجانا حالياً', 'tmg-rentals')
						);
						?>
					</p>
					<?php if (! empty($payment_settings['fee_note'])) : ?>
						<p class="tmg-entry"><?php echo esc_html($payment_settings['fee_note']); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>

			<div class="tmg-card">
				<?php if ($is_agent) : ?>
					<h2><?php esc_html_e('الإضافة من رصيد الباقة', 'tmg-rentals'); ?></h2>
					<p><?php esc_html_e('لن تظهر لك خانات الدفع هنا لأن الوكيل لا يدفع لكل إعلان على حدة. الإعلان الجديد يتم خصمه تلقائيًا من الحد المسموح به في الباقة.', 'tmg-rentals'); ?></p>
					<p class="tmg-entry"><?php esc_html_e('إذا انتهى الرصيد، فلن تتمكن من إضافة إعلان جديد حتى يتم تجديد الباقة أو ترقيتها.', 'tmg-rentals'); ?></p>
				<?php else : ?>
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
					<?php else : ?>
						<p><?php esc_html_e('لم يتم إعداد وسائل الدفع بعد من لوحة التحكم.', 'tmg-rentals'); ?></p>
					<?php endif; ?>
				<?php endif; ?>
			</div>
		</div>

		<div class="tmg-card tmg-card--soft">
			<?php
			if (! $is_logged_in) {
				echo '<p>' . esc_html__('يجب تسجيل الدخول أولاً قبل إضافة عقار.', 'tmg-rentals') . '</p>';
				echo '<p><a class="tmg-button tmg-button--primary" href="' . esc_url(home_url('/auth/?mode=login')) . '">' . esc_html__('تسجيل الدخول', 'tmg-rentals') . '</a></p>';
			} elseif (! $publish_permission['allowed']) {
				echo '<p>' . esc_html($publish_permission['message']) . '</p>';
				if ($is_agent) {
					echo '<p><a class="tmg-button tmg-button--primary" href="' . esc_url(home_url('/subscriptions/')) . '">' . esc_html__('عرض الباقات والاشتراك', 'tmg-rentals') . '</a></p>';
				} else {
					echo '<p><a class="tmg-button tmg-button--primary" href="' . esc_url(home_url('/subscriptions/')) . '">' . esc_html__('الترقية إلى باقة وكيل', 'tmg-rentals') . '</a></p>';
				}
			} elseif (function_exists('acf_form')) {
				if ($is_agent) {
					echo '<p class="tmg-entry">' . esc_html(sprintf(__('بعد الإرسال سيتم استهلاك %1$d عقار من رصيدك المتبقي (%2$d حالياً).', 'tmg-rentals'), 1, $remaining_balance)) . '</p>';
				} else {
					echo '<p class="tmg-entry">' . esc_html__('أكمل بيانات الدفع ومرجع العملية، ثم سيتم مراجعة الإعلان قبل النشر. الحساب العادي لا يمكنه إضافة أكثر من عقار واحد.', 'tmg-rentals') . '</p>';
				}

				acf_form(
					array(
						'post_id'            => 'new_property_submission',
						'field_groups'       => array('group_tmg_frontend_submission', 'group_tmg_property_details'),
						'new_post'           => array(
							'post_type'   => 'properties',
							'post_status' => 'pending',
						),
						'submit_value'       => __('إرسال العقار للمراجعة', 'tmg-rentals'),
						'updated_message'    => __('تم استلام الإعلان بنجاح وسيتم مراجعته قبل النشر.', 'tmg-rentals'),
						'html_submit_button' => '<button class="tmg-button tmg-button--primary" type="submit">%s</button>',
						'return'             => add_query_arg('submission', 'success', $return_url),
						'uploader'           => 'basic',
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

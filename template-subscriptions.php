<?php
/**
 * Agent subscriptions template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\can_user_publish_property;
use function TMG_Rentals\get_agent_packages;
use function TMG_Rentals\get_payment_method_choices;
use function TMG_Rentals\get_payment_settings;
use function TMG_Rentals\get_subscription_notice;
use function TMG_Rentals\get_user_account_type;
use function TMG_Rentals\get_user_subscription;
use function TMG_Rentals\get_user_subscription_usage;
use function TMG_Rentals\user_has_active_subscription;

get_header();

$packages         = get_agent_packages();
$payment_settings = get_payment_settings();
$notice           = get_subscription_notice();
$user_id          = get_current_user_id();
$is_logged_in     = is_user_logged_in();
$account_type     = $is_logged_in ? get_user_account_type($user_id) : 'owner';
$subscription     = $is_logged_in ? get_user_subscription($user_id) : array();
$usage            = $is_logged_in ? get_user_subscription_usage($user_id) : array('used' => 0, 'limit' => 0, 'start' => '', 'end' => '');
$permission       = $is_logged_in ? can_user_publish_property($user_id) : array('allowed' => false, 'message' => '');
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('نظام الوكلاء', 'tmg-rentals'); ?></span>
				<h1><?php esc_html_e('اشتراكات الوكلاء والباقات', 'tmg-rentals'); ?></h1>
				<p><?php esc_html_e('اختر الباقة المناسبة، ثم أرسل طلب الاشتراك وسيتم تفعيل عدد العقارات المسموح لك بنشرها أسبوعيًا أو شهريًا من خلال الإدارة.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<?php if (! empty($notice['message'])) : ?>
			<div class="tmg-auth-notice tmg-auth-notice--<?php echo esc_attr($notice['type'] ?? 'success'); ?>">
				<?php echo esc_html($notice['message']); ?>
			</div>
		<?php endif; ?>

		<?php if ($is_logged_in && $account_type === 'agent') : ?>
			<div class="tmg-card tmg-card--soft">
				<h2><?php esc_html_e('حالة اشتراكك الحالية', 'tmg-rentals'); ?></h2>
				<div class="tmg-specs-grid">
					<div><span><?php esc_html_e('الحالة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($subscription['status'] ?: __('غير مفعل', 'tmg-rentals')); ?></strong></div>
					<div><span><?php esc_html_e('الباقة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($subscription['package_title'] ?: __('لا توجد', 'tmg-rentals')); ?></strong></div>
					<div><span><?php esc_html_e('المستخدم', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) $usage['used']); ?></strong></div>
					<div><span><?php esc_html_e('المتاح', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) $usage['limit']); ?></strong></div>
				</div>
				<?php if (! $permission['allowed'] && $permission['message'] !== '') : ?>
					<p class="tmg-entry"><?php echo esc_html($permission['message']); ?></p>
				<?php endif; ?>
			</div>
		<?php endif; ?>

		<div class="tmg-packages-grid">
			<?php foreach ($packages as $package) : ?>
				<article class="tmg-package-card">
					<?php if ($package['badge'] !== '') : ?>
						<span class="tmg-badge"><?php echo esc_html($package['badge']); ?></span>
					<?php endif; ?>
					<h2><?php echo esc_html($package['title']); ?></h2>
					<p class="tmg-package-card__price"><?php echo esc_html(number_format_i18n($package['price'])); ?> <?php esc_html_e('ج.م', 'tmg-rentals'); ?></p>
					<ul class="tmg-auth-benefits">
						<li><?php echo esc_html(sprintf(__('حتى %d عقار', 'tmg-rentals'), $package['listing_limit'])); ?></li>
						<li><?php echo esc_html($package['period'] === 'weekly' ? __('تتجدد أسبوعيًا', 'tmg-rentals') : __('تتجدد شهريًا', 'tmg-rentals')); ?></li>
						<?php if ($package['description'] !== '') : ?>
							<li><?php echo esc_html($package['description']); ?></li>
						<?php endif; ?>
					</ul>
				</article>
			<?php endforeach; ?>
		</div>

		<div class="tmg-payment-panel">
			<div class="tmg-card tmg-card--soft">
				<h2><?php esc_html_e('وسائل الدفع الحالية', 'tmg-rentals'); ?></h2>
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
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div class="tmg-card">
				<h2><?php esc_html_e('طلب اشتراك وكيل', 'tmg-rentals'); ?></h2>
				<?php if (! $is_logged_in) : ?>
					<p><?php esc_html_e('يرجى تسجيل الدخول أو إنشاء حساب أولاً لإرسال طلب اشتراك.', 'tmg-rentals'); ?></p>
					<a class="tmg-button tmg-button--primary" href="<?php echo esc_url(home_url('/auth/?mode=register')); ?>"><?php esc_html_e('إنشاء حساب وكيل', 'tmg-rentals'); ?></a>
				<?php else : ?>
					<form class="tmg-auth-form" method="post" action="<?php echo esc_url(home_url('/subscriptions/')); ?>">
						<input type="hidden" name="tmg_subscription_action" value="request">
						<?php wp_nonce_field('tmg_subscription_request', 'tmg_subscription_nonce'); ?>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('اختر الباقة', 'tmg-rentals'); ?></span>
							<select name="package_key" required>
								<option value=""><?php esc_html_e('حدد الباقة', 'tmg-rentals'); ?></option>
								<?php foreach ($packages as $package) : ?>
									<option value="<?php echo esc_attr($package['key']); ?>"><?php echo esc_html($package['title']); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('طريقة الدفع', 'tmg-rentals'); ?></span>
							<select name="payment_method" required>
								<option value=""><?php esc_html_e('حدد الطريقة', 'tmg-rentals'); ?></option>
								<?php foreach (get_payment_method_choices() as $key => $label) : ?>
									<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('رقم العملية / مرجع الدفع', 'tmg-rentals'); ?></span>
							<input type="text" name="payment_reference" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('ملاحظات إضافية', 'tmg-rentals'); ?></span>
							<textarea name="payment_note" rows="3"></textarea>
						</label>
						<button class="tmg-button tmg-button--primary tmg-button--wide" type="submit"><?php esc_html_e('إرسال طلب الاشتراك', 'tmg-rentals'); ?></button>
					</form>
				<?php endif; ?>
			</div>
		</div>
	</div>
</main>
<?php
get_footer();

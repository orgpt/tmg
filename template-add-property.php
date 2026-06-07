<?php
/**
 * Template Name: Add Property
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\can_user_publish_property;
use function TMG_Rentals\get_payment_settings;
use function TMG_Rentals\get_user_account_type;
use function TMG_Rentals\get_user_pending_payment_properties;
use function TMG_Rentals\get_user_subscription_usage;

if (function_exists('acf_form_head')) {
	acf_form_head();
}

get_header();

$user_id             = get_current_user_id();
$is_logged_in        = is_user_logged_in();
$account_type        = $is_logged_in ? get_user_account_type($user_id) : 'owner';
$is_agent            = $account_type === 'agent';
$publish_permission  = $is_logged_in ? can_user_publish_property($user_id) : array('allowed' => false, 'message' => __('يرجى تسجيل الدخول أولاً.', 'tmg-rentals'));
$usage               = $is_logged_in ? get_user_subscription_usage($user_id) : array('used' => 0, 'limit' => 0, 'start' => '', 'end' => '');
$payment_settings    = get_payment_settings();
$submission_fee      = $payment_settings['fee'];
$page_title          = get_the_title() ?: __('أضف عقارك', 'tmg-rentals');
$remaining_balance   = max(0, (int) $usage['limit'] - (int) $usage['used']);
$pending_payments    = $is_logged_in && ! $is_agent ? get_user_pending_payment_properties($user_id) : array();
$return_after_submit = $is_agent
	? home_url('/agent-dashboard/?notice=submitted')
	: home_url('/property-payment/?property_id=%post_id%');
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('إضافة إعلان جديد', 'tmg-rentals'); ?></span>
				<h1><?php echo esc_html($page_title); ?></h1>
				<?php if ($is_agent) : ?>
					<p><?php esc_html_e('أدخل بيانات الإعلان أولاً، وبعد الإرسال سيتم خصم عقار واحد من رصيد الباقة وإرسال الإعلان مباشرة للمراجعة.', 'tmg-rentals'); ?></p>
				<?php else : ?>
					<p><?php esc_html_e('أدخل بيانات الإعلان أولاً. بعد الضغط على النشر سيتم حفظ العقار في حسابك كإعلان بانتظار الدفع، ثم تنتقل لصفحة الدفع لإكمال العملية.', 'tmg-rentals'); ?></p>
				<?php endif; ?>
			</div>
		</section>

		<div class="tmg-payment-panel">
			<div class="tmg-card tmg-card--soft">
				<?php if ($is_agent) : ?>
					<h2><?php esc_html_e('الرصيد الحالي من الباقة', 'tmg-rentals'); ?></h2>
					<p class="tmg-payment-fee"><?php echo esc_html((string) $remaining_balance); ?></p>
					<p class="tmg-entry"><?php echo esc_html(sprintf(__('المستخدم حالياً: %1$d من %2$d عقار في الدورة الحالية.', 'tmg-rentals'), (int) $usage['used'], (int) $usage['limit'])); ?></p>
				<?php else : ?>
					<h2><?php esc_html_e('رسوم نشر الإعلان', 'tmg-rentals'); ?></h2>
					<p class="tmg-payment-fee"><?php echo esc_html(number_format_i18n($submission_fee) . ' ' . __('ج.م', 'tmg-rentals')); ?></p>
					<p class="tmg-entry"><?php esc_html_e('لن تدخل بيانات الدفع هنا. سيتم نقلك لصفحة دفع مستقلة بعد حفظ الإعلان في حسابك.', 'tmg-rentals'); ?></p>
				<?php endif; ?>
			</div>

			<div class="tmg-card">
				<?php if ($is_agent) : ?>
					<h2><?php esc_html_e('الإضافة من رصيد الباقة', 'tmg-rentals'); ?></h2>
					<p><?php esc_html_e('الوكلاء لا يدفعون لكل إعلان على حدة. كل إعلان جديد يستهلك وحدة واحدة من الحد المتاح داخل الباقة الحالية.', 'tmg-rentals'); ?></p>
				<?php else : ?>
					<h2><?php esc_html_e('خطوتان بسيطتان', 'tmg-rentals'); ?></h2>
					<ul class="tmg-auth-benefits">
						<li><?php esc_html_e('1. أدخل بيانات العقار واضغط نشر.', 'tmg-rentals'); ?></li>
						<li><?php esc_html_e('2. انتقل إلى صفحة الدفع وأرسل مرجع العملية ليبدأ فريق الإدارة المراجعة.', 'tmg-rentals'); ?></li>
					</ul>
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
				echo '<p><a class="tmg-button tmg-button--primary" href="' . esc_url(home_url('/subscriptions/')) . '">' . esc_html__($is_agent ? 'عرض الباقات والاشتراك' : 'الترقية إلى باقة وكيل', 'tmg-rentals') . '</a></p>';
			} elseif (function_exists('acf_form')) {
				echo '<p class="tmg-entry">' . esc_html__($is_agent ? 'بعد الإرسال سيتم خصم الإعلان من رصيد باقتك الحالية.' : 'بعد الحفظ سيبقى الإعلان في حسابك بانتظار الدفع حتى تكمل العملية.', 'tmg-rentals') . '</p>';

				acf_form(
					array(
						'post_id'            => 'new_property_submission',
						'field_groups'       => array('group_tmg_frontend_submission', 'group_tmg_property_details'),
						'new_post'           => array(
							'post_type'   => 'properties',
							'post_status' => $is_agent ? 'pending' : 'draft',
						),
						'submit_value'       => __('حفظ البيانات والمتابعة', 'tmg-rentals'),
						'updated_message'    => __('تم حفظ الإعلان بنجاح.', 'tmg-rentals'),
						'html_submit_button' => '<button class="tmg-button tmg-button--primary" type="submit">%s</button>',
						'return'             => $return_after_submit,
						'uploader'           => 'basic',
					)
				);
			} else {
				echo '<p>' . esc_html__('يرجى تفعيل ACF Pro لاستخدام نموذج إضافة العقار.', 'tmg-rentals') . '</p>';
			}
			?>
		</div>

		<?php if (! $is_agent && ! empty($pending_payments)) : ?>
			<section class="tmg-card">
				<div class="tmg-section-heading">
					<div>
						<span class="tmg-kicker"><?php esc_html_e('في حسابك', 'tmg-rentals'); ?></span>
						<h2><?php esc_html_e('إعلانات بانتظار الدفع', 'tmg-rentals'); ?></h2>
					</div>
				</div>
				<div class="tmg-agent-listings">
					<?php foreach ($pending_payments as $pending_post) : ?>
						<article class="tmg-agent-listing-card">
							<div class="tmg-agent-listing-card__head">
								<div>
									<h3><?php echo esc_html(get_the_title($pending_post)); ?></h3>
									<p class="tmg-entry"><?php echo esc_html(get_the_date('', $pending_post)); ?></p>
								</div>
								<span class="tmg-badge tmg-badge--soft"><?php esc_html_e('بانتظار الدفع', 'tmg-rentals'); ?></span>
							</div>
							<div class="tmg-agent-listing-card__actions">
								<a class="tmg-button tmg-button--primary" href="<?php echo esc_url(home_url('/property-payment/?property_id=' . $pending_post->ID)); ?>"><?php esc_html_e('دفع الآن', 'tmg-rentals'); ?></a>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			</section>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();

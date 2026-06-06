<?php
/**
 * Agent dashboard template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\format_price;
use function TMG_Rentals\get_agent_dashboard_listings;
use function TMG_Rentals\get_agent_dashboard_notice;
use function TMG_Rentals\get_agent_dashboard_reports;
use function TMG_Rentals\get_agent_dashboard_stats;
use function TMG_Rentals\get_agent_profile_summary;
use function TMG_Rentals\get_primary_project_label;
use function TMG_Rentals\get_primary_rental_type_label;
use function TMG_Rentals\get_property_availability_label;
use function TMG_Rentals\get_property_availability_status;
use function TMG_Rentals\get_property_meta;
use function TMG_Rentals\get_user_account_type;

if (! is_user_logged_in()) {
	wp_safe_redirect(home_url('/auth/?mode=login'));
	exit;
}

$user_id       = get_current_user_id();
$account_type  = get_user_account_type($user_id);

if ($account_type !== 'agent') {
	wp_safe_redirect(home_url('/subscriptions/'));
	exit;
}

$profile = get_agent_profile_summary($user_id);
$stats   = get_agent_dashboard_stats($user_id);
$reports = get_agent_dashboard_reports($user_id);
$posts   = get_agent_dashboard_listings($user_id);
$notice  = get_agent_dashboard_notice();

get_header();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container tmg-form-page">
		<section class="tmg-hero tmg-hero--compact">
			<div class="tmg-agent-dashboard__hero">
				<div class="tmg-agent-dashboard__identity">
					<?php if ($profile['image_id']) : ?>
						<div class="tmg-agent-dashboard__avatar">
							<?php echo wp_get_attachment_image($profile['image_id'], 'thumbnail'); ?>
						</div>
					<?php endif; ?>
					<div>
						<span class="tmg-kicker"><?php esc_html_e('لوحة تحكم الوكيل', 'tmg-rentals'); ?></span>
						<h1><?php echo esc_html($profile['name'] ?: __('الوكيل', 'tmg-rentals')); ?></h1>
						<p><?php echo esc_html($profile['email']); ?><?php echo $profile['phone'] ? ' - ' . esc_html($profile['phone']) : ''; ?></p>
					</div>
				</div>
				<div class="tmg-agent-dashboard__actions">
					<a class="tmg-button tmg-button--primary" href="<?php echo esc_url(home_url('/add-property/')); ?>"><?php esc_html_e('إضافة عقار من رصيد الباقة', 'tmg-rentals'); ?></a>
					<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(home_url('/subscriptions/')); ?>"><?php esc_html_e('إدارة الاشتراك', 'tmg-rentals'); ?></a>
				</div>
			</div>
		</section>

		<?php if (! empty($notice['message'])) : ?>
			<div class="tmg-auth-notice tmg-auth-notice--<?php echo esc_attr($notice['type']); ?>">
				<?php echo esc_html($notice['message']); ?>
			</div>
		<?php endif; ?>

		<section class="tmg-agent-dashboard__stats">
			<div class="tmg-home-stats">
				<div><strong><?php echo esc_html((string) $stats['total']); ?></strong><span><?php esc_html_e('إجمالي الإعلانات', 'tmg-rentals'); ?></span></div>
				<div><strong><?php echo esc_html((string) $stats['published']); ?></strong><span><?php esc_html_e('إعلانات منشورة', 'tmg-rentals'); ?></span></div>
				<div><strong><?php echo esc_html((string) $stats['pending']); ?></strong><span><?php esc_html_e('قيد المراجعة', 'tmg-rentals'); ?></span></div>
				<div><strong><?php echo esc_html((string) $stats['rented']); ?></strong><span><?php esc_html_e('تم تأجيرها', 'tmg-rentals'); ?></span></div>
			</div>
		</section>

		<section class="tmg-grid tmg-agent-dashboard__overview">
			<div class="tmg-card tmg-card--soft">
				<h2><?php esc_html_e('الاشتراك الحالي', 'tmg-rentals'); ?></h2>
				<div class="tmg-specs-grid">
					<div><span><?php esc_html_e('الحالة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($stats['subscription_status']); ?></strong></div>
					<div><span><?php esc_html_e('الباقة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($stats['package_title']); ?></strong></div>
					<div><span><?php esc_html_e('المستخدم', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) $stats['usage']['used']); ?></strong></div>
					<div><span><?php esc_html_e('المتاح', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) $stats['usage']['limit']); ?></strong></div>
					<div><span><?php esc_html_e('المتبقي', 'tmg-rentals'); ?></span><strong><?php echo esc_html((string) max(0, (int) $stats['usage']['limit'] - (int) $stats['usage']['used'])); ?></strong></div>
				</div>
			</div>
			<div class="tmg-card">
				<h2><?php esc_html_e('تقارير سريعة', 'tmg-rentals'); ?></h2>
				<div class="tmg-agent-dashboard__reports">
					<div>
						<h3><?php esc_html_e('حسب المشروع', 'tmg-rentals'); ?></h3>
						<ul class="tmg-auth-benefits">
							<?php if (! empty($reports['projects'])) : ?>
								<?php foreach (array_slice($reports['projects'], 0, 4, true) as $label => $count) : ?>
									<li><?php echo esc_html($label . ' - ' . $count); ?></li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><?php esc_html_e('لا توجد بيانات بعد.', 'tmg-rentals'); ?></li>
							<?php endif; ?>
						</ul>
					</div>
					<div>
						<h3><?php esc_html_e('حسب نوع الإيجار', 'tmg-rentals'); ?></h3>
						<ul class="tmg-auth-benefits">
							<?php if (! empty($reports['rental_types'])) : ?>
								<?php foreach (array_slice($reports['rental_types'], 0, 4, true) as $label => $count) : ?>
									<li><?php echo esc_html($label . ' - ' . $count); ?></li>
								<?php endforeach; ?>
							<?php else : ?>
								<li><?php esc_html_e('لا توجد بيانات بعد.', 'tmg-rentals'); ?></li>
							<?php endif; ?>
						</ul>
					</div>
				</div>
			</div>
			<div class="tmg-card">
				<h2><?php esc_html_e('معلومات الحساب', 'tmg-rentals'); ?></h2>
				<ul class="tmg-auth-benefits">
					<li><?php echo esc_html($profile['name'] ?: __('بدون اسم', 'tmg-rentals')); ?></li>
					<li><?php echo esc_html($profile['email'] ?: __('بدون بريد', 'tmg-rentals')); ?></li>
					<li><?php echo esc_html($profile['phone'] ?: __('بدون رقم هاتف', 'tmg-rentals')); ?></li>
				</ul>
			</div>
		</section>

		<section class="tmg-card">
			<div class="tmg-section-heading">
				<div>
					<span class="tmg-kicker"><?php esc_html_e('إدارة الإعلانات', 'tmg-rentals'); ?></span>
					<h2><?php esc_html_e('عقاراتك الحالية', 'tmg-rentals'); ?></h2>
				</div>
			</div>

			<?php if (! empty($posts)) : ?>
				<div class="tmg-agent-listings">
					<?php foreach ($posts as $post) : ?>
						<?php
						$meta     = get_property_meta($post->ID);
						$status   = get_property_availability_status($post->ID);
						$project  = get_primary_project_label($post->ID);
						$rental   = get_primary_rental_type_label($post->ID);
						?>
						<article class="tmg-agent-listing-card">
							<div class="tmg-agent-listing-card__head">
								<div>
									<h3><a href="<?php echo esc_url(get_permalink($post)); ?>"><?php echo esc_html(get_the_title($post)); ?></a></h3>
									<p class="tmg-entry"><?php echo esc_html($project . ($rental ? ' - ' . $rental : '')); ?></p>
								</div>
								<div class="tmg-property-card__badges">
									<span class="tmg-badge<?php echo $status === 'rented' ? ' tmg-badge--soft' : ''; ?>"><?php echo esc_html(get_property_availability_label($post->ID)); ?></span>
									<span class="tmg-badge"><?php echo esc_html(get_post_status_object($post->post_status)->label ?? $post->post_status); ?></span>
								</div>
							</div>
							<ul class="tmg-feature-list">
								<li><?php echo esc_html(format_price((string) $meta['price'])); ?></li>
								<li><?php echo esc_html(($meta['area'] ?: '0') . ' ' . __('م²', 'tmg-rentals')); ?></li>
								<li><?php echo esc_html(($meta['bedrooms'] ?: '0') . ' ' . __('غرف', 'tmg-rentals')); ?></li>
							</ul>
							<div class="tmg-agent-listing-card__actions">
								<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(get_edit_post_link($post->ID)); ?>"><?php esc_html_e('تعديل من الإدارة', 'tmg-rentals'); ?></a>
								<form method="post" action="<?php echo esc_url(home_url('/agent-dashboard/')); ?>">
									<?php wp_nonce_field('tmg_dashboard_action', 'tmg_dashboard_nonce'); ?>
									<input type="hidden" name="tmg_dashboard_action" value="toggle_property_status">
									<input type="hidden" name="property_id" value="<?php echo esc_attr((string) $post->ID); ?>">
									<button class="tmg-button tmg-button--primary" type="submit">
										<?php echo esc_html($status === 'rented' ? __('إعادة العقار للبحث', 'tmg-rentals') : __('تعليم بأنه تم التأجير', 'tmg-rentals')); ?>
									</button>
								</form>
							</div>
						</article>
					<?php endforeach; ?>
				</div>
			<?php else : ?>
				<div class="tmg-empty-state">
					<h2><?php esc_html_e('لا توجد عقارات مضافة بعد', 'tmg-rentals'); ?></h2>
					<p><?php esc_html_e('ابدأ بإضافة أول عقار لك، ثم استخدم هذه اللوحة لمتابعة الأداء والحالة.', 'tmg-rentals'); ?></p>
				</div>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();

<?php
/**
 * Authentication template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\get_account_type_choices;
use function TMG_Rentals\get_auth_mode;
use function TMG_Rentals\get_auth_notice;
use function TMG_Rentals\get_google_auth_settings;

get_header();

$mode            = sanitize_text_field(wp_unslash($_GET['mode'] ?? get_auth_mode()));
$mode            = $mode === 'register' ? 'register' : 'login';
$notice          = get_auth_notice();
$google_settings = get_google_auth_settings();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container">
		<section class="tmg-auth-shell">
			<div class="tmg-auth-shell__visual">
				<span class="tmg-kicker"><?php esc_html_e('TMG Rentals Access', 'tmg-rentals'); ?></span>
				<h1><?php esc_html_e('ابدأ رحلتك داخل منصة إيجارات TMG', 'tmg-rentals'); ?></h1>
				<p><?php esc_html_e('أنشئ حسابًا كمالك أو وكيل، ثم ابدأ في إدارة إعلاناتك واشتراكاتك من واجهة حديثة وسريعة.', 'tmg-rentals'); ?></p>
				<ul class="tmg-auth-benefits">
					<li><?php esc_html_e('إضافة العقارات ومتابعة الطلبات', 'tmg-rentals'); ?></li>
					<li><?php esc_html_e('باقات مخصصة للوكلاء بعدد عقارات محدد', 'tmg-rentals'); ?></li>
					<li><?php esc_html_e('دخول سريع وآمن عبر Google', 'tmg-rentals'); ?></li>
				</ul>
			</div>

			<div class="tmg-auth-card">
				<div class="tmg-auth-switch">
					<a class="tmg-auth-switch__item<?php echo $mode === 'login' ? ' is-active' : ''; ?>" href="<?php echo esc_url(home_url('/auth/?mode=login')); ?>"><?php esc_html_e('تسجيل الدخول', 'tmg-rentals'); ?></a>
					<a class="tmg-auth-switch__item<?php echo $mode === 'register' ? ' is-active' : ''; ?>" href="<?php echo esc_url(home_url('/auth/?mode=register')); ?>"><?php esc_html_e('إنشاء حساب', 'tmg-rentals'); ?></a>
				</div>

				<?php if (! empty($notice['message'])) : ?>
					<div class="tmg-auth-notice tmg-auth-notice--<?php echo esc_attr($notice['type'] ?? 'info'); ?>">
						<?php echo esc_html($notice['message']); ?>
					</div>
				<?php endif; ?>

				<?php if ($mode === 'login') : ?>
					<form class="tmg-auth-form" method="post" action="<?php echo esc_url(home_url('/auth/?mode=login')); ?>">
						<input type="hidden" name="tmg_auth_action" value="login">
						<?php wp_nonce_field('tmg_auth_login', 'tmg_auth_nonce'); ?>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('البريد الإلكتروني أو اسم المستخدم', 'tmg-rentals'); ?></span>
							<input type="text" name="log" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('كلمة المرور', 'tmg-rentals'); ?></span>
							<input type="password" name="pwd" required>
						</label>
						<label class="tmg-auth-remember">
							<input type="checkbox" name="rememberme" value="1">
							<span><?php esc_html_e('تذكرني', 'tmg-rentals'); ?></span>
						</label>
						<button class="tmg-button tmg-button--primary tmg-button--wide" type="submit"><?php esc_html_e('دخول', 'tmg-rentals'); ?></button>
					</form>
				<?php else : ?>
					<form class="tmg-auth-form" method="post" action="<?php echo esc_url(home_url('/auth/?mode=register')); ?>">
						<input type="hidden" name="tmg_auth_action" value="register">
						<?php wp_nonce_field('tmg_auth_register', 'tmg_auth_nonce'); ?>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('الاسم الكامل', 'tmg-rentals'); ?></span>
							<input type="text" name="display_name" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('البريد الإلكتروني', 'tmg-rentals'); ?></span>
							<input type="email" name="user_email" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('كلمة المرور', 'tmg-rentals'); ?></span>
							<input type="password" name="user_password" required>
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('نوع الحساب', 'tmg-rentals'); ?></span>
							<select name="account_type" required>
								<?php foreach (get_account_type_choices() as $key => $label) : ?>
									<option value="<?php echo esc_attr($key); ?>"><?php echo esc_html($label); ?></option>
								<?php endforeach; ?>
							</select>
						</label>
						<button class="tmg-button tmg-button--primary tmg-button--wide" type="submit"><?php esc_html_e('إنشاء الحساب', 'tmg-rentals'); ?></button>
					</form>
				<?php endif; ?>

				<?php if ($google_settings['client_id'] !== '') : ?>
					<div class="tmg-auth-divider"><span><?php esc_html_e('أو', 'tmg-rentals'); ?></span></div>
					<div class="tmg-google-auth" data-google-auth data-mode="<?php echo esc_attr($mode); ?>">
						<div class="tmg-google-auth__button" id="tmg-google-auth-button"></div>
						<p class="tmg-entry"><?php esc_html_e('يمكنك الدخول أو إنشاء حساب باستخدام Google بنفس البريد الإلكتروني.', 'tmg-rentals'); ?></p>
					</div>
				<?php endif; ?>
			</div>
		</section>
	</div>
</main>
<?php
get_footer();

<?php
/**
 * Theme header.
 *
 * @package TMG_Rentals
 */
?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo('charset'); ?>">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<?php wp_head(); ?>
</head>
<body <?php body_class('tmg-body'); ?>>
<?php wp_body_open(); ?>
<header class="tmg-site-header">
	<div class="tmg-container tmg-site-header__inner">
		<a class="tmg-brand" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('العودة إلى الرئيسية', 'tmg-rentals'); ?>">
			<img class="tmg-brand__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-tmg-rentals.svg'); ?>" alt="<?php bloginfo('name'); ?>">
		</a>
		<div class="tmg-header-actions">
			<a class="tmg-header-link" href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>">
				<span class="tmg-header-icon" aria-hidden="true">⌂</span>
				<span><?php esc_html_e('تصفح العقارات', 'tmg-rentals'); ?></span>
			</a>
			<?php if (is_user_logged_in()) : ?>
				<a class="tmg-header-link" href="<?php echo esc_url(home_url('/subscriptions/')); ?>">
					<span class="tmg-header-icon" aria-hidden="true">★</span>
					<span><?php esc_html_e('الباقات', 'tmg-rentals'); ?></span>
				</a>
				<a class="tmg-header-link" href="<?php echo esc_url(home_url('/logout/')); ?>">
					<span class="tmg-header-icon" aria-hidden="true">↩</span>
					<span><?php esc_html_e('تسجيل الخروج', 'tmg-rentals'); ?></span>
				</a>
				<a class="tmg-button tmg-button--primary tmg-button--sm" href="<?php echo esc_url(home_url('/add-property/')); ?>">
					<span class="tmg-header-icon" aria-hidden="true">＋</span>
					<span><?php esc_html_e('أضف عقارك', 'tmg-rentals'); ?></span>
				</a>
			<?php else : ?>
				<a class="tmg-header-link" href="<?php echo esc_url(home_url('/auth/?mode=login')); ?>">
					<span class="tmg-header-icon" aria-hidden="true">◉</span>
					<span><?php esc_html_e('تسجيل الدخول', 'tmg-rentals'); ?></span>
				</a>
				<a class="tmg-button tmg-button--primary tmg-button--sm" href="<?php echo esc_url(home_url('/auth/?mode=register')); ?>">
					<span class="tmg-header-icon" aria-hidden="true">◎</span>
					<span><?php esc_html_e('إنشاء حساب', 'tmg-rentals'); ?></span>
				</a>
			<?php endif; ?>
		</div>
	</div>
</header>

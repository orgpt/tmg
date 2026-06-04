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
		<a class="tmg-brand" href="<?php echo esc_url(home_url('/')); ?>">
			<span class="tmg-brand__eyebrow"><?php esc_html_e('سوق إيجارات TMG', 'tmg-rentals'); ?></span>
			<span class="tmg-brand__name"><?php bloginfo('name'); ?></span>
		</a>
		<nav class="tmg-nav" aria-label="<?php esc_attr_e('القائمة الرئيسية', 'tmg-rentals'); ?>">
			<?php
			wp_nav_menu(
				array(
					'theme_location' => 'primary',
					'container'      => false,
					'menu_class'     => 'tmg-nav__menu',
					'fallback_cb'    => 'TMG_Rentals\\fallback_menu',
				)
			);
			?>
		</nav>
	</div>
</header>


<?php
/**
 * Theme footer.
 *
 * @package TMG_Rentals
 */
?>
<footer class="tmg-site-footer">
	<div class="tmg-container">
		<div class="tmg-site-footer__card">
			<div class="tmg-site-footer__grid">
				<div class="tmg-site-footer__brand">
					<img class="tmg-site-footer__logo" src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/logo-tmg-rentals.svg'); ?>" alt="<?php bloginfo('name'); ?>">
					<p class="tmg-site-footer__text"><?php esc_html_e('منصة متخصصة في إيجارات TMG تجمع بين العرض السريع، الهوية العربية، وتجربة استخدام بسيطة على كل الأجهزة.', 'tmg-rentals'); ?></p>
				</div>

				<div class="tmg-site-footer__column">
					<h2><?php esc_html_e('روابط سريعة', 'tmg-rentals'); ?></h2>
					<ul class="tmg-site-footer__links">
						<li><a href="<?php echo esc_url(home_url('/')); ?>"><?php esc_html_e('الرئيسية', 'tmg-rentals'); ?></a></li>
						<li><a href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>"><?php esc_html_e('تصفح العقارات', 'tmg-rentals'); ?></a></li>
						<li><a href="<?php echo esc_url(home_url('/add-property/')); ?>"><?php esc_html_e('أضف عقارك', 'tmg-rentals'); ?></a></li>
						<li><a href="<?php echo esc_url(home_url('/subscriptions/')); ?>"><?php esc_html_e('باقات الوكلاء', 'tmg-rentals'); ?></a></li>
					</ul>
				</div>

				<div class="tmg-site-footer__column">
					<h2><?php esc_html_e('ابدأ الآن', 'tmg-rentals'); ?></h2>
					<p class="tmg-site-footer__text"><?php esc_html_e('اعرض وحدتك بسرعة أو ابدأ البحث داخل مدينتي والرحاب وسيليا ونور من مكان واحد.', 'tmg-rentals'); ?></p>
					<div class="tmg-site-footer__actions">
						<a class="tmg-button tmg-button--primary" href="<?php echo esc_url(home_url('/add-property/')); ?>"><?php esc_html_e('إضافة إعلان', 'tmg-rentals'); ?></a>
						<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>"><?php esc_html_e('عرض العقارات', 'tmg-rentals'); ?></a>
					</div>
				</div>
			</div>

			<div class="tmg-site-footer__bottom">
				<p>
					<?php esc_html_e('صنع بواسطة', 'tmg-rentals'); ?>
					<a href="https://realthemes.io/" target="_blank" rel="noopener">RealThemes</a>
				</p>
				<p><?php echo esc_html(date_i18n('Y')); ?> &copy; <?php bloginfo('name'); ?>. <?php esc_html_e('جميع الحقوق محفوظة', 'tmg-rentals'); ?></p>
			</div>
		</div>
	</div>
</footer>
<?php wp_footer(); ?>
</body>
</html>

<?php
/**
 * Single property template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\format_price;
use function TMG_Rentals\get_primary_project_label;
use function TMG_Rentals\get_primary_rental_type_label;
use function TMG_Rentals\get_property_availability_label;
use function TMG_Rentals\get_property_availability_status;
use function TMG_Rentals\get_property_meta;
use function TMG_Rentals\sanitize_phone;

get_header();

while (have_posts()) :
	the_post();
	$meta          = get_property_meta(get_the_ID());
	$project_label = get_primary_project_label(get_the_ID());
	$rental_label  = get_primary_rental_type_label(get_the_ID());
	$location_badge = trim($project_label . ($meta['building'] ? ' - ' . $meta['building'] : ''));
	$gallery       = is_array($meta['gallery']) ? $meta['gallery'] : array();
	$whatsapp      = sanitize_phone($meta['whatsapp']);
	$phone         = sanitize_phone($meta['phone']);
	$whatsapp_url  = $whatsapp ? 'https://wa.me/' . rawurlencode($whatsapp) : '';
	$status        = get_property_availability_status(get_the_ID());
	?>
	<main class="tmg-shell tmg-section">
		<div class="tmg-container">
			<article class="tmg-single-property">
				<section class="tmg-single-property__hero">
					<div class="tmg-gallery" data-gallery>
						<div class="tmg-gallery__stage">
							<?php if (! empty($gallery)) : ?>
								<?php foreach ($gallery as $index => $image) : ?>
									<figure class="tmg-gallery__slide<?php echo 0 === $index ? ' is-active' : ''; ?>">
										<img src="<?php echo esc_url($image['sizes']['large'] ?? $image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
									</figure>
								<?php endforeach; ?>
							<?php elseif (has_post_thumbnail()) : ?>
								<figure class="tmg-gallery__slide is-active">
									<?php the_post_thumbnail('large'); ?>
								</figure>
							<?php else : ?>
								<div class="tmg-placeholder tmg-placeholder--large"><?php esc_html_e('أضف صوراً لهذا العقار لرفع معدل التحويل.', 'tmg-rentals'); ?></div>
							<?php endif; ?>
						</div>

						<?php if (count($gallery) > 1) : ?>
							<div class="tmg-gallery__controls">
								<button type="button" class="tmg-icon-button" data-gallery-prev aria-label="<?php esc_attr_e('السابق', 'tmg-rentals'); ?>">&#10094;</button>
								<button type="button" class="tmg-icon-button" data-gallery-next aria-label="<?php esc_attr_e('التالي', 'tmg-rentals'); ?>">&#10095;</button>
							</div>
							<div class="tmg-gallery__thumbs">
								<?php foreach ($gallery as $index => $image) : ?>
									<button type="button" class="tmg-gallery__thumb<?php echo 0 === $index ? ' is-active' : ''; ?>" data-gallery-thumb="<?php echo esc_attr($index); ?>">
										<img src="<?php echo esc_url($image['sizes']['thumbnail'] ?? $image['url']); ?>" alt="<?php echo esc_attr($image['alt'] ?: get_the_title()); ?>">
									</button>
								<?php endforeach; ?>
							</div>
						<?php endif; ?>
					</div>

					<div class="tmg-single-property__summary">
						<div class="tmg-property-card__badges">
							<span class="tmg-badge<?php echo $status === 'rented' ? ' tmg-badge--soft' : ''; ?>"><?php echo esc_html(get_property_availability_label(get_the_ID())); ?></span>
							<?php if ($project_label) : ?>
								<span class="tmg-badge"><?php echo esc_html($project_label); ?></span>
							<?php endif; ?>
							<?php if ($rental_label) : ?>
								<span class="tmg-badge tmg-badge--soft"><?php echo esc_html($rental_label); ?></span>
							<?php endif; ?>
						</div>
						<h1><?php the_title(); ?></h1>
						<?php if ($location_badge) : ?>
							<p class="tmg-location-badge"><?php echo esc_html($location_badge); ?></p>
						<?php endif; ?>
						<div class="tmg-price-block"><?php echo esc_html(format_price($meta['price'])); ?></div>

						<ul class="tmg-metrics-grid">
							<li><strong><?php echo esc_html($meta['area'] ?: '0'); ?></strong><span><?php esc_html_e('م²', 'tmg-rentals'); ?></span></li>
							<li><strong><?php echo esc_html($meta['bedrooms'] ?: '0'); ?></strong><span><?php esc_html_e('غرف', 'tmg-rentals'); ?></span></li>
							<li><strong><?php echo esc_html($meta['bathrooms'] ?: '0'); ?></strong><span><?php esc_html_e('حمامات', 'tmg-rentals'); ?></span></li>
							<li><strong><?php echo esc_html($meta['floor'] ?: '—'); ?></strong><span><?php esc_html_e('الدور', 'tmg-rentals'); ?></span></li>
						</ul>

						<div class="tmg-contact-panel">
							<h2><?php esc_html_e('تواصل مباشر', 'tmg-rentals'); ?></h2>
							<p><?php echo esc_html($meta['owner_name'] ?: __('المالك', 'tmg-rentals')); ?></p>
							<div class="tmg-contact-actions">
								<?php if ($whatsapp_url) : ?>
									<a class="tmg-button tmg-button--whatsapp" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
										<span class="tmg-button__icon" aria-hidden="true">◔</span>
										<?php esc_html_e('تواصل عبر الواتساب', 'tmg-rentals'); ?>
									</a>
								<?php endif; ?>
								<?php if ($phone) : ?>
									<a class="tmg-button tmg-button--call" href="<?php echo esc_url('tel:' . $phone); ?>">
										<span class="tmg-button__icon" aria-hidden="true">☎</span>
										<?php esc_html_e('اتصل الآن', 'tmg-rentals'); ?>
									</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				</section>

				<section class="tmg-single-property__content">
					<div class="tmg-card tmg-card--soft">
						<h2><?php esc_html_e('تفاصيل الوحدة', 'tmg-rentals'); ?></h2>
						<div class="tmg-specs-grid">
							<div><span><?php esc_html_e('المجموعة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($meta['group'] ?: '—'); ?></strong></div>
							<div><span><?php esc_html_e('رقم العمارة', 'tmg-rentals'); ?></span><strong><?php echo esc_html($meta['building'] ?: '—'); ?></strong></div>
							<div><span><?php esc_html_e('النموذج', 'tmg-rentals'); ?></span><strong><?php echo esc_html($meta['model'] ?: '—'); ?></strong></div>
							<div><span><?php esc_html_e('نوع الإيجار', 'tmg-rentals'); ?></span><strong><?php echo esc_html($rental_label ?: '—'); ?></strong></div>
						</div>
					</div>

					<div class="tmg-card">
						<h2><?php esc_html_e('وصف العقار', 'tmg-rentals'); ?></h2>
						<div class="tmg-entry"><?php the_content(); ?></div>
					</div>
				</section>
			</article>
		</div>

		<div class="tmg-mobile-sticky-cta">
			<?php if ($whatsapp_url) : ?>
				<a class="tmg-button tmg-button--whatsapp" href="<?php echo esc_url($whatsapp_url); ?>" target="_blank" rel="noopener">
					<span class="tmg-button__icon" aria-hidden="true">◔</span>
					<?php esc_html_e('واتساب', 'tmg-rentals'); ?>
				</a>
			<?php endif; ?>
			<?php if ($phone) : ?>
				<a class="tmg-button tmg-button--call" href="<?php echo esc_url('tel:' . $phone); ?>">
					<span class="tmg-button__icon" aria-hidden="true">☎</span>
					<?php esc_html_e('اتصال', 'tmg-rentals'); ?>
				</a>
			<?php endif; ?>
		</div>
	</main>
<?php endwhile; ?>
<?php
get_footer();

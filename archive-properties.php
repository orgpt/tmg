<?php
/**
 * Property archive template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\format_price;
use function TMG_Rentals\get_primary_project_label;
use function TMG_Rentals\get_primary_rental_type_label;
use function TMG_Rentals\get_property_meta;

get_header();

$selected_project     = sanitize_text_field(wp_unslash($_GET['project'] ?? ''));
$selected_rental_type = sanitize_text_field(wp_unslash($_GET['rental_type'] ?? ''));
$price_min            = sanitize_text_field(wp_unslash($_GET['price_min'] ?? ''));
$price_max            = sanitize_text_field(wp_unslash($_GET['price_max'] ?? ''));
$projects             = get_terms(array('taxonomy' => 'tmg_projects', 'hide_empty' => false));
$rental_types         = get_terms(array('taxonomy' => 'rental_types', 'hide_empty' => false));
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container">
		<section class="tmg-hero tmg-hero--archive">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('TMG Rentals Marketplace', 'tmg-rentals'); ?></span>
				<h1><?php esc_html_e('اكتشف أفضل العقارات للإيجار داخل مدن TMG', 'tmg-rentals'); ?></h1>
				<p><?php esc_html_e('واجهة سريعة وحديثة للبحث بين شقق وفيلات واستوديوهات ووحدات تجارية داخل مدينتي والرحاب وسيليا ونور.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<div class="tmg-archive-layout">
			<aside class="tmg-filter-card">
				<h2><?php esc_html_e('تصفية النتائج', 'tmg-rentals'); ?></h2>
				<form method="get" class="tmg-filter-form">
					<label for="project"><?php esc_html_e('المشروع', 'tmg-rentals'); ?></label>
					<select id="project" name="project">
						<option value=""><?php esc_html_e('كل المشاريع', 'tmg-rentals'); ?></option>
						<?php foreach ($projects as $project) : ?>
							<option value="<?php echo esc_attr($project->slug); ?>" <?php selected($selected_project, $project->slug); ?>>
								<?php echo esc_html($project->name); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<label for="rental_type"><?php esc_html_e('نوع الإيجار', 'tmg-rentals'); ?></label>
					<select id="rental_type" name="rental_type">
						<option value=""><?php esc_html_e('كل الأنواع', 'tmg-rentals'); ?></option>
						<?php foreach ($rental_types as $rental_type) : ?>
							<option value="<?php echo esc_attr($rental_type->slug); ?>" <?php selected($selected_rental_type, $rental_type->slug); ?>>
								<?php echo esc_html($rental_type->name); ?>
							</option>
						<?php endforeach; ?>
					</select>

					<div class="tmg-price-grid">
						<div>
							<label for="price_min"><?php esc_html_e('أقل سعر', 'tmg-rentals'); ?></label>
							<input id="price_min" type="number" name="price_min" placeholder="<?php esc_attr_e('مثال: 15000', 'tmg-rentals'); ?>" value="<?php echo esc_attr($price_min); ?>">
						</div>
						<div>
							<label for="price_max"><?php esc_html_e('أعلى سعر', 'tmg-rentals'); ?></label>
							<input id="price_max" type="number" name="price_max" placeholder="<?php esc_attr_e('مثال: 45000', 'tmg-rentals'); ?>" value="<?php echo esc_attr($price_max); ?>">
						</div>
					</div>

					<div class="tmg-filter-actions">
						<button type="submit" class="tmg-button tmg-button--primary"><?php esc_html_e('عرض النتائج', 'tmg-rentals'); ?></button>
						<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>"><?php esc_html_e('إعادة ضبط', 'tmg-rentals'); ?></a>
					</div>
				</form>
			</aside>

			<section class="tmg-results">
				<?php if (have_posts()) : ?>
					<div class="tmg-grid">
						<?php while (have_posts()) : the_post(); ?>
							<?php $meta = get_property_meta(get_the_ID()); ?>
							<article <?php post_class('tmg-property-card'); ?>>
								<a class="tmg-property-card__media" href="<?php the_permalink(); ?>">
									<?php if (has_post_thumbnail()) : ?>
										<?php the_post_thumbnail('large'); ?>
									<?php else : ?>
										<div class="tmg-placeholder"><?php esc_html_e('لا توجد صورة', 'tmg-rentals'); ?></div>
									<?php endif; ?>
									<span class="tmg-price-pill"><?php echo esc_html(format_price($meta['price'])); ?></span>
								</a>
								<div class="tmg-property-card__body">
									<div class="tmg-property-card__badges">
										<?php if (get_primary_project_label(get_the_ID())) : ?>
											<span class="tmg-badge"><?php echo esc_html(get_primary_project_label(get_the_ID())); ?></span>
										<?php endif; ?>
										<?php if (get_primary_rental_type_label(get_the_ID())) : ?>
											<span class="tmg-badge tmg-badge--soft"><?php echo esc_html(get_primary_rental_type_label(get_the_ID())); ?></span>
										<?php endif; ?>
									</div>
									<h2 class="tmg-property-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
									<p class="tmg-property-card__excerpt"><?php echo esc_html(wp_trim_words(get_the_excerpt() ?: get_the_content(), 18)); ?></p>
									<ul class="tmg-feature-list">
										<li><?php echo esc_html($meta['area'] ?: '0'); ?> <?php esc_html_e('م²', 'tmg-rentals'); ?></li>
										<li><?php echo esc_html($meta['bedrooms'] ?: '0'); ?> <?php esc_html_e('غرف', 'tmg-rentals'); ?></li>
										<li><?php echo esc_html($meta['bathrooms'] ?: '0'); ?> <?php esc_html_e('حمامات', 'tmg-rentals'); ?></li>
									</ul>
								</div>
							</article>
						<?php endwhile; ?>
					</div>
					<div class="tmg-pagination">
						<?php the_posts_pagination(); ?>
					</div>
				<?php else : ?>
					<div class="tmg-empty-state">
						<h2><?php esc_html_e('لا توجد عقارات مطابقة حالياً', 'tmg-rentals'); ?></h2>
						<p><?php esc_html_e('جرّب تعديل الفلاتر أو إضافة إعلان جديد للوصول إلى جمهور يبحث داخل مشاريع TMG.', 'tmg-rentals'); ?></p>
					</div>
				<?php endif; ?>
			</section>
		</div>
	</div>
</main>
<?php
get_footer();


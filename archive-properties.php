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
$selected_bedrooms    = sanitize_text_field(wp_unslash($_GET['bedrooms'] ?? ''));
$selected_bathrooms   = sanitize_text_field(wp_unslash($_GET['bathrooms'] ?? ''));
$selected_group       = sanitize_text_field(wp_unslash($_GET['group'] ?? ''));
$selected_model       = sanitize_text_field(wp_unslash($_GET['model'] ?? ''));
$search_keyword       = sanitize_text_field(wp_unslash($_GET['q'] ?? ''));
$price_min            = sanitize_text_field(wp_unslash($_GET['price_min'] ?? ''));
$price_max            = sanitize_text_field(wp_unslash($_GET['price_max'] ?? ''));
$area_min             = sanitize_text_field(wp_unslash($_GET['area_min'] ?? ''));
$area_max             = sanitize_text_field(wp_unslash($_GET['area_max'] ?? ''));
$projects             = get_terms(array('taxonomy' => 'tmg_projects', 'hide_empty' => false));
$rental_types         = get_terms(array('taxonomy' => 'rental_types', 'hide_empty' => false));
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container">
		<section class="tmg-hero tmg-hero--archive">
			<div>
				<span class="tmg-kicker"><?php esc_html_e('TMG Rentals Marketplace', 'tmg-rentals'); ?></span>
				<h1><?php esc_html_e('اكتشف أفضل العقارات للإيجار داخل مدن TMG', 'tmg-rentals'); ?></h1>
				<p><?php esc_html_e('فلتر بحث سريع وحديث يساعد المستأجر على الوصول للوحدة المناسبة داخل مدينتي والرحاب وسيليا ونور بسهولة من أي جهاز.', 'tmg-rentals'); ?></p>
			</div>
		</section>

		<section class="tmg-search-panel">
			<div class="tmg-search-panel__header">
				<div>
					<p class="tmg-search-panel__eyebrow"><?php esc_html_e('ابحث عن عقارك', 'tmg-rentals'); ?></p>
					<h2><?php esc_html_e('فلتر بحث ريسبونسف ومودرن', 'tmg-rentals'); ?></h2>
				</div>
				<button class="tmg-filter-toggle" type="button" data-filter-toggle aria-expanded="false" aria-controls="tmg-advanced-filters">
					<span><?php esc_html_e('فلاتر متقدمة', 'tmg-rentals'); ?></span>
				</button>
			</div>

			<form method="get" class="tmg-search-form">
				<div class="tmg-search-grid tmg-search-grid--primary">
					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('المشروع', 'tmg-rentals'); ?></span>
						<select name="project">
							<option value=""><?php esc_html_e('اختر المشروع', 'tmg-rentals'); ?></option>
							<?php foreach ($projects as $project) : ?>
								<option value="<?php echo esc_attr($project->slug); ?>" <?php selected($selected_project, $project->slug); ?>>
									<?php echo esc_html($project->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('نوع الإيجار', 'tmg-rentals'); ?></span>
						<select name="rental_type">
							<option value=""><?php esc_html_e('كل الأنواع', 'tmg-rentals'); ?></option>
							<?php foreach ($rental_types as $rental_type) : ?>
								<option value="<?php echo esc_attr($rental_type->slug); ?>" <?php selected($selected_rental_type, $rental_type->slug); ?>>
									<?php echo esc_html($rental_type->name); ?>
								</option>
							<?php endforeach; ?>
						</select>
					</label>

					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('عدد الغرف', 'tmg-rentals'); ?></span>
						<select name="bedrooms">
							<option value=""><?php esc_html_e('أي عدد', 'tmg-rentals'); ?></option>
							<?php for ($i = 1; $i <= 6; $i++) : ?>
								<option value="<?php echo esc_attr((string) $i); ?>" <?php selected($selected_bedrooms, (string) $i); ?>>
									<?php echo esc_html((string) $i); ?>
								</option>
							<?php endfor; ?>
						</select>
					</label>

					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('كلمة مفتاحية', 'tmg-rentals'); ?></span>
						<input type="search" name="q" value="<?php echo esc_attr($search_keyword); ?>" placeholder="<?php esc_attr_e('مثال: مفروش، فيو جاردن، B11', 'tmg-rentals'); ?>">
					</label>
				</div>

				<div id="tmg-advanced-filters" class="tmg-search-grid tmg-search-grid--advanced" hidden>
					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('عدد الحمامات', 'tmg-rentals'); ?></span>
						<select name="bathrooms">
							<option value=""><?php esc_html_e('أي عدد', 'tmg-rentals'); ?></option>
							<?php for ($i = 1; $i <= 6; $i++) : ?>
								<option value="<?php echo esc_attr((string) $i); ?>" <?php selected($selected_bathrooms, (string) $i); ?>>
									<?php echo esc_html((string) $i); ?>
								</option>
							<?php endfor; ?>
						</select>
					</label>

					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('المجموعة', 'tmg-rentals'); ?></span>
						<input type="text" name="group" value="<?php echo esc_attr($selected_group); ?>" placeholder="<?php esc_attr_e('مثال: B11', 'tmg-rentals'); ?>">
					</label>

					<label class="tmg-field">
						<span class="tmg-field__label"><?php esc_html_e('النموذج', 'tmg-rentals'); ?></span>
						<input type="text" name="model" value="<?php echo esc_attr($selected_model); ?>" placeholder="<?php esc_attr_e('مثال: 300 أو T', 'tmg-rentals'); ?>">
					</label>

					<div class="tmg-range-group">
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('سعر من', 'tmg-rentals'); ?></span>
							<input type="number" name="price_min" value="<?php echo esc_attr($price_min); ?>" placeholder="<?php esc_attr_e('15000', 'tmg-rentals'); ?>">
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('إلى', 'tmg-rentals'); ?></span>
							<input type="number" name="price_max" value="<?php echo esc_attr($price_max); ?>" placeholder="<?php esc_attr_e('45000', 'tmg-rentals'); ?>">
						</label>
					</div>

					<div class="tmg-range-group">
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('مساحة من', 'tmg-rentals'); ?></span>
							<input type="number" name="area_min" value="<?php echo esc_attr($area_min); ?>" placeholder="<?php esc_attr_e('80', 'tmg-rentals'); ?>">
						</label>
						<label class="tmg-field">
							<span class="tmg-field__label"><?php esc_html_e('إلى', 'tmg-rentals'); ?></span>
							<input type="number" name="area_max" value="<?php echo esc_attr($area_max); ?>" placeholder="<?php esc_attr_e('220', 'tmg-rentals'); ?>">
						</label>
					</div>
				</div>

				<div class="tmg-search-actions">
					<button type="submit" class="tmg-button tmg-button--primary tmg-button--wide"><?php esc_html_e('بحث', 'tmg-rentals'); ?></button>
					<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>"><?php esc_html_e('إعادة ضبط', 'tmg-rentals'); ?></a>
				</div>
			</form>
		</section>

		<section class="tmg-results">
			<?php if (have_posts()) : ?>
				<div class="tmg-results-toolbar">
					<p><?php echo esc_html(sprintf(_n('%s عقار متاح', '%s عقار متاح', (int) $wp_query->found_posts, 'tmg-rentals'), number_format_i18n((int) $wp_query->found_posts))); ?></p>
				</div>
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
					<p><?php esc_html_e('جرّب تعديل معايير البحث أو توسيع نطاق السعر والمساحة للوصول إلى نتائج أكثر.', 'tmg-rentals'); ?></p>
				</div>
			<?php endif; ?>
		</section>
	</div>
</main>
<?php
get_footer();

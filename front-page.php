<?php
/**
 * Front page template.
 *
 * @package TMG_Rentals
 */

use function TMG_Rentals\format_price;
use function TMG_Rentals\get_primary_project_label;
use function TMG_Rentals\get_primary_rental_type_label;
use function TMG_Rentals\get_property_meta;

get_header();

$projects      = get_terms(array('taxonomy' => 'tmg_projects', 'hide_empty' => false, 'number' => 4));
$rental_types  = get_terms(array('taxonomy' => 'rental_types', 'hide_empty' => false));
$featured_query = new WP_Query(
	array(
		'post_type'      => 'properties',
		'posts_per_page' => 6,
		'post_status'    => 'publish',
	)
);
$featured_count = (int) $featured_query->post_count;
$featured_grid_class = 'tmg-grid tmg-home-featured-grid';

if ($featured_count <= 1) {
	$featured_grid_class .= ' tmg-home-featured-grid--single';
} elseif ($featured_count === 2) {
	$featured_grid_class .= ' tmg-home-featured-grid--double';
}
?>
<main class="tmg-home">
	<section class="tmg-home-hero">
		<div class="tmg-container">
			<div class="tmg-home-hero__wrap">
				<div class="tmg-home-hero__content">
					<span class="tmg-home-hero__eyebrow"><?php esc_html_e('منصة إيجارات حصرية داخل مدن TMG', 'tmg-rentals'); ?></span>
					<h1><?php esc_html_e('اعثر على بيتك القادم داخل مدينتي والرحاب وسيليا ونور', 'tmg-rentals'); ?></h1>
					<p><?php esc_html_e('تجربة بحث عربية سريعة وحديثة تربط المالك بالمستأجر داخل مجتمعات TMG فقط، مع فلاتر ذكية وإعلانات موثوقة.', 'tmg-rentals'); ?></p>

					<div class="tmg-home-hero__chips">
						<span class="tmg-hero-chip"><?php esc_html_e('شقق', 'tmg-rentals'); ?></span>
						<span class="tmg-hero-chip"><?php esc_html_e('فيلات', 'tmg-rentals'); ?></span>
						<span class="tmg-hero-chip"><?php esc_html_e('تجاري', 'tmg-rentals'); ?></span>
						<span class="tmg-hero-chip"><?php esc_html_e('مفروش وقانون جديد', 'tmg-rentals'); ?></span>
					</div>

					<form class="tmg-home-search" action="<?php echo esc_url(get_post_type_archive_link('properties')); ?>" method="get">
						<div class="tmg-home-search__tabs">
							<button type="button" class="tmg-search-tab is-active"><?php esc_html_e('للإيجار', 'tmg-rentals'); ?></button>
							<button type="button" class="tmg-search-tab"><?php esc_html_e('وحدات مميزة', 'tmg-rentals'); ?></button>
						</div>

						<div class="tmg-home-search__grid">
							<label class="tmg-field">
								<span class="tmg-field__label"><?php esc_html_e('المشروع', 'tmg-rentals'); ?></span>
								<select name="project">
									<option value=""><?php esc_html_e('اختر المشروع', 'tmg-rentals'); ?></option>
									<?php foreach ($projects as $project) : ?>
										<option value="<?php echo esc_attr($project->slug); ?>"><?php echo esc_html($project->name); ?></option>
									<?php endforeach; ?>
								</select>
							</label>

							<label class="tmg-field">
								<span class="tmg-field__label"><?php esc_html_e('نوع الإيجار', 'tmg-rentals'); ?></span>
								<select name="rental_type">
									<option value=""><?php esc_html_e('حدد النوع', 'tmg-rentals'); ?></option>
									<?php foreach ($rental_types as $rental_type) : ?>
										<option value="<?php echo esc_attr($rental_type->slug); ?>"><?php echo esc_html($rental_type->name); ?></option>
									<?php endforeach; ?>
								</select>
							</label>

							<label class="tmg-field">
								<span class="tmg-field__label"><?php esc_html_e('عدد الغرف', 'tmg-rentals'); ?></span>
								<select name="bedrooms">
									<option value=""><?php esc_html_e('أي عدد', 'tmg-rentals'); ?></option>
									<?php for ($i = 1; $i <= 6; $i++) : ?>
										<option value="<?php echo esc_attr((string) $i); ?>"><?php echo esc_html((string) $i); ?></option>
									<?php endfor; ?>
								</select>
							</label>

							<label class="tmg-field">
								<span class="tmg-field__label"><?php esc_html_e('الميزانية', 'tmg-rentals'); ?></span>
								<input type="number" name="price_max" placeholder="<?php esc_attr_e('حتى 45000 ج.م', 'tmg-rentals'); ?>">
							</label>
						</div>

						<div class="tmg-home-search__actions">
							<button type="submit" class="tmg-button tmg-button--primary tmg-button--wide"><?php esc_html_e('ابدأ البحث الآن', 'tmg-rentals'); ?></button>
							<a class="tmg-button tmg-button--ghost" href="<?php echo esc_url(home_url('/add-property')); ?>"><?php esc_html_e('أضف وحدة للإيجار', 'tmg-rentals'); ?></a>
						</div>
					</form>

					<div class="tmg-home-stats">
						<div><strong>4+</strong><span><?php esc_html_e('مدن ومشروعات', 'tmg-rentals'); ?></span></div>
						<div><strong>24h</strong><span><?php esc_html_e('مراجعة للإعلانات', 'tmg-rentals'); ?></span></div>
						<div><strong>RTL</strong><span><?php esc_html_e('تجربة عربية كاملة', 'tmg-rentals'); ?></span></div>
					</div>
				</div>

				<div class="tmg-home-hero__visual">
					<div class="tmg-home-hero__visual-card">
						<img src="<?php echo esc_url(get_template_directory_uri() . '/assets/images/home-hero-banner.svg'); ?>" alt="<?php esc_attr_e('بانر رئيسي لمنصة TMG Rentals', 'tmg-rentals'); ?>">
						<div class="tmg-floating-badge tmg-floating-badge--top">
							<strong><?php esc_html_e('TMG فقط', 'tmg-rentals'); ?></strong>
							<span><?php esc_html_e('سوق متخصص داخل الكمباوندات', 'tmg-rentals'); ?></span>
						</div>
						<div class="tmg-floating-badge tmg-floating-badge--bottom">
							<strong><?php esc_html_e('واجهة أسرع', 'tmg-rentals'); ?></strong>
							<span><?php esc_html_e('بحث مباشر بدون إضافات ثقيلة', 'tmg-rentals'); ?></span>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<section class="tmg-home-section">
		<div class="tmg-container">
			<div class="tmg-section-heading">
				<div>
					<span class="tmg-kicker"><?php esc_html_e('المشروعات', 'tmg-rentals'); ?></span>
					<h2><?php esc_html_e('اختر المدينة التي تناسب نمط حياتك', 'tmg-rentals'); ?></h2>
				</div>
				<a class="tmg-header-link" href="<?php echo esc_url(get_post_type_archive_link('properties')); ?>"><?php esc_html_e('عرض كل العقارات', 'tmg-rentals'); ?></a>
			</div>

			<div class="tmg-projects-grid">
				<?php foreach ($projects as $index => $project) : ?>
					<a class="tmg-project-card" href="<?php echo esc_url(get_term_link($project)); ?>">
						<span class="tmg-project-card__index"><?php echo esc_html('0' . (string) ($index + 1)); ?></span>
						<h3><?php echo esc_html($project->name); ?></h3>
						<p><?php esc_html_e('وحدات للإيجار داخل مجتمع متكامل وخدمات يومية ومساحات متنوعة.', 'tmg-rentals'); ?></p>
					</a>
				<?php endforeach; ?>
			</div>
		</div>
	</section>

	<section class="tmg-home-section tmg-home-section--contrast">
		<div class="tmg-container">
			<div class="tmg-section-heading">
				<div>
					<span class="tmg-kicker"><?php esc_html_e('إعلانات مختارة', 'tmg-rentals'); ?></span>
					<h2><?php esc_html_e('أحدث العقارات المضافة على المنصة', 'tmg-rentals'); ?></h2>
				</div>
			</div>

			<?php if ($featured_query->have_posts()) : ?>
				<div class="<?php echo esc_attr($featured_grid_class); ?>">
					<?php while ($featured_query->have_posts()) : $featured_query->the_post(); ?>
						<?php $meta = get_property_meta(get_the_ID()); ?>
						<article <?php post_class('tmg-property-card'); ?>>
							<a class="tmg-property-card__media" href="<?php the_permalink(); ?>">
								<?php if (has_post_thumbnail()) : ?>
									<?php the_post_thumbnail('large'); ?>
								<?php else : ?>
									<div class="tmg-placeholder"><?php esc_html_e('أضف صورة للعقار', 'tmg-rentals'); ?></div>
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
								<h3 class="tmg-property-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
								<ul class="tmg-feature-list">
									<li><?php echo esc_html($meta['area'] ?: '0'); ?> <?php esc_html_e('م²', 'tmg-rentals'); ?></li>
									<li><?php echo esc_html($meta['bedrooms'] ?: '0'); ?> <?php esc_html_e('غرف', 'tmg-rentals'); ?></li>
									<li><?php echo esc_html($meta['bathrooms'] ?: '0'); ?> <?php esc_html_e('حمامات', 'tmg-rentals'); ?></li>
								</ul>
							</div>
						</article>
					<?php endwhile; ?>
				</div>
				<?php wp_reset_postdata(); ?>
			<?php else : ?>
				<div class="tmg-empty-state">
					<h2><?php esc_html_e('أضف أول عقار ليظهر هنا', 'tmg-rentals'); ?></h2>
					<p><?php esc_html_e('الصفحة الرئيسية جاهزة، وبمجرد نشر العقارات ستبدأ البطاقات في الظهور تلقائياً.', 'tmg-rentals'); ?></p>
				</div>
			<?php endif; ?>
		</div>
	</section>
</main>

<?php
/**
 * Default page template.
 *
 * @package TMG_Rentals
 */

get_header();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container">
		<?php if (have_posts()) : ?>
			<?php while (have_posts()) : the_post(); ?>
				<article <?php post_class('tmg-card tmg-card--soft'); ?>>
					<header class="tmg-page-header">
						<h1 class="tmg-card__title"><?php the_title(); ?></h1>
					</header>
					<div class="tmg-entry">
						<?php the_content(); ?>
					</div>
				</article>
			<?php endwhile; ?>
		<?php else : ?>
			<div class="tmg-empty-state">
				<h2><?php esc_html_e('لا توجد محتويات لعرضها حالياً.', 'tmg-rentals'); ?></h2>
			</div>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();

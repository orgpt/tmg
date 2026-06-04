<?php
/**
 * Fallback template.
 *
 * @package TMG_Rentals
 */

get_header();
?>
<main class="tmg-shell tmg-section">
	<div class="tmg-container">
		<?php if (have_posts()) : ?>
			<div class="tmg-stack">
				<?php while (have_posts()) : the_post(); ?>
					<article <?php post_class('tmg-card tmg-card--soft'); ?>>
						<h2 class="tmg-card__title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
						<div class="tmg-entry"><?php the_excerpt(); ?></div>
					</article>
				<?php endwhile; ?>
			</div>
		<?php else : ?>
			<p><?php esc_html_e('لا توجد محتويات لعرضها حالياً.', 'tmg-rentals'); ?></p>
		<?php endif; ?>
	</div>
</main>
<?php
get_footer();


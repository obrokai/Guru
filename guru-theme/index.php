<?php
/**
 * Fallback template — also serves the blog listing.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<div class="blg-hero">
	<div class="blg-hero-left">
		<h1 class="blg-hero-title">
			<?php echo esc_html( is_home() ? __( 'Insights', 'guru' ) : get_the_archive_title() ); ?>
		</h1>
	</div>
	<div class="blg-hero-right">
		<p class="blg-hero-desc">
			<?php
			echo esc_html(
				get_theme_mod(
					'guru_blog_intro',
					__( 'Perspectives on digital marketing, brand strategy, influencer culture and the future of creativity.', 'guru' )
				)
			);
			?>
		</p>
	</div>
</div>

<div class="blg-grid">
	<?php if ( have_posts() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();
			?>
			<a class="art-card" href="<?php the_permalink(); ?>">
				<div class="art-img">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large', array( 'class' => 'art-img-bg', 'loading' => 'lazy' ) ); ?>
					<?php else : ?>
						<div class="art-img-bg grad-strategy"></div>
					<?php endif; ?>
					<div class="art-img-pattern"></div>
				</div>
				<?php
				$guru_cat = get_the_category();
				if ( $guru_cat ) :
					?>
					<span class="art-cat cat-strategy"><?php echo esc_html( $guru_cat[0]->name ); ?></span>
				<?php endif; ?>
				<h2 class="art-title"><?php the_title(); ?></h2>
				<p class="art-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
				<div class="art-meta">
					<span><?php echo esc_html( get_the_date() ); ?></span>
				</div>
			</a>
			<?php
		endwhile;
	else :
		?>
		<p><?php esc_html_e( 'No posts yet.', 'guru' ); ?></p>
	<?php endif; ?>
</div>

<?php
the_posts_pagination( array( 'mid_size' => 2 ) );

get_footer();

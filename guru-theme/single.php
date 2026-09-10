<?php
/**
 * Single blog post.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	?>
	<article class="blg-single">
		<div class="blg-single-head">
			<?php
			$guru_cats = get_the_category();
			if ( $guru_cats ) :
				?>
				<span class="art-cat cat-strategy"><?php echo esc_html( $guru_cats[0]->name ); ?></span>
			<?php endif; ?>
			<h1 class="blg-single-title"><?php the_title(); ?></h1>
			<div class="art-meta">
				<span><?php echo esc_html( get_the_date() ); ?></span>
				<span><?php echo esc_html( get_the_author() ); ?></span>
			</div>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="blg-single-cover"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="blg-single-body guru-editor">
			<?php the_content(); ?>
		</div>

		<div class="blg-single-nav">
			<?php previous_post_link( '%link', '&larr; ' . esc_html__( 'Previous', 'guru' ) ); ?>
			<?php next_post_link( '%link', esc_html__( 'Next', 'guru' ) . ' &rarr;' ); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

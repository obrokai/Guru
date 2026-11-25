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
			<?php
			// One author record for the byline, the author box and Yoast's Schema (v1.4.2).
			$guru_author = guru_article_author();
			?>
			<div class="art-meta">
				<?php if ( '' !== $guru_author['name'] ) : ?>
					<span class="art-byline">
						<?php
						/* translators: %s: author or editorial team name. */
						printf( esc_html__( 'By %s', 'guru' ), '<a href="#art-author">' . esc_html( $guru_author['name'] ) . '</a>' );
						?>
					</span>
				<?php endif; ?>
				<span><?php echo guru_post_date(); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?></span>
			</div>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="blg-single-cover"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="blg-single-body guru-editor">
			<?php the_content(); ?>
		</div>

		<?php if ( '' !== $guru_author['name'] ) : ?>
			<div class="art-author" id="art-author">
				<p class="art-author-label"><?php esc_html_e( 'Written by', 'guru' ); ?></p>
				<p class="art-author-name"><?php echo esc_html( $guru_author['name'] ); ?></p>
				<?php if ( '' !== $guru_author['bio'] ) : ?>
					<p class="art-author-bio"><?php echo esc_html( $guru_author['bio'] ); ?></p>
				<?php endif; ?>
				<p class="art-author-date">
					<?php
					/* translators: %s: publication date. */
					// No "Updated" date: imported articles all carry the import date as their
					// modified date, and spec #15 rules out showing an update that did not happen.
					printf( esc_html__( 'Published %s', 'guru' ), guru_post_date() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper.
					?>
				</p>
			</div>
		<?php endif; ?>

		<?php guru_render_article_related(); ?>

		<div class="blg-single-nav">
			<?php previous_post_link( '%link', '&larr; ' . esc_html__( 'Previous', 'guru' ) ); ?>
			<?php next_post_link( '%link', esc_html__( 'Next', 'guru' ) . ' &rarr;' ); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

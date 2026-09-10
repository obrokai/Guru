<?php
/**
 * Default page template — any page without a dedicated template (legal pages,
 * pages the client adds later). Uses the single-post layout so it matches the
 * rest of the site.
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
			<h1 class="blg-single-title"><?php the_title(); ?></h1>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="blg-single-cover"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="blg-single-body guru-editor">
			<?php the_content(); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

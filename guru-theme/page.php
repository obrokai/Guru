<?php
/**
 * Default page template — any page without a dedicated template (legal pages,
 * pages the client adds later). Uses the single-post layout so it matches the
 * rest of the site.
 *
 * Pages inherited from the previous theme were built with page-builder
 * shortcodes that no longer exist; their leftover tags are stripped so the
 * plain text still reads.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	$guru_content = apply_filters( 'the_content', get_the_content() );
	$guru_content = preg_replace( '/\[\/?(vc_|eltd_|rev_slider|contact-form-7)[^\]]*\]/', '', $guru_content );
	?>
	<article class="blg-single">
		<div class="blg-single-head">
			<h1 class="blg-single-title"><?php the_title(); ?></h1>
		</div>

		<?php if ( has_post_thumbnail() ) : ?>
			<div class="blg-single-cover"><?php the_post_thumbnail( 'large' ); ?></div>
		<?php endif; ?>

		<div class="blg-single-body guru-editor">
			<?php echo wp_kses_post( $guru_content ); ?>
		</div>
	</article>
	<?php
endwhile;

get_footer();

<?php
/**
 * Template Name: About
 *
 * Copy comes from the page editor where possible, so the client can edit it:
 * - Page title      → hero heading
 * - Excerpt         → hero lead paragraph
 * - Page content    → the "Our Story" body
 * Offices are edited in Customize → Guru site settings → Offices.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();

	// The hand-written excerpt only — never an auto-trimmed copy of the story text.
	$guru_lead = trim( (string) get_post_field( 'post_excerpt', get_the_ID() ) );
	?>

	<div class="abt-hero">
		<h1 class="abt-hero-title"><?php echo wp_kses_post( str_replace( ' ', '<br>', get_the_title() ) ); ?></h1>
		<div class="abt-hero-bottom">
			<?php if ( $guru_lead ) : ?>
				<?php // <em> is the blue highlight the static page uses inside the lead. ?>
				<p class="abt-hero-lead"><?php echo wp_kses( $guru_lead, array( 'em' => array(), 'strong' => array() ) ); ?></p>
			<?php endif; ?>
			<div class="abt-hero-meta">
				<strong><?php echo esc_html( get_theme_mod( 'guru_founded', 'Est. 2007' ) ); ?></strong>
				<span><?php echo esc_html( get_theme_mod( 'guru_credential', "Listed 4A's Agency" ) ); ?></span>
			</div>
		</div>
	</div>

	<div class="abt-story-wrap">
		<div>
			<p class="abt-story-label"><?php esc_html_e( 'Our Story', 'guru' ); ?></p>
			<h2 class="abt-story-heading">
				<?php echo wp_kses_post( nl2br( esc_html( get_theme_mod( 'guru_story_heading', "Today's\nBreakthrough Is\nTomorrow's Legend" ) ) ) ); ?>
			</h2>
		</div>
		<div class="abt-story-right guru-editor">
			<?php
			// Paragraphs carry the same class as the static page so its typography applies.
			echo wp_kses_post( str_replace( '<p>', '<p class="abt-story-p">', apply_filters( 'the_content', get_the_content() ) ) );
			?>
		</div>
	</div>

	<?php get_template_part( 'template-parts/client-wall' ); ?>

	<?php get_template_part( 'template-parts/offices' ); ?>

	<?php
endwhile;

get_footer();

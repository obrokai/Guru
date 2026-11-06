<?php
/**
 * Showcase archive — the Work page.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();
?>

<?php // The project count beside the heading was removed at the client's request (v1.2.10). ?>
<div class="wrk-page-header">
	<h1 class="wrk-page-title"><?php esc_html_e( 'Work', 'guru' ); ?></h1>
</div>

<?php guru_work_filters(); ?>

<div class="wrk-page-grid" id="projectGrid">
	<?php
	if ( have_posts() ) :
		$guru_i = 0;
		while ( have_posts() ) :
			the_post();
			guru_work_card( null, $guru_i++ );
		endwhile;
	endif;
	?>
	<div class="wrk-empty" id="emptyState"><?php esc_html_e( 'No projects in this category yet.', 'guru' ); ?></div>
</div>

<?php
get_template_part( 'template-parts/work-modal-archive' );

get_footer();

<?php
/**
 * Single showcase item.
 *
 * Most visitors see showcase work in the lightbox on the archive; this page
 * exists so each item still has a real, shareable URL.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

while ( have_posts() ) :
	the_post();
	$guru = guru_get_work_data();
	?>
	<article class="wrk-single">
		<div class="wrk-page-header">
			<h1 class="wrk-page-title"><?php the_title(); ?></h1>
			<?php if ( $guru['client'] ) : ?>
				<div class="wrk-page-meta">
					<span class="wrk-page-count"><?php echo esc_html( $guru['client'] ); ?></span>
				</div>
			<?php endif; ?>
		</div>

		<div class="wrk-single-media">
			<?php if ( $guru['video'] ) : ?>
				<video controls playsinline preload="metadata"
					<?php echo $guru['image'] ? 'poster="' . esc_url( $guru['image'] ) . '"' : ''; ?>>
					<source src="<?php echo esc_url( $guru['video'] ); ?>" type="video/mp4" />
				</video>
			<?php elseif ( $guru['image'] ) : ?>
				<img src="<?php echo esc_url( $guru['image'] ); ?>" alt="<?php the_title_attribute(); ?>" />
			<?php endif; ?>
		</div>

		<?php if ( $guru['tags'] ) : ?>
			<div class="prj-tags">
				<?php foreach ( $guru['tags'] as $guru_tag ) : ?>
					<span class="prj-tag"><?php echo esc_html( $guru_tag ); ?></span>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<div class="wrk-single-body guru-editor"><?php the_content(); ?></div>

		<p><a href="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>" class="srv-panel-cta">
			&larr; <?php esc_html_e( 'All Projects', 'guru' ); ?>
		</a></p>
	</article>
	<?php
endwhile;

get_footer();

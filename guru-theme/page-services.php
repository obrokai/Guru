<?php
/**
 * Template Name: Solutions
 *
 * Sticky sidebar plus one panel per Solution. Everything on this page comes
 * from the Solutions post type, so editing a solution updates both this page
 * and the homepage accordion.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guru_solutions = new WP_Query( guru_solutions_query_args() );
$guru_items     = array();

if ( $guru_solutions->have_posts() ) {
	while ( $guru_solutions->have_posts() ) {
		$guru_solutions->the_post();
		$guru_items[] = array(
			'slug'    => get_post_field( 'post_name' ),
			'title'   => get_the_title(),
			'excerpt' => get_the_excerpt(),
			'content' => apply_filters( 'the_content', get_the_content() ),
		);
	}
	wp_reset_postdata();
}

$guru_work_url = get_post_type_archive_link( 'work' );
?>

<div class="srv-wrap">
	<aside class="srv-sidebar">
		<p class="srv-sidebar-label"><?php esc_html_e( 'What We Do', 'guru' ); ?></p>

		<nav class="srv-nav">
			<?php foreach ( $guru_items as $guru_i => $guru_item ) : ?>
				<a class="srv-nav-item<?php echo 0 === $guru_i ? ' active' : ''; ?>"
					href="#<?php echo esc_attr( $guru_item['slug'] ); ?>"
					data-panel="<?php echo esc_attr( $guru_item['slug'] ); ?>">
					<span class="srv-nav-num"><?php echo esc_html( sprintf( '%02d', $guru_i + 1 ) ); ?></span>
					<span class="srv-nav-name"><?php echo esc_html( $guru_item['title'] ); ?></span>
				</a>
			<?php endforeach; ?>
		</nav>

		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="srv-sidebar-back">
			&larr; <?php esc_html_e( 'Back to Home', 'guru' ); ?>
		</a>
	</aside>

	<main class="srv-main" id="srvMain">
		<?php if ( ! $guru_items ) : ?>
			<p><?php esc_html_e( 'No solutions have been added yet. Add them under Solutions in the admin.', 'guru' ); ?></p>
		<?php endif; ?>

		<?php foreach ( $guru_items as $guru_i => $guru_item ) : ?>
			<div class="srv-panel<?php echo 0 === $guru_i ? ' active' : ''; ?>" id="panel-<?php echo esc_attr( $guru_item['slug'] ); ?>">
				<div class="srv-panel-tag">
					<span class="srv-panel-tag-dot"></span><?php echo esc_html( $guru_item['title'] ); ?>
				</div>

				<h1 class="srv-panel-title"><?php echo esc_html( $guru_item['title'] ); ?></h1>

				<?php if ( $guru_item['excerpt'] ) : ?>
					<p class="srv-panel-desc"><?php echo esc_html( $guru_item['excerpt'] ); ?></p>
				<?php endif; ?>

				<div class="srv-panel-body guru-editor">
					<?php echo wp_kses_post( $guru_item['content'] ); ?>
				</div>

				<div class="srv-panel-footer">
					<a href="<?php echo esc_url( $guru_work_url ); ?>" class="srv-panel-cta">
						<?php esc_html_e( 'View Our Work', 'guru' ); ?> &rarr;
					</a>
					<?php
					$guru_next = isset( $guru_items[ $guru_i + 1 ] ) ? $guru_items[ $guru_i + 1 ] : $guru_items[0];
					?>
					<button class="srv-panel-next" data-next="<?php echo esc_attr( $guru_next['slug'] ); ?>">
						<span class="next-label"><?php echo esc_html( isset( $guru_items[ $guru_i + 1 ] ) ? __( 'Next', 'guru' ) : __( 'Back to first', 'guru' ) ); ?></span>
						&nbsp;<?php echo esc_html( $guru_next['title'] ); ?> &rarr;
					</button>
				</div>

				<?php get_template_part( 'template-parts/solution-form', null, array( 'solution' => $guru_item['title'] ) ); ?>
			</div>
		<?php endforeach; ?>
	</main>
</div>

<?php
get_footer();

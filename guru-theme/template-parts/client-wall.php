<?php
/**
 * Client logo wall (About page).
 *
 * Pulls from the Client logos post type; falls back to the logos bundled
 * with the theme so the wall is never empty on a fresh install.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_q = new WP_Query(
	array(
		'post_type'      => 'client_logo',
		'posts_per_page' => -1,
		'orderby'        => array( 'menu_order' => 'ASC', 'title' => 'ASC' ),
	)
);

$guru_logos = array();

if ( $guru_q->have_posts() ) {
	while ( $guru_q->have_posts() ) {
		$guru_q->the_post();
		$guru_src = get_the_post_thumbnail_url( get_the_ID(), 'medium' );
		if ( $guru_src ) {
			$guru_logos[] = array(
				'name' => get_the_title(),
				'src'  => $guru_src,
			);
		}
	}
	wp_reset_postdata();
}

// Fall back to the logo files shipped with the theme.
if ( ! $guru_logos ) {
	$guru_dir = get_stylesheet_directory() . '/assets/media/client-logos';
	if ( is_dir( $guru_dir ) ) {
		foreach ( glob( $guru_dir . '/*.{png,jpg,jpeg,webp,svg}', GLOB_BRACE ) as $guru_file ) {
			$guru_slug    = pathinfo( $guru_file, PATHINFO_FILENAME );
			$guru_logos[] = array(
				'name' => ucwords( str_replace( '-', ' ', $guru_slug ) ),
				'src'  => guru_asset( 'media/client-logos/' . basename( $guru_file ) ),
			);
		}
	}
}

if ( ! $guru_logos ) {
	return;
}
?>
<div class="abt-clients">
	<p class="abt-clients-label"><?php esc_html_e( 'Trusted by Leading Brands', 'guru' ); ?></p>
	<h2 class="abt-clients-heading">
		<?php echo esc_html( get_theme_mod( 'guru_clients_heading', __( 'A Roster of Brands That Trust Guru.', 'guru' ) ) ); ?>
	</h2>

	<div class="abt-clients-grid">
		<?php foreach ( $guru_logos as $guru_logo ) : ?>
			<div class="abt-client-cell">
				<img src="<?php echo esc_url( $guru_logo['src'] ); ?>"
					alt="<?php echo esc_attr( $guru_logo['name'] ); ?>" loading="lazy" />
			</div>
		<?php endforeach; ?>
	</div>
</div>

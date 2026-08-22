<?php
/**
 * Client logo ticker.
 *
 * Pulls from the Client logos post type; falls back to the logos bundled
 * with the theme so the homepage still looks right on a fresh install.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_logos = new WP_Query(
	array(
		'post_type'      => 'client_logo',
		'posts_per_page' => 12,
		'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	)
);

$guru_items = array();

if ( $guru_logos->have_posts() ) {
	while ( $guru_logos->have_posts() ) {
		$guru_logos->the_post();
		$guru_items[] = array(
			'name' => get_the_title(),
			'src'  => get_the_post_thumbnail_url( get_the_ID(), 'medium' ),
		);
	}
	wp_reset_postdata();
} else {
	foreach ( array( 'ricqles', 'lancome', 'benz', 'uniqlo' ) as $guru_slug ) {
		$guru_items[] = array(
			'name' => ucfirst( $guru_slug ),
			'src'  => guru_asset( 'media/logo-' . $guru_slug . '.png' ),
		);
	}
}

if ( ! $guru_items ) {
	return;
}
?>
<div class="logo-ticker" aria-label="<?php esc_attr_e( "Clients we've worked with", 'guru' ); ?>">
	<div class="logo-ticker-track">
		<?php // Rendered twice so the marquee loops seamlessly. ?>
		<?php for ( $guru_pass = 0; $guru_pass < 2; $guru_pass++ ) : ?>
			<?php foreach ( $guru_items as $guru_item ) : ?>
				<div class="logo-item" aria-label="<?php echo esc_attr( $guru_item['name'] ); ?>"
					<?php echo $guru_pass ? 'aria-hidden="true"' : ''; ?>>
					<img src="<?php echo esc_url( $guru_item['src'] ); ?>"
						alt="<?php echo $guru_pass ? '' : esc_attr( $guru_item['name'] ); ?>" loading="lazy" />
				</div>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>

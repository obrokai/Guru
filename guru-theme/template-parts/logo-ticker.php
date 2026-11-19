<?php
/**
 * Client logo ticker (homepage).
 *
 * Shows eight Client logos picked at random on every page load (client request,
 * v1.2.12) — the same logos as the About page client wall. When there are no
 * Client logos with an image yet, it shows the six brand wordmarks of the static
 * page. The marquee duration scales with the number of logos so the speed stays the same.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_ticker_count = 8; // How many logos each page load shows.

$guru_logos = new WP_Query(
	array(
		'post_type'      => 'client_logo',
		'posts_per_page' => $guru_ticker_count,
		'orderby'        => 'rand',
		'no_found_rows'  => true,
		'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery
			array(
				'key'     => '_thumbnail_id',
				'compare' => 'EXISTS',
			),
		),
	)
);

$guru_items        = array();
$guru_from_clients = $guru_logos->have_posts();

if ( $guru_from_clients ) {
	while ( $guru_logos->have_posts() ) {
		$guru_logos->the_post();
		$guru_items[] = array(
			'name' => get_the_title(),
			'src'  => get_the_post_thumbnail_url( get_the_ID(), 'full' ),
		);
	}
	wp_reset_postdata();
} else {
	// The static page's ticker: four image logos and two inline wordmarks.
	$guru_audi     = '<svg viewBox="0 0 164 54" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Audi"><circle cx="27" cy="27" r="22" fill="none" stroke="currentColor" stroke-width="4.5"/><circle cx="57" cy="27" r="22" fill="none" stroke="currentColor" stroke-width="4.5"/><circle cx="87" cy="27" r="22" fill="none" stroke="currentColor" stroke-width="4.5"/><circle cx="117" cy="27" r="22" fill="none" stroke="currentColor" stroke-width="4.5"/></svg>';
	$guru_hennessy = '<svg viewBox="0 0 190 44" xmlns="http://www.w3.org/2000/svg" role="img" aria-label="Hennessy"><text x="4" y="34" font-family="Georgia,\'Times New Roman\',serif" font-weight="400" font-size="32" letter-spacing="2" fill="currentColor">Hennessy</text></svg>';

	$guru_items = array(
		array( 'name' => 'Ricqles', 'src' => guru_theme_media_url( 'logo-ricqles.webp' ) ),
		array( 'name' => 'Lancôme', 'src' => guru_theme_media_url( 'logo-lancome.png' ) ),
		array( 'name' => 'Audi', 'svg' => $guru_audi ),
		array( 'name' => 'Mercedes-Benz', 'src' => guru_theme_media_url( 'logo-benz.png' ) ),
		array( 'name' => 'Hennessy', 'svg' => $guru_hennessy ),
		array( 'name' => 'Uniqlo', 'src' => guru_theme_media_url( 'logo-uniqlo.png' ) ),
	);
}

$guru_items = array_values(
	array_filter(
		$guru_items,
		static function ( $item ) {
			return ! empty( $item['src'] ) || ! empty( $item['svg'] );
		}
	)
);

if ( ! $guru_items ) {
	return;
}

// 28 s for the six-logo reference track; more logos → proportionally longer.
$guru_duration = round( 28 * max( 1, count( $guru_items ) ) / 6, 1 );
$guru_svg_kses = array(
	'svg'    => array( 'viewbox' => true, 'xmlns' => true, 'role' => true, 'aria-label' => true ),
	'circle' => array( 'cx' => true, 'cy' => true, 'r' => true, 'fill' => true, 'stroke' => true, 'stroke-width' => true ),
	'text'   => array( 'x' => true, 'y' => true, 'font-family' => true, 'font-weight' => true, 'font-size' => true, 'letter-spacing' => true, 'fill' => true ),
);
?>
<div class="logo-ticker<?php echo $guru_from_clients ? ' logo-ticker--clients' : ''; ?>" aria-label="<?php esc_attr_e( "Clients we've worked with", 'guru' ); ?>">
	<div class="logo-ticker-track" style="animation-duration: <?php echo esc_attr( $guru_duration ); ?>s">
		<?php // Rendered twice so the marquee loops seamlessly. ?>
		<?php for ( $guru_pass = 0; $guru_pass < 2; $guru_pass++ ) : ?>
			<?php foreach ( $guru_items as $guru_item ) : ?>
				<div class="logo-item" aria-label="<?php echo esc_attr( $guru_item['name'] ); ?>"
					<?php echo $guru_pass ? 'aria-hidden="true"' : ''; ?>>
					<?php if ( ! empty( $guru_item['svg'] ) ) : ?>
						<?php echo wp_kses( $guru_item['svg'], $guru_svg_kses ); ?>
					<?php else : ?>
						<img src="<?php echo esc_url( $guru_item['src'] ); ?>"
							alt="<?php echo $guru_pass ? '' : esc_attr( $guru_item['name'] ); ?>" loading="lazy" />
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		<?php endfor; ?>
	</div>
</div>

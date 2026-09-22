<?php
/**
 * Offices grid (About page). The static page had a world-map video above the
 * grid; the client asked for it to be removed (2026-09-22).
 *
 * Edited in Customize → Guru site settings → About page, one office per line:
 *   City | Region or address | badge (optional — a badge makes the card featured)
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_rows = guru_pipe_rows( guru_mod( 'guru_offices' ) );

if ( ! $guru_rows ) {
	return;
}
?>
<div class="abt-offices-section">
	<div class="abt-offices-inner">
		<div class="abt-offices-header">
			<div>
				<p class="abt-offices-label"><?php esc_html_e( 'Where We Are', 'guru' ); ?></p>
				<h2 class="abt-offices-heading">
					<?php echo wp_kses_post( nl2br( esc_html( guru_mod( 'guru_offices_heading' ) ) ) ); ?>
				</h2>
			</div>
		</div>

		<div class="abt-offices-grid">
			<?php foreach ( $guru_rows as $guru_i => $guru_parts ) : ?>
				<?php
				$guru_city   = isset( $guru_parts[0] ) ? $guru_parts[0] : '';
				$guru_detail = isset( $guru_parts[1] ) ? $guru_parts[1] : '';
				$guru_badge  = isset( $guru_parts[2] ) ? $guru_parts[2] : '';

				if ( ! $guru_city ) {
					continue;
				}
				?>
				<div class="abt-office-card<?php echo $guru_badge ? ' featured' : ''; ?>">
					<div class="abt-office-num"><?php echo esc_html( sprintf( '%02d', $guru_i + 1 ) ); ?></div>
					<div class="abt-office-city"><?php echo esc_html( $guru_city ); ?></div>
					<?php if ( $guru_badge ) : ?>
						<span class="abt-office-badge"><?php echo esc_html( $guru_badge ); ?></span>
						<div class="abt-office-tel"><?php echo esc_html( $guru_detail ); ?></div>
					<?php elseif ( $guru_detail ) : ?>
						<div class="abt-office-region"><?php echo esc_html( $guru_detail ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
	</div>
</div>

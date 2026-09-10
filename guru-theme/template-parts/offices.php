<?php
/**
 * Offices grid (About page).
 *
 * Edited in Customize → Guru site settings → Offices, one per line:
 *   City | Region or address | badge (optional)
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_default = "Hong Kong | Unit 1201 & 1216, Two Harbour Square, 180 Wai Yip Street, Kwun Tong, Kowloon | Head Office\n"
	. "Guangzhou | Mainland China\n"
	. "Nanjing | Mainland China\n"
	. "Taipei | Taiwan\n"
	. "Singapore | Southeast Asia\n"
	. "Macao | Macao SAR";

$guru_raw  = get_theme_mod( 'guru_offices', $guru_default );
$guru_rows = array_filter( array_map( 'trim', explode( "\n", $guru_raw ) ) );

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
					<?php echo wp_kses_post( nl2br( esc_html( get_theme_mod( 'guru_offices_heading', "Our Offices,\nOne Vision." ) ) ) ); ?>
				</h2>
			</div>
		</div>

		<div class="abt-offices-grid">
			<?php foreach ( $guru_rows as $guru_i => $guru_row ) : ?>
				<?php
				$guru_parts  = array_map( 'trim', explode( '|', $guru_row ) );
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

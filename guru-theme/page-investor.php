<?php
/**
 * Template Name: Investor
 *
 * Same structure as the static investor page: hero, two document lists with a
 * sidebar (listing information, useful links, IR contacts) and a disclaimer
 * strip instead of the site footer. Everything is edited in
 * Customize → Guru site settings → Investor page.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guru_docs       = guru_pipe_rows( guru_mod( 'guru_investor_docs' ) );
$guru_governance = guru_pipe_rows( guru_mod( 'guru_investor_governance' ) );
$guru_contacts   = guru_pipe_rows( guru_mod( 'guru_investor_contacts' ) );
$guru_code       = guru_mod( 'guru_stock_code' );

$guru_info = array(
	__( 'Stock Code', 'guru' )      => $guru_code,
	__( 'Exchange', 'guru' )        => guru_mod( 'guru_exchange' ),
	__( 'Board Lot', 'guru' )       => guru_mod( 'guru_board_lot' ),
	__( 'Fiscal Year End', 'guru' ) => guru_mod( 'guru_fiscal_year' ),
);

$guru_links = array(
	array( __( 'HKEXnews Announcements', 'guru' ), guru_mod( 'guru_investor_more_url' ) ),
	array( __( 'HKEX Stock Quote', 'guru' ), guru_mod( 'guru_investor_quote_url' ) ),
	array( __( 'Previous IR Website', 'guru' ), guru_mod( 'guru_investor_prev_url' ) ),
);

/**
 * One row in a document list.
 *
 * @param string $date  Left column.
 * @param string $title Document name.
 * @param string $sub   Optional subtitle.
 * @param array  $links [ [ url, label, extra class ], … ].
 */
function guru_investor_doc_row( $date, $title, $sub, $links ) {
	?>
	<div class="inv-doc-item">
		<div class="inv-doc-date"><?php echo esc_html( $date ); ?></div>
		<div class="inv-doc-content">
			<div class="inv-doc-name">
				<?php echo esc_html( $title ); ?>
				<?php if ( $sub ) : ?>
					<br /><span class="inv-doc-sub"><?php echo esc_html( $sub ); ?></span>
				<?php endif; ?>
			</div>
			<?php if ( $links ) : ?>
				<div class="inv-doc-links">
					<?php foreach ( $links as $link ) : ?>
						<a class="inv-doc-link<?php echo ! empty( $link[2] ) ? ' ' . esc_attr( $link[2] ) : ''; ?>"
							href="<?php echo esc_url( $link[0] ); ?>" target="_blank" rel="noopener"><?php echo esc_html( $link[1] ); ?> &#8599;</a>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

while ( have_posts() ) :
	the_post();
	// A hand-written excerpt overrides the Customizer intro; page content never leaks in here.
	$guru_intro = trim( (string) get_post_field( 'post_excerpt', get_the_ID() ) );
	$guru_intro = $guru_intro ? $guru_intro : guru_mod( 'guru_investor_intro' );
	?>

	<div class="inv-hero">
		<p class="inv-hero-label"><?php esc_html_e( 'Investor Relations', 'guru' ); ?></p>
		<h1 class="inv-hero-title"><?php echo wp_kses_post( nl2br( esc_html( guru_mod( 'guru_investor_entity' ) ) ) ); ?></h1>
		<?php if ( $guru_intro ) : ?>
			<p class="inv-hero-desc"><?php echo esc_html( $guru_intro ); ?></p>
		<?php endif; ?>
	</div>

	<div class="inv-body">
		<div class="inv-main">
			<?php if ( trim( get_the_content() ) ) : ?>
				<div class="guru-editor"><?php the_content(); ?></div>
			<?php endif; ?>

			<div class="inv-docs">
				<div class="inv-section-title"><?php esc_html_e( 'Financial Results & Reports', 'guru' ); ?></div>
				<?php
				foreach ( $guru_docs as $guru_p ) {
					if ( empty( $guru_p[1] ) ) {
						continue;
					}
					$guru_links_row = array();
					if ( ! empty( $guru_p[3] ) ) {
						$guru_links_row[] = array( $guru_p[3], 'EN', '' );
					}
					if ( ! empty( $guru_p[4] ) ) {
						$guru_links_row[] = array( $guru_p[4], '中文', 'inv-doc-link-zh' );
					}
					guru_investor_doc_row( $guru_p[0], $guru_p[1], isset( $guru_p[2] ) ? $guru_p[2] : '', $guru_links_row );
				}
				?>
				<?php if ( guru_mod( 'guru_investor_more_url' ) ) : ?>
					<a class="inv-more-btn" href="<?php echo esc_url( guru_mod( 'guru_investor_more_url' ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'View All Announcements on HKEXnews', 'guru' ); ?> &#8599;
					</a>
				<?php endif; ?>
			</div>

			<?php if ( $guru_governance ) : ?>
				<div class="inv-docs">
					<div class="inv-section-title"><?php esc_html_e( 'Corporate Governance', 'guru' ); ?></div>
					<?php
					foreach ( $guru_governance as $guru_p ) {
						if ( empty( $guru_p[1] ) ) {
							continue;
						}
						$guru_links_row = ! empty( $guru_p[2] ) ? array( array( $guru_p[2], ! empty( $guru_p[3] ) ? $guru_p[3] : 'View', '' ) ) : array();
						guru_investor_doc_row( $guru_p[0], $guru_p[1], '', $guru_links_row );
					}
					?>
				</div>
			<?php endif; ?>
		</div>

		<aside class="inv-sidebar">
			<div class="inv-sidebar-card">
				<div class="inv-sidebar-card-title"><?php esc_html_e( 'Listing Information', 'guru' ); ?></div>
				<?php foreach ( $guru_info as $guru_k => $guru_v ) : ?>
					<?php if ( $guru_v ) : ?>
						<div class="inv-info-row">
							<span class="inv-info-key"><?php echo esc_html( $guru_k ); ?></span>
							<span class="inv-info-val"<?php echo $guru_v === $guru_code ? ' style="color:var(--blue)"' : ''; ?>><?php echo esc_html( $guru_v ); ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<div class="inv-sidebar-card">
				<div class="inv-sidebar-card-title"><?php esc_html_e( 'Useful Links', 'guru' ); ?></div>
				<?php foreach ( $guru_links as $guru_link ) : ?>
					<?php if ( $guru_link[1] ) : ?>
						<a class="inv-hkex-link" href="<?php echo esc_url( $guru_link[1] ); ?>" target="_blank" rel="noopener">
							<?php echo esc_html( $guru_link[0] ); ?> <span>&#8599;</span>
						</a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<?php if ( $guru_contacts ) : ?>
				<div class="inv-sidebar-card">
					<div class="inv-sidebar-card-title"><?php esc_html_e( 'IR & Office Contacts', 'guru' ); ?></div>
					<?php foreach ( $guru_contacts as $guru_c ) : ?>
						<?php
						if ( empty( $guru_c[0] ) ) {
							continue;
						}
						?>
						<div class="inv-contact-row">
							<div class="inv-contact-city"><?php echo esc_html( $guru_c[0] ); ?></div>
							<?php if ( ! empty( $guru_c[1] ) ) : ?>
								<div class="inv-contact-tel"><?php echo esc_html( $guru_c[1] ); ?></div>
							<?php endif; ?>
							<?php if ( ! empty( $guru_c[2] ) ) : ?>
								<div class="inv-contact-addr"><?php echo esc_html( $guru_c[2] ); ?></div>
							<?php endif; ?>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</aside>
	</div>

	<div class="inv-footer">
		<div class="inv-footer-inner">
			<p>
				<?php
				printf(
					/* translators: 1: year, 2: company name, 3: stock code. */
					esc_html__( '© %1$s %2$s. All rights reserved. Stock Code: %3$s.HK', 'guru' ),
					esc_html( gmdate( 'Y' ) ),
					esc_html( str_replace( array( "\r", "\n" ), ' ', guru_mod( 'guru_investor_entity' ) ) ),
					esc_html( $guru_code )
				);
				?>
			</p>
			<?php if ( guru_mod( 'guru_investor_disclaimer' ) ) : ?>
				<p><?php echo esc_html( guru_mod( 'guru_investor_disclaimer' ) ); ?></p>
			<?php endif; ?>
		</div>
	</div>

	<?php
endwhile;

get_footer( 'minimal' );

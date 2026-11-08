<?php
/**
 * Template Name: Investor
 *
 * Same structure as the static investor page: hero, two document lists with a
 * sidebar (IR contacts) and a disclaimer strip instead of the site footer.
 * Everything is edited in Customize → Guru site settings → Investor page.
 * The static page's "Listing Information" and "Useful Links" sidebar cards were
 * removed at the client's request (v1.2.10).
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
			<?php
			// Old-site page-builder tags are stripped; nothing is shown if only they were there.
			$guru_content = guru_clean_page_content();
			?>
			<?php if ( trim( wp_strip_all_tags( $guru_content ) ) ) : ?>
				<div class="guru-editor"><?php echo $guru_content; // phpcs:ignore WordPress.Security.EscapeOutput -- the_content output, as before. ?></div>
			<?php endif; ?>

			<?php
			// Investor documents (Admin → Investor Doc). Without any, the Customizer list is shown.
			$guru_cats     = guru_investor_doc_categories();
			$guru_cat_rows = array();
			foreach ( $guru_cats as $guru_slug => $guru_label ) {
				$guru_cat_rows[ $guru_slug ] = guru_investor_doc_rows( $guru_slug );
			}
			$guru_has_docs = (bool) array_filter( $guru_cat_rows );

			// ?docs=<category>&pg=<n> shows that category's full list.
			$guru_list = isset( $_GET['docs'] ) ? sanitize_key( wp_unslash( $_GET['docs'] ) ) : ''; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
			$guru_list = ( $guru_has_docs && isset( $guru_cats[ $guru_list ] ) ) ? $guru_list : '';
			$guru_per  = 20;
			?>

			<?php if ( $guru_list ) : ?>
				<?php
				$guru_all   = $guru_cat_rows[ $guru_list ];
				$guru_pages = max( 1, (int) ceil( count( $guru_all ) / $guru_per ) );
				$guru_pg    = isset( $_GET['pg'] ) ? max( 1, min( $guru_pages, absint( $_GET['pg'] ) ) ) : 1; // phpcs:ignore WordPress.Security.NonceVerification.Recommended
				?>
				<div class="inv-docs" id="inv-list">
					<a class="inv-back-link" href="<?php echo esc_url( get_permalink() ); ?>">&larr; <?php esc_html_e( 'Investor Relations', 'guru' ); ?></a>
					<div class="inv-section-title"><?php echo esc_html( $guru_cats[ $guru_list ] ); ?></div>
					<?php
					foreach ( array_slice( $guru_all, ( $guru_pg - 1 ) * $guru_per, $guru_per ) as $guru_row ) {
						guru_investor_doc_row( $guru_row['date'], $guru_row['title'], $guru_row['sub'], $guru_row['links'] );
					}
					?>
					<?php if ( $guru_pages > 1 ) : ?>
						<nav class="inv-pager" aria-label="<?php esc_attr_e( 'Pages', 'guru' ); ?>">
							<?php if ( $guru_pg > 1 ) : ?>
								<a class="inv-pager-step" href="<?php echo esc_url( guru_investor_list_url( $guru_list, $guru_pg - 1 ) . '#inv-list' ); ?>">&larr; <?php esc_html_e( 'Newer', 'guru' ); ?></a>
							<?php endif; ?>
							<?php
							$guru_gap = false;
							for ( $guru_n = 1; $guru_n <= $guru_pages; $guru_n++ ) {
								// First, last, and two either side of the current page; "…" for the rest.
								if ( 1 === $guru_n || $guru_pages === $guru_n || abs( $guru_n - $guru_pg ) <= 2 ) {
									$guru_gap = false;
									if ( $guru_n === $guru_pg ) {
										echo '<span class="inv-pager-num current" aria-current="page">' . (int) $guru_n . '</span>';
									} else {
										echo '<a class="inv-pager-num" href="' . esc_url( guru_investor_list_url( $guru_list, $guru_n ) . '#inv-list' ) . '">' . (int) $guru_n . '</a>';
									}
								} elseif ( ! $guru_gap ) {
									$guru_gap = true;
									echo '<span class="inv-pager-gap">&hellip;</span>';
								}
							}
							?>
							<?php if ( $guru_pg < $guru_pages ) : ?>
								<a class="inv-pager-step" href="<?php echo esc_url( guru_investor_list_url( $guru_list, $guru_pg + 1 ) . '#inv-list' ); ?>"><?php esc_html_e( 'Older', 'guru' ); ?> &rarr;</a>
							<?php endif; ?>
						</nav>
					<?php endif; ?>
				</div>

			<?php elseif ( $guru_has_docs ) : ?>
				<?php foreach ( $guru_cats as $guru_slug => $guru_label ) : ?>
					<?php
					if ( ! $guru_cat_rows[ $guru_slug ] ) {
						continue;
					}
					?>
					<div class="inv-docs">
						<div class="inv-section-title"><?php echo esc_html( $guru_label ); ?></div>
						<?php
						foreach ( array_slice( $guru_cat_rows[ $guru_slug ], 0, 6 ) as $guru_row ) {
							guru_investor_doc_row( $guru_row['date'], $guru_row['title'], $guru_row['sub'], $guru_row['links'] );
						}
						?>
						<?php if ( count( $guru_cat_rows[ $guru_slug ] ) > 6 ) : ?>
							<a class="inv-more-btn" href="<?php echo esc_url( guru_investor_list_url( $guru_slug ) . '#inv-list' ); ?>">
								<?php
								/* translators: %s: document category, e.g. "Last Announcement". */
								printf( esc_html__( 'View All %s', 'guru' ), esc_html( $guru_label ) );
								?>
								&rarr;
							</a>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
				<?php if ( guru_mod( 'guru_investor_more_url' ) ) : ?>
					<a class="inv-more-btn inv-more-btn-ext" href="<?php echo esc_url( guru_mod( 'guru_investor_more_url' ) ); ?>" target="_blank" rel="noopener">
						<?php esc_html_e( 'View All Announcements on HKEXnews', 'guru' ); ?> &#8599;
					</a>
				<?php endif; ?>

			<?php else : ?>
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
			<?php endif; ?>

			<?php if ( ! $guru_list && $guru_governance ) : ?>
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
								<?php // "City | E-mail | Address"; older lines held a telephone number here. ?>
								<?php if ( false !== strpos( $guru_c[1], '@' ) ) : ?>
									<a class="inv-contact-tel" href="mailto:<?php echo esc_attr( $guru_c[1] ); ?>"><?php echo esc_html( $guru_c[1] ); ?></a>
								<?php else : ?>
									<div class="inv-contact-tel"><?php echo esc_html( $guru_c[1] ); ?></div>
								<?php endif; ?>
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

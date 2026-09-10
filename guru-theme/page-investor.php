<?php
/**
 * Template Name: Investor
 *
 * Financial documents are edited in Customize → Guru site settings →
 * Investor documents, one per line:
 *   YYYY.MM.DD | Title | Subtitle (optional) | EN url | CN url
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guru_docs_default = "2025.11.13 | Interim Results Announcement | Six Months Ended 30 September 2025 | https://www.hkexnews.hk | https://www.hkexnews.hk\n"
	. "2025.11.13 | Interim Report 2025 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n"
	. "2025.06.30 | Annual Report 2025 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n"
	. "2024.11.14 | Interim Results Announcement | Six Months Ended 30 September 2024 | https://www.hkexnews.hk | https://www.hkexnews.hk\n"
	. "2024.11.14 | Interim Report 2024 | | https://www.hkexnews.hk | https://www.hkexnews.hk\n"
	. "2024.06.28 | Annual Report 2024 | | https://www.hkexnews.hk | https://www.hkexnews.hk";

$guru_docs = array_filter( array_map( 'trim', explode( "\n", get_theme_mod( 'guru_investor_docs', $guru_docs_default ) ) ) );

$guru_info = array(
	__( 'Stock Code', 'guru' )      => get_theme_mod( 'guru_stock_code', '08121' ),
	__( 'Exchange', 'guru' )        => get_theme_mod( 'guru_exchange', 'HKEX GEM' ),
	__( 'Board Lot', 'guru' )       => get_theme_mod( 'guru_board_lot', '2,000 shares' ),
	__( 'Fiscal Year End', 'guru' ) => get_theme_mod( 'guru_fiscal_year', '31 March' ),
);

while ( have_posts() ) :
	the_post();
	?>

	<div class="inv-hero">
		<p class="inv-hero-label"><?php esc_html_e( 'Investor Relations', 'guru' ); ?></p>
		<h1 class="inv-hero-title"><?php the_title(); ?></h1>
		<?php if ( get_the_excerpt() ) : ?>
			<p class="inv-hero-desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
		<?php endif; ?>
	</div>

	<div class="inv-body">
		<div class="inv-main">
			<?php if ( get_the_content() ) : ?>
				<div class="guru-editor"><?php the_content(); ?></div>
			<?php endif; ?>

			<p class="inv-section-title"><?php esc_html_e( 'Financial Results & Reports', 'guru' ); ?></p>

			<div class="inv-docs">
				<?php foreach ( $guru_docs as $guru_line ) : ?>
					<?php
					$guru_p   = array_map( 'trim', explode( '|', $guru_line ) );
					$guru_date = isset( $guru_p[0] ) ? $guru_p[0] : '';
					$guru_ttl  = isset( $guru_p[1] ) ? $guru_p[1] : '';
					$guru_sub  = isset( $guru_p[2] ) ? $guru_p[2] : '';
					$guru_en   = isset( $guru_p[3] ) ? $guru_p[3] : '';
					$guru_cn   = isset( $guru_p[4] ) ? $guru_p[4] : '';
					if ( ! $guru_ttl ) {
						continue;
					}
					?>
					<div class="inv-doc-item">
						<div class="inv-doc-date"><?php echo esc_html( $guru_date ); ?></div>
						<div class="inv-doc-main">
							<div class="inv-doc-title"><?php echo esc_html( $guru_ttl ); ?></div>
							<?php if ( $guru_sub ) : ?>
								<div class="inv-doc-sub"><?php echo esc_html( $guru_sub ); ?></div>
							<?php endif; ?>
							<div class="inv-doc-links">
								<?php if ( $guru_en ) : ?>
									<a href="<?php echo esc_url( $guru_en ); ?>" class="inv-doc-link" target="_blank" rel="noopener">EN &#8599;</a>
								<?php endif; ?>
								<?php if ( $guru_cn ) : ?>
									<a href="<?php echo esc_url( $guru_cn ); ?>" class="inv-doc-link" target="_blank" rel="noopener">中文 &#8599;</a>
								<?php endif; ?>
							</div>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</div>

		<aside class="inv-sidebar">
			<div class="inv-card">
				<p class="inv-card-title"><?php esc_html_e( 'Listing Information', 'guru' ); ?></p>
				<?php foreach ( $guru_info as $guru_k => $guru_v ) : ?>
					<?php if ( $guru_v ) : ?>
						<div class="inv-info-row">
							<span class="inv-info-key"><?php echo esc_html( $guru_k ); ?></span>
							<span class="inv-info-val"><?php echo esc_html( $guru_v ); ?></span>
						</div>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>

			<div class="inv-card">
				<p class="inv-card-title"><?php esc_html_e( 'Useful Links', 'guru' ); ?></p>
				<a href="https://www.hkexnews.hk" class="inv-ext-link" target="_blank" rel="noopener">
					<?php esc_html_e( 'HKEXnews Announcements', 'guru' ); ?> <span>&#8599;</span>
				</a>
				<a href="https://www.hkex.com.hk" class="inv-ext-link" target="_blank" rel="noopener">
					<?php esc_html_e( 'HKEX Stock Quote', 'guru' ); ?> <span>&#8599;</span>
				</a>
			</div>
		</aside>
	</div>

	<?php
endwhile;

get_footer();

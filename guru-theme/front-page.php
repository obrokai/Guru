<?php
/**
 * Homepage.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

$guru_solutions_url = guru_page_url( array( 'solutions', 'services' ), '/solutions/' );
$guru_about_url     = guru_page_url( 'about', '/about/' );
$guru_work_url      = get_post_type_archive_link( 'work' );
?>

<div class="gh-wrap">
	<section class="gh" aria-labelledby="home-title">
		<?php
		$guru_hero_desktop = guru_hero_video_url( 'desktop' );
		$guru_hero_mobile  = guru_hero_video_url( 'mobile' );
		if ( $guru_hero_desktop || $guru_hero_mobile ) :
			?>
			<video id="heroVideo" autoplay muted playsinline preload="auto" aria-hidden="true">
				<?php if ( $guru_hero_mobile ) : ?>
					<source src="<?php echo esc_url( $guru_hero_mobile ); ?>" media="(max-width: 900px)" type="video/mp4" />
				<?php endif; ?>
				<?php if ( $guru_hero_desktop ) : ?>
					<source src="<?php echo esc_url( $guru_hero_desktop ); ?>" type="video/mp4" />
				<?php endif; ?>
			</video>
		<?php endif; ?>

		<?php
		/*
		 * The homepage's one H1, in the HTML from the start (v1.3.0, client spec #1 / 6.1).
		 * Bottom-left, clear of the logo in the middle of the film; a local scrim behind
		 * it keeps it readable on bright and dark frames. Edited in Customize → Homepage.
		 */
		$guru_hero_title = guru_mod( 'guru_hero_title' );
		$guru_hero_intro = guru_mod( 'guru_hero_intro' );
		?>
		<div class="gh-bottom">
			<div class="gh-copy">
				<?php if ( $guru_hero_title ) : ?>
					<h1 class="gh-title" id="home-title"><?php echo esc_html( $guru_hero_title ); ?></h1>
				<?php endif; ?>
				<?php if ( $guru_hero_intro ) : ?>
					<p class="gh-intro"><?php echo esc_html( $guru_hero_intro ); ?></p>
				<?php endif; ?>
				<div class="gh-btns">
					<a href="#contact" class="gh-btn-solid">
						<?php echo esc_html( guru_mod( 'guru_hero_cta_contact' ) ); ?> <span aria-hidden="true" style="opacity:.7">&rarr;</span>
					</a>
					<?php // Scrolls to the Our Work block below, as on the static page. ?>
					<a href="#work" class="gh-btn-ghost"><?php echo esc_html( guru_mod( 'guru_hero_cta_work' ) ); ?></a>
					<a href="<?php echo esc_url( $guru_solutions_url ); ?>" class="gh-btn-ghost"><?php echo esc_html( guru_mod( 'guru_hero_cta_solutions' ) ); ?></a>
				</div>
			</div>
		</div>
	</section>
</div>

<?php get_template_part( 'template-parts/logo-ticker' ); ?>

<?php
/*
 * About block — heading, animated statistics and two paragraphs.
 * Edited in Customize → Guru site settings → Homepage.
 */
$guru_stats = guru_pipe_rows( guru_mod( 'guru_home_about_stats' ) );
$guru_paras = array_filter( array_map( 'trim', preg_split( '/\r?\n\s*\r?\n/', guru_mod( 'guru_home_about_copy' ) ) ) );
$guru_cta   = guru_mod( 'guru_home_about_cta' );
?>
<section id="about" class="abt">
	<div class="abt-inner">
		<div>
			<h2 class="abt-title"><?php echo esc_html( guru_mod( 'guru_home_about_title' ) ); ?></h2>
			<?php if ( $guru_stats ) : ?>
				<div class="abt-stats">
					<?php foreach ( $guru_stats as $guru_stat ) : ?>
						<?php
						$guru_num    = isset( $guru_stat[0] ) ? $guru_stat[0] : '';
						$guru_suffix = isset( $guru_stat[1] ) ? $guru_stat[1] : '';
						$guru_label  = isset( $guru_stat[2] ) ? $guru_stat[2] : '';
						if ( '' === $guru_num ) {
							continue;
						}
						?>
						<div class="abt-stat">
							<span class="abt-stat-num"
								<?php // Whole numbers animate; anything else (2.5, 10k) is shown as typed. ?>
								<?php if ( ctype_digit( $guru_num ) ) : ?>
									data-count="<?php echo esc_attr( $guru_num ); ?>" data-suffix="<?php echo esc_attr( $guru_suffix ); ?>"
								<?php endif; ?>><?php echo esc_html( $guru_num . $guru_suffix ); ?></span>
							<span class="abt-stat-text"><?php echo esc_html( $guru_label ); ?></span>
						</div>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
		</div>
		<div class="abt-right">
			<div class="abt-copy">
				<?php foreach ( $guru_paras as $guru_para ) : ?>
					<p class="abt-p"><?php echo esc_html( $guru_para ); ?></p>
				<?php endforeach; ?>
				<?php if ( $guru_cta ) : ?>
					<a href="<?php echo esc_url( $guru_about_url ); ?>" class="btn-outline-dark"><?php echo esc_html( $guru_cta ); ?> <span>&rarr;</span></a>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section id="services" class="svc">
	<div class="svc-inner">
		<div class="svc-label"><?php esc_html_e( 'What We Do', 'guru' ); ?></div>
		<div class="svc-layout">
			<div class="svc-intro">
				<h2 class="svc-title"><?php esc_html_e( 'Our Solutions', 'guru' ); ?></h2>
				<p class="svc-lead"><?php echo esc_html( guru_mod( 'guru_solutions_lead' ) ); ?></p>
				<a href="<?php echo esc_url( $guru_solutions_url ); ?>" class="svc-all-link">
					<?php esc_html_e( 'View All Solutions', 'guru' ); ?> <span class="svc-arrow">&rarr;</span>
				</a>
			</div>

			<div class="svc-accordion" id="svcAccordion">
				<?php
				$guru_solutions = new WP_Query( guru_solutions_query_args() );

				if ( $guru_solutions->have_posts() ) :
					while ( $guru_solutions->have_posts() ) :
						$guru_solutions->the_post();
						?>
						<div class="svc-acc-item">
							<?php // H3 under the "Our Solutions" H2; the button inside keeps the row operable (client spec #1). ?>
							<h3 class="svc-acc-h">
								<button class="svc-acc-head" type="button" aria-expanded="false">
									<span class="svc-acc-name"><?php the_title(); ?></span>
									<svg class="svc-acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
										<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
									</svg>
								</button>
							</h3>
							<div class="svc-acc-body">
								<div class="svc-acc-inner">
									<p class="svc-acc-desc"><?php echo esc_html( get_the_excerpt() ); ?></p>
									<a href="<?php echo esc_url( $guru_solutions_url . '#' . get_post_field( 'post_name' ) ); ?>" class="svc-acc-link">
										<?php esc_html_e( 'Explore', 'guru' ); ?> <span>&rarr;</span>
									</a>
								</div>
							</div>
						</div>
						<?php
					endwhile;
					wp_reset_postdata();
				else :
					?>
					<p class="svc-acc-desc">
						<?php esc_html_e( 'No solutions have been added yet. Add them under Solutions in the admin.', 'guru' ); ?>
					</p>
				<?php endif; ?>
			</div>
		</div>
	</div>
</section>

<section id="work" class="wrk">
	<div class="wrk-inner">
		<div class="wrk-header">
			<div class="wrk-header-left">
				<h2 class="wrk-title"><?php esc_html_e( 'Our Work', 'guru' ); ?></h2>
			</div>
			<div class="wrk-header-right">
				<a href="<?php echo esc_url( $guru_work_url ); ?>" class="wrk-all">
					<?php esc_html_e( 'All Projects', 'guru' ); ?> <span class="wrk-arr">&rarr;</span>
				</a>
			</div>
		</div>

		<div class="wrk-grid">
			<?php
			$guru_featured = guru_home_works();

			if ( $guru_featured ) :
				$guru_i  = 0;
				$guru_ph = 0;
				foreach ( $guru_featured as $post ) : // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
					setup_postdata( $post );
					$guru_i++;
					$guru_data  = guru_get_work_data();
					$guru_size  = ( 1 === $guru_i ) ? 'wrk-card-large' : 'wrk-card-small';
					$guru_class = $guru_data['video'] ? 'hw-video-card' : 'hw-info-card';
					?>
					<div class="wrk-card <?php echo esc_attr( $guru_size . ' ' . $guru_class ); ?>"
						<?php if ( $guru_data['video'] ) : ?>
							data-video="<?php echo esc_url( $guru_data['video'] ); ?>"
							<?php echo guru_gallery_attr( $guru_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper; pictures after the film (v1.3.1). ?>
						<?php else : ?>
							data-url="<?php echo esc_url( get_permalink() ); ?>"
							<?php echo guru_gallery_attr( $guru_data ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- escaped in the helper. ?>
						<?php endif; ?>
						data-title="<?php echo esc_attr( $guru_data['title'] ); ?>"
						data-sub="<?php echo esc_attr( implode( ' · ', $guru_data['tags'] ) ); ?>">

						<?php if ( $guru_data['video'] ) : ?>
							<div class="wrk-video-card">
								<?php // Loaded and played only while on screen (main.js) so the homepage opens fast. ?>
								<video muted loop playsinline preload="none" data-lazy-play>
									<source src="<?php echo esc_url( $guru_data['video'] ); ?>" type="video/mp4" />
								</video>
								<?php /* translators: %s: project title. */ ?>
								<button type="button" class="wrk-play-btn" aria-label="<?php echo esc_attr( sprintf( __( 'Play film: %s', 'guru' ), $guru_data['title'] ) ); ?>"></button>
								<div class="wrk-card-hover-label"><?php esc_html_e( 'Watch Film ↗', 'guru' ); ?></div>
							</div>
						<?php else : ?>
							<div class="wrk-card-img">
								<?php if ( $guru_data['image'] ) : ?>
									<img src="<?php echo esc_url( $guru_data['image'] ); ?>"
										alt="<?php echo esc_attr( $guru_data['title'] ); ?>" loading="lazy" />
								<?php else : ?>
									<?php // Two placeholder gradients, alternating like the static page; the title is drawn on it via CSS. ?>
									<div class="wrk-img-ph<?php echo ( $guru_ph++ % 2 ) ? ' wrk-img-ph-2' : ''; ?>" data-title="<?php echo esc_attr( $guru_data['title'] ); ?>"></div>
								<?php endif; ?>
								<div class="wrk-card-hover-label"><?php esc_html_e( 'View Project ↗', 'guru' ); ?></div>
							</div>
						<?php endif; ?>

						<div class="wrk-card-info">
							<?php if ( $guru_data['tags'] ) : ?>
								<div class="wrk-card-meta">
									<?php foreach ( $guru_data['tags'] as $guru_tag ) : ?>
										<span class="wrk-tag"><?php echo esc_html( $guru_tag ); ?></span>
									<?php endforeach; ?>
								</div>
							<?php endif; ?>
							<?php // A real link to the project page, so search engines and keyboards can reach it (client spec #13). ?>
							<h3 class="wrk-card-title"><a class="wrk-card-link" href="<?php echo esc_url( get_permalink() ); ?>"><?php echo esc_html( $guru_data['title'] ); ?></a></h3>
						</div>
					</div>
					<?php
				endforeach;
				wp_reset_postdata();
			else :
				?>
				<p><?php esc_html_e( 'No showcase items yet. Add them under Showcase in the admin.', 'guru' ); ?></p>
			<?php endif; ?>
		</div>
	</div>
</section>

<?php get_template_part( 'template-parts/work-modal' ); ?>

<?php
get_footer();

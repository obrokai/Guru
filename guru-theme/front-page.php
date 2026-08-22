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

$guru_solutions_page = get_page_by_path( 'services' );
$guru_solutions_url  = $guru_solutions_page ? get_permalink( $guru_solutions_page ) : home_url( '/services/' );
$guru_work_url       = get_post_type_archive_link( 'work' );
?>

<div class="gh-wrap">
	<section class="gh">
		<video id="heroVideo" autoplay muted loop playsinline preload="auto">
			<source src="<?php echo guru_hero_video_url( 'mobile' ); ?>" media="(max-width: 900px)" type="video/mp4" />
			<source src="<?php echo guru_hero_video_url( 'desktop' ); ?>" type="video/mp4" />
		</video>

		<div class="gh-bottom">
			<div class="gh-btns">
				<a href="<?php echo esc_url( $guru_work_url ); ?>" class="gh-btn-solid">
					<?php esc_html_e( 'Our Works', 'guru' ); ?> <span style="opacity:.7">&rarr;</span>
				</a>
				<a href="<?php echo esc_url( $guru_solutions_url ); ?>" class="gh-btn-ghost">
					<?php esc_html_e( 'Our Services', 'guru' ); ?>
				</a>
			</div>
		</div>
	</section>
</div>

<?php get_template_part( 'template-parts/logo-ticker' ); ?>

<section id="services" class="svc">
	<div class="svc-inner">
		<div class="svc-label"><?php esc_html_e( 'What We Do', 'guru' ); ?></div>
		<div class="svc-layout">
			<div class="svc-intro">
				<h2 class="svc-title"><?php esc_html_e( 'Our Solutions', 'guru' ); ?></h2>
				<p class="svc-lead">
					<?php
					echo esc_html(
						get_theme_mod(
							'guru_solutions_lead',
							__( 'We craft integrated digital marketing solutions for ambitious brands — blending strategy, creativity, media, data, technology and AI into measurable growth.', 'guru' )
						)
					);
					?>
				</p>
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
							<button class="svc-acc-head" type="button">
								<span class="svc-acc-name"><?php the_title(); ?></span>
								<svg class="svc-acc-chevron" viewBox="0 0 24 24" fill="none" aria-hidden="true">
									<path d="M6 9l6 6 6-6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
								</svg>
							</button>
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
			$guru_featured = new WP_Query(
				array(
					'post_type'      => 'work',
					'posts_per_page' => 4,
					'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
				)
			);

			if ( $guru_featured->have_posts() ) :
				$guru_i = 0;
				while ( $guru_featured->have_posts() ) :
					$guru_featured->the_post();
					$guru_i++;
					$guru_data  = guru_get_work_data();
					$guru_size  = ( 1 === $guru_i ) ? 'wrk-card-large' : 'wrk-card-small';
					$guru_class = $guru_data['video'] ? 'hw-video-card' : 'hw-info-card';
					?>
					<div class="wrk-card <?php echo esc_attr( $guru_size . ' ' . $guru_class ); ?>"
						<?php if ( $guru_data['video'] ) : ?>
							data-video="<?php echo esc_url( $guru_data['video'] ); ?>"
						<?php endif; ?>
						data-title="<?php echo esc_attr( $guru_data['title'] ); ?>"
						data-sub="<?php echo esc_attr( implode( ' · ', $guru_data['tags'] ) ); ?>">

						<?php if ( $guru_data['video'] ) : ?>
							<div class="wrk-video-card">
								<video autoplay muted loop playsinline preload="metadata">
									<source src="<?php echo esc_url( $guru_data['video'] ); ?>" type="video/mp4" />
								</video>
								<div class="wrk-play-btn"></div>
								<div class="wrk-card-hover-label"><?php esc_html_e( 'Watch Film ↗', 'guru' ); ?></div>
							</div>
						<?php else : ?>
							<div class="wrk-card-img">
								<?php if ( $guru_data['image'] ) : ?>
									<img src="<?php echo esc_url( $guru_data['image'] ); ?>"
										alt="<?php echo esc_attr( $guru_data['title'] ); ?>" loading="lazy" />
								<?php else : ?>
									<div class="wrk-img-ph"></div>
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
							<h3 class="wrk-card-title"><?php echo esc_html( $guru_data['title'] ); ?></h3>
						</div>
					</div>
					<?php
				endwhile;
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

<?php
/**
 * Template helpers.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Permalink of the first page found for the given slugs, else a fallback path.
 *
 * The Solutions page is created with the slug "solutions", but older installs
 * (and the static site) used "services" — accept both.
 *
 * @param string|string[] $slugs    Page slugs to try, in order.
 * @param string          $fallback Home-relative path when no page exists.
 * @return string
 */
function guru_page_url( $slugs, $fallback = '/', $template = '' ) {
	foreach ( (array) $slugs as $slug ) {
		$page = get_page_by_path( $slug );
		if ( $page && 'publish' === $page->post_status ) {
			return get_permalink( $page );
		}
	}

	// No page with that slug — find the page using the template instead.
	if ( $template ) {
		$pages = get_pages(
			array(
				'meta_key'   => '_wp_page_template', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
				'meta_value' => $template, // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_value
				'number'     => 1,
			)
		);
		if ( $pages ) {
			return get_permalink( $pages[0] );
		}
	}

	return home_url( $fallback );
}

/**
 * ID of the navigation menu the importer created ("Guru Primary"), or 0.
 *
 * @return int
 */
function guru_menu_id() {
	$id = (int) get_option( 'guru_menu_id' );
	return ( $id && wp_get_nav_menu_object( $id ) ) ? $id : 0;
}

/**
 * Whether the header should render the WordPress menu: only when the primary
 * location points at the theme's own menu. A menu inherited from the previous
 * theme (WordPress maps it across on switch) is ignored in favour of the
 * built-in links, so the navigation is right from the moment of activation.
 *
 * @return bool
 */
function guru_use_wp_menu() {
	$menu_id = guru_menu_id();
	if ( ! $menu_id ) {
		return false;
	}
	$locations = get_nav_menu_locations();
	return ! empty( $locations['primary'] ) && (int) $locations['primary'] === $menu_id;
}

/**
 * The site's five navigation items with resolved URLs and active state.
 *
 * Used by the mobile menu and the footer, and by the header until a Primary
 * menu has been assigned under Appearance → Menus.
 *
 * @param array $labels Optional label overrides keyed by about/work/solutions/blog/investor.
 * @return array[] Each: [ 'label' => string, 'url' => string, 'active' => bool ].
 */
function guru_nav_items( $labels = array() ) {
	$blog_id  = (int) get_option( 'page_for_posts' );
	$blog_url = $blog_id ? get_permalink( $blog_id ) : guru_page_url( 'blog', '/blog/' );
	$label    = static function ( $key, $default ) use ( $labels ) {
		return isset( $labels[ $key ] ) ? $labels[ $key ] : $default;
	};

	return array(
		array(
			'label'  => $label( 'about', __( 'About', 'guru' ) ),
			'url'    => guru_page_url( 'about', '/about/', 'page-about.php' ),
			'active' => is_page_template( 'page-about.php' ),
		),
		array(
			'label'  => $label( 'work', __( 'Work', 'guru' ) ),
			'url'    => get_post_type_archive_link( 'work' ),
			'active' => is_post_type_archive( 'work' ) || is_singular( 'work' ) || is_tax( 'work_category' ),
		),
		array(
			'label'  => $label( 'solutions', __( 'Solutions', 'guru' ) ),
			'url'    => guru_page_url( array( 'solutions', 'services' ), '/solutions/', 'page-services.php' ),
			'active' => is_page_template( 'page-services.php' ) || is_singular( 'solution' ),
		),
		array(
			'label'  => $label( 'blog', __( 'Blog', 'guru' ) ),
			'url'    => $blog_url,
			'active' => ( is_home() && ! is_front_page() ) || is_singular( 'post' ) || is_category(),
		),
		array(
			'label'  => $label( 'investor', __( 'Investor', 'guru' ) ),
			'url'    => guru_page_url( 'investor', '/investor/', 'page-investor.php' ),
			'active' => is_page_template( 'page-investor.php' ),
		),
	);
}

/**
 * Escaped theme asset URL.
 *
 * @param string $path Path relative to the theme's assets folder.
 * @return string
 */
function guru_asset( $path ) {
	return esc_url( get_stylesheet_directory_uri() . '/assets/' . ltrim( $path, '/' ) );
}

/**
 * URL of a file under assets/media/, or '' when it is not shipped
 * (the slim package omits the large videos).
 *
 * @param string $file File name, e.g. "hero-bg.mp4".
 * @return string
 */
function guru_theme_media_url( $file ) {
	$file = ltrim( $file, '/' );
	return file_exists( get_stylesheet_directory() . '/assets/media/' . $file ) ? guru_asset( 'media/' . $file ) : '';
}

/**
 * Data for the current showcase item, normalised for the card templates.
 *
 * @param int|null $post_id Optional post ID.
 * @return array
 */
function guru_get_work_data( $post_id = null ) {
	$post_id = $post_id ? $post_id : get_the_ID();

	$terms = get_the_terms( $post_id, 'work_category' );
	$terms = is_wp_error( $terms ) || ! $terms ? array() : $terms;

	$slugs = array();
	$names = array();
	foreach ( $terms as $term ) {
		$slugs[] = $term->slug;
		$names[] = $term->name;
		// A child category also matches its parent's filter button.
		foreach ( get_ancestors( $term->term_id, 'work_category', 'taxonomy' ) as $ancestor_id ) {
			$ancestor = get_term( $ancestor_id, 'work_category' );
			if ( $ancestor && ! is_wp_error( $ancestor ) ) {
				$slugs[] = $ancestor->slug;
			}
		}
	}

	// Card labels: the item's own tags when set (as on the static site), else its categories.
	$tags = get_post_meta( $post_id, '_guru_tags', true );
	$tags = is_array( $tags ) ? array_values( array_filter( array_map( 'strval', $tags ) ) ) : array();

	return array(
		'title'  => get_the_title( $post_id ),
		'client' => (string) get_post_meta( $post_id, '_guru_client', true ),
		'video'  => (string) get_post_meta( $post_id, '_guru_video_url', true ),
		'image'  => get_the_post_thumbnail_url( $post_id, 'large' ),
		'cats'   => implode( ' ', array_unique( $slugs ) ),
		'tags'   => $tags ? $tags : $names,
	);
}

/**
 * The four showcase items shown on the homepage.
 *
 * Items with a "Homepage position" (1–4) come first, in that order; any
 * remaining slots are filled by the newest items in menu order.
 *
 * @return WP_Post[]
 */
function guru_home_works() {
	$picked = get_posts(
		array(
			'post_type'      => 'work',
			'posts_per_page' => 4,
			'meta_key'       => '_guru_home_order', // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_key
			// Two items with the same number should not happen (saving a slot
			// releases it from any other item), but if it does, the one edited
			// most recently wins rather than the older post.
			'orderby'        => array(
				'meta_value_num' => 'ASC',
				'modified'       => 'DESC',
			),
		)
	);

	if ( count( $picked ) < 4 ) {
		$fill = get_posts(
			array(
				'post_type'      => 'work',
				'posts_per_page' => 4 - count( $picked ),
				'post__not_in'   => wp_list_pluck( $picked, 'ID' ),
				'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'DESC' ),
			)
		);
		$picked = array_merge( $picked, $fill );
	}

	return $picked;
}

/**
 * One item per homepage slot. When $post_id takes slot $slot, every other
 * showcase item holding that number is moved off the homepage (its position
 * is cleared; it stays in the Work list). Returns the demoted post IDs so the
 * admin screen can say what happened.
 *
 * @param int $post_id The item claiming the slot.
 * @param int $slot    1–4.
 * @return int[]
 */
function guru_claim_home_slot( $post_id, $slot ) {
	$others = get_posts(
		array(
			'post_type'      => 'work',
			'post_status'    => 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'post__not_in'   => array( (int) $post_id ),
			'no_found_rows'  => true,
			'meta_query'     => array( // phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				array(
					'key'     => '_guru_home_order',
					'value'   => (int) $slot,
					'type'    => 'NUMERIC',
					'compare' => '=',
				),
			),
		)
	);

	foreach ( $others as $other_id ) {
		delete_post_meta( $other_id, '_guru_home_order' );
	}

	return array_map( 'intval', $others );
}

/**
 * Render one showcase card for the work archive — same markup as the static
 * site: .prj-img holds the film / cover / coloured placeholder, and cards fade
 * in one after another. Every card is the same 4:3 size — the static page's
 * 16:9 "large" variant was dropped at the client's request (2026-09-22).
 *
 * @param int|null $post_id Optional post ID.
 * @param int      $index   Position in the grid (0-based).
 */
function guru_work_card( $post_id = null, $index = 0 ) {
	$data = guru_get_work_data( $post_id );
	$sub  = $data['client'];
	if ( $data['tags'] ) {
		$sub .= ( $sub ? ' · ' : '' ) . implode( ' · ', $data['tags'] );
	}
	?>
	<div class="prj-card"
		data-cats="<?php echo esc_attr( $data['cats'] ); ?>"
		<?php if ( $data['video'] ) : ?>
			data-video="<?php echo esc_url( $data['video'] ); ?>"
		<?php else : ?>
			<?php // No film to play — the "View Project" label opens the item's own page. ?>
			data-url="<?php echo esc_url( get_permalink( $post_id ? $post_id : get_the_ID() ) ); ?>"
		<?php endif; ?>
		data-title="<?php echo esc_attr( $data['title'] ); ?>"
		data-client="<?php echo esc_attr( $data['client'] ); ?>"
		data-tags="<?php echo esc_attr( implode( '|', $data['tags'] ) ); ?>"
		data-sub="<?php echo esc_attr( $sub ); ?>"
		style="animation-delay:<?php echo esc_attr( number_format( 0.38 + $index * 0.06, 2 ) ); ?>s">

		<div class="prj-img">
			<?php if ( $data['video'] ) : ?>
				<video class="prj-video" src="<?php echo esc_url( $data['video'] ); ?>" autoplay muted loop playsinline preload="metadata"></video>
				<div class="prj-play-btn"></div>
				<div class="prj-hover-label"><?php esc_html_e( 'Watch Film ↗', 'guru' ); ?></div>
			<?php elseif ( $data['image'] ) : ?>
				<img src="<?php echo esc_url( $data['image'] ); ?>"
					alt="<?php echo esc_attr( $data['title'] ); ?>" loading="lazy" />
				<div class="prj-hover-label"><?php esc_html_e( 'View Project ↗', 'guru' ); ?></div>
			<?php else : ?>
				<div class="prj-img-ph c<?php echo (int) ( ( $index % 12 ) + 1 ); ?>"></div>
				<div class="prj-hover-label"><?php esc_html_e( 'View Project ↗', 'guru' ); ?></div>
			<?php endif; ?>
		</div>

		<div class="prj-info">
			<?php if ( $data['tags'] ) : ?>
				<div class="prj-tags">
					<?php foreach ( $data['tags'] as $tag ) : ?>
						<span class="prj-tag"><?php echo esc_html( $tag ); ?></span>
					<?php endforeach; ?>
				</div>
			<?php endif; ?>
			<h3 class="prj-title"><?php echo esc_html( $data['title'] ); ?></h3>
			<?php if ( $data['client'] ) : ?>
				<p class="prj-client"><?php echo esc_html( $data['client'] ); ?></p>
			<?php endif; ?>
		</div>
	</div>
	<?php
}

/**
 * Filter buttons for the work archive, built from the taxonomy.
 */
function guru_work_filters() {
	$terms = get_terms(
		array(
			'taxonomy'   => 'work_category',
			'hide_empty' => true,
		)
	);

	if ( is_wp_error( $terms ) || ! $terms ) {
		return;
	}

	// On a category archive the matching button starts out selected.
	$current = 'all';
	if ( is_tax( 'work_category' ) ) {
		$queried = get_queried_object();
		if ( $queried && ! empty( $queried->slug ) ) {
			$current = $queried->slug;
		}
	}
	?>
	<div class="wrk-filters" id="filterBar">
		<button class="filter-btn<?php echo 'all' === $current ? ' active' : ''; ?>" data-filter="all"><?php esc_html_e( 'All', 'guru' ); ?></button>
		<?php foreach ( $terms as $term ) : ?>
			<button class="filter-btn<?php echo $term->slug === $current ? ' active' : ''; ?>" data-filter="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php
}

/**
 * Image files in a folder (png/jpg/jpeg/webp/svg), sorted — without GLOB_BRACE,
 * which some PHP builds lack.
 *
 * @param string $dir Absolute folder path.
 * @return string[] Absolute file paths.
 */
function guru_image_files( $dir ) {
	if ( ! is_dir( $dir ) ) {
		return array();
	}
	$files = array();
	foreach ( (array) scandir( $dir ) as $name ) {
		if ( preg_match( '/\.(png|jpe?g|webp|svg)$/i', $name ) ) {
			$files[] = $dir . '/' . $name;
		}
	}
	sort( $files );
	return $files;
}

/* -------------------------------------------------------------------------
 * Solutions — media block and highlight cards
 * ---------------------------------------------------------------------- */

/**
 * Media types a solution panel can show.
 *
 * @return array type => label.
 */
function guru_solution_media_types() {
	return array(
		''         => __( 'None', 'guru' ),
		'phones'   => __( 'Phone mockups (images, side by side)', 'guru' ),
		'carousel' => __( 'Carousel (videos or images)', 'guru' ),
		'video'    => __( 'Single video', 'guru' ),
	);
}

/**
 * Normalised media + highlights for a solution.
 *
 * @param int $post_id Solution ID.
 * @return array { label, type, items[], highlights[] }
 */
function guru_get_solution_media( $post_id ) {
	$type  = (string) get_post_meta( $post_id, '_guru_media_type', true );
	$items = get_post_meta( $post_id, '_guru_media_items', true );
	$hl    = get_post_meta( $post_id, '_guru_highlights', true );

	return array(
		'label'      => (string) get_post_meta( $post_id, '_guru_media_label', true ),
		'type'       => array_key_exists( $type, guru_solution_media_types() ) ? $type : '',
		'items'      => is_array( $items ) ? array_values( array_filter( $items, 'is_array' ) ) : array(),
		'highlights' => is_array( $hl ) ? array_values( array_filter( $hl, 'is_array' ) ) : array(),
	);
}

/**
 * Whether a URL points at a video file.
 *
 * @param string $url URL.
 * @return bool
 */
function guru_is_video_url( $url ) {
	$path = (string) wp_parse_url( $url, PHP_URL_PATH );
	return (bool) preg_match( '/\.(mp4|webm|mov|m4v)$/i', $path );
}

/**
 * Output a solution's media block and highlight cards — same markup as the
 * static site, so the Solutions stylesheet applies unchanged.
 *
 * @param int $post_id Solution ID.
 */
function guru_solution_media( $post_id ) {
	$m     = guru_get_solution_media( $post_id );
	$items = array();

	foreach ( $m['items'] as $item ) {
		if ( ! empty( $item['src'] ) ) {
			$items[] = $item;
		}
	}

	if ( $m['type'] && $items ) :
		if ( $m['label'] ) :
			?>
			<div class="srv-media-label"><?php echo esc_html( $m['label'] ); ?></div>
			<?php
		endif;

		if ( 'phones' === $m['type'] ) :
			?>
			<div class="srv-phones-row">
				<?php foreach ( $items as $item ) : ?>
					<div class="srv-phone-wrap">
						<div class="srv-phone">
							<div class="srv-phone-screen">
								<img src="<?php echo esc_url( $item['src'] ); ?>"
									alt="<?php echo esc_attr( isset( $item['alt'] ) ? $item['alt'] : '' ); ?>" draggable="false" loading="lazy" />
							</div>
						</div>
						<?php if ( ! empty( $item['caption'] ) ) : ?>
							<p class="srv-phone-cap"><?php echo esc_html( $item['caption'] ); ?></p>
						<?php endif; ?>
					</div>
				<?php endforeach; ?>
			</div>
			<?php
		elseif ( 'carousel' === $m['type'] ) :
			?>
			<div class="srv-carousel-outer">
				<div class="srv-carousel-track-wrap">
					<div class="srv-carousel-track">
						<?php foreach ( $items as $item ) : ?>
							<div class="srv-carousel-slide">
								<?php if ( guru_is_video_url( $item['src'] ) ) : ?>
									<video muted loop playsinline preload="none">
										<source src="<?php echo esc_url( $item['src'] ); ?>" type="video/mp4" />
									</video>
								<?php else : ?>
									<img src="<?php echo esc_url( $item['src'] ); ?>"
										alt="<?php echo esc_attr( isset( $item['alt'] ) ? $item['alt'] : '' ); ?>" loading="lazy" />
								<?php endif; ?>
							</div>
						<?php endforeach; ?>
					</div>
					<?php if ( count( $items ) > 1 ) : ?>
						<button class="srv-carousel-btn srv-carousel-prev" type="button" aria-label="<?php esc_attr_e( 'Previous', 'guru' ); ?>">&#8592;</button>
						<button class="srv-carousel-btn srv-carousel-next" type="button" aria-label="<?php esc_attr_e( 'Next', 'guru' ); ?>">&#8594;</button>
						<span class="srv-carousel-count">1 / <?php echo count( $items ); ?></span>
					<?php endif; ?>
				</div>
				<?php if ( count( $items ) > 1 ) : ?>
					<div class="srv-carousel-dots">
						<?php foreach ( $items as $i => $item ) : ?>
							<div class="srv-carousel-dot<?php echo 0 === $i ? ' active' : ''; ?>" data-idx="<?php echo (int) $i; ?>"></div>
						<?php endforeach; ?>
					</div>
				<?php endif; ?>
			</div>
			<?php
		elseif ( 'video' === $m['type'] ) :
			?>
			<div class="srv-video-wrap">
				<video autoplay muted loop playsinline preload="metadata">
					<source src="<?php echo esc_url( $items[0]['src'] ); ?>" type="video/mp4" />
				</video>
			</div>
			<?php
		endif;
	endif;

	if ( $m['highlights'] ) :
		?>
		<div class="srv-highlights">
			<?php foreach ( $m['highlights'] as $h ) : ?>
				<?php
				if ( empty( $h['title'] ) ) {
					continue;
				}
				?>
				<div class="srv-highlight">
					<?php if ( ! empty( $h['icon'] ) ) : ?>
						<div class="srv-highlight-icon"><?php echo esc_html( $h['icon'] ); ?></div>
					<?php endif; ?>
					<div class="srv-highlight-title"><?php echo esc_html( $h['title'] ); ?></div>
					<?php if ( ! empty( $h['desc'] ) ) : ?>
						<div class="srv-highlight-desc"><?php echo esc_html( $h['desc'] ); ?></div>
					<?php endif; ?>
				</div>
			<?php endforeach; ?>
		</div>
		<?php
	endif;
}

/**
 * Outputs primary-menu items as the flat <a class="gn-link"> markup the
 * stylesheet expects — no <ul>/<li> wrappers.
 */
class Guru_Nav_Walker extends Walker_Nav_Menu {

	/** No sub-menus in this design. */
	public function start_lvl( &$output, $depth = 0, $args = null ) {}

	/** No sub-menus in this design. */
	public function end_lvl( &$output, $depth = 0, $args = null ) {}

	/**
	 * @param string   $output Walker output.
	 * @param WP_Post  $item   Menu item.
	 * @param int      $depth  Depth.
	 * @param stdClass $args   Args.
	 * @param int      $id     Item ID.
	 */
	public function start_el( &$output, $item, $depth = 0, $args = null, $id = 0 ) {
		$classes = array( 'gn-link' );

		if ( in_array( 'current-menu-item', (array) $item->classes, true )
			|| in_array( 'current_page_item', (array) $item->classes, true ) ) {
			$classes[] = 'active';
		}

		$output .= sprintf(
			'<a href="%1$s" class="%2$s"%3$s>%4$s</a>',
			esc_url( $item->url ),
			esc_attr( implode( ' ', $classes ) ),
			$item->target ? ' target="' . esc_attr( $item->target ) . '" rel="noopener"' : '',
			esc_html( $item->title )
		);
	}

	/**
	 * @param string  $output Walker output.
	 * @param WP_Post $item   Menu item.
	 * @param int     $depth  Depth.
	 * @param array   $args   Args.
	 */
	public function end_el( &$output, $item, $depth = 0, $args = null ) {}
}

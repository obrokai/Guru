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
	}

	return array(
		'title'  => get_the_title( $post_id ),
		'client' => (string) get_post_meta( $post_id, '_guru_client', true ),
		'video'  => (string) get_post_meta( $post_id, '_guru_video_url', true ),
		'image'  => get_the_post_thumbnail_url( $post_id, 'large' ),
		'cats'   => implode( ' ', $slugs ),
		'tags'   => $names,
	);
}

/**
 * Render one showcase card for the work archive.
 *
 * @param int|null $post_id Optional post ID.
 */
function guru_work_card( $post_id = null ) {
	$data = guru_get_work_data( $post_id );
	?>
	<div class="prj-card"
		data-cats="<?php echo esc_attr( $data['cats'] ); ?>"
		<?php if ( $data['video'] ) : ?>
			data-video="<?php echo esc_url( $data['video'] ); ?>"
		<?php endif; ?>
		data-title="<?php echo esc_attr( $data['title'] ); ?>"
		data-client="<?php echo esc_attr( $data['client'] ); ?>"
		data-tags="<?php echo esc_attr( implode( ', ', $data['tags'] ) ); ?>">

		<div class="prj-media">
			<?php if ( $data['video'] ) : ?>
				<video muted loop playsinline preload="metadata">
					<source src="<?php echo esc_url( $data['video'] ); ?>" type="video/mp4" />
				</video>
				<div class="prj-play"></div>
			<?php elseif ( $data['image'] ) : ?>
				<img src="<?php echo esc_url( $data['image'] ); ?>"
					alt="<?php echo esc_attr( $data['title'] ); ?>" loading="lazy" />
			<?php else : ?>
				<div class="prj-img-ph"></div>
			<?php endif; ?>
			<div class="prj-hover-label"><?php esc_html_e( 'View Project ↗', 'guru' ); ?></div>
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
	?>
	<div class="wrk-filters" id="filterBar">
		<button class="filter-btn active" data-filter="all"><?php esc_html_e( 'All', 'guru' ); ?></button>
		<?php foreach ( $terms as $term ) : ?>
			<button class="filter-btn" data-filter="<?php echo esc_attr( $term->slug ); ?>">
				<?php echo esc_html( $term->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
	<?php
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

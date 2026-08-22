<?php
/**
 * Custom post types and taxonomies.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Showcase / Work post type.
 *
 * This is the thing the client adds to most often, so it gets a top-level
 * admin menu item rather than living under a submenu.
 */
function guru_register_work_post_type() {
	$labels = array(
		'name'               => __( 'Showcase', 'guru' ),
		'singular_name'      => __( 'Showcase item', 'guru' ),
		'menu_name'          => __( 'Showcase', 'guru' ),
		'add_new'            => __( 'Add new', 'guru' ),
		'add_new_item'       => __( 'Add new showcase item', 'guru' ),
		'edit_item'          => __( 'Edit showcase item', 'guru' ),
		'new_item'           => __( 'New showcase item', 'guru' ),
		'view_item'          => __( 'View showcase item', 'guru' ),
		'search_items'       => __( 'Search showcase', 'guru' ),
		'not_found'          => __( 'No showcase items yet', 'guru' ),
		'not_found_in_trash' => __( 'No showcase items in trash', 'guru' ),
		'featured_image'     => __( 'Cover image', 'guru' ),
		'set_featured_image' => __( 'Set cover image', 'guru' ),
	);

	register_post_type(
		'work',
		array(
			'labels'        => $labels,
			'public'        => true,
			'has_archive'   => true,
			'menu_icon'     => 'dashicons-portfolio',
			'menu_position' => 5,
			'supports'      => array( 'title', 'editor', 'thumbnail', 'excerpt', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'work' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'guru_register_work_post_type' );

/**
 * Showcase categories — these drive the filter buttons on the work archive.
 */
function guru_register_work_taxonomy() {
	register_taxonomy(
		'work_category',
		'work',
		array(
			'labels'            => array(
				'name'          => __( 'Showcase categories', 'guru' ),
				'singular_name' => __( 'Showcase category', 'guru' ),
				'menu_name'     => __( 'Categories', 'guru' ),
				'add_new_item'  => __( 'Add new category', 'guru' ),
			),
			'public'            => true,
			'hierarchical'      => true,
			'show_admin_column' => true,
			'show_in_rest'      => true,
			'rewrite'           => array( 'slug' => 'work-category' ),
		)
	);
}
add_action( 'init', 'guru_register_work_taxonomy' );

/**
 * Seed the default showcase categories on theme activation so the filter bar
 * matches the current site out of the box. Existing terms are left alone.
 */
function guru_seed_work_categories() {
	$defaults = array(
		'china-digital' => __( 'China Digital', 'guru' ),
		'influencer'    => __( 'Influencer', 'guru' ),
		'social'        => __( 'Social Media', 'guru' ),
		'video'         => __( 'Video', 'guru' ),
		'campaign'      => __( 'Campaign & PR', 'guru' ),
		'go-global'     => __( 'Go-Global', 'guru' ),
	);

	foreach ( $defaults as $slug => $name ) {
		if ( ! term_exists( $slug, 'work_category' ) ) {
			wp_insert_term( $name, 'work_category', array( 'slug' => $slug ) );
		}
	}
}
add_action( 'after_switch_theme', 'guru_seed_work_categories' );

/**
 * Flush rewrite rules once when the theme is activated, so /work/ resolves
 * without the client having to re-save permalinks.
 */
function guru_flush_rewrites() {
	guru_register_work_post_type();
	guru_register_work_taxonomy();
	flush_rewrite_rules();
}
add_action( 'after_switch_theme', 'guru_flush_rewrites', 20 );

/**
 * Show newest showcase items first, and show them all on the archive.
 */
function guru_work_archive_query( $query ) {
	if ( is_admin() || ! $query->is_main_query() ) {
		return;
	}
	if ( $query->is_post_type_archive( 'work' ) || $query->is_tax( 'work_category' ) ) {
		$query->set( 'posts_per_page', -1 );
		$query->set( 'orderby', array( 'menu_order' => 'ASC', 'date' => 'DESC' ) );
	}
}
add_action( 'pre_get_posts', 'guru_work_archive_query' );

/**
 * Solutions / services.
 *
 * One editable record per solution, used by both the homepage accordion and
 * the Solutions page, so the client only maintains the copy in one place.
 */
function guru_register_solution_post_type() {
	register_post_type(
		'solution',
		array(
			'labels'        => array(
				'name'          => __( 'Solutions', 'guru' ),
				'singular_name' => __( 'Solution', 'guru' ),
				'menu_name'     => __( 'Solutions', 'guru' ),
				'add_new_item'  => __( 'Add new solution', 'guru' ),
				'edit_item'     => __( 'Edit solution', 'guru' ),
			),
			'public'        => true,
			'has_archive'   => false,
			'menu_icon'     => 'dashicons-screenoptions',
			'menu_position' => 6,
			'supports'      => array( 'title', 'editor', 'excerpt', 'thumbnail', 'page-attributes' ),
			'rewrite'       => array( 'slug' => 'solutions' ),
			'show_in_rest'  => true,
		)
	);
}
add_action( 'init', 'guru_register_solution_post_type' );

/**
 * Solutions are hand-ordered by the client, oldest-first by menu order.
 *
 * @param array $args Query args.
 * @return array
 */
function guru_solutions_query_args( $args = array() ) {
	return wp_parse_args(
		$args,
		array(
			'post_type'      => 'solution',
			'posts_per_page' => -1,
			'orderby'        => array( 'menu_order' => 'ASC', 'date' => 'ASC' ),
		)
	);
}

/**
 * Client logos, shown in the homepage ticker and the About logo wall.
 * Title = brand name (used as alt text), featured image = the logo.
 */
function guru_register_client_post_type() {
	register_post_type(
		'client_logo',
		array(
			'labels'        => array(
				'name'               => __( 'Client logos', 'guru' ),
				'singular_name'      => __( 'Client logo', 'guru' ),
				'menu_name'          => __( 'Client logos', 'guru' ),
				'add_new_item'       => __( 'Add new client logo', 'guru' ),
				'featured_image'     => __( 'Logo image', 'guru' ),
				'set_featured_image' => __( 'Set logo image', 'guru' ),
			),
			'public'        => false,
			'show_ui'       => true,
			'menu_icon'     => 'dashicons-awards',
			'menu_position' => 7,
			'supports'      => array( 'title', 'thumbnail', 'page-attributes' ),
		)
	);
}
add_action( 'init', 'guru_register_client_post_type' );

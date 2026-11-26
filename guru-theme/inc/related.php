<?php
/**
 * Related services, related work and an enquiry box on Insights articles
 * (v1.4.2, spec #15: articles link to the relevant services, cases and Contact).
 *
 * Editors tick the related Solutions and Work items on each article, in the
 * "Related services & work" box of the edit screen. Nothing is chosen
 * automatically. Every link is a plain <a href> so search engines follow it.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Post meta keys and the post type each one points to.
 *
 * @return array meta key => post type.
 */
function guru_related_fields() {
	return array(
		'_guru_related_solutions' => 'solution',
		'_guru_related_work'      => 'work',
	);
}

/**
 * Add the box to the article edit screen.
 */
function guru_add_related_meta_box() {
	add_meta_box(
		'guru_related',
		__( 'Related services & work', 'guru' ),
		'guru_render_related_meta_box',
		'post',
		'side',
		'default'
	);
}
add_action( 'add_meta_boxes', 'guru_add_related_meta_box' );

/**
 * Published items of a post type, in menu order then title.
 *
 * @param string $post_type Post type.
 * @return WP_Post[]
 */
function guru_related_choices( $post_type ) {
	return get_posts(
		array(
			'post_type'      => $post_type,
			'post_status'    => 'publish',
			'posts_per_page' => 200,
			'orderby'        => array(
				'menu_order' => 'ASC',
				'title'      => 'ASC',
			),
			'no_found_rows'  => true,
		)
	);
}

/**
 * IDs saved on an article for one field, published items only.
 *
 * @param int    $post_id Article ID.
 * @param string $key     Meta key.
 * @return int[]
 */
function guru_related_ids( $post_id, $key ) {
	$fields = guru_related_fields();
	$ids    = array_filter( array_map( 'absint', (array) get_post_meta( $post_id, $key, true ) ) );
	$out    = array();
	foreach ( $ids as $id ) {
		if ( isset( $fields[ $key ] ) && get_post_type( $id ) === $fields[ $key ] && 'publish' === get_post_status( $id ) ) {
			$out[] = $id;
		}
	}
	return $out;
}

/**
 * Render the box: a tick list per post type.
 *
 * @param WP_Post $post Article.
 */
function guru_render_related_meta_box( $post ) {
	wp_nonce_field( 'guru_save_related', 'guru_related_nonce' );
	$labels = array(
		'_guru_related_solutions' => __( 'Related services (Solutions)', 'guru' ),
		'_guru_related_work'      => __( 'Related work (Showcase)', 'guru' ),
	);
	echo '<p class="description">' . esc_html__( 'Tick what this article is about. They are listed at the end of the article, above the enquiry box.', 'guru' ) . '</p>';
	foreach ( guru_related_fields() as $key => $post_type ) {
		$chosen  = guru_related_ids( $post->ID, $key );
		$choices = guru_related_choices( $post_type );
		echo '<p><strong>' . esc_html( $labels[ $key ] ) . '</strong></p>';
		if ( ! $choices ) {
			echo '<p class="description">' . esc_html__( 'Nothing published yet.', 'guru' ) . '</p>';
			continue;
		}
		echo '<div style="max-height:220px;overflow:auto;border:1px solid #dcdcde;padding:6px 8px;margin-bottom:12px">';
		foreach ( $choices as $item ) {
			printf(
				'<label style="display:block;margin:3px 0"><input type="checkbox" name="%1$s[]" value="%2$d" %3$s /> %4$s</label>',
				esc_attr( ltrim( $key, '_' ) ),
				(int) $item->ID,
				checked( in_array( (int) $item->ID, $chosen, true ), true, false ),
				esc_html( get_the_title( $item ) )
			);
		}
		echo '</div>';
	}
}

/**
 * Save the ticks.
 *
 * @param int $post_id Article ID.
 */
function guru_save_related( $post_id ) {
	if ( ! isset( $_POST['guru_related_nonce'] ) || ! wp_verify_nonce( sanitize_key( wp_unslash( $_POST['guru_related_nonce'] ) ), 'guru_save_related' ) ) {
		return;
	}
	if ( ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) || wp_is_post_revision( $post_id ) || ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}
	foreach ( guru_related_fields() as $key => $post_type ) {
		$field = ltrim( $key, '_' );
		$ids   = isset( $_POST[ $field ] ) ? array_map( 'absint', (array) wp_unslash( $_POST[ $field ] ) ) : array();
		$ids   = array_values(
			array_filter(
				array_unique( $ids ),
				function ( $id ) use ( $post_type ) {
					return $id && get_post_type( $id ) === $post_type;
				}
			)
		);
		if ( $ids ) {
			update_post_meta( $post_id, $key, $ids );
		} else {
			delete_post_meta( $post_id, $key );
		}
	}
}
add_action( 'save_post_post', 'guru_save_related' );

/**
 * Link to a Solution: its panel on the Solutions page.
 *
 * @param int $id Solution ID.
 * @return string
 */
function guru_solution_link( $id ) {
	$base = guru_page_url( array( 'solutions', 'services' ), '/solutions/', 'page-services.php' );
	return $base . '#' . get_post_field( 'post_name', $id );
}

/**
 * Print the related services, related work and the enquiry box.
 *
 * @param int|null $post_id Article; default the current one.
 */
function guru_render_article_related( $post_id = null ) {
	$post_id   = $post_id ? (int) $post_id : get_the_ID();
	$solutions = guru_related_ids( $post_id, '_guru_related_solutions' );
	$work      = guru_related_ids( $post_id, '_guru_related_work' );

	if ( $solutions || $work ) :
		?>
		<section class="art-related" aria-labelledby="art-related-heading">
			<h2 id="art-related-heading" class="art-related-heading"><?php esc_html_e( 'Related', 'guru' ); ?></h2>

			<?php if ( $solutions ) : ?>
				<div class="art-related-group">
					<h3 class="art-related-label"><?php esc_html_e( 'Services', 'guru' ); ?></h3>
					<ul class="art-related-services">
						<?php foreach ( $solutions as $id ) : ?>
							<li><a href="<?php echo esc_url( guru_solution_link( $id ) ); ?>"><?php echo esc_html( get_the_title( $id ) ); ?><span aria-hidden="true">→</span></a></li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>

			<?php if ( $work ) : ?>
				<div class="art-related-group">
					<h3 class="art-related-label"><?php esc_html_e( 'Work', 'guru' ); ?></h3>
					<ul class="art-related-work">
						<?php foreach ( $work as $id ) : ?>
							<?php $client = trim( (string) get_post_meta( $id, '_guru_client', true ) ); ?>
							<li>
								<a href="<?php echo esc_url( get_permalink( $id ) ); ?>">
									<span class="art-related-thumb">
										<?php if ( has_post_thumbnail( $id ) ) : ?>
											<?php echo get_the_post_thumbnail( $id, 'medium_large', array( 'alt' => '', 'loading' => 'lazy' ) ); ?>
										<?php endif; ?>
									</span>
									<span class="art-related-name"><?php echo esc_html( get_the_title( $id ) ); ?></span>
									<?php if ( $client ) : ?>
										<span class="art-related-client"><?php echo esc_html( $client ); ?></span>
									<?php endif; ?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</div>
			<?php endif; ?>
		</section>
		<?php
	endif;

	$heading = trim( guru_mod( 'guru_article_cta_heading' ) );
	$button  = trim( guru_mod( 'guru_article_cta_button' ) );
	if ( '' === $button ) {
		return;
	}
	?>
	<aside class="art-cta" aria-label="<?php esc_attr_e( 'Enquiry', 'guru' ); ?>">
		<div class="art-cta-copy">
			<?php if ( $heading ) : ?>
				<p class="art-cta-heading"><?php echo esc_html( $heading ); ?></p>
			<?php endif; ?>
			<?php $text = trim( guru_mod( 'guru_article_cta_text' ) ); ?>
			<?php if ( $text ) : ?>
				<p class="art-cta-text"><?php echo esc_html( $text ); ?></p>
			<?php endif; ?>
		</div>
		<a class="art-cta-btn" href="#contact"><?php echo esc_html( $button ); ?> <span aria-hidden="true">→</span></a>
	</aside>
	<?php
}

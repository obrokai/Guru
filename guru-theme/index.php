<?php
/**
 * Fallback template — the blog listing, plus category / search / 404 views.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

get_header();

// The stylesheet has colour classes for these category slugs; anything else uses the default.
$guru_known_slugs = array( 'strategy', 'influencer', 'china', 'video', 'tech', 'social', 'campaign', 'global' );

// Category filter buttons, only on the main blog listing. Like the static site,
// they show even before any post exists — except the default "Uncategorized".
$guru_filter_cats = array();
if ( is_home() ) {
	foreach ( get_categories( array( 'hide_empty' => false ) ) as $guru_cat ) {
		if ( (int) get_option( 'default_category' ) !== (int) $guru_cat->term_id ) {
			$guru_filter_cats[] = $guru_cat;
		}
	}
}

if ( is_home() ) {
	$guru_heading = __( 'Insights', 'guru' );
} elseif ( is_search() ) {
	/* translators: %s: search query. */
	$guru_heading = sprintf( __( 'Search: %s', 'guru' ), get_search_query() );
} elseif ( is_404() ) {
	$guru_heading = __( 'Page not found', 'guru' );
} else {
	$guru_heading = wp_strip_all_tags( get_the_archive_title() );
}
?>

<div class="blg-hero">
	<div class="blg-hero-left">
		<h1 class="blg-hero-title"><?php echo esc_html( $guru_heading ); ?></h1>
	</div>
	<div class="blg-hero-right">
		<p class="blg-hero-desc">
			<?php
			if ( is_404() ) {
				esc_html_e( 'The page you were looking for does not exist or has moved. Use the navigation above, or start from the homepage.', 'guru' );
			} else {
				echo esc_html( guru_mod( 'guru_blog_intro' ) );
			}
			?>
		</p>
	</div>
</div>

<?php if ( $guru_filter_cats ) : ?>
	<div class="blg-filters">
		<button class="blg-filter-btn active" type="button" data-cat="all"><?php esc_html_e( 'All', 'guru' ); ?></button>
		<?php foreach ( $guru_filter_cats as $guru_cat ) : ?>
			<button class="blg-filter-btn" type="button" data-cat="<?php echo esc_attr( $guru_cat->slug ); ?>">
				<?php echo esc_html( $guru_cat->name ); ?>
			</button>
		<?php endforeach; ?>
	</div>
<?php endif; ?>

<div class="blg-grid">
	<?php if ( have_posts() && ! is_404() ) : ?>
		<?php
		while ( have_posts() ) :
			the_post();

			$guru_cats  = get_the_category();
			$guru_slugs = array();
			foreach ( $guru_cats as $guru_c ) {
				$guru_slugs[] = $guru_c->slug;
			}
			// Colour classes follow the first category's slug (cat-influencer, grad-china, …).
			$guru_first = ( $guru_cats && in_array( $guru_cats[0]->slug, $guru_known_slugs, true ) ) ? $guru_cats[0]->slug : 'strategy';
			?>
			<a class="art-card" href="<?php the_permalink(); ?>" data-cat="<?php echo esc_attr( implode( ' ', $guru_slugs ) ); ?>">
				<div class="art-img">
					<?php if ( has_post_thumbnail() ) : ?>
						<?php the_post_thumbnail( 'large', array( 'class' => 'art-img-bg', 'loading' => 'lazy' ) ); ?>
					<?php else : ?>
						<div class="art-img-bg grad-<?php echo esc_attr( $guru_first ); ?>"></div>
					<?php endif; ?>
					<div class="art-img-pattern"></div>
				</div>
				<?php if ( $guru_cats ) : ?>
					<span class="art-cat cat-<?php echo esc_attr( $guru_first ); ?>"><?php echo esc_html( $guru_cats[0]->name ); ?></span>
				<?php endif; ?>
				<h2 class="art-title"><?php the_title(); ?></h2>
				<p class="art-excerpt"><?php echo esc_html( wp_trim_words( get_the_excerpt(), 24 ) ); ?></p>
				<div class="art-meta">
					<span><?php echo esc_html( get_the_date() ); ?></span>
				</div>
			</a>
			<?php
		endwhile;
	elseif ( ! is_404() ) :
		?>
		<p><?php esc_html_e( 'No posts yet.', 'guru' ); ?></p>
	<?php endif; ?>
</div>

<?php if ( $guru_filter_cats ) : ?>
	<?php // Shown by the filter script when a category has no articles. ?>
	<p class="blg-empty" id="blgEmpty"><?php esc_html_e( 'No articles in this category yet.', 'guru' ); ?></p>
<?php endif; ?>

<?php
the_posts_pagination( array( 'mid_size' => 2 ) );

get_footer();

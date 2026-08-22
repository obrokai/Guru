<?php
/**
 * Site header and navigation.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_logo    = guru_asset( 'media/guru-logo.png' );
$guru_wa      = get_theme_mod( 'guru_whatsapp', 'https://wa.me/85200000000' );
$guru_contact = home_url( '/#contact' );

/**
 * Nav items. Editable in the admin under Appearance → Menus (Primary);
 * this array is the fallback when no menu has been assigned yet.
 */
$guru_nav = array(
	'about'    => __( 'About', 'guru' ),
	'work'     => __( 'Work', 'guru' ),
	'services' => __( 'Solutions', 'guru' ),
	'blog'     => __( 'Blog', 'guru' ),
	'investor' => __( 'Investor', 'guru' ),
);
?>
<!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
	<meta charset="<?php bloginfo( 'charset' ); ?>" />
	<meta name="viewport" content="width=device-width, initial-scale=1.0" />
	<?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>

<div class="pg-intro" aria-hidden="true"></div>

<nav class="gn-nav">
	<div class="gn-nav-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gn-logo" aria-label="<?php esc_attr_e( 'Guru — home', 'guru' ); ?>">
			<img src="<?php echo $guru_logo; ?>" alt="<?php esc_attr_e( 'Guru', 'guru' ); ?>" />
		</a>

		<div class="gn-pill">
			<div class="gn-nav-links">
				<?php if ( has_nav_menu( 'primary' ) ) : ?>
					<?php
					wp_nav_menu(
						array(
							'theme_location' => 'primary',
							'container'      => false,
							'items_wrap'     => '%3$s',
							'depth'          => 1,
							'link_before'    => '',
							'walker'         => new Guru_Nav_Walker(),
						)
					);
					?>
				<?php else : ?>
					<?php foreach ( $guru_nav as $guru_slug => $guru_label ) : ?>
						<?php
						$guru_page   = get_page_by_path( $guru_slug );
						$guru_url    = $guru_page ? get_permalink( $guru_page ) : home_url( '/' . $guru_slug . '/' );
						$guru_active = ( $guru_page && is_page( $guru_page->ID ) )
							|| ( 'work' === $guru_slug && ( is_post_type_archive( 'work' ) || is_singular( 'work' ) ) )
							|| ( 'blog' === $guru_slug && ( is_home() || is_singular( 'post' ) ) );
						?>
						<a href="<?php echo esc_url( $guru_url ); ?>"
							class="gn-link<?php echo $guru_active ? ' active' : ''; ?>">
							<?php echo esc_html( $guru_label ); ?>
						</a>
					<?php endforeach; ?>
				<?php endif; ?>
			</div>

			<a href="<?php echo esc_url( $guru_contact ); ?>" class="gn-link-cta"><?php esc_html_e( 'Contact Us', 'guru' ); ?></a>
			<a href="<?php echo esc_url( $guru_wa ); ?>" class="gn-link-whatsapp" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'guru' ); ?></a>

			<button class="gn-hamburger" id="gnToggle" aria-label="<?php esc_attr_e( 'Toggle menu', 'guru' ); ?>" aria-expanded="false">
				<span></span><span></span><span></span>
			</button>
		</div>
	</div>
</nav>

<div class="gn-mobile-menu" id="gnMobileMenu" role="dialog" aria-label="<?php esc_attr_e( 'Navigation', 'guru' ); ?>">
	<?php foreach ( $guru_nav as $guru_slug => $guru_label ) : ?>
		<?php
		$guru_page = get_page_by_path( $guru_slug );
		$guru_url  = $guru_page ? get_permalink( $guru_page ) : home_url( '/' . $guru_slug . '/' );
		?>
		<a href="<?php echo esc_url( $guru_url ); ?>" class="gn-mobile-link" data-close><?php echo esc_html( $guru_label ); ?></a>
	<?php endforeach; ?>

	<div class="gn-mobile-ctas">
		<a href="<?php echo esc_url( $guru_contact ); ?>" class="gn-link-cta" data-close><?php esc_html_e( 'Contact Us', 'guru' ); ?></a>
		<a href="<?php echo esc_url( $guru_wa ); ?>" class="gn-link-whatsapp" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'guru' ); ?></a>
	</div>
</div>

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
$guru_wa      = guru_mod( 'guru_whatsapp' );
// On the homepage the contact form is on the same page, so the link scrolls to it.
$guru_contact = is_front_page() ? '#contact' : home_url( '/#contact' );
// The static Solutions and Investor pages have no page-load cover.
$guru_intro   = ! ( is_page_template( 'page-services.php' ) || is_page_template( 'page-investor.php' ) );

/*
 * Nav items. The desktop links come from the "Guru Primary" menu (Appearance →
 * Menus) once the importer has created it; until then — and for the mobile
 * menu always — the built-in list below is used.
 */
$guru_nav = guru_nav_items();
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

<?php if ( $guru_intro ) : ?>
	<div class="pg-intro" aria-hidden="true"></div>
<?php endif; ?>

<nav class="gn-nav">
	<div class="gn-nav-inner">
		<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="gn-logo" aria-label="<?php esc_attr_e( 'Guru — home', 'guru' ); ?>">
			<img src="<?php echo $guru_logo; ?>" alt="<?php esc_attr_e( 'Guru', 'guru' ); ?>" />
		</a>

		<div class="gn-pill">
			<div class="gn-nav-links">
				<?php if ( guru_use_wp_menu() ) : ?>
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
					<?php foreach ( $guru_nav as $guru_item ) : ?>
						<a href="<?php echo esc_url( $guru_item['url'] ); ?>"
							class="gn-link<?php echo $guru_item['active'] ? ' active' : ''; ?>">
							<?php echo esc_html( $guru_item['label'] ); ?>
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
	<?php foreach ( $guru_nav as $guru_item ) : ?>
		<a href="<?php echo esc_url( $guru_item['url'] ); ?>" class="gn-mobile-link" data-close><?php echo esc_html( $guru_item['label'] ); ?></a>
	<?php endforeach; ?>

	<div class="gn-mobile-ctas">
		<a href="<?php echo esc_url( $guru_contact ); ?>" class="gn-link-cta" data-close><?php esc_html_e( 'Contact Us', 'guru' ); ?></a>
		<a href="<?php echo esc_url( $guru_wa ); ?>" class="gn-link-whatsapp" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'guru' ); ?></a>
	</div>
</div>

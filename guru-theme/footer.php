<?php
/**
 * Site footer, including the contact form.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_email  = get_theme_mod( 'guru_email', 'hello@guruonline.com.hk' );
$guru_phone  = get_theme_mod( 'guru_phone', '+852 3952 1100' );
$guru_city   = get_theme_mod( 'guru_city', 'Hong Kong' );
$guru_wa     = get_theme_mod( 'guru_whatsapp', 'https://wa.me/85200000000' );
$guru_head   = get_theme_mod( 'guru_contact_heading', __( 'Talk to Us and We Build Something Great Together.', 'guru' ) );
$guru_socials = array(
	'Instagram' => get_theme_mod( 'guru_instagram', '' ),
	'LinkedIn'  => get_theme_mod( 'guru_linkedin', '' ),
	'Facebook'  => get_theme_mod( 'guru_facebook', '' ),
);

$guru_nav = array(
	'about'    => __( 'About Us', 'guru' ),
	'work'     => __( 'Work', 'guru' ),
	'services' => __( 'Solutions', 'guru' ),
	'blog'     => __( 'Blog', 'guru' ),
	'investor' => __( 'Investor', 'guru' ),
);

$guru_services = array(
	'Content Marketing',
	'Creative and Production',
	'Digital Campaign',
	'Digital Media Placement',
	'E-Commerce Solution',
	'Influencer Marketing',
	'Social Media Marketing',
	'Others',
);

$guru_sent = isset( $_GET['contact'] ) ? sanitize_key( wp_unslash( $_GET['contact'] ) ) : '';
?>

<footer id="contact" class="ftr">
	<div class="ftr-top">
		<div class="ftr-inner">
			<div class="ftr-contact">
				<div class="ftr-cta-block">
					<p class="ftr-cta-label"><?php esc_html_e( 'Ready to start?', 'guru' ); ?></p>
					<h2 class="ftr-cta-heading"><?php echo esc_html( $guru_head ); ?></h2>
				</div>

				<form class="cf-form" id="contactForm" method="post"
					action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
					<input type="hidden" name="action" value="guru_contact" />
					<?php wp_nonce_field( 'guru_contact', 'guru_contact_nonce' ); ?>
					<input type="hidden" name="guru_redirect" value="<?php echo esc_url( get_permalink() ? get_permalink() : home_url( '/' ) ); ?>" />
					<?php // Honeypot — bots fill this, humans never see it. ?>
					<div class="cf-hp" aria-hidden="true">
						<label>Website<input type="text" name="guru_website" tabindex="-1" autocomplete="off" /></label>
					</div>

					<div class="cf-field">
						<input class="cf-input" type="text" id="cf-name" name="name"
							placeholder="<?php esc_attr_e( 'Name *', 'guru' ); ?>"
							aria-label="<?php esc_attr_e( 'Name', 'guru' ); ?>" required />
					</div>
					<div class="cf-field">
						<input class="cf-input" type="text" id="cf-company" name="company"
							placeholder="<?php esc_attr_e( 'Company Name *', 'guru' ); ?>"
							aria-label="<?php esc_attr_e( 'Company Name', 'guru' ); ?>" required />
					</div>
					<div class="cf-field">
						<div class="cf-phone-group">
							<select class="cf-input cf-cc" name="cc" aria-label="<?php esc_attr_e( 'Country code', 'guru' ); ?>">
								<option value="+852" data-len="8" selected>+852</option>
								<option value="+853" data-len="">+853</option>
								<option value="+86" data-len="">+86</option>
								<option value="+886" data-len="">+886</option>
								<option value="+65" data-len="">+65</option>
								<option value="+44" data-len="">+44</option>
								<option value="+1" data-len="">+1</option>
								<option value="+81" data-len="">+81</option>
								<option value="+82" data-len="">+82</option>
								<option value="+61" data-len="">+61</option>
								<option value="+other" data-len=""><?php esc_html_e( 'Other', 'guru' ); ?></option>
							</select>
							<input class="cf-input cf-phone" type="tel" id="cf-phone" name="phone"
								placeholder="<?php esc_attr_e( 'Contact No. *', 'guru' ); ?>" inputmode="numeric"
								aria-label="<?php esc_attr_e( 'Contact No.', 'guru' ); ?>" required />
						</div>
					</div>
					<div class="cf-field">
						<input class="cf-input" type="email" id="cf-email" name="email"
							placeholder="<?php esc_attr_e( 'Business Email *', 'guru' ); ?>"
							aria-label="<?php esc_attr_e( 'Business Email', 'guru' ); ?>" required />
					</div>
					<div class="cf-field full">
						<select class="cf-select" id="cf-service" name="service"
							aria-label="<?php esc_attr_e( "Service You're Looking For", 'guru' ); ?>">
							<option value=""><?php esc_html_e( "Service You're Looking For", 'guru' ); ?></option>
							<?php foreach ( $guru_services as $guru_service ) : ?>
								<option><?php echo esc_html( $guru_service ); ?></option>
							<?php endforeach; ?>
						</select>
					</div>
					<div class="cf-field full">
						<textarea class="cf-textarea" id="cf-message" name="message"
							aria-label="<?php esc_attr_e( 'Tell Us More', 'guru' ); ?>"
							placeholder="<?php esc_attr_e( 'How can we help you?', 'guru' ); ?>"></textarea>
					</div>

					<p class="cf-note" id="cfNote">
						<?php if ( 'sent' === $guru_sent ) : ?>
							<?php esc_html_e( 'Thank you — we have received your enquiry and will be in touch.', 'guru' ); ?>
						<?php elseif ( 'error' === $guru_sent ) : ?>
							<span style="color:#c0392b"><?php esc_html_e( 'Sorry, something went wrong. Please try again or email us directly.', 'guru' ); ?></span>
						<?php endif; ?>
					</p>

					<button class="cf-submit" type="submit"><?php esc_html_e( 'Send Message', 'guru' ); ?> <span>&rarr;</span></button>
				</form>
			</div>
		</div>
	</div>

	<div class="ftr-bottom">
		<div class="ftr-inner">
			<div class="ftr-logo-col">
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ftr-logo" aria-label="<?php esc_attr_e( 'Guru', 'guru' ); ?>">
					<img src="<?php echo guru_asset( 'media/guru-logo-footer.png' ); ?>" alt="<?php esc_attr_e( 'Guru', 'guru' ); ?>" />
				</a>
			</div>

			<div>
				<p class="ftr-nav-heading"><?php esc_html_e( 'Navigation', 'guru' ); ?></p>
				<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="ftr-link"><?php esc_html_e( 'Home', 'guru' ); ?></a>
				<?php foreach ( $guru_nav as $guru_slug => $guru_label ) : ?>
					<?php
					$guru_page = get_page_by_path( $guru_slug );
					$guru_url  = $guru_page ? get_permalink( $guru_page ) : home_url( '/' . $guru_slug . '/' );
					?>
					<a href="<?php echo esc_url( $guru_url ); ?>" class="ftr-link"><?php echo esc_html( $guru_label ); ?></a>
				<?php endforeach; ?>
				<a href="#contact" class="ftr-link"><?php esc_html_e( 'Contact', 'guru' ); ?></a>
			</div>

			<div>
				<p class="ftr-nav-heading"><?php esc_html_e( 'Contact', 'guru' ); ?></p>
				<a href="mailto:<?php echo esc_attr( $guru_email ); ?>" class="ftr-link"><?php echo esc_html( $guru_email ); ?></a>
				<a href="tel:<?php echo esc_attr( preg_replace( '/[^0-9+]/', '', $guru_phone ) ); ?>" class="ftr-link"><?php echo esc_html( $guru_phone ); ?></a>
				<p class="ftr-link"><?php echo esc_html( $guru_city ); ?></p>
			</div>

			<div>
				<p class="ftr-nav-heading"><?php esc_html_e( 'Follow Us', 'guru' ); ?></p>
				<?php foreach ( $guru_socials as $guru_name => $guru_href ) : ?>
					<?php if ( $guru_href ) : ?>
						<a href="<?php echo esc_url( $guru_href ); ?>" class="ftr-link" target="_blank" rel="noopener"><?php echo esc_html( $guru_name ); ?></a>
					<?php endif; ?>
				<?php endforeach; ?>
			</div>
		</div>

		<div class="ftr-base">
			<p class="ftr-copy">
				<?php
				/* translators: %1$s: year, %2$s: site name. */
				printf( esc_html__( '© %1$s %2$s. All rights reserved.', 'guru' ), esc_html( gmdate( 'Y' ) ), esc_html( get_bloginfo( 'name' ) ) );
				?>
			</p>
			<p class="ftr-copy"><?php esc_html_e( 'Designed with ♥ in Hong Kong', 'guru' ); ?></p>
		</div>
	</div>
</footer>

<?php wp_footer(); ?>
</body>
</html>

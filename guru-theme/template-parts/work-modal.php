<?php
/**
 * Showcase lightbox — video player plus an inline contact form.
 *
 * Shared by the homepage and the work archive; the JS fills in the title,
 * media and tags from the clicked card.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_wa = get_theme_mod( 'guru_whatsapp', 'https://wa.me/85200000000' );

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
?>
<div class="hw-vmodal" id="hwVmodal">
	<div class="hw-vmodal-box">
		<div class="hw-vmodal-bar">
			<div>
				<div class="hw-vmodal-title" id="hwVmodalTitle"></div>
				<div class="hw-vmodal-sub" id="hwVmodalSub"></div>
			</div>
			<button class="hw-vmodal-close" id="hwVmodalClose" aria-label="<?php esc_attr_e( 'Close', 'guru' ); ?>">&times;</button>
		</div>

		<div class="hw-vmodal-video-wrap" id="hwVmodalVideoWrap">
			<video id="hwVmodalVideo" controls playsinline></video>
		</div>

		<div class="hw-cta-row">
			<button type="button" class="hw-cta-btn hw-cta-btn-contact" id="hwContactBtn"><?php esc_html_e( 'Contact Us', 'guru' ); ?></button>
			<a href="<?php echo esc_url( $guru_wa ); ?>" class="hw-cta-btn hw-cta-btn-wa" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'guru' ); ?></a>
		</div>

		<div class="hw-form-wrap" id="hwFormWrap">
			<form class="hwf-form" id="hwContactForm" method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
				<input type="hidden" name="action" value="guru_contact" />
				<?php wp_nonce_field( 'guru_contact', 'guru_contact_nonce' ); ?>
				<input type="hidden" name="guru_redirect" value="<?php echo esc_url( home_url( add_query_arg( array() ) ) ); ?>" />
				<input type="hidden" name="project" id="hwf-project" value="" />
				<div class="cf-hp" aria-hidden="true">
					<label>Website<input type="text" name="guru_website" tabindex="-1" autocomplete="off" /></label>
				</div>

				<div class="hwf-field">
					<input class="hwf-input" type="text" id="hwf-name" name="name"
						placeholder="<?php esc_attr_e( 'Name *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Name', 'guru' ); ?>" required />
				</div>
				<div class="hwf-field">
					<input class="hwf-input" type="text" id="hwf-company" name="company"
						placeholder="<?php esc_attr_e( 'Company Name *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Company Name', 'guru' ); ?>" required />
				</div>
				<div class="hwf-field">
					<div class="hwf-phone-group">
						<select class="hwf-input hwf-cc" name="cc" aria-label="<?php esc_attr_e( 'Country code', 'guru' ); ?>">
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
						<input class="hwf-input hwf-phone" type="tel" id="hwf-phone" name="phone"
							placeholder="<?php esc_attr_e( 'Contact No. *', 'guru' ); ?>" inputmode="numeric"
							aria-label="<?php esc_attr_e( 'Contact No.', 'guru' ); ?>" required />
					</div>
				</div>
				<div class="hwf-field">
					<input class="hwf-input" type="email" id="hwf-email" name="email"
						placeholder="<?php esc_attr_e( 'Business Email *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Business Email', 'guru' ); ?>" required />
				</div>
				<div class="hwf-field full">
					<select class="hwf-select" id="hwf-service" name="service" aria-label="<?php esc_attr_e( "Service You're Looking For", 'guru' ); ?>">
						<option value=""><?php esc_html_e( "Service You're Looking For", 'guru' ); ?></option>
						<?php foreach ( $guru_services as $guru_service ) : ?>
							<option><?php echo esc_html( $guru_service ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="hwf-field full">
					<textarea class="hwf-textarea" id="hwf-message" name="message"
						aria-label="<?php esc_attr_e( 'Tell Us More', 'guru' ); ?>"
						placeholder="<?php esc_attr_e( 'How can we help you?', 'guru' ); ?>"></textarea>
				</div>
				<p class="hwf-note" id="hwfNote"></p>
				<button class="hwf-submit" type="submit"><?php esc_html_e( 'Send Message', 'guru' ); ?> <span>&rarr;</span></button>
			</form>
		</div>
	</div>

	<button type="button" class="hw-scroll-hint" id="hwScrollHint" aria-label="<?php esc_attr_e( 'Scroll down for more', 'guru' ); ?>">
		<span><?php esc_html_e( 'More', 'guru' ); ?></span>
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true">
			<path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" />
		</svg>
	</button>
</div>

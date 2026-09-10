<?php
/**
 * Showcase lightbox for the Work archive — the static work page's modal:
 * title + client in the top bar, category pills under the video, contact
 * buttons and an inline form. Styled by assets/css/work.css.
 *
 * The homepage uses template-parts/work-modal.php (its own design); both post
 * to the same contact handler.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_wa = guru_mod( 'guru_whatsapp' );

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
<div class="vmodal-overlay" id="vmodal">
	<div class="vmodal-box">
		<div class="vmodal-topbar">
			<div class="vmodal-meta">
				<h3 class="vmodal-title" id="vmodalTitle"></h3>
				<p class="vmodal-client" id="vmodalClient"></p>
			</div>
			<button class="vmodal-close" id="vmodalClose" aria-label="<?php esc_attr_e( 'Close', 'guru' ); ?>">&times;</button>
		</div>
		<div class="vmodal-video-wrap" id="vmodalVideoWrap">
			<video id="vmodalVideo" controls playsinline></video>
		</div>
		<div class="vmodal-tags" id="vmodalTags"></div>
		<div class="vmodal-cta-row">
			<button type="button" class="vmodal-btn vmodal-btn-contact" id="vmodalContactBtn"><?php esc_html_e( 'Contact Us', 'guru' ); ?></button>
			<a href="<?php echo esc_url( $guru_wa ); ?>" class="vmodal-btn vmodal-btn-wa" target="_blank" rel="noopener"><?php esc_html_e( 'WhatsApp', 'guru' ); ?></a>
		</div>
		<div class="vmodal-form-wrap" id="vmodalFormWrap">
			<form class="vmf-form" id="vmodalForm" method="post"
				action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
				<input type="hidden" name="action" value="guru_contact" />
				<?php wp_nonce_field( 'guru_contact', 'guru_contact_nonce' ); ?>
				<input type="hidden" name="guru_redirect" value="<?php echo esc_url( get_post_type_archive_link( 'work' ) ); ?>" />
				<input type="hidden" name="project" id="vmf-project" value="" />
				<div class="cf-hp" aria-hidden="true">
					<label>Website<input type="text" name="guru_website" tabindex="-1" autocomplete="off" /></label>
				</div>

				<div class="vmf-field">
					<input class="vmf-input" type="text" id="vmf-name" name="name"
						placeholder="<?php esc_attr_e( 'Name *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Name', 'guru' ); ?>" required />
				</div>
				<div class="vmf-field">
					<input class="vmf-input" type="text" id="vmf-company" name="company"
						placeholder="<?php esc_attr_e( 'Company Name *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Company Name', 'guru' ); ?>" required />
				</div>
				<div class="vmf-field">
					<div class="vmf-phone-group">
						<select class="vmf-input vmf-cc" name="cc" aria-label="<?php esc_attr_e( 'Country code', 'guru' ); ?>">
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
						<input class="vmf-input vmf-phone" type="tel" id="vmf-phone" name="phone"
							placeholder="<?php esc_attr_e( 'Contact No. *', 'guru' ); ?>" inputmode="numeric"
							aria-label="<?php esc_attr_e( 'Contact No.', 'guru' ); ?>" required />
					</div>
				</div>
				<div class="vmf-field">
					<input class="vmf-input" type="email" id="vmf-email" name="email"
						placeholder="<?php esc_attr_e( 'Business Email *', 'guru' ); ?>" aria-label="<?php esc_attr_e( 'Business Email', 'guru' ); ?>" required />
				</div>
				<div class="vmf-field full">
					<select class="vmf-select" id="vmf-service" name="service" aria-label="<?php esc_attr_e( "Service You're Looking For", 'guru' ); ?>">
						<option value=""><?php esc_html_e( "Service You're Looking For", 'guru' ); ?></option>
						<?php foreach ( $guru_services as $guru_service ) : ?>
							<option><?php echo esc_html( $guru_service ); ?></option>
						<?php endforeach; ?>
					</select>
				</div>
				<div class="vmf-field full">
					<textarea class="vmf-textarea" id="vmf-message" name="message"
						aria-label="<?php esc_attr_e( 'Tell Us More', 'guru' ); ?>"
						placeholder="<?php esc_attr_e( 'How can we help you?', 'guru' ); ?>"></textarea>
				</div>
				<p class="vmf-note" id="vmfNote"></p>
				<button class="vmf-submit" type="submit"><?php esc_html_e( 'Send Message', 'guru' ); ?> <span>&rarr;</span></button>
			</form>
		</div>
	</div>
	<button type="button" class="vmodal-scroll-hint" id="vmodalScrollHint" aria-label="<?php esc_attr_e( 'Scroll down for more', 'guru' ); ?>">
		<span><?php esc_html_e( 'More', 'guru' ); ?></span>
		<svg width="16" height="16" viewBox="0 0 16 16" fill="none" aria-hidden="true"><path d="M4 6l4 4 4-4" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>
	</button>
</div>

<?php
/**
 * Per-solution contact form — same fields as the static page's form template,
 * including the country-code selector next to the phone number.
 *
 * @package Guru
 *
 * @var array $args Passed from get_template_part(): [ 'solution' => string ].
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$guru_solution = isset( $args['solution'] ) ? $args['solution'] : '';
// One page holds a form per solution: each gets its own ids so every label points at its own field.
$guru_uid = wp_unique_id( 'spf-' );

$guru_services = array(
	'China Digital Expansion',
	'Influencer Marketing',
	'Video Production',
	'Go-Global',
	'Threads Management',
	'Social Media Marketing',
	'GEO & Search Strategy',
	'AI & Development',
	'Campaign / PR Events',
	'Others',
);
?>
<div class="spf">
	<div class="spf-intro">
		<p class="spf-label"><?php esc_html_e( 'Ready to start?', 'guru' ); ?></p>
		<h3 class="spf-heading"><?php esc_html_e( 'Talk to Us About This Solution', 'guru' ); ?></h3>
		<p class="spf-desc">
			<?php esc_html_e( "Tell us a little about your brand and goals — we'll get back to you with how Guru can help.", 'guru' ); ?>
		</p>
	</div>

	<form class="spf-form" method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" novalidate>
		<input type="hidden" name="action" value="guru_contact" />
		<input type="hidden" name="guru_contact_nonce" value="<?php echo esc_attr( wp_create_nonce( 'guru_contact' ) ); ?>" />
		<input type="hidden" name="guru_redirect" value="<?php echo esc_url( get_permalink() ); ?>" />
		<input type="hidden" name="project" value="<?php echo esc_attr( $guru_solution ); ?>" />
		<div class="cf-hp" aria-hidden="true">
			<label>Website<input type="text" name="guru_website" tabindex="-1" autocomplete="off" /></label>
		</div>

		<div class="spf-field">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-name"><?php esc_html_e( 'Name', 'guru' ); ?> <span class="guru-label-req" aria-hidden="true">*</span></label>
			<input class="spf-input" type="text" id="<?php echo esc_attr( $guru_uid ); ?>-name" name="name" autocomplete="name" required />
		</div>
		<div class="spf-field">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-company"><?php esc_html_e( 'Company Name', 'guru' ); ?> <span class="guru-label-req" aria-hidden="true">*</span></label>
			<input class="spf-input" type="text" id="<?php echo esc_attr( $guru_uid ); ?>-company" name="company" autocomplete="organization" required />
		</div>
		<div class="spf-field">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-phone"><?php esc_html_e( 'Contact No.', 'guru' ); ?> <span class="guru-label-opt"><?php esc_html_e( '(optional)', 'guru' ); ?></span></label>
			<div class="cf-phone-group">
				<select class="spf-input cf-cc spf-cc" name="cc" aria-label="<?php esc_attr_e( 'Country code', 'guru' ); ?>">
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
				<input class="spf-input cf-phone spf-phone" type="tel" id="<?php echo esc_attr( $guru_uid ); ?>-phone" name="phone" inputmode="numeric" autocomplete="tel-national" />
			</div>
		</div>
		<div class="spf-field">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-email"><?php esc_html_e( 'Business Email', 'guru' ); ?> <span class="guru-label-req" aria-hidden="true">*</span></label>
			<input class="spf-input" type="email" id="<?php echo esc_attr( $guru_uid ); ?>-email" name="email" autocomplete="email"
				placeholder="<?php esc_attr_e( 'name@company.com', 'guru' ); ?>" required />
		</div>
		<div class="spf-field full">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-service"><?php esc_html_e( "Service You're Looking For", 'guru' ); ?></label>
			<select class="spf-select" id="<?php echo esc_attr( $guru_uid ); ?>-service" name="service">
				<option value=""><?php esc_html_e( 'Select a service', 'guru' ); ?></option>
				<?php foreach ( $guru_services as $guru_service ) : ?>
					<option <?php selected( $guru_service, $guru_solution ); ?>><?php echo esc_html( $guru_service ); ?></option>
				<?php endforeach; ?>
			</select>
		</div>
		<div class="spf-field full">
			<label class="guru-label" for="<?php echo esc_attr( $guru_uid ); ?>-message"><?php esc_html_e( 'Tell Us More', 'guru' ); ?></label>
			<textarea class="spf-textarea" id="<?php echo esc_attr( $guru_uid ); ?>-message" name="message"
				placeholder="<?php esc_attr_e( 'How can we help you?', 'guru' ); ?>"></textarea>
		</div>
		<?php guru_form_privacy_note(); ?>
		<p class="spf-note" id="<?php echo esc_attr( wp_unique_id( 'spf-note-' ) ); ?>"></p>
		<button class="spf-submit" type="submit"><?php esc_html_e( 'Send Message', 'guru' ); ?> <span>&rarr;</span></button>
	</form>
</div>

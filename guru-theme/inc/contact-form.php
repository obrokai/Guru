<?php
/**
 * Contact form handling.
 *
 * Replaces the static site's mailto: link — enquiries are emailed to the
 * address set in the Customizer and stored in the enquiries table (see
 * inc/enquiries.php) so nothing is lost if mail delivery fails.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Handle a submission from the footer / modal / per-solution contact forms.
 */
function guru_handle_contact() {
	$redirect = isset( $_POST['guru_redirect'] )
		? esc_url_raw( wp_unslash( $_POST['guru_redirect'] ) )
		: home_url( '/' );

	// Only redirect to this site — checked by host, so "https://this-host.evil.com/" is rejected too.
	$redirect = wp_validate_redirect( $redirect, home_url( '/' ) );

	$fail = add_query_arg( 'contact', 'error', $redirect ) . '#contact';
	$ok   = add_query_arg( 'contact', 'sent', $redirect ) . '#contact';

	if ( ! isset( $_POST['guru_contact_nonce'] )
		|| ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['guru_contact_nonce'] ) ), 'guru_contact' ) ) {
		wp_safe_redirect( $fail );
		exit;
	}

	// Honeypot: a filled "website" field means a bot. Pretend success.
	if ( ! empty( $_POST['guru_website'] ) ) {
		wp_safe_redirect( $ok );
		exit;
	}

	$field = static function ( $key ) {
		return isset( $_POST[ $key ] ) ? sanitize_text_field( wp_unslash( $_POST[ $key ] ) ) : '';
	};

	$name    = $field( 'name' );
	$company = $field( 'company' );
	$cc      = $field( 'cc' );
	$phone   = $field( 'phone' );
	$service = $field( 'service' );
	$project = $field( 'project' );
	$email   = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( wp_unslash( $_POST['message'] ) ) : '';

	// Required fields. Contact No. is optional (client spec, section 9, v1.4.0).
	$phone = preg_replace( '/[^0-9]/', '', $phone );
	if ( '' === $name || '' === $company || ! is_email( $email ) ) {
		wp_safe_redirect( $fail );
		exit;
	}

	$cc    = ( '+other' === $cc ) ? '' : $cc;
	$tel   = '' === $phone ? '—' : trim( $cc . ' ' . $phone );
	$lines = array(
		__( 'Name', 'guru' )           => $name,
		__( 'Company', 'guru' )        => $company,
		__( 'Contact No.', 'guru' )    => $tel,
		__( 'Business Email', 'guru' ) => $email,
		__( 'Service', 'guru' )        => $service ? $service : '—',
	);

	if ( $project ) {
		$lines[ __( 'Project', 'guru' ) ] = $project;
	}

	$body = '';
	foreach ( $lines as $label => $value ) {
		$body .= $label . ': ' . $value . "\n";
	}
	$body .= "\n" . __( 'Message', 'guru' ) . ":\n" . ( $message ? $message : '—' ) . "\n";

	// Archive it first, so a mail failure never loses the enquiry.
	$stored = guru_enquiry_insert(
		array(
			'name'       => $name,
			'company'    => $company,
			'email'      => $email,
			'phone'      => $tel,
			'service'    => $service,
			'project'    => $project,
			'message'    => $message,
			'source_url' => $redirect,
		)
	);

	$to = guru_mod( 'guru_email' );
	if ( ! is_email( $to ) ) {
		$to = get_option( 'admin_email' );
	}
	$subject = sprintf(
		/* translators: %s: company name. */
		__( 'Website Enquiry — %s', 'guru' ),
		$company
	);
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	$mailed = wp_mail( $to, $subject, $body, $headers );

	// Only report success when the enquiry actually went somewhere (v1.3.0):
	// neither stored nor e-mailed means it is lost, so ask the visitor to retry.
	if ( ! $stored && ! $mailed ) {
		wp_safe_redirect( $fail );
		exit;
	}

	wp_safe_redirect( $ok );
	exit;
}
add_action( 'admin_post_nopriv_guru_contact', 'guru_handle_contact' );

/**
 * One line under every contact form saying what the details are used for, with a
 * link to the Privacy Policy page when one is set in Settings → Privacy
 * (client spec, section 9, v1.4.0). Wording to be confirmed by GURU.
 */
function guru_form_privacy_note() {
	$url = function_exists( 'get_privacy_policy_url' ) ? get_privacy_policy_url() : '';
	echo '<p class="guru-form-privacy">';
	esc_html_e( 'We only use these details to reply to your enquiry.', 'guru' );
	if ( $url ) {
		echo ' <a href="' . esc_url( $url ) . '">' . esc_html__( 'Privacy Policy', 'guru' ) . '</a>';
	}
	echo '</p>';
}
add_action( 'admin_post_guru_contact', 'guru_handle_contact' );

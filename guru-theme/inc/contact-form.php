<?php
/**
 * Contact form handling.
 *
 * Replaces the static site's mailto: link — enquiries are emailed to the
 * address set in the Customizer and stored as an "Enquiry" record so nothing
 * is lost if mail delivery fails.
 *
 * @package Guru
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Private post type that archives every enquiry in the admin.
 */
function guru_register_enquiry_post_type() {
	register_post_type(
		'guru_enquiry',
		array(
			'labels'          => array(
				'name'          => __( 'Enquiries', 'guru' ),
				'singular_name' => __( 'Enquiry', 'guru' ),
				'menu_name'     => __( 'Enquiries', 'guru' ),
			),
			'public'          => false,
			'show_ui'         => true,
			'menu_icon'       => 'dashicons-email-alt',
			'menu_position'   => 26,
			'supports'        => array( 'title', 'editor' ),
			'capabilities'    => array( 'create_posts' => 'do_not_allow' ),
			'map_meta_cap'    => true,
		)
	);
}
add_action( 'init', 'guru_register_enquiry_post_type' );

/**
 * Handle a submission from the footer / modal contact form.
 */
function guru_handle_contact() {
	$redirect = isset( $_POST['guru_redirect'] )
		? esc_url_raw( wp_unslash( $_POST['guru_redirect'] ) )
		: home_url( '/' );

	// Only redirect to this site.
	if ( 0 !== strpos( $redirect, home_url() ) ) {
		$redirect = home_url( '/' );
	}

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

	// Required fields.
	if ( '' === $name || '' === $company || '' === $phone || ! is_email( $email ) ) {
		wp_safe_redirect( $fail );
		exit;
	}

	$cc    = ( '+other' === $cc ) ? '' : $cc;
	$tel   = trim( $cc . ' ' . $phone );
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
	$post_id = wp_insert_post(
		array(
			'post_type'    => 'guru_enquiry',
			'post_status'  => 'publish',
			/* translators: %1$s: person's name, %2$s: company. */
			'post_title'   => sprintf( __( '%1$s — %2$s', 'guru' ), $name, $company ),
			'post_content' => $body,
		)
	);

	if ( $post_id && ! is_wp_error( $post_id ) ) {
		foreach ( array( 'name' => $name, 'company' => $company, 'phone' => $tel, 'email' => $email, 'service' => $service ) as $k => $v ) {
			update_post_meta( $post_id, '_guru_' . $k, $v );
		}
	}

	$to      = get_theme_mod( 'guru_email', get_option( 'admin_email' ) );
	$subject = sprintf(
		/* translators: %s: company name. */
		__( 'Website Enquiry — %s', 'guru' ),
		$company
	);
	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'Reply-To: ' . $name . ' <' . $email . '>',
	);

	wp_mail( $to, $subject, $body, $headers );

	wp_safe_redirect( $ok );
	exit;
}
add_action( 'admin_post_nopriv_guru_contact', 'guru_handle_contact' );
add_action( 'admin_post_guru_contact', 'guru_handle_contact' );

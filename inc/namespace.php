<?php

namespace Fsylum\RegistrationPassword;


use WP_Error;
use WP_User;

if ( ! defined( 'ABSPATH' ) ) exit;

function bootstrap() {
	add_action('login_enqueue_scripts', __NAMESPACE__ . '\\load_user_profile_script');
	add_action('register_form', __NAMESPACE__ . '\\add_password_fields_to_the_registration_page');
	add_filter('registration_errors', __NAMESPACE__ . '\\validate_password_after_submission');
	add_filter('wp_pre_insert_user_data', __NAMESPACE__ . '\\set_user_custom_password_for_registration', 10, 2);
	add_filter('wp_new_user_notification_email', __NAMESPACE__ . '\\modify_new_user_registration_email_message', 10, 2);
}

/**
 * Load user-profile script on the registration page.
 *
 * @return void
 */
function load_user_profile_script(): void {
	if (!wp_script_is('user-profile')) {
		wp_enqueue_script('user-profile');
	}
}

/**
 * Add a slightly modified version of password fields on the registration page based on wp-login.php.
 *
 * @return void
 */
function add_password_fields_to_the_registration_page(): void {
    wp_nonce_field('fs_registration_password_nonce', 'fs_registration_password_nonce');
	?>
    <div class="user-pass1-wrap">
        <p>
            <label for="pass1"><?php esc_html_e('Password', 'fs-registration-password'); ?></label>
        </p>

        <div class="wp-pwd">
            <input type="password" data-reveal="1" data-pw="<?php echo esc_attr(wp_generate_password(16)); ?>" name="pass1" id="pass1" class="input password-input" size="24" value="" autocomplete="new-password" aria-describedby="pass-strength-result">

            <button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e('Hide password', 'fs-registration-password'); ?>">
                <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
            </button>
            <div id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php esc_html_e('Strength indicator', 'fs-registration-password'); ?></div>
        </div>
        <div class="pw-weak">
            <input type="checkbox" name="pw_weak" id="pw-weak" class="pw-checkbox">
            <label for="pw-weak"><?php esc_html_e('Confirm use of weak password', 'fs-registration-password'); ?></label>
        </div>
    </div>

    <p class="user-pass2-wrap">
        <label for="pass2"><?php esc_html_e('Confirm password', 'fs-registration-password'); ?></label>
        <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="new-password" spellcheck="false">
    </p>

    <p class="description indicator-hint"><?php esc_html(wp_get_password_hint()); ?></p>
	<?php
}

/**
 * Ensure that password is not empty before registering the user.
 *
 * @param WP_Error $errors A WP_Error object containing any errors encountered during registration.
 *
 * @return WP_Error
 */
function validate_password_after_submission(WP_Error $errors): WP_Error {
    // No verification required as we are only checking for value existence.
    // phpcs:ignore WordPress.Security.NonceVerification.Missing
	if (empty($_POST['pass1'])) {
		$errors->add('empty_password', '<strong>Error</strong>: Please enter your password.');
	}

	return $errors;
}

/**
 * @param array $data The current user data to be inserted
 * @param bool $update Whether the user is being updated rather than created.
 *
 * @return array
 */
function set_user_custom_password_for_registration(array $data, bool $update): array {
	if ($update) {
		return $data;
	}

    if (!isset($_SERVER['REQUEST_METHOD'])) {
        return $data;
    }

	if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
		return $data;
	}

	if ( !isset($_POST['wp-submit'])) {
		return $data;
	}

	if ( !isset($_POST['user_login'])) {
		return $data;
	}

	if ( !isset($_POST['user_email'])) {
		return $data;
	}

	if ( !isset($_POST['pass1'])) {
		return $data;
	}

	if ($_POST['user_login'] !== $data['user_login']) {
		return $data;
	}

	if ($_POST['user_email'] !== $data['user_email']) {
		return $data;
	}

	if ($_POST['wp-submit'] !== 'Register') {
		return $data;
	}

	if (!isset($_POST['fs_registration_password_nonce']) ) {
		return $data;
	}

	// Intentionally not sanitised because we're validating the nonce.
	// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
    if (!wp_verify_nonce(wp_unslash($_POST['fs_registration_password_nonce']), 'fs_registration_password_nonce')) {
        return $data;
    }

    // Intentionally not sanitised because we're hashing the raw input.
    // phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
	$data['user_pass'] = wp_hash_password(wp_unslash($_POST['pass1']));

	return $data;
}

/**
 * @param array $wp_new_user_notification_email Current WP mail data
 * @param WP_User $user User object for new user.
 *
 * @return array
 */
function modify_new_user_registration_email_message(array $wp_new_user_notification_email, WP_User $user): array {
	/* translators: %s: The current user's username */
	$message  = sprintf(__('Username: %s', 'fs-registration-password'), $user->user_login) . "\r\n\r\n";
	$message .= __('You can now log in to the site using the password you\'ve provided during the registration.', 'fs-registration-password') . "\r\n\r\n";
	$message .= wp_login_url() . "\r\n";

	$wp_new_user_notification_email['message'] = $message;

	return $wp_new_user_notification_email;
}

<?php

namespace Fsylum\RegistrationPassword\WP;

use Fsylum\RegistrationPassword\Contracts\Runnable;

class Auth implements Runnable
{
    public function run()
    {
        add_action('login_enqueue_scripts', [$this, 'loadUserProfileJs']);
        add_action('register_form', [$this, 'addPasswordFields']);
        add_filter('registration_errors', [$this, 'validatePassword']);
        add_filter('random_password', [$this, 'setUserPassword']);
        add_filter('wp_new_user_notification_email', [$this, 'modifyEmailNotification'], 10, 2);
    }

    public function loadUserProfileJs()
    {
        if (!wp_script_is('user-profile')) {
            wp_enqueue_script('user-profile');
        }
    }

    public function addPasswordFields()
    {
        // taken directly from wp-login.php, with slight modification to suit the context
        ?>
            <input type="hidden" name="fs_is_password_for_registration" value="yes">
            <div class="user-pass1-wrap">
                <p>
                    <label for="pass1"><?php _e('Password'); ?></label>
                </p>

                <div class="wp-pwd">
                    <input type="password" data-reveal="1" data-pw="<?php echo esc_attr(wp_generate_password(16)); ?>" name="pass1" id="pass1" class="input password-input" size="24" value="" autocomplete="off" aria-describedby="pass-strength-result">

                    <button type="button" class="button button-secondary wp-hide-pw hide-if-no-js" data-toggle="0" aria-label="<?php esc_attr_e('Hide password'); ?>">
                        <span class="dashicons dashicons-hidden" aria-hidden="true"></span>
                    </button>
                    <div id="pass-strength-result" class="hide-if-no-js" aria-live="polite"><?php _e('Strength indicator'); ?></div>
                </div>
                <div class="pw-weak">
                    <input type="checkbox" name="pw_weak" id="pw-weak" class="pw-checkbox">
                    <label for="pw-weak"><?php _e('Confirm use of weak password'); ?></label>
                </div>
            </div>

            <p class="user-pass2-wrap">
                <label for="pass2"><?php _e('Confirm password'); ?></label>
                <input type="password" name="pass2" id="pass2" class="input" size="20" value="" autocomplete="off">
            </p>

            <p class="description indicator-hint"><?php echo wp_get_password_hint(); ?></p>
            <br class="clear">
        <?php
    }

    public function validatePassword($errors)
    {
        if (empty($_POST['pass1'])) {
            $errors->add('empty_password', '<strong>Error</strong>: Please enter your password.');
        }

        return $errors;
    }

    public function setUserPassword($password)
    {
        if (!isset($_POST['fs_is_password_for_registration']) ) {
            return $password;
        }

        if (sanitize_text_field($_POST['fs_is_password_for_registration']) !== 'yes') {
            return $password;
        }

        return $_POST['pass1'];
    }

    public function modifyEmailNotification($wp_new_user_notification_email, $user)
    {
        $message  = sprintf(__('Username: %s'), $user->user_login) . "\r\n\r\n";
        $message .= __('You can now log in to the site using the password you\'ve provided during the registration.') . "\r\n\r\n";
        $message .= wp_login_url() . "\r\n";

        $wp_new_user_notification_email['message'] = $message;

        return $wp_new_user_notification_email;
    }
}

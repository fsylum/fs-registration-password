<?php
/**
 * Plugin Name: FS Registration Password
 * Plugin URI: https://github.com/fsylum/fs-registration-password
 * Description: Allow users to set their own password during site registration
 * Version: 2.0.1
 * Author: Firdaus Zahari
 * Author URI: https://fsylum.net
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * Tested up to: 6.9
 * Requires at least: 5.9
 * Requires PHP: 8.2
 */

namespace Fsylum\RegistrationPassword;

if ( ! defined( 'ABSPATH' ) ) exit;

require __DIR__ . '/inc/namespace.php';

bootstrap();

<?php
/**
 * Plugin Name: Registration Password
 * Plugin URI: https://github.com/fsylum/fs-registration-password
 * Description: Allow users to set their own password during site registration
 * Version: 1.0.1
 * Author: Firdaus Zahari
 * Author URI: https://fsylum.net
 * Requires at least: 5.6
 * Requires PHP:      7.3
 */

require __DIR__ . '/vendor/autoload.php';

define('FSRP_PLUGIN_URL', untrailingslashit(plugin_dir_url(__FILE__)));
define('FSRP_PLUGIN_PATH', untrailingslashit(plugin_dir_path(__FILE__)));
define('FSRP_PLUGIN_BASENAME', plugin_basename(__FILE__));
define('FSRP_PLUGIN_VERSION', '1.0.1');

$app = new Fsylum\RegistrationPassword\App;

$app->addService(new Fsylum\RegistrationPassword\WP\Auth);

// Finally run it
$app->run();

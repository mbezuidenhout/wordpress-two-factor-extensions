<?php
/**
 * The plugin bootstrap file
 *
 * This file is read by WordPress to generate the plugin information in the plugin
 * admin area. This file also includes all of the dependencies used by the plugin,
 * registers the activation and deactivation functions, and defines a function
 * that starts the plugin.
 *
 * @link              https://www.facebook.com/marius.bezuidenhout1
 * @since             1.0.0
 * @package           Two_Factor_Extensions
 *
 * @wordpress-plugin
 * Plugin Name:       Two Factor Extensions
 * Plugin URI:        https://github.com/mbezuidenhout/two-factor-extensions
 * Description:       Extensions to the Two-Factor plugin providing additional sms authentication.
 * Version:           1.0.0
 * Author:            Marius Bezuidenhout
 * Author URI:        https://www.facebook.com/marius.bezuidenhout1
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       two-factor-extensions
 * Domain Path:       /languages
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

/**
 * Currently plugin version.
 * Start at version 1.0.0 and use SemVer - https://semver.org
 * Rename this for your plugin and update it as you release new versions.
 */
define( 'TWO_FACTOR_EXTENSIONS_VERSION', '1.0.0' );

/**
 * The code that runs during plugin activation.
 * This action is documented in includes/class-two-factor-extensions-activator.php
 */
function activate_two_factor_extensions() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-two-factor-extensions-activator.php';
	Two_Factor_Extensions_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 * This action is documented in includes/class-two-factor-extensions-deactivator.php
 */
function deactivate_two_factor_extensions() {
	require_once plugin_dir_path( __FILE__ ) . 'includes/class-two-factor-extensions-deactivator.php';
	Two_Factor_Extensions_Deactivator::deactivate();
}

register_activation_hook( __FILE__, 'activate_two_factor_extensions' );
register_deactivation_hook( __FILE__, 'deactivate_two_factor_extensions' );

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require plugin_dir_path( __FILE__ ) . 'includes/class-two-factor-extensions.php';

/**
 * Begins execution of the plugin.
 *
 * Since everything within the plugin is registered via hooks,
 * then kicking off the plugin from this point in the file does
 * not affect the page life cycle.
 *
 * @since    1.0.0
 */
function run_two_factor_extensions() {

	$plugin = new Two_Factor_Extensions();
	$plugin->run();

}
run_two_factor_extensions();

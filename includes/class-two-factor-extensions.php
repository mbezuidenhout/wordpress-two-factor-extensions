<?php
/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Two_Factor_Extensions {

	/**
	 * The loader that's responsible for maintaining and registering all hooks that power
	 * the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      Two_Factor_Extensions_Loader $loader Maintains and registers all hooks for the plugin.
	 */
	protected $loader;

	/**
	 * The unique identifier of this plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $plugin_name The string used to uniquely identify this plugin.
	 */
	protected $plugin_name;

	/**
	 * The current version of the plugin.
	 *
	 * @since    1.0.0
	 * @access   protected
	 * @var      string $version The current version of the plugin.
	 */
	protected $version;

	/**
	 * Define the core functionality of the plugin.
	 *
	 * Set the plugin name and the plugin version that can be used throughout the plugin.
	 * Load the dependencies, define the locale, and set the hooks for the admin area and
	 * the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function __construct() {
		if ( defined( 'TWO_FACTOR_EXTENSIONS_VERSION' ) ) {
			$this->version = TWO_FACTOR_EXTENSIONS_VERSION;
		} else {
			$this->version = '1.0.0';
		}
		$this->plugin_name = 'two-factor-extensions';

		$this->load_dependencies();
		$this->set_locale();
		$this->define_public_hooks();

		// Load admin dependencies.
		$this->loader->add_action( 'init', $this, 'load_admin' );
	}

	/**
	 * Load admin classes and hooks
	 */
	public function load_admin() {
		/**
		 * The class responsible for defining all actions that occur in the admin area.
		 */
		if ( is_user_logged_in() && is_admin() ) {
			require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/class-two-factor-extensions-admin.php';
			$this->define_admin_hooks();
		}
	}

	/**
	 * Load the required dependencies for this plugin.
	 *
	 * Include the following files that make up the plugin:
	 *
	 * - Two_Factor_Extensions_Loader. Orchestrates the hooks of the plugin.
	 * - Two_Factor_Extensions_i18n. Defines internationalization functionality.
	 * - Two_Factor_Extensions_Admin. Defines all hooks for the admin area.
	 * - Two_Factor_Extensions_Public. Defines all hooks for the public side of the site.
	 *
	 * Create an instance of the loader which will be used to register the hooks
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function load_dependencies() {

		/**
		 * The class responsible for orchestrating the actions and filters of the
		 * core plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-loader.php';

		/**
		 * The class responsible for defining internationalization functionality
		 * of the plugin.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-i18n.php';

		/**
		 * The class responsible for defining all actions that occur in the public-facing
		 * side of the site.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/class-two-factor-extensions-public.php';

		/**
		 * An API class for settings pages.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-settings-api.php';

		/**
		 * The class responsible for showing and storing of plugin settings.
		 */
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-settings.php';

		$this->loader = new Two_Factor_Extensions_Loader();

	}

	/**
	 * Define the locale for this plugin for internationalization.
	 *
	 * Uses the Two_Factor_Extensions_i18n class in order to set the domain and to register the hook
	 * with WordPress.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function set_locale() {

		$plugin_i18n = new Two_Factor_Extensions_i18n();

		$this->loader->add_action( 'plugins_loaded', $plugin_i18n, 'load_plugin_textdomain' );

	}

	/**
	 * Register all of the hooks related to the admin area functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_admin_hooks() {

		$plugin_admin = new Two_Factor_Extensions_Admin( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_styles' );
		$this->loader->add_action( 'admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts' );

		$this->loader->add_action( 'admin_menu', $plugin_admin, 'admin_menu_options' );
		$this->loader->add_action( 'admin_init', Two_Factor_Extensions_Settings::get_instance(), 'add_settings_fields' );

		$this->loader->run();
	}

	/**
	 * Register all of the hooks related to the public-facing functionality
	 * of the plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 */
	private function define_public_hooks() {

		$plugin_public = new Two_Factor_Extensions_Public( $this->get_plugin_name(), $this->get_version() );

		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_styles' );
		$this->loader->add_action( 'wp_enqueue_scripts', $plugin_public, 'enqueue_scripts' );

		$this->loader->add_action( 'plugins_loaded', $plugin_public, 'add_extensions' );
		$this->loader->add_filter( 'user_contactmethods', $plugin_public, 'user_contactmethods' );
		$this->loader->add_action( 'register_form', $plugin_public, 'register_form' );

		$this->loader->add_action( 'wp_login', $plugin_public, 'check_enforced_2fa', 1, 2 );
		$this->loader->add_action( 'login_form_validate_2fa_mobile_number', $plugin_public, 'validate_2fa_mobile_number' );
		$this->loader->add_action( 'login_enqueue_scripts', $plugin_public, 'login_enqueue_script' );
		$this->loader->add_action( 'login_enqueue_scripts', $plugin_public, 'login_enqueue_style' );
	}

	/**
	 * Locate a template and return the path for inclusion.
	 *
	 * This is the load order:
	 *
	 * @since 1.0.0
	 *
	 * yourtheme/$template_path/$template_name
	 * yourtheme/$template_name
	 * $default_path/$template_name
	 *
	 * @param string $template_name Template name.
	 * @param string $template_path Template path. (default: '').
	 * @param string $default_path Default path. (default: '').
	 *
	 * @return string
	 */
	public static function locate_template( $template_name, $template_path = '', $default_path = '' ) {
		if ( ! $template_path ) {
			$template_path = get_stylesheet_directory() . 'playsms/';
		}

		if ( ! $default_path ) {
			$default_path = plugin_dir_path( dirname( __FILE__ ) ) . 'templates/';
		}

		// Look within passed path within the theme - this is priority.
		$template = locate_template(
			[
				trailingslashit( $template_path ) . $template_name,
				$template_name,
			]
		);

		// Get default template/.
		if ( ! $template ) {
			$template = $default_path . $template_name;
		}

		// Return what we found.
		return apply_filters( '2fe_locate_template', $template, $template_name, $template_path );
	}

	/**
	 * Run the loader to execute all of the hooks with WordPress.
	 *
	 * @since    1.0.0
	 */
	public function run() {
		$this->loader->run();
	}

	/**
	 * The name of the plugin used to uniquely identify it within the context of
	 * WordPress and to define internationalization functionality.
	 *
	 * @return    string    The name of the plugin.
	 * @since     1.0.0
	 */
	public function get_plugin_name() {
		return $this->plugin_name;
	}

	/**
	 * The reference to the class that orchestrates the hooks with the plugin.
	 *
	 * @return    Two_Factor_Extensions_Loader    Orchestrates the hooks of the plugin.
	 * @since     1.0.0
	 */
	public function get_loader() {
		return $this->loader;
	}

	/**
	 * Retrieve the version number of the plugin.
	 *
	 * @return    string    The version number of the plugin.
	 * @since     1.0.0
	 */
	public function get_version() {
		return $this->version;
	}

}

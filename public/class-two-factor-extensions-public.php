<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/public
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Two_Factor_Extensions_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of the plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Two_Factor_Extensions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Two_Factor_Extensions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/two-factor-extensions-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Two_Factor_Extensions_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Two_Factor_Extensions_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/two-factor-extensions-public.js', array( 'jquery' ), $this->version, false );

	}

	/**
	 * Show admin notice about missing plugins
	 */
	public function admin_notice_plugin_missing() {
		?>
        <div class="error">
            <p><?php esc_html_e( 'Two Factor Extensions is enabled but not effective. It requires the Two-Factor and WP SMS plugins in order to work.', 'two-factor-extensions' ); ?></p>
        </div>
		<?php
	}

	/**
	 * Load extensions to the two-factor WordPress plugin.
	 */
	public function add_extensions() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'plugin.php';
		}
		if ( is_plugin_active( 'two-factor/two-factor.php' ) && ( is_plugin_active( 'playsms/playsms.php' || is_plugin_active( 'wp-sms/wp-sms.php' ) ) ) ) {
			add_filter( 'two_factor_providers', array( $this, 'add_providers' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notice_plugin_missing' ) );
		}
	}

	/**
	 * Add more 2fa providers
	 *
	 * @param array $providers List of configured providers.
	 *
	 * @return array
	 */
	public function add_providers( $providers ) {
		$disabled_providers                     = array(
			//'Two_Factor_Email',
			'Two_Factor_Totp',
			'Two_Factor_FIDO_U2F',
			'Two_Factor_Backup_Codes',
			'Two_Factor_Dummy',
		);
		$providers['Two_Factor_Extensions_SMS'] = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-sms.php';

		foreach ( $providers as $provider => $class_file ) {
			if ( in_array( $provider, $disabled_providers ) ) {
				unset( $providers[ $provider ] );
			}
		}

		return $providers;
	}

	/**
	 * Add the mobile number field to the user registration form.
	 */
	public function register_form() {
	}

	/**
	 * Add company field to user contact methods.
	 *
	 * @param array $methods Associative array of contact methods.
	 *
	 * @return mixed
	 */
	public function user_contactmethods( $methods ) {
		$new_methods = array(
			'mobile' => __( 'Mobile', 'two-factor-extensions' ),
		);

		return array_merge( $methods, $new_methods );
	}

}

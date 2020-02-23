<?php
/**
 * Defines the plugin settings.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/includes
 */

/**
 * Class Two_Factor_Extensions_Settings defined the plugin options.
 */
class Two_Factor_Extensions_Settings {
	/**
	 * Singleton instance of this class.
	 *
	 * @var Two_Factor_Extensions_Settings
	 */
	private static $instance;

	/**
	 * Instance of the Playsms_Settings_API class.
	 *
	 * @var Two_Factor_Extensions_Settings_API
	 */
	private $settings_api;

	/**
	 * Array of plugin settings.
	 *
	 * @var array
	 */
	protected $basic_settings;

	/**
	 * Array of debug settings.
	 *
	 * @var array
	 */
	protected $debug_settings;

	/**
	 * Returns the singleton instance of the settings class
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	/**
	 * Get setting by name
	 *
	 * @param string $name    Name of setting to retrieve.
	 * @param string $section Name of the setting section.
	 *
	 * @return mixed
	 */
	public function get_setting( $name, $section = 'basic_settings' ) {
		if ( isset( $this->$section[ $name ] ) ) {
			return $this->$section[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Return all settings
	 *
	 * @param string $section Name of the section.
	 *
	 * @return array
	 */
	public function get_settings( $section = 'basic_settings' ) {
		return $this->$section;
	}

	/**
	 * Two_Factor_Extensions_Settings constructor.
	 */
	public function __construct() {
		$default_settings       = [];
		$default_debug_settings = [];
		$this->settings_api     = new Two_Factor_Extensions_Settings_API();

		foreach ( $this->get_settings_fields()['two_factor_extensions_basics'] as $setting ) {
			$default_settings[ $setting['name'] ] = $setting['default'];
		}
		foreach ( $this->get_settings_fields()['two_factor_extensions_debug'] as $setting ) {
			$default_debug_settings[ $setting['name'] ] = $setting['default'];
		}

		$settings             = empty( get_option( 'two_factor_extensions_basics' ) ) ? [] : get_option( 'two_factor_extensions_basics' );
		$debug                = empty( get_option( 'two_factor_extensions_debug' ) ) ? [] : get_option( 'two_factor_extensions_debug' );
		$this->basic_settings = wp_parse_args( $settings, $default_settings );
		$this->debug_settings = wp_parse_args( $debug, $default_debug_settings );
	}

	/**
	 * Register plugin settings
	 */
	public function settings_page() {
		echo '<div class="wrap">';

		$this->settings_api->show_navigation();
		$this->settings_api->show_forms();

		echo '</div>';
	}

	/**
	 * Define the settings sections.
	 *
	 * @return array
	 */
	private function get_settings_sections() {
		$sections = [
			[
				'id'    => 'two_factor_extensions_basics',
				'title' => __( 'Basic Settings', 'two-factor-extensions' ),
			],
			[
				'id'    => 'two_factor_extensions_debug',
				'title' => __( 'Debug Settings', 'two-factor-extensions' ),
			],
		];

		return apply_filters( 'two_factor_extensions_settings_sections', $sections );
	}

	/**
	 * Registers plugin settings
	 */
	public function add_settings_fields() {
		// set the settings.
		$this->settings_api->set_sections( $this->get_settings_sections() );
		$this->settings_api->set_fields( $this->get_settings_fields() );

		// initialize settings.
		$this->settings_api->admin_init();
	}

	/**
	 * Returns all the settings fields
	 *
	 * @return array settings fields
	 */
	public function get_settings_fields() {
		$settings_fields = array(
			'two_factor_extensions_basics' => [
				[
					'name'              => 'require_otp',
					'label'             => __( 'Require OTP', 'two-factor-extensions' ),
					'desc'              => __( 'Require users to use a one-time-pin sent to their mobile device', 'two-factor-extensions' ),
					'type'              => 'checkbox',
					'default'           => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
			'two_factor_extensions_debug'  => [
				[
					'name'              => 'send_debug_email',
					'label'             => __( 'Send debug e-mails', 'two-factor-extensions' ),
					'desc'              => __( 'Send debug e-mails', 'two-factor-extensions' ),
					'type'              => 'checkbox',
					'default'           => false,
					'sanitize_callback' => 'sanitize_text_field',
				],
				[
					'name'              => 'debug_email',
					'label'             => __( 'Debug E-mail Address', 'two-factor-extensions' ),
					'desc'              => __( 'Which address to send debug messages to', 'two-factor-extensions' ),
					'type'              => 'text',
					'default'           => get_option( 'admin_email' ),
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		);

		return apply_filters( 'two_factor_extensions_settings', $settings_fields );
	}
}
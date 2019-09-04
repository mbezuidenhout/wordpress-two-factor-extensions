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
	 * @param string $name Name of setting to retrieve.
	 *
	 * @return mixed
	 */
	public function get_setting( $name ) {
		if ( isset( $this->basic_settings[ $name ] ) ) {
			return $this->basic_settings[ $name ];
		} else {
			return false;
		}
	}

	/**
	 * Return all settings
	 *
	 * @return array
	 */
	public function get_settings() {
		return $this->basic_settings;
	}

	/**
	 * Two_Factor_Extensions_Settings constructor.
	 */
	public function __construct() {
		$this->settings_api = new Two_Factor_Extensions_Settings_API();

		$default_settings = array();
		foreach ( $this->get_settings_fields()['two_factor_extensions_basics'] as $setting ) {
			$default_settings[ $setting['name'] ] = $setting['default'];
		}

		$settings             = empty( get_option( 'two_factor_extensions_basics' ) ) ? array() : get_option( 'two_factor_extensions_basics' );
		$this->basic_settings = wp_parse_args( $settings, $default_settings );
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
		$sections = array(
			array(
				'id'    => 'two_factor_extensions_basics',
				'title' => __( 'Basic Settings', 'two-factor-extensions' ),
			),
		);

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
			'two_factor_extensions_basics' => array(
				array(
					'name'              => 'require_otp',
					'label'             => __( 'Require OTP', 'two-factor-extensions' ),
					'desc'              => __( 'Require users to use a one-time-pin sent to their mobile device', 'two-factor-extensions' ),
					'type'              => 'checkbox',
					'default'           => false,
					'sanitize_callback' => 'sanitize_text_field',
				),
			),
		);

		return apply_filters( 'two_factor_extensions_settings', $settings_fields );
	}
}
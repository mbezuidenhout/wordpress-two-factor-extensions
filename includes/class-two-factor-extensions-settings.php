<?php
/**
 * Defined the plugin settings.
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
	 * Singleton instance of this class
	 *
	 * @var Two_Factor_Extensions_Settings
	 */
	private static $instance;

	/**
	 * Returns the singleton instance of the settings class
	 */
	public static function get_instance() {
		if ( ! self::$instance instanceof self ) {
			self::$instance = new static();
		}

		return self::$instance;
	}

	protected function save_settings() {

	}

	/**
	 * Register plugin settings
	 */
	public function settings_page() {
		$this->add_settings_fields();
		// Check that the user is allowed to update options.
		if ( ! current_user_can( 'manage_options' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'two_factor_extensions' ) );
		}

		// save options.
		$this->save_settings();

		?>
		<?php settings_errors(); ?>

        <form method="post" action="">

			<?php settings_fields( 'two_factor_extensions_settings_group' );               //settings group, defined as first argument in register_setting ?>
			<?php do_settings_sections( 'two_factor_extensions_settings_page_section' );   //same as last argument used in add_settings_section ?>
			<?php submit_button(); ?>

			<?php wp_nonce_field( 'two_factor_extensions_settings_nonce' ); ?>
            <div class="clear"></div>
        </form>
		<?php
	}

	public function validate_settings_fields() {

	}

	public function render_settings_field() {

	}

	public function get_settings() {
		$settings = apply_filters(
			'playsms_settings',
			array(
				array(
					'id'      => 'two_factor_extensions_enable_options',
					'title'   => __( 'Enabled options', 'two_factor_extensions' ),
					'desc'    => 'Options available to the user',
					'default' => '',
					'type'    => 'radio',
					'tip'     => true,
				),
				array(
					'id'      => 'playsms_password',
					'title'   => __( 'Password', 'playsms' ),
					'desc'    => '',
					'default' => '',
					'type'    => 'password',
					'tip'     => true,
				),
				array(
					'id'      => 'playsms_webservices_token',
					'title'   => __( 'Web Services Token', 'playsms' ),
					'desc'    => '',
					'default' => '',
					'type'    => 'text',
					'tip'     => true,
				),
			)
		);

		return $settings;
	}

	/**
	 * Registers plugin settings
	 */
	protected function add_settings_fields() {
		register_setting( 'two_factor_extensions_settings_group', 'two_factor_extensions_settings', array(
			$this,
			'validate_settings_fields'
		) );
		add_settings_section( 'two_factor_extensions_settings_section', null, null, 'two_factor_extensions_settings_page_section' );

		foreach ( $this->get_settings() as $setting ) {
			add_settings_field( $setting['id'], $setting['title'], array( $this, 'render_settings_field' ),
				'two_factor_extensions_settings_page_section', 'two_factor_extensions_settings_section' );
		}
	}


}
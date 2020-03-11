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
		if ( is_plugin_active( 'two-factor/two-factor.php' ) && ( is_plugin_active( 'playsms/playsms.php' ) || is_plugin_active( 'wp-sms/wp-sms.php' ) ) ) {
			add_filter( 'two_factor_providers', array( $this, 'add_providers' ) );
		} else {
			add_action( 'admin_notices', array( $this, 'admin_notice_plugin_missing' ) );
		}
	}

	/**
	 * Add styles to the login pages.
	 */
	public function login_enqueue_style() {
		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/two-factor-extensions-login.css', array(), $this->version, 'all' );
	}

	/**
	 * Add scripts to the login page.
	 */
	public function login_enqueue_script() {
		wp_enqueue_script( $this->plugin_name . '-login', plugin_dir_url( __FILE__ ) . 'js/two-factor-extensions-login.js', array( 'jquery' ), $this->version, false );

		$script_localization = array(
			'resendCodeLabel' => __( 'Resend Code', 'two-factor' ),
			'resendCodeName'  => Two_Factor_Extensions_SMS::INPUT_NAME_RESEND_CODE,
			'nonce'           => wp_create_nonce( 'mobile-number-verification' ),
			'ajaxUrl'         => admin_url( 'admin-ajax.php' ),
			'ajaxAction'      => 'add_mobile',
		);

		wp_localize_script( $this->plugin_name . '-login', 'loginPage', $script_localization );
	}

	/**
	 * Add more 2fa providers
	 *
	 * @param array $providers List of configured providers.
	 *
	 * @return array
	 */
	public function add_providers( $providers ) {
		$providers['Two_Factor_Extensions_SMS'] = plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-two-factor-extensions-sms.php';

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

	/**
	 * Check enforced 2fa methods and obtain necessary parameters.
	 *
	 * @param string  $user_login Username.
	 * @param WP_User $user       WP_User object of the logged-in user.
	 */
	public function check_enforced_2fa( $user_login, $user ) {
		if ( ! Two_Factor_Extensions_SMS::is_enforcing_mobile_two_factor() ) {
			return;
		}

		$enabled_providers = Two_Factor_Core::get_enabled_providers_for_user( $user );

		if ( ! in_array( 'Two_Factor_Extensions_SMS', $enabled_providers, true ) ) {
			$enabled_providers[] = 'Two_Factor_Extensions_SMS';
			update_user_meta( $user->ID, Two_Factor_Core::ENABLED_PROVIDERS_USER_META_KEY, $enabled_providers );
		}

		$user_mobile = get_user_meta( $user->ID, 'mobile', true );
		if ( ! empty( $user_mobile ) ) {
			return;
		}

		wp_clear_auth_cookie();

		self::show_mobile_number_verification( $user );
		exit;
	}

	/**
	 * Validate mobile number supplied for two factor authentication.
	 */
	public function validate_2fa_mobile_number() {
		//phpcs:disable WordPress.Security.NonceVerification
		if ( ! isset( $_POST['wp-auth-id'], $_POST['wp-auth-nonce'] ) ) {
			return;
		}

		$user = get_userdata( (int) $_POST['wp-auth-id'] );
		if ( ! $user ) {
			return;
		}

		$nonce = sanitize_key( $_POST['wp-auth-nonce'] );
		if ( true !== Two_Factor_Core::verify_login_nonce( $user->ID, $nonce ) ) {
			wp_safe_redirect( get_bloginfo( 'url' ) );
			exit;
		}

		if ( isset( $_POST['provider'] ) ) {
			/* @var Two_Factor_Provider[] $providers An array of two factor authentication providers. */
			$providers = Two_Factor_Core::get_providers();
			if ( isset( $providers[ $_POST['provider'] ] ) ) {
				$provider = $providers[ $_POST['provider'] ]; // phpcs:ignore WordPress.Security.ValidatedSanitizedInput
			} else {
				wp_die( esc_html__( 'Cheatin&#8217; uh?', 'two-factor' ), 403 );
			}
		} else {
			$provider = Two_Factor_Core::get_primary_provider_for_user( $user->ID );
		}

		// Allow the provider to re-send codes, etc.
		if ( true === $provider->pre_process_authentication( $user ) ) {
			$login_nonce = Two_Factor_Core::create_login_nonce( $user->ID );
			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'two-factor' ) );
			}

			self::verify_mobile_html( $user, $login_nonce['key'], $_REQUEST['redirect_to'], '', $provider );
			exit;
		}

		// Ask the provider to verify the second factor.
		if ( true !== $provider->validate_authentication( $user ) ) {
			do_action( 'wp_login_failed', $user->user_login );

			$login_nonce = Two_Factor_Core::create_login_nonce( $user->ID );
			if ( ! $login_nonce ) {
				wp_die( esc_html__( 'Failed to create a login nonce.', 'two-factor' ) );
			}

			self::verify_mobile_html( $user, $login_nonce['key'], $_REQUEST['redirect_to'], esc_html__( 'ERROR: Invalid verification code.', 'two-factor' ), $provider );
			exit;
		}

		Two_Factor_Core::delete_login_nonce( $user->ID );

		$rememberme = false;
		if ( isset( $_REQUEST['rememberme'] ) && $_REQUEST['rememberme'] ) {
			$rememberme = true;
		}

		wp_set_auth_cookie( $user->ID, $rememberme );

		// Must be global because that's how login_header() uses it.
		global $interim_login;
		$interim_login = isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited


		if ( $interim_login ) {
			$customize_login = isset( $_REQUEST['customize-login'] );
			if ( $customize_login ) {
				wp_enqueue_script( 'customize-base' );
			}
			$message       = '<p class="message">' . __( 'You have logged in successfully.', 'two-factor' ) . '</p>';
			$interim_login = 'success'; // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited
			login_header( '', $message );
			?>
            </div>
			<?php
			/** This action is documented in wp-login.php */
			do_action( 'login_footer' );
			?>
			<?php if ( $customize_login ) : ?>
                <script type="text/javascript">setTimeout(function () {
                        new wp.customize.Messenger({
                            url: '<?php echo wp_customize_url(); /* WPCS: XSS OK. */ ?>',
                            channel: 'login'
                        }).send('login')
                    }, 1000);</script>
			<?php endif; ?>
            </body></html>
			<?php
			exit;
		}
		$redirect_to = apply_filters( 'login_redirect', $_REQUEST['redirect_to'], $_REQUEST['redirect_to'], $user );
		wp_safe_redirect( $redirect_to );

		exit;
		//phpcs:enable
	}

	/**
	 * Display the mobile number verification form.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public static function show_mobile_number_verification( $user ) {
		if ( ! $user ) {
			$user = wp_get_current_user();
		}

		$nonce = Two_Factor_Core::create_login_nonce( $user->ID );

		$redirect_to = isset( $_REQUEST['redirect_to'] ) ? $_REQUEST['redirect_to'] : admin_url(); // phpcs:ignore WordPress.Security.NonceVerification

		self::verify_mobile_html( $user, $nonce['key'], $redirect_to, '', Two_Factor_Extensions_SMS::get_instance() );
	}

	/**
	 * Generate the html form for obtaining the user's mobile number.
	 *
	 * @param WP_User             $user         WP_User object of the logged-in user.
	 * @param string              $login_nonce  A string nonce stored in usermeta.
	 * @param string              $redirect_to  The URL to which the user would like to be redirected.
	 * @param string              $error_msg    The error message to show.
	 * @param Two_Factor_Provider $provider     Instance of the 2fa provider.
	 */
	public static function verify_mobile_html( $user, $login_nonce, $redirect_to, $error_msg, $provider ) {
		$interim_login = isset( $_REQUEST['interim-login'] ); // phpcs:ignore WordPress.Security.NonceVerification
		$newmobile     = get_user_meta( $user->ID, '_new_mobile', true );

		$resend_code = method_exists( $provider, 'user_has_token' ) && $provider->user_has_token( $user->ID );
		$rememberme  = 0;
		if ( isset( $_REQUEST['rememberme'] ) && $_REQUEST['rememberme'] ) { // phpcs:ignore WordPress.Security.NonceVerification
			$rememberme = 1;
		}

		if ( ! function_exists( 'login_header' ) ) {
			// We really should migrate login_header() out of `wp-login.php` so it can be called from an includes file.
			include_once TWO_FACTOR_DIR . 'includes/function.login-header.php';
		}

		login_header();

		if ( ! empty( $error_msg ) ) {
			echo '<div id="login_error"><strong>' . esc_html( $error_msg ) . '</strong><br /></div>';
		}
		?>

        <form name="validate_mobile_number_form" id="loginform"
              action="<?php echo esc_url( Two_Factor_Core::login_url( array( 'action' => 'validate_2fa_mobile_number' ), 'login_post' ) ); ?>"
              method="post" autocomplete="off">
            <input type="hidden" name="provider" id="provider" value="<?php echo esc_attr( get_class( $provider ) ); ?>"/>
            <input type="hidden" name="wp-auth-id" id="wp-auth-id" value="<?php echo esc_attr( $user->ID ); ?>"/>
            <input type="hidden" name="wp-auth-nonce" id="wp-auth-nonce"
                   value="<?php echo esc_attr( $login_nonce ); ?>"/>
			<?php if ( $interim_login ) { ?>
                <input type="hidden" name="interim-login" value="1"/>
			<?php } else { ?>
                <input type="hidden" name="redirect_to" value="<?php echo esc_attr( $redirect_to ); ?>"/>
			<?php } ?>
            <input type="hidden" name="rememberme" id="rememberme" value="<?php echo esc_attr( $rememberme ); ?>"/>

            <p><?php esc_html_e( 'Enter your a mobile number below where you can receive text messages.', 'two-factor-extensions' ); ?></p>
            <p>
                <label for="mobilenumber"><?php esc_html_e( 'Mobile Number:', 'two-factor' ); ?></label>
                <input type="tel" name="newmobile" id="mobilenumber" class="input"
                       value="<?php echo esc_attr( $newmobile ) ?>" size="8"
                       pattern="[0-9]*" autocomplete="mobile"/>
            </p>
            <p class="two-factor-extensions-message <?php echo ! $resend_code ? 'hidden' : '' ?>"><?php esc_html_e( 'A verification code has been sent to the mobile number.', 'two-factor-extensions' ); ?></p>
            <p class="two-factor-extensions-otp <?php echo ! $resend_code ? 'hidden' : '' ?>">
                <label for="authcode"><?php esc_html_e( 'Verification Code:', 'two-factor' ); ?></label>
                <input type="password" name="two-factor-sms-code" id="authcode" class="input" value="" size="20"
                       pattern="[0-9]*" autocomplete="one-time-code"/>
            </p>
            <p class="two-factor-mobile-send">
                <input type="submit" class="button" name="<?php echo esc_attr( Two_Factor_Extensions_SMS::INPUT_NAME_SEND_CODE ); ?>"
                       value="<?php echo ! $resend_code ? esc_attr__( 'Send Code', 'two-factor-extensions' ) : __( 'Resend Code', 'two-factor' ); ?>"/>
                <input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large <?php echo ! $resend_code ? 'hidden' : '' ?>"
                       value="Log In">
            </p>
        </form>

        <p id="backtoblog">
            <a href="<?php echo esc_url( home_url( '/' ) ); ?>"
               title="<?php esc_attr_e( 'Are you lost?', 'two-factor' ); ?>">
				<?php
				echo esc_html(
					sprintf(
						// translators: %s: site name.
						__( '&larr; Back to %s', 'two-factor' ),
						get_bloginfo( 'title', 'display' )
					)
				);
				?>
            </a>
        </p>
        </div>
		<?php
		/** This action is documented in wp-login.php */
		do_action( 'login_footer' ); ?>
        <div class="clear"></div>
        </body>
        </html>
		<?php
	}

}

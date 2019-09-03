<?php
/**
 * Class for creating an sms provider.
 *
 * @link       https://www.facebook.com/marius.bezuidenhout1
 * @since      1.0.0
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/includes
 */

/**
 * Class for creating an sms provider.
 *
 * Functions to add  two factor SMS capability to the
 * Two Factor WordPress plugin.
 *
 * @package    Two_Factor_Extensions
 * @subpackage Two_Factor_Extensions/includes
 * @author     Marius Bezuidenhout <marius.bezuidenhout@gmail.com>
 */
class Two_Factor_Extensions_SMS extends Two_Factor_Provider {

	const TOKEN_META_KEY         = '_two_factor_extensions_sms_token';
	const INPUT_NAME_RESEND_CODE = 'sms-resend-code';

	/**
	 * Two_Factor_Extensions_SMS constructor.
	 */
	protected function __construct() {
		add_action( 'two-factor-user-options-' . __CLASS__, array( $this, 'user_options' ) );
		add_action( 'wp_ajax_nopriv_add_mobile', array( $this, 'add_mobile' ) );

		return parent::__construct();
	}

	/** Returns the name of the provider. */
	public function get_label() {
		return __( 'Text Message', 'two-factor-extensions' );
	}

	/**
	 * Inserts markup at the end of the user profile field for this provider.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @since 1.0.0
	 */
	public function user_options( $user ) {
		$mobile = $user->get( 'mobile' );
		?>
        <div>
			<?php
			echo esc_html(
				sprintf(
				/* translators: %s: mobile phone number */
					__( 'Authentication codes will be sent to %s.', 'two-factor-extensions' ),
					$mobile
				)
			);
			?>
        </div>
		<?php
	}

	/**
	 * Generate the user token.
	 *
	 * @param int $user_id User ID.
	 *
	 * @return string
	 */
	protected function generate_token( $user_id ) {
		$token = $this->get_code();
		update_user_meta( $user_id, self::TOKEN_META_KEY, wp_hash( $token ) );

		return $token;
	}

	/**
	 * Checks if the user has a token waiting.
	 *
	 * @param int $user_id WordPress user id.
	 *
	 * @return bool
	 */
	protected function user_has_token( $user_id ) {
		$hashed_token = $this->get_user_token( $user_id );

		if ( ! empty( $hashed_token ) ) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Send code to user's new mobile number for verification.
	 *
	 * @param int $user_id User ID number.
	 * @param string $mobile User's new mobile number.
	 */
	public function add_mobile() {
		$nonce_verified = Two_Factor_Core::verify_login_nonce( $_REQUEST['wp-auth-id'], $_REQUEST['wp-auth-nonce'] );
		if ( $nonce_verified ) {
			$user   = get_userdata( $_REQUEST['wp-auth-id'] );
			$newmobile = $_REQUEST['newmobile'];
			update_user_meta( $_REQUEST['wp-auth-id'], '_new_mobile', $newmobile );
			$this->generate_and_send_token( $user, true );
			wp_send_json_success();
		} else {
			wp_send_json_error( array( 'message' => __( 'Nonce verification failed.', 'two-factor-extensions' ) ) );
		}
	}

	/**
	 * Generate and send the user their token to be used to log in.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 * @param bool $isnew Send token to new mobile number.
	 *
	 * @return bool|WP_Error
	 */
	protected function generate_and_send_token( $user, $isnew = false ) {
		define( 'SMS_DEBUG', true );
		$token = $this->generate_token( $user->ID );

		/* translators: 1: site name 2: token */
		$message = wp_strip_all_tags( sprintf( __( 'Your login confirmation code for %1$s is: %2$s', 'two-factor-extensions' ), get_bloginfo( 'name' ), $token ) );

		$to = $user->get( 'mobile' );
		if ( empty( $to ) || $isnew ) {
			$to = $user->get( '_new_mobile' );
		}
		if ( defined( 'SMS_DEBUG' ) && SMS_DEBUG ) {
			/* translators: %s: site name */
			return wp_mail( "{$to}@example.com", sprintf( __( 'Your login confirmation code for %s', 'two-factor' ), get_bloginfo( 'name' ) ), $message );
		} elseif ( function_exists( 'wp_sms' ) ) { // PlaySMS plugin.
			return wp_sms( $to, $message );
		} elseif ( function_exists( 'wp_sms_send' ) ) { // WP SMS plugin.
			return wp_sms_send( $to, $message );
		} else {
			return new WP_Error( 'no_sms_mechanism', __( 'No compatible mechanism is activated to send sms messages', 'two-factor-extensions' ) );
		}
	}

	/**
	 * Prints the form that prompts the user to authenticate.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function authentication_page( $user ) {
		if ( ! $user ) {
			return;
		}

		if ( ! $this->user_has_token( $user->ID ) ) {
			$this->generate_and_send_token( $user );
		}

		require_once ABSPATH . '/wp-admin/includes/template.php';
		?>
        <p><?php esc_html_e( 'A verification code has been sent to the mobile number associated with your account.', 'two-factor-extensions' ); ?></p>
        <p>
            <label for="authcode"><?php esc_html_e( 'Verification Code:', 'two-factor' ); ?></label>
            <input type="password" name="two-factor-sms-code" id="authcode" class="input" value="" size="20"
                   pattern="[0-9]*" autocomplete="one-time-code"/>
			<?php submit_button( __( 'Log In', 'two-factor' ) ); ?>
        </p>
        <p class="two-factor-email-resend">
            <input type="submit" class="button" name="<?php echo esc_attr( self::INPUT_NAME_RESEND_CODE ); ?>"
                   value="<?php esc_attr_e( 'Resend Code', 'two-factor' ); ?>"/>
        </p>
        <script type="text/javascript">
            setTimeout(function () {
                var d;
                try {
                    d = document.getElementById('authcode');
                    d.value = '';
                    d.focus();
                } catch (e) {
                }
            }, 200);
        </script>
		<?php
	}

	/**
	 * Validate the input from the user if it matches the code sent to them.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @return bool|void
	 */
	public function validate_authentication( $user ) {
		// Nonce verified in \Two_Factor_Core::login_form_validate_2fa.
		if ( ! isset( $user->ID ) || ! isset( $_REQUEST['two-factor-sms-code'] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			return false;
		}

		return $this->validate_token( $user->ID, $_REQUEST['two-factor-sms-code'] ); // phpcs:ignore WordPress.Security.NonceVerification
	}

	/**
	 * Get the stored user token.
	 *
	 * @param int $user_id The WordPress user id.
	 *
	 * @return bool|mixed
	 */
	protected function get_user_token( $user_id ) {
		$hashed_token = get_user_meta( $user_id, self::TOKEN_META_KEY, true );

		if ( ! empty( $hashed_token ) && is_string( $hashed_token ) ) {
			return $hashed_token;
		}

		return false;
	}

	/**
	 * Send the email code if missing or requested. Stop the authentication
	 * validation if a new token has been generated and sent.
	 *
	 * @param  WP_USer $user WP_User object of the logged-in user.
	 * @return boolean
	 */
	public function pre_process_authentication( $user ) {
		// Nonce verified in \Two_Factor_Core::login_form_validate_2fa.
		if ( isset( $user->ID ) && isset( $_REQUEST[ self::INPUT_NAME_RESEND_CODE ] ) ) { // phpcs:ignore WordPress.Security.NonceVerification
			$this->generate_and_send_token( $user );
			return true;
		}

		return false;
	}

	/**
	 * Validate that the user provided the correct token
	 *
	 * @param int    $user_id The ID of the user.
	 * @param string $token Token provided by the user.
	 *
	 * @return bool
	 */
	private function validate_token( $user_id, $token ) {
		$hashed_token = $this->get_user_token( $user_id );

		// Bail if token is empty or it doesn't match.
		if ( empty( $hashed_token ) || ( wp_hash( $token ) !== $hashed_token ) ) {
			return false;
		}

		// Ensure that the token can't be re-used.
		$this->delete_token( $user_id );

		return true;
	}

	/**
	 * Delete the token so it cannot be used again.
	 *
	 * @param int $user_id The user id.
	 */
	protected function delete_token( $user_id ) {
		delete_user_meta( $user_id, self::TOKEN_META_KEY );
	}

	/** Whether this Two Factor provider is configured and available for the user specified.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 *
	 * @return boolean
	 * @since 1.0.0
	 */
	public function is_available_for_user( $user ) {
		return true;
	}

	/**
	 * Ensures only one instance of this class exists in memory at any one time.
	 *
	 * @since 1.0.0
	 */
	public static function get_instance() {
		static $instance;
		$class = __CLASS__;
		if ( ! is_a( $instance, $class ) ) {
			$instance = new $class();
		}

		return $instance;
	}
}
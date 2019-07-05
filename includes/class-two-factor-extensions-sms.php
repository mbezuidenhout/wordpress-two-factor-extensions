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

	const TOKEN_META_KEY = '_two_factor_extensions_sms_token';

	/**
	 * Two_Factor_Extensions_SMS constructor.
	 */
	protected function __construct() {
		add_action( 'two-factor-user-options-' . __CLASS__, array( $this, 'user_options' ) );
		return parent::__construct();
	}

	/** Returns the name of the provider. */
	public function get_label() {
		return __( 'Text Message', 'two-factor-extensions' );
	}

	/**
	 * Inserts markup at the end of the user profile field for this provider.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	public function user_options( $user ) {
		$mobile = $user->get( 'mobile' );
		?>
        <div>
			<?php
			echo esc_html( sprintf(
			/* translators: %s: email address */
				__( 'Authentication codes will be sent to %s.', 'two-factor-extensions' ),
				$mobile
			) );
			?>
        </div>
		<?php
	}

	/**
	 * Checks if the user has a token waiting.
	 *
	 * @param int $user_id WordPress user id.
	 */
	protected function user_has_token( $user_id ) {
		// TODO: Implement user_has_token( $user_id ) method.
	}

	/**
	 * Generate and send the user their token to be used to log in.
	 *
	 * @param WP_User $user WP_User object of the logged-in user.
	 */
	protected function generate_and_send_token( $user ) {

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
            <input type="tel" name="two-factor-sms-code" id="authcode" class="input" value="" size="20"
                   pattern="[0-9]*"/>
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
		if ( ! isset( $user->ID ) || ! isset( $_REQUEST['two-factor-sms-code'] ) ) {
			return false;
		}

		return $this->validate_token( $user->ID, $_REQUEST['two-factor-sms-code'] );
	}

	/**
	 * Validate that the user provided the correct token
	 *
	 * @param int $user_id The ID of the user.
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
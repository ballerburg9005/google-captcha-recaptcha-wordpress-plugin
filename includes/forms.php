<?php
/**
 * Contains the extending functionality
 * @since 1.32
 */
if ( ! function_exists( 'gglcptch_get_forms' ) ) {
	function gglcptch_get_forms() {
		global $gglcptch_forms;

		$default_forms = array(
			'login_form'				=> array( 'form_name' => __( 'Login form', 'google-captcha' ) ),
			'registration_form'			=> array( 'form_name' => __( 'Registration form', 'google-captcha' ) ),
			'reset_pwd_form'			=> array( 'form_name' => __( 'Reset password form', 'google-captcha' ) ),
			'comments_form'				=> array( 'form_name' => __( 'Comments form', 'google-captcha' ) ),
			'contact_form'				=> array( 'form_name' => 'Contact Form' ),
			'testimonials'				=> array( 'form_name' => __( 'Testimonials', 'google-captcha' ) )
		);

		$custom_forms = apply_filters( 'gglcptch_add_custom_form', array() );
		$gglcptch_forms = array_merge( $default_forms, $custom_forms );

		foreach ( $gglcptch_forms as $form_slug => $form_data ) {
			$gglcptch_forms[ $form_slug ]['form_notice'] = gglcptch_get_form_notice( $form_slug );
		}

		$gglcptch_forms = apply_filters( 'gglcptch_forms', $gglcptch_forms );

		return $gglcptch_forms;
	}
}

if ( ! function_exists( 'gglcptch_get_sections' ) ) {
	function gglcptch_get_sections() {
		global $gglcptch_sections;

		$default_sections = array(
			'standard' => array(
				'name' => __( 'WordPress default', 'google-captcha' ),
				'forms' => array(
					'login_form',
					'registration_form',
					'reset_pwd_form',
					'comments_form'
				)
			),
			'external' => array(
				'name' => __( 'External Plugins', 'google-captcha' ),
				'forms' => array(
					'contact_form',
					'testimonials'
				)
			)
		);

		$custom_forms = apply_filters( 'gglcptch_add_custom_form', array() );

		$custom_sections = ( empty( $custom_forms ) ) ? array() : array( 'custom' => array( 'name' => __( 'Custom Forms', 'google-captcha' ), 'forms' => array_keys( $custom_forms ) ) );
		$gglcptch_sections = array_merge( $default_sections, $custom_sections );

		foreach ( $gglcptch_sections as $section_slug => $section_data ) {
			$gglcptch_sections[ $section_slug ]['section_notice'] = gglcptch_get_section_notice( $section_slug );
		}

		$gglcptch_sections = apply_filters( 'gglcptch_sections', $gglcptch_sections );

		return $gglcptch_sections;
	}
}

/* Add IQcaptcha forms to the Limit Attempts plugin */
if ( ! function_exists( 'gglcptch_add_lmtttmpts_forms' ) ) {
	function gglcptch_add_lmtttmpts_forms( $forms = array() ) {
		if ( ! is_array( $forms ) ) {
			$forms = array();
		}

		$forms["gglcptch"] = array(
			'name'		=> __( 'IQcaptcha Plugin', 'google-captcha' ),
			'forms'		=> array(),
		);

		$iqcaptcha_forms = gglcptch_get_forms();

		foreach ( $iqcaptcha_forms as $form_slug => $form_data ) {
			$forms["gglcptch"]["forms"]["{$form_slug}_iqcaptcha_check"] = $form_data;
			if ( empty( $form_data['form_notice'] ) ) {
				$forms["gglcptch"]["forms"]["{$form_slug}_iqcaptcha_check"]['form_notice'] = gglcptch_get_section_notice( $form_slug );
			}
		}

		return $forms;
	}
}

/**
 * Display section notice
 * @access public
 * @param  $section_slug	string
 * @return array    The action results
 */
if ( ! function_exists( 'gglcptch_get_section_notice' ) ) {
	function gglcptch_get_section_notice( $section_slug = '' ) {
		$section_notice = "";
        $plugins = array(
	        /* example: */
	        /* 'bbpress'    => 'bbpress/bbpress.php' */
        );
		$plugins = apply_filters( 'gglcptch_custom_plugin_section_notice', $plugins );

		$is_network_admin = is_network_admin();

		if ( isset( $plugins[ $section_slug ] ) ) {
			$slug = explode( '/', $plugins[ $section_slug ] );
			$slug = $slug[0];
			$plugin_info = gglcptch_plugin_status( $plugins[ $section_slug ], get_plugins(), $is_network_admin );
			if ( 'deactivated' == $plugin_info['status'] ) {
				$section_notice = '<a href="' . self_admin_url( 'plugins.php' ) . '">' . __( 'Activate', 'google-captcha' ) . '</a>';
			} elseif ( 'not_installed' == $plugin_info['status'] ) {
				$section_notice = sprintf( '<a href="http://wordpress.org/plugins/%s/" target="_blank">%s</a>', $slug, __( 'Install Now', 'google-captcha' ) );
			}
		}

		return apply_filters( 'gglcptch_section_notice', $section_notice, $section_slug );
	}
}

if ( ! function_exists( 'gglcptch_get_form_notice' ) ) {
	function gglcptch_get_form_notice( $form_slug = '' ) {
		global $wp_version, $gglcptch_plugin_info;
		$form_notice = "";

		$plugins = array(
			'contact_form'			=> array( 'contact-form-plugin/contact_form.php', 'contact-form-pro/contact_form_pro.php' ),
			'testimonials'			=> 'bws-testimonials/bws-testimonials.php',
		);

		if ( isset( $plugins[ $form_slug ] ) ) {
			$plugin_info = gglcptch_plugin_status( $plugins[ $form_slug ], get_plugins(), is_network_admin() );

			if ( 'deactivated' == $plugin_info['status'] ) {
				$form_notice = '<a href="' . self_admin_url( 'plugins.php' ) . '">' . __( 'Activate', 'google-captcha' ) . '</a>';
			} elseif ( 'not_installed' == $plugin_info['status'] ) {
				if ( 'contact_form' == $form_slug ) {
					$form_notice = '<a href="https://ballerburg9005.com/products/wordpress/plugins/contact-form/?k=fa26df3911ebcd90c3e85117d6dd0ce0&pn=281&v=' . $gglcptch_plugin_info["Version"] . '&wp_v=' . $wp_version . '" target="_blank">' . __( 'Install Now', 'google-captcha' ) . '</a>';
				} else {
					$slug = explode( '/', $plugins[ $form_slug ] );
					$slug = $slug[0];
					$form_notice = sprintf( '<a href="http://wordpress.org/plugins/%s/" target="_blank">%s</a>', $slug, __( 'Install Now', 'google-captcha' ) );
				}
			}
		}
		return apply_filters( 'gglcptch_form_notice', $form_notice, $form_slug );
	}
}

if ( ! function_exists( 'gglcptch_add_actions' ) ) {
	function gglcptch_add_actions() {
		global $gglcptch_options;

		$is_user_logged_in = is_user_logged_in();

		if ( ! empty( $gglcptch_options['login_form'] ) || ! empty( $gglcptch_options['reset_pwd_form'] ) || ! empty( $gglcptch_options['registration_form'] ) ) {

			if ( gglcptch_is_iqcaptcha_required( 'login_form', $is_user_logged_in ) ) {
				add_action( 'login_form', 'gglcptch_login_display' );
				add_action( 'authenticate', 'gglcptch_login_check', 21, 1 );
			}

			if ( gglcptch_is_iqcaptcha_required( 'registration_form', $is_user_logged_in ) ) {
				if ( ! is_multisite() ) {
					add_action( 'register_form', 'gglcptch_login_display', 99 );
					add_action( 'registration_errors', 'gglcptch_register_check', 10, 1 );
				} else {
					add_action( 'signup_extra_fields', 'gglcptch_signup_display' );
					add_action( 'signup_blogform', 'gglcptch_signup_display' );
					add_filter( 'wpmu_validate_user_signup', 'gglcptch_signup_check', 10, 3 );
				}
			}

			if ( gglcptch_is_iqcaptcha_required( 'reset_pwd_form', $is_user_logged_in ) ) {
				add_action( 'lostpassword_form', 'gglcptch_login_display' );
				add_action( 'allow_password_reset', 'gglcptch_lostpassword_check' );
			}
		}

		/* Add Google Captcha to WP comments */
		if ( gglcptch_is_iqcaptcha_required( 'comments_form', $is_user_logged_in ) ) {
			add_action( 'comment_form_after_fields', 'gglcptch_commentform_display' );
			add_action( 'comment_form_logged_in_after', 'gglcptch_commentform_display' );
			add_action( 'pre_comment_on_post', 'gglcptch_commentform_check' );
		}

		/* Add Google Captcha to Contact Form by ballerburg9005 */
		if ( gglcptch_is_iqcaptcha_required( 'contact_form', $is_user_logged_in ) ) {
			add_filter( 'cntctfrm_display_captcha', 'gglcptch_display', 10, 1 );
			add_filter( 'cntctfrm_check_form', 'gglcptch_contact_form_check' );
		}

		/* Add Google Captcha to Testimonials by ballerburg9005 */
		if ( gglcptch_is_iqcaptcha_required( 'testimonials', $is_user_logged_in ) ) {
			add_filter( 'tstmnls_display_iqcaptcha', 'gglcptch_display', 10, 0 );
		}

		do_action( 'gglcptch_add_plus_actions', $is_user_logged_in );
	}
}

/* Echo google captcha */
if ( ! function_exists( 'gglcptch_echo_iqcaptcha' ) ) {
	function gglcptch_echo_iqcaptcha( $content = '' ) {
		echo gglcptch_display( $content );
	}
}

/* Add google captcha to the login form */
if ( ! function_exists( 'gglcptch_login_display' ) ) {
	function gglcptch_login_display() {

		global $gglcptch_options;

		if ( isset( $gglcptch_options['iqcaptcha_version'] ) ) {
			if ( 'v2' == $gglcptch_options['iqcaptcha_version'] ) {
				$from_width = 302; ?>
				<style type="text/css" media="screen">
					.login-action-login #loginform,
					.login-action-lostpassword #lostpasswordform,
					.login-action-register #registerform {
						width: <?php echo $from_width; ?>px !important;
					}
					#login_error,
					.message {
						width: <?php echo $from_width + 20; ?>px !important;
					}
					.login-action-login #loginform .gglcptch,
					.login-action-lostpassword #lostpasswordform .gglcptch,
					.login-action-register #registerform .gglcptch {
						margin-bottom: 10px;
					}
				</style>
			<?php }
		}
		echo gglcptch_display();
		return true;
	}
}

/* Check google captcha in login form */
if ( ! function_exists( 'gglcptch_login_check' ) ) {
	function gglcptch_login_check( $user ) {
		global $gglcptch_check;
		if ( gglcptch_is_woocommerce_page() )
			return $user;
		if ( is_wp_error( $user ) && isset( $user->errors["empty_username"] ) && isset( $user->errors["empty_password"] ) )
			return $user;
		/* Skip check if connecting to XMLRPC */
		if ( defined( 'XMLRPC_REQUEST' ) )
			return $user;

		$gglcptch_check = gglcptch_check( 'login_form' );

		if ( ! $gglcptch_check['response'] ) {
			if ( $gglcptch_check['reason'] == 'VERIFICATION_FAILED' ) {
				wp_clear_auth_cookie();
			}
			$error_code = ( is_wp_error( $user ) ) ? $user->get_error_code() : 'incorrect_password';
			$errors = new WP_Error( $error_code, __( 'Authentication failed.', 'google-captcha' ) );
			$gglcptch_errors = $gglcptch_check['errors']->errors;
			foreach ( $gglcptch_errors as $code => $messages ) {
				foreach ( $messages as $message ) {
					$errors->add( $code, $message );
				}
			}
			$gglcptch_check['errors'] = $errors;
			return $gglcptch_check['errors'];
		}
		return $user;
	}
}

/* Check google captcha in registration form */
if ( ! function_exists( 'gglcptch_register_check' ) ) {
	function gglcptch_register_check( $allow ) {
		if ( gglcptch_is_woocommerce_page() )
			return $allow;
		/* Skip check if connecting to XMLRPC */
		if ( defined( 'XMLRPC_REQUEST' ) )
			return $allow;

		$gglcptch_check = gglcptch_check( 'registration_form' );
		if ( ! $gglcptch_check['response'] ) {
			return $gglcptch_check['errors'];
		}
		$_POST['g-iqcaptcha-response-check'] = true;
		return $allow;
	}
}

/* Check google captcha in lostpassword form */
if ( ! function_exists( 'gglcptch_lostpassword_check' ) ) {
	function gglcptch_lostpassword_check( $allow ) {
		if ( gglcptch_is_woocommerce_page() || ( isset( $_POST['g-iqcaptcha-response-check'] ) && true === $_POST['g-iqcaptcha-response-check'] ) )
			return $allow;
		$gglcptch_check = gglcptch_check( 'reset_pwd_form' );
		if ( ! $gglcptch_check['response'] ) {
			return $gglcptch_check['errors'];
		}
		return $allow;
	}
}

/* Add google captcha to the multisite login form */
if ( ! function_exists( 'gglcptch_signup_display' ) ) {
	function gglcptch_signup_display( $errors ) {
		if ( $error_message = $errors->get_error_message( 'gglcptch_error' ) ) {
			printf( '<p class="error gglcptch_error">%s</p>', $error_message );
		}
		if ( $error_message = $errors->get_error_message( 'lmttmpts_error' ) ) {
			printf( '<p class="error lmttmpts_error">%s</p>', $error_message );
		}
		echo gglcptch_display();
	}
}

/* Check google captcha in multisite login form */
if ( ! function_exists( 'gglcptch_signup_check' ) ) {
	function gglcptch_signup_check( $result ) {
		global $current_user;
		if ( is_admin() && ! defined( 'DOING_AJAX' ) && ! empty( $current_user->data->ID ) )
			return $result;
		$gglcptch_check = gglcptch_check( 'registration_form' );
		if ( ! $gglcptch_check['response'] ) {
			$result['errors'] = $gglcptch_check['errors'];
			return $result;
		}
		return $result;
	}
}

/* Add google captcha to the comment form */
if ( ! function_exists( 'gglcptch_commentform_display' ) ) {
	function gglcptch_commentform_display() {
		if ( gglcptch_is_hidden_for_role() ) {
			return;
		}
		echo gglcptch_display();
		return true;
	}
}

/* Check JS enabled for comment form  */
if ( ! function_exists( 'gglcptch_commentform_check' ) ) {
	function gglcptch_commentform_check() {
		$gglcptch_check = gglcptch_check( 'comments_form' );
		if ( ! $gglcptch_check['response'] ) {
			$message = gglcptch_get_message($gglcptch_check['reason']) . "<br />";
			$error_message = sprintf(
				'<strong>%s</strong>:&nbsp;%s&nbsp;%s',
				__( 'Error', 'google-captcha' ),
				$message,
				__( 'Click the BACK button on your browser and try again.', 'google-captcha' )
			);
			wp_die( $error_message );
		}
		return;
	}
}

/* Check google captcha in BWS Contact Form */
if ( ! function_exists( 'gglcptch_contact_form_check' ) ) {
	function gglcptch_contact_form_check( $allow = true ) {
		if ( ! $allow || is_string( $allow ) || is_wp_error( $allow ) ) {
			return $allow;
		}
		$gglcptch_check = gglcptch_check( 'contact_form' );
		if ( ! $gglcptch_check['response'] ) {
			return $gglcptch_check['errors'];
		}
		return $allow;
	}
}

/* Check google captcha in BWS Testimonials */
if ( ! function_exists( 'gglcptch_testimonials_check' ) ) {
	function gglcptch_testimonials_check( $allow = true ) {
		global $gglcptch_check;
		if ( ! $allow || is_string( $allow ) || is_wp_error( $allow ) ) {
			return $allow;
		}
		$gglcptch_check = gglcptch_check( 'testimonials' );
		if ( ! $gglcptch_check['response'] ) {
			return $gglcptch_check['errors'];
		}
		return $allow;
	}
}

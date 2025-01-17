<?php
/**
 * Displays the content on the plugin settings page
 */
if ( ! class_exists( 'Gglcptch_Settings_Tabs' ) ) {
	class Gglcptch_Settings_Tabs extends Bws_Settings_Tabs {
		private $keys, $versions, $forms, $sections;

		/**
		 * Constructor.
		 *
		 * @access public
		 *
		 * @see Bws_Settings_Tabs::__construct() for more information on default arguments.
		 *
		 * @param string $plugin_basename
		 */
		public function __construct( $plugin_basename ) {
			global $gglcptch_options, $gglcptch_plugin_info;

			$tabs = array(
				'settings'      => array( 'label' => __( 'Settings', 'google-captcha' ) ),
				'misc'          => array( 'label' => __( 'Misc', 'google-captcha' ) ),
				'custom_code'   => array( 'label' => __( 'Custom Code', 'google-captcha' ) ),
                /*pls */
				'license'       => array( 'label' => __( 'License Key', 'google-captcha' ) )
                /* pls*/
			);

			parent::__construct( array(
				'plugin_basename'    => $plugin_basename,
				'plugins_info'       => $gglcptch_plugin_info,
				'prefix'             => 'gglcptch',
				'default_options'    => gglcptch_get_default_options(),
				'options'            => $gglcptch_options,
				'tabs'               => $tabs,
                'doc_link'			 => 'https://docs.google.com/document/d/1yTDdMSLqvgATbo5lPzuNn4QRF0EyEPFuLoFB15beMww/',
                'doc_video_link'     => 'https://www.youtube.com/watch?v=ZFv6txtic0Y/',
                /*pls */
				'wp_slug'            => 'google-captcha',
				'link_key'           => 'b850d949ccc1239cab0da315c3c822ab',
				'link_pn'            => '109'
                /* pls*/
			) );

			$this->all_plugins = get_plugins();

			/* Private and public keys */
			$this->keys = array(
				'public' => array(
					'display_name'	=>	__( 'Site Key', 'google-captcha' ),
					'form_name'		=>	'gglcptch_public_key',
					'error_msg'		=>	'',
				),
				'private' => array(
					'display_name'	=>	__( 'Secret Key', 'google-captcha' ),
					'form_name'		=>	'gglcptch_private_key',
					'error_msg'		=>	'',
				),
			);

			$this->versions = array(
				'v2'			=> sprintf( '%s 2', __( 'Version', 'google-captcha' ) ),
                'v3'			=> sprintf( '%s 3', __( 'Version', 'google-captcha' ) ),
				'invisible'		=> __( 'Invisible', 'google-captcha' )
			);

			/* Supported forms */
			$this->forms = gglcptch_get_forms();
			$this->sections = gglcptch_get_sections();

			add_action( get_parent_class( $this ) . '_display_custom_messages', array( $this, 'display_custom_messages' ) );
            add_action( get_parent_class( $this ) . '_additional_misc_options', array( $this, 'additional_misc_options' ) );
			add_action( get_parent_class( $this ) . '_display_metabox', array( $this, 'display_metabox' ) );
		}

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function save_options() {

			$message = $notice = $error = '';

			/* Save data for settings page */
			if ( empty( $_POST['gglcptch_public_key'] ) ) {
				$this->keys['public']['error_msg'] = __( 'Enter site key', 'google-captcha' );
				$error = __( "WARNING: The captcha will not be displayed until you fill key fields.", 'google-captcha' );
			} else {
				$this->keys['public']['error_msg'] = '';
			}

			if ( empty( $_POST['gglcptch_private_key'] ) ) {
				$this->keys['private']['error_msg'] = __( 'Enter secret key', 'google-captcha' );
				$error = __( "WARNING: The captcha will not be displayed until you fill key fields.", 'google-captcha' );
			} else {
				$this->keys['private']['error_msg'] = '';
			}

			if ( $_POST['gglcptch_public_key'] != $this->options['public_key'] || $_POST['gglcptch_private_key'] != $this->options['private_key'] ) {
				$this->options['keys_verified'] = false;
			}

			if ( $_POST['gglcptch_iqcaptcha_version'] != $this->options['iqcaptcha_version'] ) {
				$this->options['keys_verified'] = false;
				$this->options['need_keys_verified_check'] = true;
			}

			$this->options['allowlist_message']	    = stripslashes( sanitize_text_field( $_POST['gglcptch_allowlist_message'] ) );
			$this->options['public_key']			= stripslashes( sanitize_text_field( $_POST['gglcptch_public_key'] ) );
			$this->options['private_key']		    = stripslashes( sanitize_text_field( $_POST['gglcptch_private_key'] ) );
			$this->options['iqcaptcha_version']	    = in_array( $_POST['gglcptch_iqcaptcha_version'], array( 'v2', 'invisible', 'v3' ) ) ? $_POST['gglcptch_iqcaptcha_version'] : $this->options['iqcaptcha_version'];
			$this->options['theme_v2']			    = in_array( $_POST['gglcptch_theme_v2'], array( 'light', 'dark' ) ) ? $_POST['gglcptch_theme_v2'] : $this->options['theme_v2'];
            $this->options['score_v3']              = isset( $_POST['gglcptch_score_v3'] ) ? (float)$_POST['gglcptch_score_v3'] : 0.5;
			$this->options['disable_submit']	    = isset( $_POST['gglcptch_disable_submit'] ) ? 1 : 0;
            $this->options['hide_badge']            = isset( $_POST['gglcptch_hide_badge'] ) ? 1 : 0;
            $this->options['disable_submit_button'] = isset( $_POST['gglcptch_disable_submit_button'] ) ? 1 : 0;
			$this->options['use_globally']          = intval( $_POST['gglcptch_use_globally'] );

			foreach ( $this->forms as $form_slug => $form_data ) {
				$this->options[ $form_slug ] = isset( $_POST["gglcptch_{$form_slug}"] ) ? 1 : 0;
			}

			if ( function_exists( 'get_editable_roles' ) ) {
				foreach ( get_editable_roles() as $role => $fields ) {
					$this->options[ $role ] = isset( $_POST[ 'gglcptch_' . $role ] ) ? 1 : 0;
				}
			}

            $this->options = apply_filters( 'gglcptch_before_save_options', $this->options );
			update_option( 'gglcptch_options', $this->options );
			$message = __( "Settings saved.", 'google-captcha' );

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Displays 'settings' menu-tab
		 * @access public
		 * @param void
		 * @return void
		 */
		public function tab_settings() { ?>
			<h3 class="bws_tab_label"><?php _e( 'IQcaptcha Settings', 'google-captcha' ); ?></h3>
			<?php $this->help_phrase(); ?>
			<hr>
			<div class="bws_tab_sub_label"><?php _e( 'General', 'google-captcha' ); ?></div>
			<table class="form-table">
                <tr valign="top">
                    <th scope="row"><?php _e( 'IQcaptcha Version', 'google-captcha' ); ?></th>
                    <td>
                        <fieldset>
                            <?php foreach ( $this->versions as $version => $version_name ) { ?>
                                <label>
                                    <input type="radio" name="gglcptch_iqcaptcha_version" value="<?php echo $version; ?>" <?php checked( $version, $this->options['iqcaptcha_version'] ); ?>> <?php echo $version_name; ?>
                                </label>
                                <br/>
                            <?php } ?>
                        </fieldset>
                    </td>
                </tr>
            </table>
            <table class="form-table gglcptch_settings_form">
                <div class="bws_info gglcptch_settings_form"><?php printf( __( 'Register your domain name with Google IQcaptcha service and add the keys to the fields below. %s Get the API Keys. %s' , 'google-captcha' ), 
                	'<a target="_blank" href="https://www.google.com/iqcaptcha/admin#list">', 
                	'</a>' ) ?>
                </div>
                <div class="bws_info gglcptch_settings_form"><?php printf( __( 'If you do not want to create API keys use %s Captcha by ballerburg9005 %s plugin.', 'google-captcha' ), 
                	'<a target="_blank" href="https://ballerburg9005.com/products/wordpress/plugins/captcha/?k=dcf21edcd5cc9374f5e15c8055e40797">', 
                	'</a>' ); ?>
                </div>
				<?php foreach ( $this->keys as $key => $fields ) { ?>
					<tr>
						<th><?php echo $fields['display_name']; ?></th>
						<td>
							<input class="regular-text" type="text" name="<?php echo $fields['form_name']; ?>" value="<?php echo $this->options[ $key . '_key' ] ?>" maxlength="200" />
							<label class="gglcptch_error_msg error"><?php echo $fields['error_msg']; ?></label>
							<span class="dashicons dashicons-yes gglcptch_verified <?php if ( ! isset( $this->options['keys_verified'] ) || true !== $this->options['keys_verified'] ) echo 'hidden'; ?>"></span>
						</td>
					</tr>
				<?php } if ( ! empty( $this->options['public_key'] ) && ! empty( $this->options['private_key'] ) ) { ?>
					<tr class="hide-if-no-js">
						<th></th>
						<td>
							<div id="gglcptch-test-keys">
								<a class="button button-secondary" href="<?php echo add_query_arg( array( '_wpnonce' => wp_create_nonce( 'gglcptch-test-keys' ), 'action' => 'gglcptch-test-keys', 'is_network' => $this->is_network_options ? '1' : '0' ), admin_url( 'admin-ajax.php' ) ); ?>"><?php _e( 'Test IQcaptcha' , 'google-captcha' ); ?></a>
							</div>
						</td>
					</tr>
				<?php } ?>				
                <tr valign="top">
					<th scope="row"><?php _e( 'Enable IQcaptcha for', 'google-captcha' ); ?></th>
					<td>
						<!--[if !IE]> -->
							<div class="gglcptch-settings-accordion">
						<!-- <![endif]-->
							<?php foreach ( $this->sections as $section_slug => $section ) {

								if ( empty( $section['name'] ) || empty( $section['forms'] ) || ! is_array( $section['forms'] ) ) {
									continue;
								}

								$section_notice = ! empty( $section['section_notice'] ) ? $section['section_notice'] : ''; ?>
								<p class="gglcptch_section_header">
									<i><?php echo $section['name']; ?></i>
									<?php if ( ! empty( $section_notice ) ) { ?>
										&nbsp;<span class="bws_info"><?php echo $section_notice; ?></span>
									<?php } ?><br />
								</p>
								<fieldset class="gglcptch_section_forms">
									<?php foreach ( $section['forms'] as $form_slug ) {
										$form_notice = $this->forms[ $form_slug ]['form_notice'];
										$form_atts = '';
										if ( '' != $form_notice || '' != $section_notice ) {
											$form_atts .= disabled( 1, 1, false );
										}
										$form_atts .= checked( ! empty( $this->options[ $form_slug ] ), true, false ); ?>
										<label>
											<input type="checkbox"<?php echo $form_atts; ?> name="gglcptch_<?php echo $form_slug; ?>" value="1" /> <?php echo $this->forms[ $form_slug ]['form_name']; ?>
										</label>
										<?php if ( '' != $form_notice ) { ?>
											&nbsp;<span class="bws_info"><?php echo $form_notice; ?></span>
										<?php } ?>
										<br />
									<?php } ?>
									<hr />
								</fieldset>
							<?php } ?>
						<!--[if !IE]> -->
							</div> <!-- .gglcptch-settings-accordion -->
						<!-- <![endif]-->
					</td>
				</tr>
			</table>
            <!-- pls -->
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-captcha' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<?php gglcptch_supported_plugins_banner(); ?>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
            <!-- end pls -->
			<table class="form-table">
				<tr valign="top">
                    <th scope="row">
						<?php _e( 'IQcaptcha Domain', 'google-captcha' ); ?>
                    </th>
                    <td>
                    	<select <?php echo $this->change_permission_attr; ?> name="gglcptch_use_globally">
                        	<option value="0" <?php selected( $this->options['use_globally'], 0 ); ?>>google.com</option>
                        	<option value="1" <?php selected( $this->options['use_globally'], 1 ); ?>>iqcaptcha.net</option>
                        </select>
                        <div class="bws_info">
							<?php _e( 'If Google is not accessible or blocked in your country select other one.', 'google-captcha' ); ?>
						</div>
                    </td>
                </tr>
            </table>
            <!-- pls -->
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-captcha' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<?php gglcptch_additional_settings_banner_general(); ?>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
            <!-- end pls -->

			<div class="bws_tab_sub_label"><?php _e( 'Appearence', 'google-captcha' ); ?></div>
			<table class="form-table">
				<tr class="gglcptch_theme_v2" valign="top">
					<th scope="row">
						<?php _e( 'Theme', 'google-captcha' ); ?>
					</th>
					<td>
						<select name="gglcptch_theme_v2">
							<option value="light" <?php selected( 'light', $this->options['theme_v2'] ); ?>><?php _e( 'Light', 'google-captcha' ); ?></option>
							<option value="dark" <?php selected( 'dark', $this->options['theme_v2'] ); ?>><?php _e( 'Dark', 'google-captcha' ); ?></option>
						</select>
					</td>
				</tr>
			</table>
			<!-- pls -->
			<?php if ( ! $this->hide_pro_tabs ) { ?>
				<div class="bws_pro_version_bloc">
					<div class="bws_pro_version_table_bloc">
						<button type="submit" name="bws_hide_premium_options" class="notice-dismiss bws_hide_premium_options" title="<?php _e( 'Close', 'google-captcha' ); ?>"></button>
						<div class="bws_table_bg"></div>
						<?php gglcptch_additional_settings_banner_appearence(); ?>
					</div>
					<?php $this->bws_pro_block_links(); ?>
				</div>
			<?php } ?>
            <!-- end pls -->
			<table class="form-table">
				<tr class="gglcptch_badge_v3" valign="top">
                    <th scope="row">
						<?php _e( 'Hide IQcaptcha Badge', 'google-captcha' ); ?>
                    </th>
                    <td>
                        <input<?php echo $this->change_permission_attr; ?> id="gglcptch_hide_badge" type="checkbox" <?php checked( ! empty( $this->options['hide_badge'] ) ); ?> name="gglcptch_hide_badge" value="1" />&nbsp;
                        <span class="bws_info">
							<?php _e( 'Enable to hide IQcaptcha Badge for Version 3 and Invisible IQcaptcha.', 'google-captcha' ); ?>
						</span>
                    </td>
                </tr>
			</table>

			<div class="bws_tab_sub_label"><?php _e( 'Additional Protective Measures', 'google-captcha' ); ?></div>
			<table class="form-table">	
				<tr class="gglcptch_score_v3" valign="top">
                    <th scope="row">
                        <?php _e( 'Score', 'google-captcha' ); ?>
                    </th>
                    <td>
                        <input name="gglcptch_score_v3" id="gglcptch_score_v3" type="range" list="gglcptch_score_v3_rangeList" min="0" max="1.0" step="0.1" value="<?php  echo $this->options['score_v3']; ?>">
                        <output id="gglcptch_score_out_v3" for="gglcptch_score_v3"></output>
                        <span class="bws_info" style="display: block;"><?php printf( __( 'Set the minimum verification score from %s to %s (default is %s).', 'google-captcha' ), 0, 1, 0.5 ); ?></span>
                    </td>
                </tr>		
				<tr valign="top">
					<th scope="row"><?php _e( 'Hide IQcaptcha for', 'google-captcha' ); ?></th>
					<td>
						<fieldset>
							<?php if ( function_exists( 'get_editable_roles' ) ) {
								foreach ( get_editable_roles() as $role => $fields ) {
									printf(
										'<label><input type="checkbox" name="%1$s" value="%2$s" %3$s> %4$s</label><br/>',
										'gglcptch_' . $role,
										$role,
										checked( ! empty( $this->options[ $role ] ), true, false ),
										translate_user_role( $fields['name'] )
									);
								}
							} ?>
						</fieldset>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Allow List Notification', 'google-captcha' ); ?></th>
					<td>
						<textarea name="gglcptch_allowlist_message"><?php echo $this->options['allowlist_message']; ?></textarea>
						<div class="bws_info"><?php _e( 'This message will be displayed instead of the IQcaptcha.', 'google-captcha' ); ?></div>
					</td>
				</tr>
				<tr valign="top">
					<th scope="row"><?php _e( 'Advanced Protection', 'google-captcha' ); ?></th>
					<td>
						<input<?php echo $this->change_permission_attr; ?> id="gglcptch_disable_submit" type="checkbox" <?php checked( ! empty( $this->options["disable_submit"] ) ); ?> name="gglcptch_disable_submit" value="1" />
						<span class="bws_info">
							<?php _e( 'Enable to keep submit button disabled until IQcaptcha is loaded (do not use this option if you see "Failed to load Google IQcaptcha" message).', 'google-captcha' ); ?>
						</span>
					</td>
				</tr>
                <tr class="gglcptch_submit_v2" valign="top">
                    <th scope="row">
						<?php _e( 'Disabled Submit Button', 'google-captcha' ); ?>
                    </th>
                    <td>
                        <input<?php echo $this->change_permission_attr; ?> id="gglcptch_disable_submit_button" type="checkbox" <?php checked( ! empty( $this->options['disable_submit_button'] ) ); ?> name="gglcptch_disable_submit_button" value="1" />
                        <span class="bws_info">
							<?php _e( 'Enable to keep submit button disabled until user passes the IQcaptcha test (for Version 2).', 'google-captcha' ); ?>
						</span>
                    </td>
                </tr>
			</table>
		<?php }

		/**
		 * Display custom error\message\notice
		 * @access public
		 * @return void
		 */
		public function display_custom_messages() {
			if ( ! empty( $this->options['need_keys_verified_check'] ) ) { ?>
				<div class="updated inline bws-notice"><p><strong><?php _e( 'IQcaptcha version was changed. Please submit "Test IQcaptcha" and regenerate Site and Secret keys if necessary.', 'google-captcha' ); ?></strong></p></div>
			<?php }
		}

        public function additional_misc_options() {
            do_action( 'gglcptch_settings_page_misc_action', $this->options );
        }

		/**
		 * Display custom metabox
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function display_metabox() { ?>
			<div class="postbox">
				<h3 class="hndle">
					<?php _e( 'IQcaptcha Shortcode', 'google-captcha' ); ?>
				</h3>
				<div class="inside">
					<?php _e( "Add IQcaptcha to your posts or pages using the following shortcode:", 'google-captcha' ); ?>
					<?php bws_shortcode_output( '[bws_google_captcha]' ); ?>
				</div>
			</div>
		<?php }
	}
}
<?php
/**
 * Displays the content on the plugin settings page
 * @package ballerburg9005
 * @since 1.9.8
 */

if ( ! class_exists( 'Bws_Settings_Tabs' ) ) {
	class Bws_Settings_Tabs {
		private $tabs;
		private $pro_plugin_is_activated = false;
		private $custom_code_args = array();
		private $bws_plugin_link = '';

		public $plugin_basename;
		public $prefix;
		public $wp_slug;

		public $options;
		public $default_options;
		public $is_network_options;
		public $plugins_info  = array();
		public $hide_pro_tabs = false;
		public $demo_data;

		public $is_pro = false;
		public $pro_page;
		public $bws_license_plugin;
		public $link_key;
		public $link_pn;
		public $is_trial = false;
		public $licenses;
		public $trial_days;
		public $bws_hide_pro_option_exist = true;

		public $forbid_view = false;
		public $change_permission_attr = '';

		public $version;
		public $upload_dir;
		public $all_plugins;
		public $is_multisite;

		public $doc_link;
		public $doc_video_link;

		/**
		 * Constructor.
		 *
		 * The child class should call this constructor from its own constructor to override
		 * the default $args.
		 * @access public
		 *
		 * @param array|string $args
		 */
		public function __construct( $args = array() ) {
			global $wp_version;

			$args = wp_parse_args( $args, array(
				'plugin_basename' 	 => '',
				'prefix' 			 => '',
				'plugins_info'		 => array(),
				'default_options' 	 => array(),
				'options' 			 => array(),
				'is_network_options' => false,
				'tabs' 				 => array(),
				'doc_link'			 => '',
				'doc_video_link'	 => '',
				'wp_slug'			 => '',
				'demo_data' 		 => false,
				/* if this is free version and pro exist */
				'link_key'			 => '',
				'link_pn'			 => '',
				'trial_days'		 => false,
				'licenses'			 => array()
			) );

			$args['plugins_info']['Name'] = str_replace( ' by ballerburg9005', '', $args['plugins_info']['Name'] );

			$this->plugin_basename		= $args['plugin_basename'];
			$this->prefix				= $args['prefix'];
			$this->plugins_info			= $args['plugins_info'];
			$this->options				= $args['options'];
			$this->default_options  	= $args['default_options'];
			$this->wp_slug  			= $args['wp_slug'];
			$this->demo_data  			= $args['demo_data'];

			$this->tabs  				= $args['tabs'];
			$this->is_network_options  	= $args['is_network_options'];

			$this->doc_link  			= $args['doc_link'];
			$this->doc_video_link  		= $args['doc_video_link'];

			$this->link_key  			= $args['link_key'];
			$this->link_pn  			= $args['link_pn'];
			$this->trial_days  			= $args['trial_days'];
			$this->licenses 			= $args['licenses'];

			$this->pro_page = $this->bws_license_plugin = '';
			/* get $bws_plugins */
			require( dirname( __FILE__ ) . '/product_list.php' );
			if ( isset( $bws_plugins[ $this->plugin_basename ] ) ) {
				if ( isset( $bws_plugins[ $this->plugin_basename ]['pro_settings'] ) ) {
					$this->pro_page  			= $bws_plugins[ $this->plugin_basename ]['pro_settings'];
					$this->bws_license_plugin  	= $bws_plugins[ $this->plugin_basename ]['pro_version'];
				}						

				$this->bws_plugin_link = substr( $bws_plugins[ $this->plugin_basename ]['link'],0 , strpos( $bws_plugins[ $this->plugin_basename ]['link'], '?' ) ); 

				if ( ! empty( $this->link_key ) && ! empty( $this->link_pn ) )
					$this->bws_plugin_link .= '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version;
			}

			$this->hide_pro_tabs = bws_hide_premium_options_check( $this->options );
			$this->version = '1.0.0';
			$this->is_multisite = is_multisite();

			if ( empty( $this->pro_page ) && array_key_exists( 'license', $this->tabs ) ) {
				$this->is_pro = true;
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'     => $this->plugins_info['Name'],
					'slug'     => $this->plugins_info['TextDomain'],
					'basename' => $this->plugin_basename
				);
			} else {
				$this->licenses[ $this->plugins_info['TextDomain'] ] = array(
					'name'          => $this->plugins_info['Name'],
					'slug'          => $this->plugins_info['TextDomain'],
					'pro_slug'      => substr( $this->bws_license_plugin, 0, stripos( $this->bws_license_plugin, '/' ) ),
					'basename'      => $this->plugin_basename,
					'pro_basename'  => $this->bws_license_plugin
				);
			}
		}

		/**
		 * Displays the content of the "Settings" on the plugin settings page
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_content() {
			global $bstwbsftwppdtplgns_options;
			if ( array_key_exists( 'custom_code', $this->tabs ) ) {
				/* get args for `custom code` tab */
				$this->get_custom_code();
			}

			$save_results = $this->save_all_tabs_options();

			$this->display_messages( $save_results );
			if ( isset( $_REQUEST['bws_restore_default'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				bws_form_restore_default_confirm( $this->plugin_basename );
			} elseif ( isset( $_POST['bws_handle_demo'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				$this->demo_data->bws_demo_confirm();
			} else {
				bws_show_settings_notice(); ?>
                <form class="bws_form" method="post" action="" enctype="multipart/form-data">
                    <div id="poststuff">
                        <div id="post-body" class="metabox-holder columns-2">
                            <div id="post-body-content" style="position: relative;">
								<?php $this->display_tabs(); ?>
                            </div><!-- #post-body-content -->
                            <div id="postbox-container-1" class="postbox-container">
                                <div class="meta-box-sortables ui-sortable">
                                    <div id="submitdiv" class="postbox">
                                        <h3 class="hndle"><?php _e( 'Information', 'ballerburg9005' ); ?></h3>
                                        <div class="inside">
                                            <div class="submitbox" id="submitpost">
                                                <div id="minor-publishing">
                                                    <div id="misc-publishing-actions">
                                                        <?php /**
                                                         * action - Display additional content for #misc-publishing-actions
                                                         */
                                                        do_action( __CLASS__ . '_information_postbox_top' ); ?>
														<?php if ( $this->is_pro ) {
															if ( isset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $this->plugin_basename ] ) || empty( $bstwbsftwppdtplgns_options['time_out'] ) || ! array_key_exists( $this->plugin_basename, $bstwbsftwppdtplgns_options['time_out'] ) ) {
																$license_type = 'Pro';
																$license_status = __( 'Inactive', 'ballerburg9005' ) . ' <a href="#' . $this->prefix . '_license_tab" class="bws_trigger_tab_click">' . __( 'Learn More', 'ballerburg9005' ) . '</a>';
															} else {
																$finish = strtotime( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] );
																$today = strtotime( date( "m/d/Y" ) );
																if ( isset( $bstwbsftwppdtplgns_options['trial'][ $this->plugin_basename ] ) ) {
																	$license_type = 'Trial Pro';

																	if ( $finish < $today ) {
																		$license_status = __( 'Expired', 'ballerburg9005' );
																	} else {
																		$daysleft = floor( ( $finish - $today ) / ( 60*60*24 ) );
																		$license_status = sprintf( __( '%s day(-s) left', 'ballerburg9005' ), $daysleft );
																	}
																	$license_status .= '. <a target="_blank" href="' . esc_url( $this->plugins_info['PluginURI'] ) . '">' . __( 'Upgrade to Pro', 'ballerburg9005' ) . '</a>';
																} else {
																	$license_type = isset( $bstwbsftwppdtplgns_options['nonprofit'][ $this->plugin_basename ] ) ? 'Nonprofit Pro' : 'Pro';
																	if ( ! empty( $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) && $finish < $today ) {
																		$license_status = sprintf( __( 'Expired on %s', 'ballerburg9005' ), $bstwbsftwppdtplgns_options['time_out'][ $this->plugin_basename ] ) . '. <a target="_blank" href="https://support.ballerburg9005.com/entries/53487136">' . __( 'Renew Now', 'ballerburg9005' ) . '</a>';
																	} else {
																		$license_status = __( 'Active', 'ballerburg9005' );
																	}
																}
															} ?>
                                                            <div class="misc-pub-section">
                                                                <strong><?php _e( 'License', 'ballerburg9005' ); ?>:</strong> <?php echo $license_type; ?>
                                                            </div>
                                                            <div class="misc-pub-section">
                                                                <strong><?php _e( 'Status', 'ballerburg9005' ); ?>:</strong> <?php echo $license_status; ?>
                                                            </div><!-- .misc-pub-section -->
														<?php } ?>
                                                        <div class="misc-pub-section">
                                                            <strong><?php _e( 'Version', 'ballerburg9005' ); ?>:</strong> <?php echo $this->plugins_info['Version']; ?>
                                                        </div><!-- .misc-pub-section -->
                                                        <?php /**
                                                         * action - Display additional content for #misc-publishing-actions
                                                         */
                                                        do_action( __CLASS__ . '_information_postbox_bottom' ); ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                                <div id="major-publishing-actions">
                                                    <div id="publishing-action">
                                                        <input type="hidden" name="<?php echo $this->prefix; ?>_form_submit" value="submit" />
                                                        <input id="bws-submit-button" type="submit" class="button button-primary button-large" value="<?php _e( 'Save Changes', 'ballerburg9005' ); ?>" />
														<?php wp_nonce_field( $this->plugin_basename, 'bws_nonce_name' ); ?>
                                                    </div>
                                                    <div class="clear"></div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
									<?php /**
									 * action - Display custom metabox
									 */
									do_action( __CLASS__ . '_display_metabox' ); ?>
                                </div>
                            </div>
                            <div id="postbox-container-2" class="postbox-container">
								<?php /**
								 * action - Display additional content for #postbox-container-2
								 */
								do_action( __CLASS__ . '_display_second_postbox' ); ?>
                                <div class="submit">
                                    <input type="submit" class="button button-primary button-large" value="<?php _e( 'Save Changes', 'ballerburg9005' ); ?>" />
                                </div>
								<?php if ( ! empty( $this->wp_slug ) )
									bws_plugin_reviews_block( $this->plugins_info['Name'], $this->wp_slug ); ?>
                            </div>
                        </div>
                </form>
                </div>
			<?php }
		}

		/**
		 * Displays the Tabs
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function display_tabs() { ?>
            <div id="bws_settings_tabs_wrapper">
                <ul id="bws_settings_tabs">
					<?php $this->display_tabs_list(); ?>
                </ul>
				<?php $this->display_tabs_content(); ?>
                <div class="clear"></div>
                <input type="hidden" name="bws_active_tab" value="<?php if ( isset( $_REQUEST['bws_active_tab'] ) ) echo esc_attr( $_REQUEST['bws_active_tab'] ); ?>" />
            </div>
		<?php }

		/**
		 * Displays the list of tabs
		 * @access private
		 * @return void
		 */
		private function display_tabs_list() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs )
					continue;
				$tab_class = 'bws-tab-' . $tab_slug;
				if ( ! empty( $data['is_pro'] ) )
					$tab_class .= ' bws_pro_tab';
				if ( ! empty( $data['class'] ) )
					$tab_class .= ' ' . $data['class']; ?>
                <li class="<?php echo $tab_class; ?>" data-slug="<?php echo $tab_slug; ?>">
                    <a href="#<?php echo $this->prefix; ?>_<?php echo $tab_slug; ?>_tab">
                        <span><?php echo esc_html( $data['label'] ); ?></span>
                    </a>
                </li>
			<?php }
		}

		/**
		 * Displays the content of tabs
		 * @access private
		 * @param  string $tab_slug
		 * @return void
		 */
		public function display_tabs_content() {
			foreach ( $this->tabs as $tab_slug => $data ) {
				if ( ! empty( $data['is_pro'] ) && $this->hide_pro_tabs )
					continue; ?>
                <div class="bws_tab ui-tabs-panel ui-widget-content ui-corner-bottom" id="<?php echo esc_attr( $this->prefix . '_' . $tab_slug . '_tab' ); ?>" aria-labelledby="ui-id-2" role="tabpanel" aria-hidden="false" style="display: block;">
					<?php $tab_slug = str_replace( '-', '_', $tab_slug );
					if ( method_exists( $this, 'tab_' . $tab_slug ) ) {
						call_user_func( array( $this, 'tab_' . $tab_slug ) );
						do_action_ref_array( __CLASS__ . '_after_tab_' . $tab_slug, array( &$this ) );
					} ?>
                </div>
			<?php }
		}

		/**
		 * Save all options from all tabs and display errors\messages
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function save_all_tabs_options() {
			$message = $notice = $error = '';
			/* Restore default settings */
			if ( isset( $_POST['bws_restore_confirm'] ) && check_admin_referer( $this->plugin_basename, 'bws_settings_nonce_name' ) ) {
				$this->restore_options();
				$message = __( 'All plugin settings were restored.', 'ballerburg9005' );
				/* Go Pro - check license key */
			} elseif ( isset( $_POST['bws_license_submit'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
				$result = $this->save_options_license_key();
				if ( ! empty( $result['empty_field_error'] ) )
					$error = $result['empty_field_error'];
				if ( ! empty( $result['error'] ) )
					$error = $result['error'];
				if ( ! empty( $result['message'] ) )
					$message = $result['message'];
				if ( ! empty( $result['notice'] ) )
					$notice = $result['notice'];
				/* check demo data */
			} else {
				$demo_result = ! empty( $this->demo_data ) ? $this->demo_data->bws_handle_demo_data() : false;
				if ( false !== $demo_result ) {
					if ( ! empty( $demo_result ) && is_array( $demo_result ) ) {
						$error   = $demo_result['error'];
						$message = $demo_result['done'];
						if ( ! empty( $demo_result['done'] ) && ! empty( $demo_result['options'] ) )
							$this->options = $demo_result['options'];
					}
					/* Save options */
				} elseif ( ! isset( $_REQUEST['bws_restore_default'] ) && ! isset( $_POST['bws_handle_demo'] ) && isset( $_REQUEST[ $this->prefix . '_form_submit'] ) && check_admin_referer( $this->plugin_basename, 'bws_nonce_name' ) ) {
					/* save tabs */
					$result = $this->save_options();
					if ( ! empty( $result['error'] ) )
						$error = $result['error'];
					if ( ! empty( $result['message'] ) )
						$message = $result['message'];
					if ( ! empty( $result['notice'] ) )
						$notice = $result['notice'];

					if ( '' == $this->change_permission_attr ) {
						/* save `misc` tab */
						$result = $this->save_options_misc();
						if ( ! empty( $result['notice'] ) )
							$notice .= $result['notice'];
					}

					if ( array_key_exists( 'custom_code', $this->tabs ) ) {
						/* save `custom code` tab */
						$this->save_options_custom_code();
					}
				}
			}

			return compact( 'message', 'notice', 'error' );
		}

		/**
		 * Display error\message\notice
		 * @access public
		 * @param  $save_results - array with error\message\notice
		 * @return void
		 */
		public function display_messages( $save_results ) {
			/**
			 * action - Display custom error\message\notice
			 */
			do_action( __CLASS__ . '_display_custom_messages', $save_results ); ?>
            <div class="updated fade inline" <?php if ( empty( $save_results['message'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['message']; ?></strong></p></div>
            <div class="updated bws-notice inline" <?php if ( empty( $save_results['notice'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['notice']; ?></strong></p></div>
            <div class="error inline" <?php if ( empty( $save_results['error'] ) ) echo "style=\"display:none\""; ?>><p><strong><?php echo $save_results['error']; ?></strong></p></div>
		<?php }

		/**
		 * Save plugin options to the database
		 * @access public
		 * @param  ab
		 * @return array    The action results
		 * @abstract
		 */
		public function save_options() {
			die( 'function Bws_Settings_Tabs::save_options() must be over-ridden in a sub-class.' );
		}

		/**
		 * Get 'custom_code' status and content
		 * @access private
		 */
		private function get_custom_code() {
			global $bstwbsftwppdtplgns_options;

			$this->custom_code_args = array(
				'is_css_active' => false,
				'content_css'  	=> '',
				'css_writeable'	=> false,
				'is_php_active' => false,
				'content_php' 	=> '',
				'php_writeable'	=> false,
				'is_js_active' 	=> false,
				'content_js' 	=> '',
				'js_writeable'	=> false,
			);

			if ( ! $this->upload_dir )
				$this->upload_dir = wp_upload_dir();

			$folder = $this->upload_dir['basedir'] . '/bws-custom-code';
			if ( ! $this->upload_dir["error"] ) {
				if ( ! is_dir( $folder ) )
					wp_mkdir_p( $folder, 0755 );

				$index_file = $this->upload_dir['basedir'] . '/bws-custom-code/index.php';
				if ( ! file_exists( $index_file ) ) {
					if ( $f = fopen( $index_file, 'w+' ) )
						fclose( $f );
				}
			}

			if ( $this->is_multisite )
				$this->custom_code_args['blog_id'] = get_current_blog_id();

			foreach ( array( 'css', 'php', 'js' ) as $extension ) {
				$file = 'bws-custom-code.' . $extension;
				$real_file = $folder . '/' . $file;

				if ( file_exists( $real_file ) ) {
					update_recently_edited( $real_file );
					$this->custom_code_args["content_{$extension}"] = file_get_contents( $real_file );
					if ( ( $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] ) ) ||
					     ( ! $this->is_multisite && isset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] ) ) ) {
						$this->custom_code_args["is_{$extension}_active"] = true;
					}
					if ( is_writeable( $real_file ) )
						$this->custom_code_args["{$extension}_writeable"] = true;
				} else {
					$this->custom_code_args["{$extension}_writeable"] = true;
					if ( 'php' == $extension )
						$this->custom_code_args["content_{$extension}"] = "<?php" . "\n" . "if ( ! defined( 'ABSPATH' ) ) exit;" . "\n" . "if ( ! defined( 'BWS_GLOBAL' ) ) exit;" . "\n\n" . "/* Start your code here */" . "\n";
				}
			}
		}

		/**
		 * Display 'custom_code' tab
		 * @access private
		 */
		private function tab_custom_code() { ?>
            <h3 class="bws_tab_label"><?php _e( 'Custom Code', 'ballerburg9005' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php if ( ! current_user_can( 'edit_plugins' ) ) {
				echo '<p>' . __( 'You do not have sufficient permissions to edit plugins for this site.', 'ballerburg9005' ) . '</p>';
				return;
			}

			$list = array(
				'css' => array( 'description' 	=> __( 'These styles will be added to the header on all pages of your site.', 'ballerburg9005' ),
				                'learn_more_link'	=> 'https://developer.mozilla.org/en-US/docs/Web/Guide/CSS/Getting_started'
				),
				'php' => array( 'description' 	=> sprintf( __( 'This PHP code will be hooked to the %s action and will be printed on front end only.', 'ballerburg9005' ), '<a href="https://codex.wordpress.org/Plugin_API/Action_Reference/init" target="_blank"><code>init</code></a>' ),
				                'learn_more_link'	=> 'https://php.net/'
				),
				'js' => array( 'description' 	=> __( 'These code will be added to the header on all pages of your site.', 'ballerburg9005' ),
				               'learn_more_link'	=> 'https://developer.mozilla.org/en-US/docs/Web/JavaScript'
				),
			);

			if ( ! $this->custom_code_args['css_writeable'] ||
			     ! $this->custom_code_args['php_writeable'] ||
			     ! $this->custom_code_args['js_writeable'] ) { ?>
                <p><em><?php printf( __( 'You need to make this files writable before you can save your changes. See %s the Codex %s for more information.', 'ballerburg9005' ),
							'<a href="https://codex.wordpress.org/Changing_File_Permissions" target="_blank">',
							'</a>' ); ?></em></p>
			<?php }

			foreach ( $list as $extension => $extension_data ) {
				$name = 'js' == $extension ? 'JavaScript' : strtoupper( $extension ); ?>
                <p><big>
                        <strong><?php echo $name; ?></strong>
						<?php if ( ! $this->custom_code_args["{$extension}_writeable"] )
							echo '(' . __( 'Browsing', 'ballerburg9005' ) . ')'; ?>
                    </big></p>
                <p class="bws_info">
                    <label>
                        <input type="checkbox" name="bws_custom_<?php echo $extension; ?>_active" value="1" <?php if ( $this->custom_code_args["is_{$extension}_active"] ) echo "checked"; ?> />
						<?php printf( __( 'Activate custom %s code.', 'ballerburg9005' ), $name ); ?>
                    </label>
                </p>
                <textarea cols="70" rows="25" name="bws_newcontent_<?php echo $extension; ?>" id="bws_newcontent_<?php echo $extension; ?>"><?php if ( isset( $this->custom_code_args["content_{$extension}"] ) ) echo esc_textarea( $this->custom_code_args["content_{$extension}"] ); ?></textarea>
                <p class="bws_info">
					<?php echo $extension_data['description']; ?>
                    <br>
                    <a href="<?php echo esc_url( $extension_data['learn_more_link'] ); ?>" target="_blank">
						<?php printf( __( 'Learn more about %s', 'ballerburg9005' ), $name ); ?>
                    </a>
                </p>
			<?php }
		}

		/**
		 * Save plugin options to the database
		 * @access private
		 * @return array    The action results
		 */
		private function save_options_custom_code() {
			global $bstwbsftwppdtplgns_options;
			$folder = $this->upload_dir['basedir'] . '/bws-custom-code';

			foreach ( array( 'css', 'php', 'js' ) as $extension ) {
				$file = 'bws-custom-code.' . $extension;
				$real_file = $folder . '/' . $file;

				if ( isset( $_POST["bws_newcontent_{$extension}"] ) &&
				     $this->custom_code_args["{$extension}_writeable"] ) {
					$newcontent = trim( wp_unslash( $_POST["bws_newcontent_{$extension}"] ) );
					if ( 'css' == $extension )
						$newcontent = wp_kses( $newcontent, array( '\'', '\"' ) );

					if ( ! empty( $newcontent ) && isset( $_POST["bws_custom_{$extension}_active"] ) ) {
						$this->custom_code_args["is_{$extension}_active"] = true;
						if ( $this->is_multisite ) {
							$bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] = ( 'php' == $extension ) ? $real_file : $this->upload_dir['baseurl'] . '/bws-custom-code/' . $file;
						} else {
							$bstwbsftwppdtplgns_options['custom_code'][ $file ] = ( 'php' == $extension ) ? $real_file : $this->upload_dir['baseurl'] . '/bws-custom-code/' . $file;
						}
					} else {
						$this->custom_code_args["is_{$extension}_active"] = false;
						if ( $this->is_multisite ) {
							if ( isset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] ) )
								unset( $bstwbsftwppdtplgns_options['custom_code'][ $this->custom_code_args['blog_id'] ][ $file ] );
						} else {
							if ( isset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] ) )
								unset( $bstwbsftwppdtplgns_options['custom_code'][ $file ] );
						}
					}
					if ( $f = fopen( $real_file, 'w+' ) ) {
						fwrite( $f, $newcontent );
						fclose( $f );
						$this->custom_code_args["content_{$extension}"] = $newcontent;
					}
				}
			}

			if ( $this->is_multisite )
				update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			else
				update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
		}

		/**
		 * Display 'misc' tab
		 * @access private
		 */
		private function tab_misc() {
			global $bstwbsftwppdtplgns_options; ?>
            <h3 class="bws_tab_label"><?php _e( 'Miscellaneous Settings', 'ballerburg9005' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php /**
			 * action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_misc_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) { ?>
                <div class="error inline bws_visible"><p><strong><?php _e( "Notice", 'ballerburg9005' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to change %s settings on this site in the %s network settings.", 'ballerburg9005' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php }
			if ( $this->forbid_view ) { ?>
                <div class="error inline bws_visible"><p><strong><?php _e( "Notice", 'ballerburg9005' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to view %s settings on this site in the %s network settings.", 'ballerburg9005' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php } else { ?>
                <table class="form-table">
					<?php /**
					 * action - Display custom options on the 'misc' tab
					 */
					do_action( __CLASS__ . '_additional_misc_options_affected' );
					if ( ! empty( $this->pro_page ) && $this->bws_hide_pro_option_exist ) { ?>
                        <tr>
                            <th scope="row"><?php _e( 'Pro Options', 'ballerburg9005' ); ?></th>
                            <td>
                                <label>
                                    <input <?php echo $this->change_permission_attr; ?> name="bws_hide_premium_options_submit" type="checkbox" value="1" <?php if ( ! $this->hide_pro_tabs ) echo 'checked="checked "'; ?> />
                                    <span class="bws_info"><?php _e( 'Enable to display plugin Pro options.', 'ballerburg9005' ); ?></span>
                                </label>
                            </td>
                        </tr>
					<?php } ?>
                    <tr>
                        <th scope="row"><?php _e( 'Track Usage', 'ballerburg9005' ); ?></th>
                        <td>
                            <label>
                                <input <?php echo $this->change_permission_attr; ?> name="bws_track_usage" type="checkbox" value="1" <?php if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) echo 'checked="checked "'; ?>/>
                                <span class="bws_info"><?php _e( 'Enable to allow tracking plugin usage anonymously in order to make it better.', 'ballerburg9005' ); ?></span>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php _e( 'Default Settings', 'ballerburg9005' ); ?></th>
                        <td>
                            <input<?php echo $this->change_permission_attr; ?> name="bws_restore_default" type="submit" class="button" value="<?php _e( 'Restore Settings', 'ballerburg9005' ); ?>" />
                            <div class="bws_info"><?php _e( 'This will restore plugin settings to defaults.', 'ballerburg9005' ); ?></div>
                        </td>
                    </tr>
                </table>
			<?php }
		}

		/**
		 * Display 'Import / Export' tab
		 * @access private
		 */
		public function tab_import_export() { ?>
            <h3 class="bws_tab_label"><?php _e( 'Import / Export', 'ballerburg9005' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php /**
			 * action - Display custom options on the Import / Export' tab
			 */
			do_action( __CLASS__ . '_additional_import_export_options' );

			if ( ! $this->forbid_view && ! empty( $this->change_permission_attr ) ) { ?>
                <div class="error inline bws_visible"><p><strong><?php _e( "Notice", 'ballerburg9005' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to change %s settings on this site in the %s network settings.", 'ballerburg9005' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php }
			if ( $this->forbid_view ) { ?>
                <div class="error inline bws_visible"><p><strong><?php _e( "Notice", 'ballerburg9005' ); ?>:</strong> <strong><?php printf( __( "It is prohibited to view %s settings on this site in the %s network settings.", 'ballerburg9005' ), $this->plugins_info["Name"], $this->plugins_info["Name"] ); ?></strong></p></div>
			<?php } else { ?>
                <table class="form-table">
					<?php /**
					 * action - Display custom options on the Import / Export' tab
					 */
					do_action( __CLASS__ . '_additional_import_export_options_affected' ); ?>
                </table>
			<?php }
		}

		/**
		 * Save plugin options to the database
		 * @access private
		 */
		private function save_options_misc() {
			global $bstwbsftwppdtplgns_options, $wp_version;
			$notice = '';

			/* hide premium options */
			if ( ! empty( $this->pro_page ) ) {
				if ( isset( $_POST['bws_hide_premium_options'] ) ) {
					$hide_result = bws_hide_premium_options( $this->options );
					$this->hide_pro_tabs = true;
					$this->options = $hide_result['options'];
					if ( ! empty( $hide_result['message'] ) )
						$notice = $hide_result['message'];
					if ( $this->is_network_options )
						update_site_option( $this->prefix . '_options', $this->options );
					else
						update_option( $this->prefix . '_options', $this->options );
				} else if ( isset( $_POST['bws_hide_premium_options_submit'] ) ) {
					if ( ! empty( $this->options['hide_premium_options'] ) ) {
						$key = array_search( get_current_user_id(), $this->options['hide_premium_options'] );
						if ( false !== $key )
							unset( $this->options['hide_premium_options'][ $key ] );
						if ( $this->is_network_options )
							update_site_option( $this->prefix . '_options', $this->options );
						else
							update_option( $this->prefix . '_options', $this->options );
					}
					$this->hide_pro_tabs = false;
				} else {
					if ( empty( $this->options['hide_premium_options'] ) ) {
						$this->options['hide_premium_options'][] = get_current_user_id();
						if ( $this->is_network_options )
							update_site_option( $this->prefix . '_options', $this->options );
						else
							update_option( $this->prefix . '_options', $this->options );
					}
					$this->hide_pro_tabs = true;
				}
			}
			/* Save 'Track Usage' option */
			if ( isset( $_POST['bws_track_usage'] ) ) {
				if ( empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					$bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] = true;
					$track_usage = true;
				}
			} else {
				if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ) ) {
					unset( $bstwbsftwppdtplgns_options['track_usage']['products'][ $this->plugin_basename ] ); false;
					$track_usage = false;
				}
			}
			if ( isset( $track_usage ) ) {
				$usage_id = ! empty( $bstwbsftwppdtplgns_options['track_usage']['usage_id'] ) ? $bstwbsftwppdtplgns_options['track_usage']['usage_id'] : false;
				/* send data */
				$options = array(
					'timeout' => 3,
					'body' => array(
						'url' 			=> get_bloginfo( 'url' ),
						'wp_version' 	=> $wp_version,
						'is_active'		=> $track_usage,
						'product'		=> $this->plugin_basename,
						'version'		=> $this->plugins_info['Version'],
						'usage_id'		=> $usage_id,
					),
					'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
				);
				$raw_response = wp_remote_post( 'https://ballerburg9005.com/wp-content/plugins/products-statistics/track-usage/', $options );

				if ( ! is_wp_error( $raw_response ) && 200 == wp_remote_retrieve_response_code( $raw_response ) ) {
					$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );

					if ( is_array( $response ) &&
					     ! empty( $response['usage_id'] ) &&
					     $response['usage_id'] != $usage_id ) {
						$bstwbsftwppdtplgns_options['track_usage']['usage_id'] = $response['usage_id'];
					}
				}

				if ( $this->is_multisite )
					update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				else
					update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
			}

			return compact( 'notice' );
		}

		/**
		 *
		 */
		public function tab_license() {
			global $wp_version, $bstwbsftwppdtplgns_options; ?>
            <h3 class="bws_tab_label"><?php _e( 'License Key', 'ballerburg9005' ); ?></h3>
			<?php $this->help_phrase(); ?>
            <hr>
			<?php
			foreach ( $this->licenses as $single_license ) {
				$pro_plugin_name = ( strpos( $single_license['name'], 'Pro' ) ) ? $single_license['name'] : $single_license['name'] . ' ' . 'Pro';
				if ( ! empty( $this->pro_page ) || ! empty( $single_license['pro_basename'] )  ) {

					if ( $this->pro_plugin_is_activated && ( empty( $single_license['pro_basename'] ) || isset( $this->bws_license_plugin ) ) ) {
						$url = 'https://ballerburg9005.com/wp-content/plugins/paid-products/plugins/downloads/?bws_first_download=' . $this->bws_license_plugin . '&bws_license_key=' . $bstwbsftwppdtplgns_options[ $this->bws_license_plugin ] . '&download_from=5'; ?>
						<table class="form-table">
                            <tr>
                                <th scope="row"><?php echo $pro_plugin_name . ' License'; ?></th>
                                <td>
                                    <p>
										<strong><?php _e( 'Your Pro plugin is ready', 'ballerburg9005' ); ?></strong>
										<br>
										<?php _e( 'Your plugin has been zipped, and now is ready to download.', 'ballerburg9005' ); ?>
									</p>
									<p>
										<a class="button button-secondary" target="_parent" href="<?php echo esc_url( $url ); ?>"><?php _e( 'Download Now', 'ballerburg9005' ); ?></a>
									</p>
									<br>
									<p>
										<strong><?php _e( 'Need help installing the plugin?', 'ballerburg9005' ); ?></strong>
										<br>
										<a target="_blank" href="https://docs.google.com/document/d/1-hvn6WRvWnOqj5v5pLUk7Awyu87lq5B_dO-Tv-MC9JQ/"><?php _e( 'How to install WordPress plugin from your admin Dashboard (ZIP archive)', 'ballerburg9005' ); ?></a>
									</p>
									<br>					
									<p>
										<strong><?php _e( 'Get Started', 'ballerburg9005' ); ?></strong>
										<br>
										<a target="_blank" href="https://drive.google.com/drive/u/0/folders/0B5l8lO-CaKt9VGh0a09vUjNFNjA"><?php _e( 'Documentation', 'ballerburg9005' ); ?></a>
										<br>
										<a target="_blank" href="https://www.youtube.com/user/ballerburg9005"><?php _e( 'Video Instructions', 'ballerburg9005' ); ?></a>
										<br>
										<a target="_blank" href="https://support.ballerburg9005.com"><?php _e( 'Knowledge Base', 'ballerburg9005' ); ?></a>
									</p>
                                </td>
                            </tr>
                        </table>
					<?php } else {
						$attr = $license_key = '';
						if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['count'] ) &&
						     '5' < $bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['count'] &&
						     $bstwbsftwppdtplgns_options['go_pro'][ $this->bws_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) )
							$attr = 'disabled="disabled"';

						if ( ! empty( $single_license['pro_basename'] ) ) {
							$license_key = ! empty( $bstwbsftwppdtplgns_options[ $single_license['pro_basename'] ] ) ? $bstwbsftwppdtplgns_options[ $single_license['pro_basename'] ] : '';
						} ?>
                        <table class="form-table">
                            <tr>
                                <th scope="row"><?php echo $pro_plugin_name . ' License'; ?></th>
                                <td>
                                    <input <?php echo $attr; ?> type="text" name="bws_license_key_<?php echo ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug']; ?>" value="<?php echo esc_attr( $license_key ); ?>" />
                                    <input <?php echo $attr; ?> type="hidden" name="bws_license_plugin_<?php echo ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug']; ?>" value="<?php echo esc_attr( ( ! empty( $single_license['pro_slug'] ) ) ? $single_license['pro_slug'] : $single_license['slug'] ); ?>" />
                                    <input <?php echo $attr; ?> type="submit" class="button button-secondary" name="bws_license_submit" value="<?php _e( 'Activate', 'ballerburg9005' ); ?>" />
                                    <div class="bws_info">
										<?php printf( __( 'Enter your license key to activate %s and get premium plugin features.', 'ballerburg9005' ), '<a href="' . $this->bws_plugin_link . '" target="_blank" title="' . $pro_plugin_name . '">' . $pro_plugin_name . '</a>' ); ?>
                                    </div>
									<?php if ( '' != $attr ) { ?>
                                        <p><?php _e( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'ballerburg9005' ); ?></p>
									<?php }
									if ( $this->trial_days !== false )
										echo '<p>' . __( 'or', 'ballerburg9005' ) . ' <a href="' . esc_url( $this->plugins_info['PluginURI'] . 'trial/?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version ) . '" target="_blank">' . sprintf( __( 'Start Your Free %s-Day Trial Now', 'ballerburg9005' ), $this->trial_days ) . '</a></p>'; ?>
                                </td>
                            </tr>
                        </table>
					<?php }
				} else {
					global $bstwbsftwppdtplgns_options;
					$license_key = ( isset( $bstwbsftwppdtplgns_options[ $single_license['basename'] ] ) ) ? $bstwbsftwppdtplgns_options[ $single_license['basename'] ] : ''; ?>
                    <table class="form-table">
                        <tr>
                            <th scope="row"><?php echo $pro_plugin_name . ' License'; ?></th>
                            <td>
                                <input type="text" maxlength="100" name="bws_license_key_<?php echo $single_license['slug']; ?>" value="<?php echo esc_attr( $license_key ); ?>" />
                                <input type="submit" class="button button-secondary" name="bws_license_submit" value="<?php _e( 'Check license key', 'ballerburg9005' ); ?>" />
                                <div class="bws_info">
									<?php _e( 'If necessary, you can check if the license key is correct or reenter it in the field below.', 'ballerburg9005' ); ?>
                                </div>
                            </td>
                        </tr>
                    </table>
				<?php }
			} ?>
            <table class="form-table">
                <tr>
                    <th scope="row"><?php _e( 'Manage License Settings', 'ballerburg9005' ); ?></th>
                    <td>
                        <a class="button button-secondary" href="https://ballerburg9005.com/client-area" target="_blank"><?php _e( 'Login to Client Area', 'ballerburg9005' ); ?></a>
                        <div class="bws_info">
							<?php _e( 'Manage active licenses, download BWS products, and view your payment history using ballerburg9005 Client Area.', 'ballerburg9005' ); ?>
                        </div>
                    </td>
                </tr>
            </table>
		<?php }

		/**
		 * Save plugin options to the database
		 * @access private
		 * @param  ab
		 * @return array    The action results
		 */
		private function save_options_license_key() {
			global $wp_version, $bstwbsftwppdtplgns_options;
			/*$empty_field_error - added to avoid error when 1 field is empty while another field contains license key*/
			
			$error = $message = $empty_field_error = '';
			
			foreach ( $this->licenses as $single_license) {
				$bws_license_key = ( isset( $_POST[ ( ! empty( $single_license['pro_slug'] ) ) ? 'bws_license_key_' . $single_license['pro_slug'] : 'bws_license_key_' . $single_license['slug'] ] ) ) ? stripslashes( sanitize_text_field( $_POST[ ( ! empty( $single_license['pro_slug'] ) ) ? 'bws_license_key_' . $single_license['pro_slug'] : 'bws_license_key_' . $single_license['slug'] ] ) ) : '';
				if ( '' != $bws_license_key ) {
					if ( strlen( $bws_license_key ) != 18 ) {
						$error = __( 'Wrong license key', 'ballerburg9005' );
					} else {

						/* CHECK license key */
						if ( $this->is_pro && empty( $single_license['pro_basename'] ) ) {
							delete_transient( 'bws_plugins_update' );
							if ( ! $this->all_plugins ) {
								if ( ! function_exists( 'get_plugins' ) ) {
									require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
								}
								$this->all_plugins = get_plugins();
							}
							$current = get_site_transient( 'update_plugins' );

							if ( ! empty( $this->all_plugins ) && ! empty( $current ) && isset( $current->response ) && is_array( $current->response ) ) {
								$to_send = array();
								$to_send["plugins"][ $single_license['basename'] ] = $this->all_plugins[ $single_license['basename'] ];
								$to_send["plugins"][ $single_license['basename'] ]["bws_license_key"] = $bws_license_key;
								$to_send["plugins"][ $single_license['basename'] ]["bws_illegal_client"] = true;
								$options                                                            = array(
									'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
									'body'       => array( 'plugins' => serialize( $to_send ) ),
									'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
								);
								$raw_response = wp_remote_post( 'https://ballerburg9005.com/wp-content/plugins/paid-products/plugins/pro-license-check/1.0/', $options );

								if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
									$error = __( 'Something went wrong. Please try again later. If the error appears again, please contact us', 'ballerburg9005' ) . ': <a href=https://support.ballerburg9005.com>ballerburg9005</a>. ' . __( 'We are sorry for inconvenience.', 'ballerburg9005' );
								} else {
									$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
									if ( is_array( $response ) && ! empty( $response ) ) {
										foreach ( $response as $single_response ) {
											if ( "wrong_license_key" == $single_response->package ) {
												$error = __( 'Wrong license key.', 'ballerburg9005' );
											} else if ( "wrong_domain" == $single_response->package ) {
												$error = __( 'This license key is bound to another site.', 'ballerburg9005' );
											} else if ( "time_out" == $single_response->package ) {
												$message = __( 'This license key is valid, but Your license has expired. If you want to update our plugin in future, you should extend the license.', 'ballerburg9005' );
											} elseif ( "you_are_banned" == $single_response->package ) {
												$error = __( "Unfortunately, you have exceeded the number of available tries.", 'ballerburg9005' );
											} elseif ( "duplicate_domen_for_trial" == $single_response->package ) {
												$error = __( "Unfortunately, the Pro Trial licence was already installed to this domain. The Pro Trial license can be installed only once.", 'ballerburg9005' );
											}
											if ( empty( $error ) ) {
												if ( empty( $message ) ) {
													if ( isset( $single_response->trial ) ) {
														$message = __( 'The Pro Trial license key is valid.', 'ballerburg9005' );
													} else {
														$message = __( 'The license key is valid.', 'ballerburg9005' );
													}

													if ( ! empty( $single_response->time_out ) ) {
														$message .= ' ' . __( 'Your license will expire on', 'ballerburg9005' ) . ' ' . $single_response->time_out . '.';
													} else {
														/* lifetime */
														$single_response->time_out = NULL;
													}

													if ( isset( $single_response->trial ) && $this->is_trial ) {
														$message .= ' ' . sprintf( __( 'In order to continue using the plugin it is necessary to buy a %s license.', 'ballerburg9005' ), '<a href="' . esc_url( $this->plugins_info['PluginURI'] . '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version ) . '" target="_blank" title="' . $this->plugins_info["Name"] . '">Pro</a>' );
													}
												}

												if ( isset( $single_response->trial ) ) {
													$bstwbsftwppdtplgns_options['trial'][ $single_license['basename'] ] = 1;
												} else {
													unset( $bstwbsftwppdtplgns_options['trial'][ $single_license['basename'] ] );
												}

												if ( isset( $single_response->nonprofit ) ) {
													$bstwbsftwppdtplgns_options['nonprofit'][ $single_license['basename'] ] = 1;
												} else {
													unset( $bstwbsftwppdtplgns_options['nonprofit'][ $single_license['basename'] ] );
												}

												if ( ! isset( $bstwbsftwppdtplgns_options[ $single_license['basename'] ] ) || $bstwbsftwppdtplgns_options[ $single_license['basename'] ] != $bws_license_key ) {
													$bstwbsftwppdtplgns_options[ $single_license['basename'] ] = $bws_license_key;

													$file = @fopen( dirname( dirname( __FILE__ ) ) . "/license_key.txt", "w+" );
													if ( $file ) {
														@fwrite( $file, $bws_license_key );
														@fclose( $file );
													}
													$update_option = true;
												}

												if ( isset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $single_license['basename'] ] ) ) {
													unset( $bstwbsftwppdtplgns_options['wrong_license_key'][ $single_license['basename'] ] );
													$update_option = true;
												}

												if ( ! isset( $bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] ) || $bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] != $single_response->time_out ) {
													$bstwbsftwppdtplgns_options['time_out'][ $single_license['basename'] ] = $single_response->time_out;
													$update_option = true;
												}

												if ( isset( $update_option ) ) {
													if ( $this->is_multisite ) {
														update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
													} else {
														update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
													}
												}
											}
										}
									} else {
										$error = __( 'Something went wrong. Please try again later. If the error appears again, please contact us', 'ballerburg9005' ) . ' <a href=https://support.ballerburg9005.com>ballerburg9005</a>. ' . __( 'We are sorry for inconvenience.', 'ballerburg9005' );
									}
								}
							}
							/* Go Pro */
						} else {

							$bws_license_plugin = stripslashes( sanitize_text_field( $_POST[ ( ! empty( $single_license['pro_slug'] ) ) ? 'bws_license_plugin_' .  $single_license['pro_slug'] : 'bws_license_plugin_' .  $single_license['slug'] ] ) );
							if ( isset( $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] ) && $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time'] > ( time() - ( 24 * 60 * 60 ) ) ) {
								$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = $bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] + 1;
							} else {
								$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['count'] = 1;
								$bstwbsftwppdtplgns_options['go_pro'][ $bws_license_plugin ]['time']  = time();
							}

							/* download Pro */
							if ( ! $this->all_plugins ) {
								if ( ! function_exists( 'get_plugins' ) ) {
									require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
								}
								$this->all_plugins = get_plugins();
							}

							if ( ! array_key_exists( $bws_license_plugin, $this->all_plugins ) ) {
								$current = get_site_transient( 'update_plugins' );
								if ( ! empty( $current ) && isset( $current->response ) && is_array( $current->response ) ) {
									$to_send                                                         = array();
									$to_send["plugins"][ $bws_license_plugin ]                       = array();
									$to_send["plugins"][ $bws_license_plugin ]["bws_license_key"]    = $bws_license_key;
									$to_send["plugins"][ $bws_license_plugin ]["bws_illegal_client"] = true;
									$options                                                         = array(
										'timeout'    => ( ( defined( 'DOING_CRON' ) && DOING_CRON ) ? 30 : 3 ),
										'body'       => array( 'plugins' => serialize( $to_send ) ),
										'user-agent' => 'WordPress/' . $wp_version . '; ' . get_bloginfo( 'url' )
									);
									$raw_response = wp_remote_post( 'https://ballerburg9005.com/wp-content/plugins/paid-products/plugins/pro-license-check/1.0/', $options );

									if ( is_wp_error( $raw_response ) || 200 != wp_remote_retrieve_response_code( $raw_response ) ) {
										$error = __( "Something went wrong. Please try again later. If the error appears again, please contact us", 'ballerburg9005' ) . ': <a href="https://support.ballerburg9005.com">ballerburg9005</a>. ' . __( "We are sorry for inconvenience.", 'ballerburg9005' );
									} else {
										$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );
										if ( is_array( $response ) && ! empty( $response ) ) {
											foreach ( $response as $single_response ) {
												if ( "wrong_license_key" == $single_response->package ) {
													$error = __( "Wrong license key.", 'ballerburg9005' );
												} elseif ( "wrong_domain" == $single_response->package ) {
													$error = __( "This license key is bound to another site.", 'ballerburg9005' );
												} elseif ( "you_are_banned" == $single_response->package ) {
													$error = __( "Unfortunately, you have exceeded the number of available tries per day. Please, upload the plugin manually.", 'ballerburg9005' );
												} elseif ( "time_out" == $single_response->package ) {
													$error = sprintf( __( "Unfortunately, Your license has expired. To continue getting top-priority support and plugin updates, you should extend it in your %s.", 'ballerburg9005' ), ' <a href="https://ballerburg9005.com/client-area">Client Area</a>' );
												} elseif ( "duplicate_domen_for_trial" == $single_response->package ) {
													$error = __( "Unfortunately, the Pro licence was already installed to this domain. The Pro Trial license can be installed only once.", 'ballerburg9005' );
												}
											}
											if ( empty( $error ) ) {
												$bws_license_plugin = ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'];

												$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;
												$this->pro_plugin_is_activated = true;
											}
										} else {
											$error = __( "Something went wrong. Try again later or upload the plugin manually. We are sorry for inconvenience.", 'ballerburg9005' );
										}
									}
								}
							} else {
								$bstwbsftwppdtplgns_options[ $bws_license_plugin ] = $bws_license_key;
								/* activate Pro */
								if ( ! is_plugin_active( $bws_license_plugin ) ) {
									if ( $this->is_multisite && is_plugin_active_for_network( ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ) ) {
										/* if multisite and free plugin is network activated */
										$network_wide = true;
									} else {
										/* activate on a single blog */
										$network_wide = false;
									}
									activate_plugin( $bws_license_plugin, null, $network_wide );
									$this->pro_plugin_is_activated = true;
								}
							}
							/* add 'track_usage' for Pro version */
							if ( ! empty( $bstwbsftwppdtplgns_options['track_usage'][ ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ] ) &&
							     empty( $bstwbsftwppdtplgns_options['track_usage'][ $bws_license_plugin ] ) ) {
								$bstwbsftwppdtplgns_options['track_usage'][ $bws_license_plugin ] = $bstwbsftwppdtplgns_options['track_usage'][ ( ! empty( $single_license['pro_basename'] ) ) ? $single_license['pro_basename'] : $single_license['basename'] ];
							}

							if ( $this->is_multisite ) {
								update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
							} else {
								update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
							}

							if ( $this->pro_plugin_is_activated ) {
								delete_transient( 'bws_plugins_update' );
							}
						}
					}
				} else {
					$empty_field_error = __( "Please, enter Your license key", 'ballerburg9005' );
				}
			}
			return compact( 'error', 'message', 'empty_field_error' );
		}

		/**
		 * Display help phrase
		 * @access public
		 * @param  void
		 * @return array    The action results
		 */
		public function help_phrase() {
			echo '<div class="bws_tab_description">' . __( 'Need Help?', 'ballerburg9005' ) . ' ';
			if ( '' != $this->doc_link )
				echo '<a href="' . esc_url( $this->doc_link ) . '" target="_blank">' . __( 'Read the Instruction', 'ballerburg9005' );
			else
				echo '<a href="https://support.ballerburg9005.com/hc/en-us/" target="_blank">' . __( 'Visit Help Center', 'ballerburg9005' );
			if ( '' != $this->doc_video_link )
				echo '</a>' . ' ' . __( 'or', 'ballerburg9005' ) . ' ' . '<a href="' . esc_url( $this->doc_video_link ) . '" target="_blank">' . __( 'Watch the Video', 'ballerburg9005' );
			echo '</a></div>';
		}

		public function bws_pro_block_links() {
			global $wp_version; ?>
            <div class="bws_pro_version_tooltip">
                <a class="bws_button" href="<?php echo esc_url( $this->plugins_info['PluginURI'] ); ?>?k=<?php echo $this->link_key; ?>&amp;pn=<?php echo $this->link_pn; ?>&amp;v=<?php echo $this->plugins_info["Version"]; ?>&amp;wp_v=<?php echo $wp_version; ?>" target="_blank" title="<?php echo $this->plugins_info["Name"]; ?>"><?php _e( 'Upgrade to Pro', 'ballerburg9005' ); ?></a>
				<?php if ( $this->trial_days !== false ) { ?>
                    <span class="bws_trial_info">
						<?php _e( 'or', 'ballerburg9005' ); ?>
                        <a href="<?php echo esc_url( $this->plugins_info['PluginURI'] . '?k=' . $this->link_key . '&pn=' . $this->link_pn . '&v=' . $this->plugins_info["Version"] . '&wp_v=' . $wp_version ); ?>" target="_blank" title="<?php echo $this->plugins_info["Name"]; ?>"><?php _e( 'Start Your Free Trial', 'ballerburg9005' ); ?></a>
					</span>
				<?php } ?>
                <div class="clear"></div>
            </div>
		<?php }

		/**
		 * Restore plugin options to defaults
		 * @access public
		 * @param  void
		 * @return void
		 */
		public function restore_options() {
			unset(
				$this->default_options['first_install'],
				$this->default_options['suggest_feature_banner'],
				$this->default_options['display_settings_notice']
			);
			/**
			 * filter - Change default_options array OR process custom functions
			 */
			$this->options = apply_filters( __CLASS__ . '_additional_restore_options', $this->default_options );
			if ( $this->is_network_options ) {
				$this->options['network_apply'] = 'default';
				$this->options['network_view'] = '1';
				$this->options['network_change'] = '1';
				update_site_option( $this->prefix . '_options', $this->options );
			} else {
				update_option( $this->prefix . '_options', $this->options );
			}
		}

		public function add_request_feature() { ?>
			<div id="bws_request_feature" class="widget-access-link">
				<button type="button" class="button" ><?php _e( 'Request a Feature', 'ballerburg9005' ); ?></button>
			</div>
			<?php $modal_html = '<div class="bws-modal bws-modal-deactivation-feedback bws-modal-request-feature">
		    	<div class="bws-modal-dialog">
		    		<div class="bws-modal-body">
		    			<h2>' . sprintf( __( 'How can we improve %s?', 'ballerburg9005' ), $this->plugins_info['Name'] ) . '</h2>
		    			<div class="bws-modal-panel active">
		    				<p>' . __( 'We look forward to hear your ideas.', 'ballerburg9005' ) . '</p>
		    				<p>
		    					<textarea placeholder="' . __( 'Describe your idea', 'ballerburg9005' ) . '..."></textarea>
		    				</p>
		    				<label class="bws-modal-anonymous-label">
			    				<input type="checkbox" /> ' . __( 'Send website data and allow to contact me back', 'ballerburg9005' ) . '
							</label>
						</div>
					</div>
					<div class="bws-modal-footer">
						<a href="#" class="button disabled bws-modal-button button-primary">' . __( 'Submit', 'ballerburg9005' ) . '</a>
						<span class="bws-modal-processing hidden">' . __( 'Processing', 'ballerburg9005' ) . '...</span>
						<span class="bws-modal-thank-you hidden">' . __( 'Thank you!', 'ballerburg9005' ) . '</span>
						<div class="clear"></div>
					</div>
				</div>
			</div>';

			$script = "(function($) {
				var modalHtml = " . json_encode( $modal_html ) . ",
					\$modal = $( modalHtml );
				
				\$modal.appendTo( $( 'body' ) );

				$( '#bws_request_feature .button' ).on( 'click', function() {
					/* Display the dialog box.*/
					\$modal.addClass( 'active' );
					$( 'body' ).addClass( 'has-bws-modal' );				
				});

				\$modal.on( 'keypress', 'textarea', function( evt ) {
					BwsModalEnableButton();
				});

				\$modal.on( 'click', '.bws-modal-footer .button', function( evt ) {
					evt.preventDefault();

					if ( $( this ).hasClass( 'disabled' ) ) {
						return;
					}
					var info = \$modal.find( 'textarea' ).val();

					if ( info.length == 0 ) {
						return;
					}

					var _parent = $( this ).parents( '.bws-modal:first' ),
						_this =  $( this );

					var is_anonymous = ( \$modal.find( '.bws-modal-anonymous-label' ).find( 'input' ).is( ':checked' ) ) ? 0 : 1;

					$.ajax({
						url       : ajaxurl,
						method    : 'POST',
						data      : {
							'action'			: 'bws_submit_request_feature_action',
							'plugin'			: '" . $this->plugin_basename . "',
							'info'				: info,
							'is_anonymous'		: is_anonymous,
							'bws_ajax_nonce'	: '" . wp_create_nonce( 'bws_ajax_nonce' ) . "'
						},
						beforeSend: function() {
							_parent.find( '.bws-modal-footer .bws-modal-button' ).hide();
							_parent.find( '.bws-modal-footer .bws-modal-processing' ).show();
							_parent.find( 'textarea, input' ).attr( 'disabled', 'disabled' );
						},
						complete  : function( message ) {
							_parent.find( '.bws-modal-footer .bws-modal-processing' ).hide();
							_parent.find( '.bws-modal-footer .bws-modal-thank-you' ).show();
						}
					});
				});

				/* If the user has clicked outside the window, cancel it. */
				\$modal.on( 'click', function( evt ) {
					var \$target = $( evt.target );

					/* If the user has clicked anywhere in the modal dialog, just return. */
					if ( \$target.hasClass( 'bws-modal-body' ) || \$target.hasClass( 'bws-modal-footer' ) ) {
						return;
					}

					/* If the user has not clicked the close button and the clicked element is inside the modal dialog, just return. */
					if ( ! \$target.hasClass( 'bws-modal-button-close' ) && ( \$target.parents( '.bws-modal-body' ).length > 0 || \$target.parents( '.bws-modal-footer' ).length > 0 ) ) {
						return;
					}

					/* Close the modal dialog */
					\$modal.removeClass( 'active' );
					$( 'body' ).removeClass( 'has-bws-modal' );

					return false;
				});

				function BwsModalEnableButton() {
					\$modal.find( '.bws-modal-button' ).removeClass( 'disabled' ).show();
					\$modal.find( '.bws-modal-processing' ).hide();
				}

				function BwsModalDisableButton() {
					\$modal.find( '.bws-modal-button' ).addClass( 'disabled' );
				}

				function BwsModalShowPanel() {
					\$modal.find( '.bws-modal-panel' ).addClass( 'active' );
				}
			})(jQuery);";

			/* add script in FOOTER */
			wp_register_script( 'bws-request-feature-dialog', '', array( 'jquery' ), false, true );
			wp_enqueue_script( 'bws-request-feature-dialog' );
			wp_add_inline_script( 'bws-request-feature-dialog', sprintf( $script ) );
		}
	}
}


/**
 * Called after the user has submitted his reason for deactivating the plugin.
 *
 * @since  2.1.3
 */
if ( ! function_exists( 'bws_submit_request_feature_action' ) ) {
	function bws_submit_request_feature_action() {
		global $bstwbsftwppdtplgns_options, $wp_version, $bstwbsftwppdtplgns_active_plugins, $current_user;

		wp_verify_nonce( $_REQUEST['bws_ajax_nonce'], 'bws_ajax_nonce' );

		$basename = isset( $_REQUEST['plugin'] ) ? stripcslashes( sanitize_text_field( $_REQUEST['plugin'] ) ) : '';
		$info = stripcslashes( sanitize_text_field( $_REQUEST['info'] ) );

		if ( empty( $info ) || empty( $basename ) ) {
			exit;
		}
		
		$info = substr( $info, 0, 255 );
		$is_anonymous = isset( $_REQUEST['is_anonymous'] ) && 1 == $_REQUEST['is_anonymous'];

		$options = array(
			'product'	=> $basename,
			'info'		=> $info,
		);

		if ( ! $is_anonymous ) {
			if ( ! isset( $bstwbsftwppdtplgns_options ) )
				$bstwbsftwppdtplgns_options = ( is_multisite() ) ? get_site_option( 'bstwbsftwppdtplgns_options' ) : get_option( 'bstwbsftwppdtplgns_options' );

			if ( ! empty( $bstwbsftwppdtplgns_options['track_usage']['usage_id'] ) ) {
				$options['usage_id'] = $bstwbsftwppdtplgns_options['track_usage']['usage_id'];
			} else {
				$options['usage_id'] = false;
				$options['url'] = get_bloginfo( 'url' );
				$options['wp_version'] = $wp_version;
				$options['is_active'] = false;
				$options['version'] = $bstwbsftwppdtplgns_active_plugins[ $basename ]['Version'];
			}

			$options['email'] = $current_user->data->user_email;
		}

		/* send data */
		$raw_response = wp_remote_post( 'https://ballerburg9005.com/wp-content/plugins/products-statistics/request-feature/', array(
			'method'  => 'POST',
			'body'    => $options,
			'timeout' => 15,
		) );

		if ( ! is_wp_error( $raw_response ) && 200 == wp_remote_retrieve_response_code( $raw_response ) ) {
			if ( ! $is_anonymous ) {
				$response = maybe_unserialize( wp_remote_retrieve_body( $raw_response ) );			

				if ( is_array( $response ) && ! empty( $response['usage_id'] ) && $response['usage_id'] != $options['usage_id'] ) {
					$bstwbsftwppdtplgns_options['track_usage']['usage_id'] = $response['usage_id'];

					if ( is_multisite() )
						update_site_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
					else
						update_option( 'bstwbsftwppdtplgns_options', $bstwbsftwppdtplgns_options );
				}
			}

			echo 'done';
		} else {
			echo $response->get_error_code() . ': ' . $response->get_error_message();
		}
		exit;
	}
}

add_action( 'wp_ajax_bws_submit_request_feature_action', 'bws_submit_request_feature_action' );
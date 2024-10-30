<?php
namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Settings Class
 */
class Settings {

	/**
	 * Core Objet
	 *
	 * @var object
	 */
	public static $core;
	/**
	 * Plugin Info
	 *
	 * @var object
	 */
	public static $plugin_info;

	/**
	 * Settings Tab Key
	 *
	 * @var string
	 */
	protected static $settings_tab_key;

	/**
	 * Settings Key.
	 *
	 * @var string
	 */
	protected static $settings_key;

	/**
	 * Settings Tab name
	 *
	 * @var array
	 */
	protected $settings_tab;

	/**
	 * Settings Tabs Array.
	 *
	 * @var array
	 */
	protected $settings_tabs = array();

	/**
	 * Current Settings Active Tab.
	 *
	 * @var string
	 */
	protected $current_active_tab;

	/**
	 * Settings Tab Fields
	 *
	 * @var array
	 */
	protected $fields = array();

	/**
	 * Settings Array.
	 *
	 * @var array
	 */
	public static $settings = array();

	/**
	 * Default Settings.
	 *
	 * @var array
	 */
	protected static $default_settings = array();

	/**
	 * Class Constructor.
	 */
	public function __construct( $core, $plugin_info ) {
		self::$core               = $core;
		self::$plugin_info        = $plugin_info;
		self::$settings_tab_key   = self::$plugin_info['name'];
		self::$settings_key       = self::$plugin_info['name'] . '-main-settings';
		$this->settings_tab       = array( self::$settings_tab_key => esc_html__( 'Coming Soon Products [GrandPlugins]', 'gpls-wcsamm-coming-soon-for-woocommerce' ) );
		$this->current_active_tab = isset( $_GET['tab'] ) ? sanitize_text_field( wp_unslash( $_GET['tab'] ) ) : 'general';
		$this->settings_tabs      = array(
			array(
				'key'   => 'general',
				'label' => esc_html__( 'General', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
			),
			array(
				'key'   => 'badge',
				'label' => esc_html__( 'Badge', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
			),
			array(
				'key'   => 'countdown',
				'label' => esc_html__( 'Countdown', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
			),
			array(
				'key'   => 'subscribe',
				'label' => esc_html__( 'Subscribe', 'woo-coming-soon-products' ),
			),
			array(
				'key'   => 'email',
				'label' => esc_html__( 'Subscribed Emails', 'woo-coming-soon-products' ),
			),
		);
		self::$default_settings   = array(
			'general'   => array(
				'coming_soon_text'                   => wp_kses_post( '<h3>' . esc_html__( 'Coming Soon', 'woo-coming-soon-products' ) . '</h3>' ),
				'single_placement_position'          => 'woocommerce_single_product_summary',
				'single_placement_position_priority' => 29,
			),
			'badge'     => array(
				'badge_status'          => 'off',
				'badge_icon'            => self::$plugin_info['url'] . 'assets/images/coming-soon-icon-9.png',
				'badge_width'           => 60,
				'badge_height'          => 60,
				'badge_left'            => 0,
				'badge_top'             => 0,
				'badge_angle'           => 0,
				'badge_resp_breakpoint' => 480,
				'badge_resp_width'      => 30,
				'badge_resp_height'     => 30,
				'badge_side'            => 'top_left',
			),
			'countdown' => array(
				'loop'    => array(
					'status'      => 'off',
					'text_status' => 'off',
				),
				'seconds' => array(
					'title_color'         => '#000',
					'counter_front_color' => '#FFF',
					'counter_back_color'  => '#000',
					'divider_color'       => '#000',
				),
				'minutes' => array(
					'title_color'         => '#000',
					'counter_front_color' => '#FFF',
					'counter_back_color'  => '#000',
					'divider_color'       => '#000',
				),
				'hours'   => array(
					'title_color'         => '#000',
					'counter_front_color' => '#FFF',
					'counter_back_color'  => '#000',
					'divider_color'       => '#000',
				),
				'days'    => array(
					'title_color'         => '#000',
					'counter_front_color' => '#FFF',
					'counter_back_color'  => '#000',
					'divider_color'       => '#000',
				),
			),
		);

		$this->init();
		$this->register_hooks();
	}


	/**
	 * Init Settings.
	 *
	 * @return void
	 */
	public function init() {
		$this->settings_tabs    = apply_filters( self::$plugin_info['name'] . '-settings-tabs', $this->settings_tabs );
		self::$default_settings = apply_filters( self::$plugin_info['name'] . '-default-settings', self::$default_settings );
		self::$settings         = self::get_settings();
	}

	/**
	 * Register Settings Hooks.
	 *
	 * @return void
	 */
	public function register_hooks() {
		add_filter( 'woocommerce_settings_tabs_array', array( $this, 'add_settings_tab' ), 100, 1 );

		foreach ( array_keys( $this->settings_tab ) as $name ) {
			add_action( 'woocommerce_settings_' . $name, array( $this, 'settings_tab_action' ), 10 );
			add_action( 'woocommerce_after_settings_' . $name, array( $this, 'after_settings' ) );
			add_action( 'woocommerce_update_options_' . $name, array( $this, 'save_settings' ), 10 );
		}

		add_filter( 'admin_enqueue_scripts', array( $this, 'add_settings_assets' ), 10000 );
		add_action( 'woocommerce_sections_' . self::$settings_tab_key, array( $this, 'settings_tabs' ), 100 );
		add_filter( 'admin_footer_text', '__return_false', PHP_INT_MAX );

		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-custom-redirect-link-select', array( $this, 'ajax_custom_redirect_link_select' ) );

		add_action( 'wp_ajax_' . self::$plugin_info['name'] . '-upload-custom-badge-icon-action', array( $this, 'ajax_upload_badge_icon' ) );

		add_action( 'plugin_action_links_' . self::$plugin_info['basename'], array( $this, 'settings_link' ), 5, 1 );
	}

	/**
	 * Settings Link.
	 *
	 * @param array $links Plugin Row Links.
	 * @return array
	 */
	public function settings_link( $links ) {
		$links[] = '<a href="' . esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . self::$plugin_info['name'] ) ) . '">' . esc_html__( 'Settings' ) . '</a>';
		return $links;
	}

	/**
	 * Settings Assets
	 *
	 * @return void
	 */
	public function add_settings_assets() {
		if ( ! empty( $_GET['tab'] ) && in_array( sanitize_text_field( wp_unslash( $_GET['tab'] ) ), array_keys( $this->settings_tab ) ) ) {
			if ( ! wp_style_is( 'select2' ) ) {
				wp_enqueue_style( 'select2' );
			}
			if ( ! wp_script_is( 'jquery', 'enqueued' ) ) {
				wp_enqueue_script( 'jquery' );
			}

			wp_deregister_style( 'porto_admin' );
			wp_dequeue_style( 'porto_admin' );

			wp_enqueue_style( self::$plugin_info['name'] . '-settings-bootstrap-css', self::$core->core_assets_lib( 'bootstrap', 'css' ), array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_style( self::$plugin_info['name'] . '-settings-styles', self::$plugin_info['url'] . 'assets/dist/css/settings-styles.min.css', array(), self::$plugin_info['version'], 'all' );

			wp_enqueue_script( 'wp-color-picker' );
			wp_enqueue_style( 'wp-color-picker' );
			wp_enqueue_style( self::$plugin_info['name'] . '-flipdown-responsive-style', self::$plugin_info['url'] . 'assets/dist/css/flipdown.min.css', array(), self::$plugin_info['version'], 'all' );
			wp_enqueue_script( self::$plugin_info['name'] . '-flipdown-responsive', self::$plugin_info['url'] . 'core/assets/libs/flipdown.min.js', array( 'jquery' ), self::$plugin_info['version'], true );

			wp_enqueue_script( self::$plugin_info['name'] . '-dmuploader-js', self::$core->core_assets_lib( 'jquery.dm-uploader', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-settings-bootstrap-js', self::$core->core_assets_lib( 'bootstrap.bundle', 'js' ), array( 'jquery' ), self::$plugin_info['version'], true );
			wp_enqueue_script( self::$plugin_info['name'] . '-settings-script', self::$plugin_info['url'] . 'assets/dist/js/settings-actions.min.js', array( 'jquery', 'wp-i18n', self::$plugin_info['name'] . '-flipdown-responsive' ), self::$plugin_info['version'], true );

			wp_localize_script(
				self::$plugin_info['name'] . '-settings-script',
				self::$plugin_info['localize_var'],
				array(
					'ajaxUrl'                     => admin_url( 'admin-ajax.php' ),
					'nonce'                       => wp_create_nonce( self::$plugin_info['name'] . '_nonce' ),
					'preview_nonce'               => wp_create_nonce( self::$plugin_info['name'] . '-popup-preview' ),
					'prefix'                      => self::$plugin_info['name'],
					'custom_redirect_link_action' => self::$plugin_info['name'] . '-custom-redirect-link-select',
					'badge_icons_url_base'        => self::$plugin_info['url'] . 'assets/images/',
					'badge_settings'              => self::get_settings( 'badge' ),
					'uploadBadgeIconAction'       => self::$plugin_info['name'] . '-upload-custom-badge-icon-action',
					'labels'                      => array(
						'only_images'     => esc_html__( 'Only images are allowed', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'flipDownHeading' => array(
							'days'    => esc_html__( 'Days', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
							'hours'   => esc_html__( 'Hours', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
							'minutes' => esc_html__( 'Minutes', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
							'seconds' => esc_html__( 'Seconds', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						),
					),
					'classes_prefix'              => self::$plugin_info['classes_prefix'],
				)
			);

			if ( empty( $_GET['action'] ) ) {
				wp_enqueue_media();
				wp_enqueue_editor();
				wp_enqueue_code_editor(
					array(
						'type' => 'text/html',
					)
				);
		 	}

			if ( empty( $_GET['action'] ) ) {
				wp_enqueue_style( 'woocommerce' );
				wp_enqueue_script( 'woocommerce' );
				wp_enqueue_script( 'wc-single-product' );
			}
		}
	}

	/**
	 * Ajax Upload Badge Icon.
	 */
	public function ajax_upload_badge_icon() {
		check_admin_referer( self::$plugin_info['name'] . '_nonce', 'nonce' );

		if ( empty( $_FILES['file'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'no file uploaded!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				)
			);
		}
		$uploads              = wp_upload_dir();
		$badges_path          = trailingslashit( $uploads['basedir'] ) . trailingslashit( self::$plugin_info['name'] );
		$badge_file           = $_FILES['file'];
		$badge_file_name      = sanitize_text_field( wp_unslash( $badge_file['name'] ) );
		$upload_error_strings = array(
			false,
			sprintf(
				/* translators: 1: upload_max_filesize, 2: php.ini */
				esc_html__( 'The uploaded file exceeds the %1$s directive in %2$s.' ),
				'upload_max_filesize',
				'php.ini'
			),
			sprintf(
				/* translators: %s: MAX_FILE_SIZE */
				esc_html__( 'The uploaded file exceeds the %s directive that was specified in the HTML form.' ),
				'MAX_FILE_SIZE'
			),
			esc_html__( 'The uploaded file was only partially uploaded.' ),
			esc_html__( 'No file was uploaded.' ),
			'',
			esc_html__( 'Missing a temporary folder.' ),
			esc_html__( 'Failed to write file to disk.' ),
			esc_html__( 'File upload stopped by extension.' ),
		);

							// === apply checks on the file === //.
		// uploaded check.
		if ( ! is_uploaded_file( $badge_file['tmp_name'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file upload is failed!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				)
			);
		}

		// Already Exists?.
		if ( @file_exists( $badges_path . $badge_file_name ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file already exists!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				)
			);
		}

		// Unknow Error.
		if ( isset( $badge_file['error'] ) && ! is_numeric( $badge_file['error'] ) && ( 0 !== $badge_file['error'] ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html( sanitize_text_field( wp_unslash( $badge_file['error'] ) ) ),
				)
			);
		}

		// Known Error.
		if ( isset( $badge_file['error'] ) && $badge_file['error'] > 0 ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html( $upload_error_strings[ absint( $badge_file['error'] ) ] ),
				)
			);
		}

		// Size Check.
		$file_size = filesize( $badge_file['tmp_name'] );
		if ( ! ( $file_size > 0 ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file is empty!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				)
			);
		}

		$check      = wp_check_filetype( $badge_file_name );
		$image_exts = array( 'jpg', 'jpeg', 'jpe', 'gif', 'png', 'webp' );

		if ( empty( $check['ext'] ) || ( ! in_array( $check['ext'], $image_exts ) ) || empty( $check['type'] ) || ( 0 !== strpos( $check['type'], 'image' ) ) ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => esc_html__( 'file type is invalid!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				)
			);
		}
								// == End Checks == //.
		if ( ! is_dir( $badges_path ) ) {
			@mkdir( $badges_path );
		}

		// Move the file to the plugin badges folder.
		$new_badge_file = $badges_path . $badge_file_name;
		$moved_new_file = @move_uploaded_file( $badge_file['tmp_name'], $new_badge_file );

		if ( false === $moved_new_file ) {
			wp_send_json_success(
				array(
					'result'  => false,
					'status'  => 'error',
					'message' => sprintf( esc_html__( 'The uploaded file could not be moved to %s.', 'gpls-wcsamm-coming-soon-for-woocommerce' ), $new_badge_file ),
				)
			);
		}

		// Set correct file permissions.
		$stat  = stat( dirname( $new_badge_file ) );
		$perms = $stat['mode'] & 0000666;
		chmod( $new_badge_file, $perms );

		// Get all available fonts and return.
		$badges = $this->badge_icons_list_html();
		wp_send_json_success(
			array(
				'result'  => true,
				'status'  => 'success',
				'badges'  => $badges,
				'message' => esc_html__( 'Badge icons is added successfully!', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
			)
		);
	}

	/**
	 * Badge Icons HTML.
	 *
	 * @return string
	 */
	private function badge_icons_list_html() {
		$badge_settings = self::get_settings( 'badge' );
		$badges         = self::get_badges();
		ob_start();
		foreach ( $badges as $badge ) {
			?>
			<div class="badge-icon-element col boder shadow-sm px-3 py-1">
				<input type="radio" <?php echo esc_attr( $badge['name'] === $badge_settings['badge_icon'] ? 'checked' : '' ); ?>  value="<?php echo esc_attr( $badge['name'] ); ?>" name="<?php echo esc_attr( self::$plugin_info['name'] . '[badge][badge_icon]' ); ?>" class="edit edit-badge-icon-radio d-block mx-auto my-3">
				<img width="75" height="75" src="<?php echo esc_url_raw( $badge['url'] ); ?>" alt="coming-soon-badge-icon" class="d-block mx-auto pb-2">
			</div>
			<?php
		}
		return ob_get_clean();
	}

	/**
	 * Get Badge URL by name.
	 *
	 * @param string $badge_name
	 * @return string|false
	 */
	public static function get_badge_url( $badge_name ) {
		$badges = self::get_badges();
		foreach ( $badges as $badge ) {
			if ( $badge['name'] === $badge_name ) {
				return $badge['url'];
			}
		}
		return self::$plugin_info['url'] . 'assets/images/coming-soon-icon-9.png';
	}

	/**
	 * Settings Tabs.
	 *
	 * @return void
	 */
	public function settings_tabs() {
		?>
		<nav class="nav-tab-wrapper woo-nav-tab-wrapper wp-clearfix">
			<?php
			foreach ( $this->settings_tabs as $tab_index => $tab_arr ) :
				$classname = 'nav-tab';
				if ( 0 === $tab_index ) {
					if ( empty( $_GET['action'] ) || ( $tab_arr['key'] === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
						$classname .= ' nav-tab-active';
					}
				} else {
					if ( ! empty( $_GET['action'] ) && ( $tab_arr['key'] === sanitize_text_field( wp_unslash( $_GET['action'] ) ) ) ) {
						$classname .= ' nav-tab-active';
					}
				}
				?>
			<a href="<?php echo esc_url( admin_url( 'admin.php?page=wc-settings&tab=' . self::$plugin_info['name'] . ( 0 === $tab_index ? '' : '&action=' . esc_attr( $tab_arr['key'] ) ) ) ); ?>" class="<?php echo esc_attr( $classname ); ?>"><?php printf( esc_html__( '%s', 'gpls-wcsamm-coming-soon-for-woocommerce' ), $tab_arr['label'] ); ?></a>
			<?php endforeach; ?>
		</nav>
		<?php
	}

	/**
	 * Get Settings Values.
	 *
	 * @return array|string
	 */
	public static function get_settings( $custom_key = null, $custom_subkey = null ) {
		self::$settings = self::rec_parse_args( maybe_unserialize( get_option( self::$settings_key, self::$default_settings ) ), self::$default_settings );
		$result         = self::$settings;
		if ( $custom_key && isset( self::$settings[ $custom_key ] ) ) {
			$result = self::$settings[ $custom_key ];
			if ( $custom_subkey && isset( self::$settings[ $custom_key ][ $custom_subkey ] ) ) {
				$result = self::$settings[ $custom_key ][ $custom_subkey ];
			}
		}
		return $result;
	}

	/**
	 * Plugin Settings Tab in WordPress Settings Page.
	 *
	 * @return array
	 */
	public function add_settings_tab( $settings_tabs ) {
		foreach ( array_keys( $this->settings_tab ) as $name ) {
			$settings_tabs[ $name ] = $this->settings_tab[ $name ];
		}
		return $settings_tabs;
	}

	/**
	 * SHow the Settings Tab Fields.
	 *
	 * @return void
	 */
	public function settings_tab_action() {
		if ( ! empty( $_GET['action'] ) ) {
			$action      = sanitize_text_field( wp_unslash( $_GET['action'] ) );
			$method_name = $action . '_tab';
			if ( in_array( $action, array_column( $this->settings_tabs, 'key' ) ) && method_exists( __CLASS__, $method_name ) ) {
				$this->{$method_name}();
			}
		} else {
			$this->general_tab();
		}
		do_action( self::$plugin_info['name'] . '-main-settings-tabs-action', self::$plugin_info, self::$settings );
	}

	/**
	 * General Settings.
	 *
	 * @return void
	 */
	public function general_tab() {
		$core             = self::$core;
		$plugin_info      = self::$plugin_info;
		$general_settings = self::get_settings( 'general' );
		include_once self::$plugin_info['path'] . 'templates/settings/general-settings.php';
	}

	/**
	 * Badge Settings.
	 *
	 * @return void
	 */
	public function badge_tab() {
		$available_badges = self::get_badges();
		$plugin_info      = self::$plugin_info;
		$core             = self::$core;
		$badge_settings   = self::get_settings( 'badge' );
		$preview_url      = $this->get_badge_preview_image_url();
		require_once self::$plugin_info['path'] . 'templates/settings/badge-settings.php';
	}

	/**
	 * CountDown Tab.
	 *
	 * @return void
	 */
	public function countdown_tab() {
		$plugin_info        = self::$plugin_info;
		$core               = self::$core;
		$countdown_settings = self::get_settings( 'countdown' );
		$core               = self::$core;
		require_once self::$plugin_info['path'] . 'templates/settings/countdown-settings.php';
	}

	/**
	 * Redirects Tab
	 *
	 * @return void
	 */
	public function subscribe_tab() {
		$plugin_info                 = self::$plugin_info;
		$core                        = self::$core;
		$GLOBALS['hide_save_button'] = true;
		require_once self::$plugin_info['path'] . 'templates/settings/subscribe-settings.php';
	}

	/**
	 * Redirects Tab
	 *
	 * @return void
	 */
	public function email_tab() {
		$plugin_info                 = self::$plugin_info;
		$core                        = self::$core;
		$GLOBALS['hide_save_button'] = true;
		require_once self::$plugin_info['path'] . 'templates/settings/email-settings.php';
	}

	/**
	 * Get Badges Icons.
	 *
	 * @return array
	 */
	public static function get_badges() {
		require_once \ABSPATH . 'wp-admin/includes/file.php';

		$badges       = array();
		$uploads      = wp_upload_dir();
		$badges_path  = self::$plugin_info['path'] . 'assets/images/';
		$badges_url   = self::$plugin_info['url'] . 'assets/images/';
		$badges_files = list_files( $badges_path );

		foreach ( $badges_files as $badge ) {
			$badge_name = wp_basename( $badge );
			$badge_ext  = pathinfo( $badge, PATHINFO_EXTENSION );
			$badges[]   = array(
				'name' => $badge_name,
				'ext'  => $badge_ext,
				'url'  => $badges_url . $badge_name,
				'path' => $badge,
			);
		}

		$badges_path  = trailingslashit( $uploads['basedir'] ) . trailingslashit( self::$plugin_info['name'] );
		$badges_url   = trailingslashit( $uploads['baseurl'] ) . trailingslashit( self::$plugin_info['name'] );
		$badges_files = list_files( $badges_path );

		foreach ( $badges_files as $badge ) {
			$badge_name = wp_basename( $badge );
			$badge_ext  = pathinfo( $badge, PATHINFO_EXTENSION );
			$badges[]   = array(
				'name' => $badge_name,
				'ext'  => $badge_ext,
				'url'  => $badges_url . $badge_name,
				'path' => $badge,
			);
		}

		return $badges;
	}

	/**
	 * GET Badge Preview Image URL.
	 */
	public function get_badge_preview_image_url() {
		$preview_url = wc_placeholder_img_src( 'woocommerce_single' );
		$products    = wc_get_products(
			array(
				'type' => 'simple',
			)
		);
		ob_start();
		foreach ( $products as $product ) {
			if ( $product->get_image_id() ) {
				$gallery_thumbnail = wc_get_image_size( 'woocommerce_single' );
				$thumbnail_size    = apply_filters( 'woocommerce_gallery_thumbnail_size', array( $gallery_thumbnail['width'], $gallery_thumbnail['height'] ) );
				if ( empty( $thumbnail_size[1] ) ) {
					$thumbnail_size[1] = 0;
				}
				$preview_url = wp_get_attachment_image_src( $product->get_image_id(), $thumbnail_size );
				break;
			}
		}
		return is_array( $preview_url ) ? $preview_url[0] : $preview_url;
	}


	/**
	 * Get Countdown For Preview.
	 *
	 * @param boolean $echo
	 *
	 * @return string|void
	 */
	public static function get_countdown_preview( $echo = false ) {
		if ( ! $echo ) {
			ob_start();
		}
		$current_time = ( current_datetime()->getTimestamp() );
		$arrival_time = strtotime( '+1 year' );
		?>
		<div id="flipdown" class="flipdown flipper flipper-dark <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-flipper' . ' ' ); ?>"
			data-datetime="<?php echo esc_attr( $arrival_time ); ?>"
			data-template="ddd|HH|ii|ss"
			data-labels="Days|Hours|Minutes|Seconds"
			data-reverse="true"
			data-now="<?php echo esc_attr( $current_time ); ?>"
		>
		</div>
		<?php
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Get Countdown Styles.
	 *
	 * @param boolean $echo
	 *
	 * @return string|void
	 */
	public static function get_countdown_styles( $echo = false ) {
		$countdown_settings = self::get_settings( 'countdown' );
		if ( ! $echo ) {
			ob_start();
		}
		foreach ( $countdown_settings as $counter_name => $counter_data ) :
			foreach ( $counter_data as $counter_name_key => $counter_key_value ) :
				$group_class = '.flipdown.flipdown__theme-dark .rotor-group-' . $counter_name;
				if ( 'title_color' === $counter_name_key ) {
					$sub_class   = '.rotor-group-heading span';
					$css_command = 'color';
				} else {
					$sub_class   = '.rotor-painter';
					$css_command = 'background';
				}
				if ( 'counter_front_color' === $counter_name_key ) {
					$css_command = 'color';
				}
				if ( 'counter_back_color' === $counter_name_key ) {
					$css_command = 'background';
				}
				if ( 'divider_color' === $counter_name_key ) {
					$css_command = 'border-top-color';
					$sub_class   = '.rotor:after';
				}
				?>
				<?php echo esc_attr( $group_class . ' ' . $sub_class ); ?> {
				<?php echo esc_attr( $css_command . ': ' . $counter_key_value ); ?>
				}
				<?php
				if ( 'counter_back_color' === $counter_name_key ) {
					echo esc_attr( $group_class . ' .rotor' );
					?>
					 {
					<?php echo esc_attr( $css_command . ': ' . $counter_key_value ); ?>
				}
					<?php
				}
			endforeach;
		endforeach;
		if ( ! $echo ) {
			return ob_get_clean();
		}
	}

	/**
	 * Save Tab Settings.
	 *
	 * @return void
	 */
	public function save_settings() {
		$default_settings = self::$settings;
		foreach ( $this->settings_tabs as $tab_arr ) {
			if ( ! empty( $_POST[ self::$plugin_info['name'] . '-' . $tab_arr['key'] . '-settings-nonce' ] ) && wp_verify_nonce( wp_unslash( $_POST[ self::$plugin_info['name'] . '-' . $tab_arr['key'] . '-settings-nonce' ] ), self::$plugin_info['name'] . '-' . $tab_arr['key'] . '-settings-nonce' ) ) {
				// Special fields.

				if ( isset( $_POST[ self::$plugin_info['name'] ]['general']['coming_soon_text'] ) ) {
					$default_settings['general']['coming_soon_text'] = wp_kses_post( $_POST[ self::$plugin_info['name'] ]['general']['coming_soon_text'] );
				} else {
					if ( isset( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ] ) ) {
						$default_settings[ $tab_arr['key'] ] = self::$default_settings[ $tab_arr['key'] ];
						foreach ( $default_settings[ $tab_arr['key'] ] as $main_key => $key_value ) {
							if ( isset( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ] ) ) {
								if ( is_array( $default_settings[ $tab_arr['key'] ][ $main_key ] ) ) {
									foreach ( $default_settings[ $tab_arr['key'] ][ $main_key ] as $sub_key => $sub_key_value ) {
										if ( isset( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ][ $main_key ][ $sub_key ] ) ) {
											$value = sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ][ $main_key ][ $sub_key ] ) );
											$default_settings[ $tab_arr['key'] ][ $main_key ][ $sub_key ] = is_numeric( $value ) ? absint( $value ) : $value;
										}
									}
								} else {
									if ( isset( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ][ $main_key ] ) ) {
										$value = sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] ][ $tab_arr['key'] ][ $main_key ] ) );
										$default_settings[ $tab_arr['key'] ][ $main_key ] = is_numeric( $value ) ? absint( $value ) : $value;
									}
								}
							}
						}
					}
				}

				if ( isset( $_POST[ self::$plugin_info['name'] ]['general']['single_placement_position'] ) ) {
					$default_settings['general']['single_placement_position'] = sanitize_text_field( $_POST[ self::$plugin_info['name'] ]['general']['single_placement_position'] );
				}
				if ( isset( $_POST[ self::$plugin_info['name'] ]['general']['single_placement_position_priority'] ) ) {
					$default_settings['general']['single_placement_position_priority'] = (int) sanitize_text_field( $_POST[ self::$plugin_info['name'] ]['general']['single_placement_position_priority'] );
				}

				self::$settings = $default_settings;
				update_option( self::$settings_key, $default_settings );
				break;
			}
		}
		do_action( self::$plugin_info['name'] . '-main-settings-save' );
	}

	private static function rec_parse_args( $args, $defaults ) {
		$new_args = (array) $defaults;
		foreach ( $args as $key => $value ) {
			if ( is_array( $value ) && isset( $new_args[ $key ] ) ) {
				$new_args[ $key ] = self::rec_parse_args( $value, $new_args[ $key ] );
			} else {
				$new_args[ $key ] = $value;
			}
		}
		return $new_args;
	}

	/**
	 * Single Product Placement actions.
	 *
	 * @return array
	 */
	public static function single_product_placement_actions() {
		return array(
			'disable',
			'woocommerce_before_single_product',
			'woocommerce_before_single_product_summary',
			'woocommerce_single_product_summary',
			'woocommerce_after_single_product_summary',
			'woocommerce_after_single_product',
		);
	}

	/**
	 * Single Product Placement Actions SELECT html.
	 *
	 * @param boolean $return
	 * @return mixed
	 */
	public static function single_product_placement_actions_select( $name, $selected_value = '', $return = false ) {
		if ( $return ) {
			ob_start();
		}
		?>
		<select name="<?php echo esc_attr( $name ); ?>">
			<?php
			$single_placement_actions = self::single_product_placement_actions();
			foreach ( $single_placement_actions as $action ) :
			?>
			<option <?php selected( $selected_value, $action ); ?>  value="<?php echo esc_attr( $action ); ?>"><?php echo esc_html( $action ); ?></option>
			<?php endforeach; ?>
		</select>
		<?php
		if ( $return ) {
			return ob_get_clean();
		}
	}

	/**
	 * Settings Footer.
	 *
	 * @return void
	 */
	public function after_settings() {
		self::$core->plugins_sidebar( 'woo-coming-soon' );
		self::$core->review_notice( '', true, true );
		self::$core->default_footer_section();
	}

}

<?php
namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

/**
 * Plugin Name:     Coming Soon Products for WooCommerce
 * Description:     Allow Coming Soon mode for WooCommerce Products.
 * Author:          GrandPlugins
 * Author URI:      https://grandplugins.com
 * Text Domain:     gpls-wcsamm-coming-soon-for-woocommerce
 * Std Name:        gpls-wcsamm-coming-soon-for-woocommerce
 * Version:         1.2.0
 *
 * @package         WooCommerce_Coming_Soon_Products
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use GPLSCore\GPLS_PLUGIN_WCSAMM\Core;
use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonFrontend;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonBadge;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonBackend;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonShortCode;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonComp;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoonBulkQuickEdits;

if ( ! class_exists( __NAMESPACE__ . '\GPLS_WCSAMM_Class' ) ) :


	/**
	 * Main Class.
	 */
	class GPLS_WCSAMM_Class {

		/**
		 * The class Single Instance.
		 *
		 * @var object
		 */
		private static $instance = null;

		/**
		 * Plugin Info
		 *
		 * @var array
		 */
		private static $plugin_info;

		/**
		 * Debug Mode Status
		 *
		 * @var bool
		 */
		protected $debug;

		/**
		 * Is Preview.
		 *
		 * @var boolean
		 */
		public $is_preview = false;

		/**
		 * Is Strict applied.
		 *
		 * @var boolean
		 */
		public $is_strict = false;

		/**
		 * Core Object
		 *
		 * @var object
		 */
		private static $core;

		/**
		 * Initialize the class instance.
		 *
		 * @return object
		 */
		public static function init() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}
			return self::$instance;
		}

		/**
		 * Plugin Activated Function
		 *
		 * @return void
		 */
		public static function plugin_activated() {
			self::setup_plugin_info();
			if ( ! is_plugin_active( 'woocommerce/woocommerce.php' ) ) {
				deactivate_plugins( self::$plugin_info['basename'] );
				wp_die( esc_html__( 'WooCommerce plugin is required in order to activate the plugin', 'gpls-wcsamm-coming-soon-for-woocommerce' ) );
			}
			self::disable_duplicate();
		}

		/**
		 * Class Constructor.
		 */
		private function __construct() {
			self::setup_plugin_info();
			$this->load_languages();
			self::includes();
			$this->load();
		}

		/**
		 * Load Classes.
		 *
		 * @return void
		 */
		public function load() {
			if ( ! class_exists( 'woocommerce' ) ) {
				require_once \ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( self::$plugin_info['basename'] );
				return;
			}
			self::$core = new Core( self::$plugin_info );

			new Settings( self::$core, self::$plugin_info );
			new ComingSoon( self::$core, self::$plugin_info );
			ComingSoonBackend::init();
			ComingSoonFrontend::init();
			ComingSoonBadge::init();
			ComingSoonShortCode::init();
			ComingSoonComp::init();
			ComingSoonBulkQuickEdits::init();
		}

		/**
		 * Define Constants
		 *
		 * @param string $key
		 * @param string $value
		 * @return void
		 */
		public function define( $key, $value ) {
			if ( ! defined( $key ) ) {
				define( $key, $value );
			}
		}

		/**
		 * Set Plugin Info
		 *
		 * @return array
		 */
		public static function setup_plugin_info() {
			$plugin_data = get_file_data(
				__FILE__,
				array(
					'Version'     => 'Version',
					'Name'        => 'Plugin Name',
					'URI'         => 'Plugin URI',
					'SName'       => 'Std Name',
					'text_domain' => 'Text Domain',
				),
				false
			);

			self::$plugin_info = array(
				'basename'        => plugin_basename( __FILE__ ),
				'version'         => $plugin_data['Version'],
				'name'            => $plugin_data['SName'],
				'text_domain'     => $plugin_data['text_domain'],
				'file'            => __FILE__,
				'plugin_url'      => $plugin_data['URI'],
				'public_name'     => $plugin_data['Name'],
				'path'            => trailingslashit( plugin_dir_path( __FILE__ ) ),
				'url'             => trailingslashit( plugin_dir_url( __FILE__ ) ),
				'options_page'    => $plugin_data['SName'],
				'localize_var'    => str_replace( '-', '_', $plugin_data['SName'] ) . '_localize_data',
				'type'            => 'pro',
				'classes_prefix'  => 'gpls-wcsamm',
				'classes_general' => 'gpls-general',
				'prefix'          => 'gpls-wcsamm',
				'prefix_under'    => 'gpls_wcsamm',
				'related_plugins' => array(
					'quick_view_and_buy_now' => 'gpls-arcw-quick-view-buy-now-for-woocommerce',
				),
				'pro_link'        => 'https://grandplugins.com/product/woo-coming-soon-products/?utm_source=free&utm_medium=btn&utm_content=' . $plugin_data['SName'],
				'review_link'     => 'https://wordpress.org/support/plugin/coming-soon-products-for-woocommerce/reviews/#new-post',
				'duplicate_base'  => 'gpls-wcsamm-coming-soon-products-for-woocommerce/gpls-wcsamm-woo-coming-soon-products-for-woocommerce.php',
			);
		}

		/**
		 * Disable duplicate.
		 *
		 * @return void
		 */
		private static function disable_duplicate() {
			require_once \ABSPATH . 'wp-admin/includes/plugin.php';
			if ( ! empty( self::$plugin_info['duplicate_base'] ) && is_plugin_active( self::$plugin_info['duplicate_base'] ) ) {
				require_once \ABSPATH . 'wp-admin/includes/plugin.php';
				deactivate_plugins( self::$plugin_info['duplicate_base'] );
			}
		}

		/**
		 * Include plugin files
		 *
		 * @return void
		 */
		public static function includes() {
			require_once trailingslashit( plugin_dir_path( __FILE__ ) ) . 'core/bootstrap.php';
		}

		/**
		 * Load languages Folder.
		 *
		 * @return void
		 */
		public function load_languages() {
			load_plugin_textdomain( self::$plugin_info['text_domain'], false, self::$plugin_info['path'] . 'languages/' );
		}

	}

	add_action( 'plugins_loaded', array( __NAMESPACE__ . '\GPLS_WCSAMM_Class', 'init' ), 1 );
	register_activation_hook( __FILE__, array( __NAMESPACE__ . '\GPLS_WCSAMM_Class', 'plugin_activated' ) );

endif;

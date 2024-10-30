<?php
namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

defined( 'ABSPATH' ) || exit;

use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;

/**
 * Coming Soon Compatibility Class.
 */
class ComingSoonComp extends ComingSoon {

	/**
	 * Singleton Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Singleton Init.
	 *
	 * @param object $core
	 * @param array  $plugin_info
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Hooks.
	 *
	 * @return void
	 */
	private function hooks() {
		// Blocksy Front Assets Comp.
		// add_action( self::$plugin_info['name'] . '-frontend-inline-styles', array( $this, 'blocksy_inline_styles' ) );
	}

	/**
	 * Blocksy Comp Inline Styles.
	 *
	 * @return void
	 */
	public function blocksy_inline_styles() {
		if ( ! $this->is_theme_active( 'blocksy' ) ) {
			return;
		}
		?>
		.ct-image-container .<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-badge-img-wrapper' ); ?> {
			width: 100% !important;
			height: 100% !important;
		}
		<?php
	}
}

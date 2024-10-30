<?php
/**
 * Handles the Coming Soon State of WooCommerce Products Frontend Side.
 * p
 *
 * @category class
 * @package  ComingSoon
 */

namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;

/**
 * Coming Soon Badge Class
 */
class ComingSoonBadge extends ComingSoon {

	/**
	 * Instance.
	 *
	 * @var self
	 */
	private static $instance = null;

	/**
	 * Constructor
	 */
	private function __construct() {
		$this->hooks();
	}

	/**
	 * Constructor.
	 *
	 * @return self
	 */
	public static function init() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	/**
	 * Hooks Function.
	 *
	 * @return void
	 */
	private function hooks() {
		// Soon Badge in loop and single page.
		add_action( 'woocommerce_before_shop_loop_item', array( $this, 'loop_coming_soon_badge' ), 11 );
		add_action( 'woocommerce_before_shop_loop_item_title', array( $this, 'loop_coming_soon_badge' ), 1 );

		add_filter( self::$plugin_info['classes_prefix'] . '-front-localize-data', array( $this, 'badge_localize_data' ), 100, 1 );
		add_action( 'woocommerce_product_thumbnails', array( $this, 'single_add_coming_soon_badge' ), PHP_INT_MAX );

		// Some STUPID Themes clear the styles, overwrite in here.
		add_action( self::$plugin_info['name'] . '-frontend-inline-styles', array( $this, 'badge_styles' ) );

		add_filter( 'woocommerce_single_product_image_gallery_classes', array( $this, 'filter_woo_gallery_wrapper_classes' ), PHP_INT_MAX, 1 );

		add_action( 'woocommerce_single_product_summary', array( $this, 'clear_marked_badged_products_single' ), PHP_INT_MAX );
		add_action( 'woocommerce_after_shop_loop', array( $this, 'clear_marked_badged_products_loop' ), PHP_INT_MAX );
	}

	/**
	 * Add unique Woo Gallery Class.
	 *
	 * @param array $classes
	 * @return array
	 */
	public function filter_woo_gallery_wrapper_classes( $classes ) {
		$classes[] = self::$plugin_info['classes_prefix'] . '-woo-product-gallery-wrapper';
		return $classes;
	}

	/**
	 * Badge Styles.
	 */
	public function badge_styles() {
		?>
		.<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-badge-img-wrapper' ); ?>{ <?php echo esc_attr( self::get_base_styles() ); ?> }
		<?php
		$badge_settings  = Settings::get_settings( 'badge' );
		$rotate_style    = '';
		$position_styles = array(
			'top_left'  => array(
				'bottom' => 'unset !important',
				'right'  => 'unset !important',
				'left'   => $badge_settings['badge_left'] . 'px',
				'top'    => $badge_settings['badge_top'] . 'px',
			),
			'top_right' => array(
				'left'   => 'unset !important',
				'bottom' => 'unset !important',
				'right'  => $badge_settings['badge_left'] . 'px',
				'top'    => $badge_settings['badge_top'] . 'px',
			),

		);
		if ( ! empty( $badge_settings['badge_angle'] ) ) {
			$rotate_style = 'rotate(' . $badge_settings['badge_angle'] . 'deg)';
		}

		foreach ( $position_styles as $position_class => $position_styles ) {
			echo esc_attr( '.' . self::$plugin_info['classes_prefix'] . '-coming-soon-badge-img-wrapper.' . $position_class . '{' );
			$transform_styles = array();
			if ( ! empty( $rotate_style ) ) {
				$transform_styles[] = $rotate_style;
			}
			foreach ( $position_styles as $style_key => $style_value ) {
				if ( 'transform' === $style_key ) {
					$transform_styles[] = $style_value;
				} else {
					echo esc_attr( $style_key ) . ':' . esc_attr( $style_value ) . ';';
				}
			}
			if ( ! empty( $transform_styles ) ) {
				echo esc_attr( 'transform:' . implode( ' ', $transform_styles ) ) . ';';
			}
			echo esc_attr( '}' );
		}
		echo esc_attr( '.' . self::$plugin_info['classes_prefix'] . '-woo-product-gallery-wrapper{position:relative;}' );
		if ( ! empty( $badge_settings['badge_width'] ) || ! empty( $badge_settings['badge_height'] ) ) {
			echo esc_attr( '.' . self::$plugin_info['classes_prefix'] . '-coming-soon-badge-img-wrapper .' . self::$plugin_info['prefix'] . '-coming-soon-badge {' );
			$width_styles = ( ! empty( $badge_settings['badge_width'] ) ? 'width:' . $badge_settings['badge_width'] . 'px !important;min-width:' . $badge_settings['badge_width'] . 'px !important;' : '' ) . ( ! empty( $badge_settings['badge_height'] ) ? 'height:' . $badge_settings['badge_height'] . 'px !important;' : '' );
			echo esc_attr( $width_styles . '}' );
		}

	}

	/**
	 * Badge Localize Data.
	 *
	 * @param array $data
	 * @return array
	 */
	public function badge_localize_data( $data ) {
		$badge_details       = Settings::get_settings( 'badge' );
		$front_badge_details = array(
			'status' => $badge_details['badge_status'],
			'angle'  => ! empty( $badge_details['badge_angle'] ) ? (int) $badge_details['badge_angle'] : 0,
			'left'   => ! empty( $badge_details['badge_left'] ) ? (int) $badge_details['badge_left'] : 0,
			'top'    => ! empty( $badge_details['badge_top'] ) ? (int) $badge_details['badge_top'] : 0,
		);
		$data['badge']       = $front_badge_details;
		return $data;
	}

	/**
	 * Coming Soon Badge HTML.
	 *
	 * @param string $badge_url
	 * @param array  $badge_details
	 * @return string
	 */
	protected static function coming_soon_badge( $badge_url, $badge_details, $return_only_styles = false, $context = 'loop', $img_classes = array() ) {
		$badge_url = empty( $badge_url ) ? self::$plugin_info['url'] . 'assets/images/coming-soon-icon-9.png' : $badge_url;
		if ( $return_only_styles ) {
			$styles = self::get_base_styles();
			return $styles;
		}
		ob_start();
		?>
		<img src="<?php echo esc_url_raw( $badge_url ); ?>" data-<?php echo esc_attr( self::$plugin_info['prefix_under'] . '_src' ); ?>="<?php echo esc_url_raw( $badge_url ); ?>" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-badge ' . self::$plugin_info['classes_prefix'] . '-coming-soon--' . $context . '-badge' ); ?> " <?php echo ! empty( $img_classes ) ? ( esc_attr( 'data-classes' ) . '="' . esc_attr( implode( ' ', $img_classes ) ) . '"' ) : ''; ?> alt="<?php esc_html_e( 'coming soon badge', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?>">
		<?php
		$GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-badge' ] = true;
		return ob_get_clean();
	}

	/**
	 * Get Base Styles.
	 *
	 * @return string
	 */
	private static function get_base_styles() {
		$badge_settings = Settings::get_settings( 'badge' );
		$styles         = 'position:absolute;z-index:1000;margin:unset;display:none;opacity:1 !important;';
		$styles        .= ( ! empty( $badge_settings['badge_width'] ) ? 'width:' . $badge_settings['badge_width'] . 'px !important;min-width:' . $badge_settings['badge_width'] . 'px !important;' : '' ) . ( ! empty( $badge_settings['badge_height'] ) ? 'height:' . $badge_settings['badge_height'] . 'px !important;' : '' );
		return $styles;
	}

	/**
	 * Badge Base Attributes.
	 *
	 * @return void
	 */
	private static function base_attrs( $context = 'loop' ) {
		$badge_settings  = Settings::get_settings( 'badge' );
		$horz_margin_val = ! empty( $badge_settings['badge_left'] ) ? (int) $badge_settings['badge_left'] : 0;
		$vert_margin_val = ! empty( $badge_settings['badge_top'] ) ? (int) $badge_settings['badge_top'] : 0;
		$badge_side_key  = 'badge_side';
		$attrs           = array(
			'data-' . self::$plugin_info['prefix_under'] . '_position'        => $badge_settings[ $badge_side_key ],
			'data-' . self::$plugin_info['prefix_under'] . '_horz_margin_val' => $horz_margin_val,
			'data-' . self::$plugin_info['prefix_under'] . '_vert_margin_val' => $vert_margin_val,
		);

		foreach ( $attrs as $key => $val ) {
			echo esc_attr( $key ) . '="' . esc_attr( $val ) . '" ';
		}
	}

	/**
	 * Add Coming Soon Badge to products.
	 *
	 * @param string $image_thumbnail_html
	 * @param int    $thumbnail_id
	 * @return string
	 */
	public static function add_coming_soon_badge( $image_thumbnail_html, $product = null, $context = 'loop', $img_classes = array() ) {
		if ( is_null( $product ) ) {
			global $product;
		}

		if ( self::is_bypass_badge() ) {
			return $image_thumbnail_html;
		}

		if ( ! is_a( $product, '\WC_Product' ) ) {
			return $image_thumbnail_html;
		}

		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return $image_thumbnail_html;
		}

		$badge_details = Settings::get_settings( 'badge' );
		$badge_url     = Settings::get_badge_url( $badge_details['badge_icon'] );

		if ( 'on' !== $badge_details['badge_status'] ) {
			return $image_thumbnail_html;
		}

		// Check if custom Badge.
		$custom_badge_status = self::get_settings( $product->get_id(), 'custom_badge_status' );
		$custom_badge        = self::get_settings( $product->get_id(), 'custom_badge' );

		if ( 'yes' === $custom_badge_status && ! empty( $custom_badge ) ) {
			$badge_url = Settings::get_badge_url( $custom_badge );
		}

		$image_thumbnail_html .= self::coming_soon_badge( $badge_url, $badge_details, false, $context, $img_classes );

		return $image_thumbnail_html;
	}

	/**
	 * Clear Marked Badged Single Products.
	 *
	 * @return void
	 */
	public function clear_marked_badged_products_single() {
		unset( $GLOBALS[ self::$plugin_info['name'] . '-single-badged-products' ] );
	}

	/**
	 * Clear Marked Badged loop Products.
	 *
	 * @return void
	 */
	public function clear_marked_badged_products_loop() {
		unset( $GLOBALS[ self::$plugin_info['name'] . '-loop-badged-products' ] );
	}

	/**
	 * Already Badged Products.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	protected function already_badged( $product_id, $context = 'loop' ) {
		return ( ! empty( $GLOBALS[ self::$plugin_info['name'] . '-' . $context . '-badged-products' ] ) && in_array( $product_id, $GLOBALS[ self::$plugin_info['name'] . '-' . $context . '-badged-products' ] ) );
	}

	/**
	 * Mark a product as badged.
	 *
	 * @param int $product_id
	 * @return void
	 */
	protected function mark_badged_product( $product_id, $context = 'loop' ) {
		if ( ! isset( $GLOBALS[ self::$plugin_info['name'] . '-' . $context . '-badged-produts' ] ) ) {
			$GLOBALS[ self::$plugin_info['name'] . '-' . $context . '-badged-products' ] = array();
		}

		$GLOBALS[ self::$plugin_info['name'] . '-' . $context . '-badged-products' ][] = $product_id;
	}

	/**
	 * Loop Coming Soon Badge
	 *
	 * @return void
	 */
	public function loop_coming_soon_badge() {
		global $product;

		if ( ! is_a( $product, '\WC_Product' ) ) {
			return;
		}

		if ( ! $this->is_badge_enabled() ) {
			return;
		}

		if ( self::is_bypass_badge() ) {
			return;
		}

		if ( $this->already_badged( $product->get_id(), 'loop' ) ) {
			return;
		}

		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return;
		}

		$coming_soon_badge = self::coming_soon_badge_wrapper_start( $product, 'loop' ) . self::add_coming_soon_badge( '', $product, 'loop' ) . self::coming_soon_badge_wrapper_end( $product, 'loop' );
		$this->mark_badged_product( $product->get_id(), 'loop' );

		echo $this->gpls_kses_post( $coming_soon_badge );
	}

	/**
	 * Loop / Single Product Coming Soon Badge hooking to Main Product Image.
	 *
	 * @param string       $img_html
	 * @param int          $attachment_id
	 * @param array|string $size
	 * @param bool         $icon
	 * @param array        $attr
	 * @return string
	 */
	public function loop_and_single_add_coming_soon_badge( $img_html, $attachment_id, $size, $icon, $attr ) {
		if ( is_admin() ) {
			return $img_html;
		}

		if ( self::is_bypass_badge() ) {
			return $img_html;
		}

		global $product;

		if ( ! is_a( $product, '\WC_Product' ) ) {
			return $img_html;
		}

		$is_single_thumbnail = $this->is_single_page_main_image( $product, $attachment_id );

		// Place the icon once, no duplicates.'
		if ( $this->already_badged( $product->get_id(), $is_single_thumbnail ? 'single' : 'loop' ) ) {
			return $img_html;
		}

		if ( ! $this->in_shop_loop_item() && ! $is_single_thumbnail ) {
			return $img_html;
		}

		if ( $this->is_widget() || $this->is_sidebar() ) {
			return $img_html;
		}

		if ( $this->is_cart_table() || $this->is_checkout_table() || $this->is_email() || is_account_page() ) {
			return $img_html;
		}

		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return $img_html;
		}

		if ( ! $this->is_badge_enabled() ) {
			return $img_html;
		}

		$img_html = $this->place_badge( $product, $is_single_thumbnail ? 'single' : 'loop', $img_html );

		$this->mark_badged_product( $product->get_id(), $is_single_thumbnail ? 'single' : 'loop' );

		return $img_html;
	}

	/**
	 * Place Badge.
	 *
	 * @param \WC_Product $product
	 * @param string      $context
	 * @param string      $img_html
	 * @return string
	 */
	protected function place_badge( $product, $context, $img_html ) {
		$pattern = '/<img[^>]+>/i';
		preg_match_all( $pattern, $img_html, $matches );
		$found_img_tags = $matches[0];
		foreach ( $found_img_tags as $img_tag ) {
			$img_classes = $this->get_class_attribute( $img_tag );
			$new_img_tag = self::add_coming_soon_badge( '', $product, $context, ! empty( $img_classes ) ? explode( ' ', $img_classes ) : array() ) . $img_tag;
			$img_html    = str_replace( $img_tag, $new_img_tag, $img_html );
		}
		return $img_html;
	}

	/**
	 * Get Class Attribute.
	 *
	 * @param string $img_tag
	 * @return string
	 */
	protected function get_class_attribute( $img_tag ) {
		preg_match( '/class=["\'](.*?)["\']/i', $img_tag, $class_matches );
		$img_classes = isset( $class_matches[1] ) ? $class_matches[1] : '';
		return $img_classes;
	}

	/**
	 * Single Add Coming Soon Badge.
	 *
	 * @param string $img_html
	 * @param int    $attachment_id
	 * @return void
	 */
	public function single_add_coming_soon_badge() {
		if ( is_admin() ) {
			return;
		}

		global $product;

		if ( ! is_a( $product, '\WC_Product' ) ) {
			return;
		}

		if ( ! $this->is_badge_enabled() ) {
			return;
		}

		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return;
		}

		// Place the icon once, no duplicates.'
		if ( $this->already_badged( $product->get_id(), 'single' ) ) {
			return;
		}

		$coming_soon_badge = self::coming_soon_badge_wrapper_start( $product, 'single' ) . self::add_coming_soon_badge( '', $product, 'single' ) . self::coming_soon_badge_wrapper_end( $product, 'single' );

		$this->mark_badged_product( $product->get_id(), 'single' );
		echo wp_kses_post( $coming_soon_badge );
	}

	/**
	 * Coming Soon Badge Wrapper Start.
	 *
	 * @return string
	 */
	public static function coming_soon_badge_wrapper_start( $product = null, $context = 'loop' ) {
		if ( is_null( $product ) ) {
			global $product;
		}
		if ( ! is_a( $product, '\WC_Product' ) ) {
			return '';
		}
		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return '';
		}

		if ( self::pypass_badge_wrapper( $context ) ) {
			return '';
		}

		$badge_settings = Settings::get_settings( 'badge' );
		$badge_side     = $badge_settings['badge_side'];
		ob_start();
		?>
		<div <?php self::base_attrs( $context ); ?> style="display:none;" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-badge-img-wrapper small-badge coming-soon-' . $context . '-badge-wrapper ' . $badge_side ); ?>">
		<?php
		return ob_get_clean();
	}

	/**
	 * Coming Soon Badge Wrapper Start.
	 *
	 * @return string
	 */
	public static function coming_soon_badge_wrapper_end( $product = null, $context = 'loop' ) {
		if ( is_null( $product ) ) {
			global $product;
		}
		if ( ! is_a( $product, '\WC_Product' ) ) {
			return '';
		}
		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return '';
		}
		if ( self::pypass_badge_wrapper( $context ) ) {
			return '';
		}
		ob_start();
		?>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Is badge enabled.
	 *
	 * @return boolean
	 */
	private function is_badge_enabled() {
		$badge_details = Settings::get_settings( 'badge' );
		return ( 'on' === $badge_details['badge_status'] );
	}

	/**
	 * Add Coming Soon Badge for Loop WC Blocks.
	 *
	 * @param string $product_grid_item_html
	 * @param object $data
	 * @param object $product
	 * @return string
	 */
	public static function add_coming_soon_badge_for_blocks( $product ) {
		$badge_details = Settings::get_settings( 'badge' );
		$badge_url     = Settings::get_badge_url( $badge_details['badge_icon'] );

		if ( 'on' !== $badge_details['badge_status'] ) {
			return '';
		}

		$custom_badge_status = self::get_settings( $product->get_id(), 'custom_badge_status' );
		$custom_badge        = self::get_settings( $product->get_id(), 'custom_badge' );

		if ( 'yes' === $custom_badge_status && ! empty( $custom_badge ) ) {
			$badge_url = Settings::get_badge_url( $custom_badge );
		}

		return self::coming_soon_badge_wrapper_start( $product ) . self::coming_soon_badge( $badge_url, $badge_details ) . self::coming_soon_badge_wrapper_end( $product );
	}

	/**
	 * Check if the image is the main image thumbnail in single page.
	 *
	 * @param \WC_Product $product
	 * @param int         $attachment_id
	 *
	 * @return boolean
	 */
	private function is_single_page_main_image( $product, $attachment_id ) {
		if ( did_action( 'woocommerce_before_single_product' ) && ! did_action( 'woocommerce_after_single_product_summary' ) ) {
			$product_thumbnail_id = $product->get_image_id();
			return ( (int) $attachment_id === (int) $product_thumbnail_id );
		}
		return false;
	}

	/**
	 * Check if inside Cart table.
	 *
	 * @return boolean
	 */
	private function is_cart_table() {
		return did_action( 'woocommerce_before_cart_table' ) && ! did_action( 'woocommerce_after_cart_table' );
	}

	/**
	 * Check if inside Checkout Table.
	 *
	 * @return boolean
	 */
	private function is_checkout_table() {
		return did_action( 'woocommerce_review_order_before_cart_contents' ) && ! did_action( 'woocommerce_review_order_after_cart_contents' );
	}

	/**
	 * Is in email.
	 *
	 * @return boolean
	 */
	private function is_email() {
		return did_action( 'woocommerce_mail_content' ) && ! did_action( 'woocommerce_email_sent' );
	}

	/**
	 * Check if a product in widget.
	 *
	 * @return boolean
	 */
	private function is_widget() {
		return ( 'widget_block_content' === current_action() ) || ( did_action( 'woocommerce_widget_product_item_start' ) > did_action( 'woocommerce_widget_product_item_end' ) );
	}

	/**
	 * Check if in sidebar.
	 *
	 * @return boolean
	 */
	private function is_sidebar() {
		return ( did_action( 'dynamic_sidebar_before' ) > did_action( 'dynamic_sidebar_after' ) );
	}

	/**
	 * Check if bypass badge.
	 *
	 * @return boolean
	 */
	private static function is_bypass_badge() {
		return ! empty( $GLOBALS[ self::$plugin_info['name'] . '-bypass-coming-soon-badge' ] );
	}

	/**
	 * Inside SHop Loop Item.
	 *
	 * @return boolean
	 */
	private function in_shop_loop_item() {
		return did_filter( 'woocommerce_product_loop_start' ) > did_filter( 'woocommerce_product_loop_end' );
	}

	/**
	 * Check if pypass badge wrapper.
	 *
	 * @return boolean
	 */
	private static function pypass_badge_wrapper( $context = 'loop' ) {
		if ( ! empty( $GLOBALS[ self::$plugin_info['name'] . '-pypass-badge-wrapper' ] ) ) {
			return true;
		}

		// $pypass_themes_single = array( 'blocksy' );
		$pypass_themes_single = array();
		// $pypass_themes_loop   = array( 'avada' );
		$pypass_themes_loop = array();
		$stylesheet         = get_stylesheet();

		if ( 'loop' === $context && in_array( $stylesheet, $pypass_themes_loop ) ) {
			return true;
		}

		if ( 'single' === $context && in_array( $stylesheet, $pypass_themes_single ) ) {
			return true;
		}

		return false;
	}
}

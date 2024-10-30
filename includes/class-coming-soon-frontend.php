<?php
/**
 * Handles the Coming Soon State of WooCommerce Products Frontend Side.
 *
 * @category class
 * @package  ComingSoon
 */

namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

use DateTime;
use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;

/**
 * Coming Soon Class
 */
class ComingSoonFrontend extends ComingSoon {

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
	 * @param array $plugin_info
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
	 * Hooks Function.
	 *
	 * @return void
	 */
	private function hooks() {

		// Assets.
		add_action( 'wp_footer', array( get_called_class(), 'front_assets' ), 18 );
		add_action( 'wp_footer', array( $this, 'late_assets' ), 1 );

		// 1) Show "Coming Soon Section" in single product page.
		add_action( 'woocommerce_before_single_product', array( $this, 'setup_single_product_coming_soon_section' ), 0 );

		// Filter Price.
		add_filter( 'woocommerce_get_price_html', array( $this, 'filter_product_price' ), PHP_INT_MAX, 2 );

		// Disable Add to cart.
		add_filter( 'woocommerce_is_purchasable', array( $this, 'make_coming_soon_product_unpurchasable' ), PHP_INT_MAX, 2 );

		// Disable Add to cart for coming soon external products.
		add_filter( 'woocommerce_product_add_to_cart_url', array( $this, 'redirect_coming_soon_external_to_product_page' ), 1000, 2 );
		add_filter( 'woocommerce_product_add_to_cart_text', array( $this, 'change_coming_soon_external_cart_btn_text' ), 1000, 2 );

		// Remove Add to cart buttons.
		add_action( 'woocommerce_before_single_product', array( $this, 'handle_add_to_cart_button' ), 1 );

		add_action( 'woocommerce_after_shop_loop_item', array( $this, 'loop_coming_soon_text' ), 8 );
		add_action( 'woocommerce_after_shop_loop_item', array( get_called_class(), 'loop_countdown_section' ), 9 );

		// Availability Text.
		add_filter( 'woocommerce_get_availability', array( $this, 'is_hide_availability_text' ), PHP_INT_MAX, 2 );

		// Handle variable variations purchasable.
		add_filter( 'woocommerce_variation_is_purchasable', array( $this, 'filter_variation_purchasable' ), PHP_INT_MAX, 2 );

		add_action( 'init', array( $this, 'coming_soon_shortcodes' ) );
	}

	/**
	 * Coming Soon Shortcode Registration.
	 *
	 * @return void
	 */
	public function coming_soon_shortcodes() {
		add_shortcode( str_replace( '-', '_', self::$plugin_info['classes_prefix'] . '-coming-soon-section' ), array( $this, 'coming_soon_product_section_shortcode' ) );
	}

	/**
	 * Coming Soon Product Section Shortcode.
	 *
	 * @param array $attrs
	 * @return string
	 */
	public function coming_soon_product_section_shortcode( $attrs ) {
		ob_start();
		$this->get_coming_soon_section();
		return ob_get_clean();
	}

	/**
	 * Is Assets needed.
	 *
	 * @return boolean
	 */
	public static function assets_needed() {
		return (
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-text' ] )
			||
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-countdown' ] )
			||
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-subscription-form' ] )
			||
			! empty( $GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-badge' ] )
		);
	}

	/**
	 * Setup Single Product Coming Soon Section.
	 *
	 * @return void
	 */
	public function setup_single_product_coming_soon_section() {
		if ( ! is_product() ) {
			return;
		}
		global $post;
		if ( ! is_a( $post, '\WP_Post' ) ) {
			return;
		}

		if ( ! self::is_product_coming_soon( $post->ID ) ) {
			return;
		}

		$general_settings = Settings::get_settings( 'general' );
		if ( 'disable' === $general_settings['single_placement_position'] ) {
			return;
		}
		add_action( $general_settings['single_placement_position'], array( $this, 'get_coming_soon_section' ), $general_settings['single_placement_position_priority'] );
	}

	/**
	 * Filter Variation is purchasable.
	 *
	 * @param boolean $is_purchasable
	 * @param \WC_Product_Variation $variation
	 * @return boolean
	 */
	public function filter_variation_purchasable( $is_purchasable, $variation ) {
		if ( ! $is_purchasable ) {
			return $is_purchasable;
		}

		if ( self::is_product_unpurchasable( $variation->get_parent_id() ) ) {
			$is_purchasable = false;
		}

		return $is_purchasable;
	}

	/**
	 * Late Assets.
	 */
	public function late_assets() {
		if ( ! self::assets_needed() ) {
			return;
		}
		wp_enqueue_style( self::$plugin_info['name'] . '-flipdown-responsive-style', self::$plugin_info['url'] . 'assets/dist/css/flipdown.min.css', array(), self::$plugin_info['version'], 'all' );
		ob_start();
		?>
		.gpls-wcsamm-flipper{max-width:100%;}.gpls-wcsamm-flipper figure{position:absolute !important;}.gpls-wcsamm-coming-soon-badge-img-wrapper img{margin:0px !important}.product_list_widget li{position:relative}.gpls-wcsamm-coming-soon-subscribe-form .gpls-wcsamm-coming-soon-subscribe-email-field{min-width:250px}.gpls-wcsamm-coming-soon-subscribe-form .gpls-wcsamm-coming-soon-subscribe-email-field:invalid{background-color:#ff8c8c}.gpls-wcsamm-subscribe-submit-btn{padding:10px;cursor:pointer}.gpls-wcsamm-post-submit-text{display:none}.rotor-group .rotors-wrapper{width:100%;display:flex;justify-content:center}.rotor-group .rotors-wrapper .rotor{overflow:hidden;width:40%}.rotor-group .rotors-wrapper .rotor .rotor-leaf{width:100%}.rotor-group .rotors-wrapper .rotor .rotor-painter{width:100%}.rotor-group .rotors-wrapper .rotor:after{width:100%}
		<?php
		do_action( self::$plugin_info['name'] . '-frontend-inline-styles' );
		Settings::get_countdown_styles( true );
		$inline_styles = ob_get_clean();
		?>
		<style type="text/css"><?php echo esc_attr( $inline_styles ); ?></style>
		<?php
	}

	/**
	 * Frontend Assets.
	 *
	 * @return void
	 */
	public static function front_assets() {
		if ( ! self::assets_needed() ) {
			return;
		}
		wp_enqueue_script( self::$plugin_info['name'] . '-dist-single-product-actions', self::$plugin_info['url'] . 'assets/dist/js/front-single-product-actions.min.js', array( 'jquery' ), self::$plugin_info['version'], true );
		$localize_data = array(
			'ajaxUrl'                   => admin_url( 'admin-ajax.php' ),
			'nonce'                     => wp_create_nonce( self::$plugin_info['name'] . '-nonce' ),
			'prefix'                    => self::$plugin_info['name'],
			'prefix_under'              => self::$plugin_info['prefix_under'],
			'subSubmitAction'           => self::$plugin_info['name'] . '-subscription-submit-action',
			'classes_prefix'            => self::$plugin_info['classes_prefix'],
			'related_plugins'           => self::$plugin_info['related_plugins'],
			'labels'                    => array(
				'flipDownHeading' => array(
					'days'    => esc_html__( 'Days', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					'hours'   => esc_html__( 'Hours', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					'minutes' => esc_html__( 'Minutes', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					'seconds' => esc_html__( 'Seconds', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
				),
			),
			'is_single'                 => (int) is_product(),
		);
		$localize_data = apply_filters( self::$plugin_info['classes_prefix'] . '-front-localize-data', $localize_data );

		wp_localize_script(
			self::$plugin_info['name'] . '-dist-single-product-actions',
			self::$plugin_info['localize_var'],
			$localize_data
		);
	}

	/**
	 * Get Coming-Soon Section of a product.
	 *
	 * @return void
	 */
	public function get_coming_soon_section() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$product_id = $product->get_id();
		$settings   = self::get_settings( $product_id );
		if ( ! self::is_product_coming_soon( $product_id ) ) {
			return;
		}
		// 1) Coming Soon Text.
		self::coming_soon_text( $product_id, $settings );

		// 2) Arrival Time Countdown.
		self::countdown_section( $product_id, $settings );
	}

	/**
	 * Coming Soon Text.
	 *
	 * @param int   $product_id
	 * @param array $settings
	 * @return void
	 */
	public static function coming_soon_text( $product_id, $settings = array() ) {
		if ( empty( $settings ) ) {
			$coming_soon_text = self::get_settings( $product_id, 'coming_soon_text' );
		} else {
			$coming_soon_text = $settings['coming_soon_text'];
		}
		$content = apply_filters( 'the_content', $coming_soon_text );
		$content = str_replace( ']]>', ']]&gt;', $content );
		// phpcs:ignore WordPress.Security.EscapeOutput
		?>
		<div class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-text' ); ?>" >
		<?php
		echo wp_kses_post( $content );
		?>
		</div>
		<?php
		$GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-text' ] = true;
	}

	/**
	 * CountDown Section.
	 *
	 * @param int $product_id Product ID.
	 *
	 * @return void
	 */
	public static function countdown_section( $product_id, $settings = array() ) {
		if ( empty( $settings ) ) {
			$settings = self::get_settings( $product_id );
		}
		if ( self::is_show_arrival_time_countdown( $product_id ) && ! self::is_product_arrival_time_passed( $product_id ) ) :
			$current_time = ( current_datetime()->getTimestamp() );
			$arrival_time = DateTime::createFromFormat( 'Y-m-d\TH:i', $settings['arrival_time'], wp_timezone() );
			if ( empty( $arrival_time ) ) {
				return;
			}
			?>
			<div id="flipdown"
				class="flipdown flipdown-size-sm flipper flipper-dark <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-flipper ' . self::$plugin_info['classes_prefix'] . '-flipper-' . $product_id ); ?>"
				data-datetime="<?php echo esc_attr( $arrival_time->getTimestamp() ); ?>"
				data-template="ddd|HH|ii|ss"
				data-labels="Days|Hours|Minutes|Seconds"
				data-reverse="true"
				data-auto_enable="false"
				data-hides=""
				data-now="<?php echo esc_attr( $current_time ); ?>"
			>
			</div>
			<?php
			$GLOBALS[ self::$plugin_info['prefix'] . '-coming-soon-countdown' ] = true;
		endif;
	}

	/**
	 * Coming Soon Text in Loop
	 *
	 * @return void
	 */
	public function loop_coming_soon_text() {
		global $product;
		if ( ! $product ) {
			return;
		}
		$loop_settings = Settings::get_settings( 'countdown', 'loop' );
		if ( ! $loop_settings['text_status'] || ( 'off' === $loop_settings['text_status'] ) ) {
			return;
		}
		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return;
		}
		self::coming_soon_text( $product->get_id() );
	}

	/**
	 * Countdown Section in loop.
	 *
	 * @return void
	 */
	public static function loop_countdown_section( $_product = null ) {
		if ( empty( $_product ) || is_null( $_product ) ) {
			global $product;
		} else {
			$product = $_product;
		}
		if ( ! $product ) {
			return;
		}
		$loop_settings = Settings::get_settings( 'countdown', 'loop' );
		if ( ! $loop_settings['status'] || ( 'off' === $loop_settings['status'] ) ) {
			return;
		}
		if ( ! self::is_product_coming_soon( $product->get_id() ) ) {
			return;
		}
		self::countdown_section( $product->get_id() );
	}

	/**
	 * Filter Product Price.
	 *
	 * @param string $price_html
	 * @param \WC_Product $_product
	 * @return string
	 */
	public function filter_product_price( $price_html, $_product ) {
		$settings   = self::get_settings( $_product->get_id() );
		if ( self::is_product_coming_soon( $_product->get_id() ) && ( 'yes' === $settings['hide_price'] ) ) {
			return '';
		}
		return $price_html;
	}

	/**
	 * Direct External Product Link to Product single page.
	 *
	 * @param string $product_url
	 * @param object $product_obj
	 * @return string
	 */
	public function redirect_coming_soon_external_to_product_page( $product_url, $_product ) {
		if ( is_a( $_product, \WC_Product_External::class ) && self::is_product_coming_soon( $_product->get_id() ) && ! self::is_keep_product_purchasable( $_product->get_id() ) ) {
			return $_product->get_permalink();
		}
		return $product_url;
	}

	/**
	 * Change Coming Soon External Add To Cart Button Text.
	 *
	 * @param string $add_to_cart_text
	 * @param object $_product
	 * @return string
	 */
	public function change_coming_soon_external_cart_btn_text( $add_to_cart_text, $_product ) {
		if ( is_a( $_product, \WC_Product_External::class ) && self::is_product_coming_soon( $_product->get_id() ) && ! self::is_keep_product_purchasable( $_product->get_id() ) ) {
			return esc_html__( 'Read more', 'woocommerce' );
		}
		return $add_to_cart_text;
	}


	/**
	 * Hide - Show Availability Text.
	 *
	 * @param array  $avialability_data Availability Text and Class Array.
	 * @param object $product_obj The product Object.
	 *
	 * @return array
	 */
	public function is_hide_availability_text( $avialability_data, $product_obj ) {
		if ( ! $product_obj ) {
			return $avialability_data;
		}
		if ( self::is_product_unpurchasable( $product_obj->get_id() ) ) {
			return array(
				'availability' => false,
				'class'        => '',
			);
		}
		return $avialability_data;
	}

	/**
	 * Disable add to cart function of coming soon product by making it unpurchasable.
	 *
	 * @param boolean $is_purchasable
	 * @param object  $product_obj
	 * @return boolean
	 */
	public function make_coming_soon_product_unpurchasable( $is_purchasable, $product_obj ) {
		if ( is_null( $product_obj ) || empty( $product_obj ) || is_wp_error( $product_obj ) ) {
			return $is_purchasable;
		}
		if ( self::is_product_unpurchasable( $product_obj->get_id() ) ) {
			return false;
		}
		return $is_purchasable;
	}

	/**
	 * Handle the Add to cart button for coming soon products.
	 *
	 * @return void
	 */
	public function handle_add_to_cart_button() {
		$products_types = wc_get_product_types();
		foreach ( $products_types as $type_name => $type_label ) {
			add_action( 'woocommerce_' . $type_name . '_add_to_cart', array( $this, 'remove_add_to_cart_button_for_coming_soon' ), 1 );
		}
	}

	/**
	 * Remove Add to cart hook for coming soon products.
	 *
	 * @return void
	 */
	public function remove_add_to_cart_button_for_coming_soon() {
		global $product;
		if ( ! $product || is_wp_error( $product ) ) {
			return;
		}
		$product_id = $product->get_id();
		if ( self::is_product_unpurchasable( $product_id ) ) {
			$priority = has_action( 'woocommerce_' . $product->get_type() . '_add_to_cart', 'woocommerce_' . $product->get_type() . '_add_to_cart' );
			if ( is_numeric( $priority ) ) {
				remove_action( 'woocommerce_' . $product->get_type() . '_add_to_cart', 'woocommerce_' . $product->get_type() . '_add_to_cart', $priority );
			}
		}
	}
}

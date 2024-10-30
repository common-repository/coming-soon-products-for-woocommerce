<?php
/**
 * Handles the Coming Soon State of WooCommerce Products Backend Side.
 *
 * @category class
 * @package  ComingSoon
 */

namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;
use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;

/**
 * Coming Soon Backend Class
 */
class ComingSoonBackend extends ComingSoon {

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
		// admin bar menu.
		add_action( 'admin_bar_menu', array( $this, 'coming_soon_products_list_admin_tab' ), 1000 );

		// Assets.
		add_action( 'admin_enqueue_scripts', array( $this, 'admin_assets' ) );

		// Coming Soon Settings in Products edit page.
		add_filter( 'woocommerce_product_data_tabs', array( $this, 'coming_soon_tab_in_single_product' ), 100, 1 );
		add_action( 'woocommerce_product_data_panels', array( $this, 'coming_soon_tab_in_single_product_settings' ) );

		// Save Settings.
		add_action( 'woocommerce_admin_process_product_object', array( $this, 'save_settings' ), 100, 1 );

		// WPML Custom Fields Translation Labels.
		add_filter( 'wcml_product_content_label', array( $this, 'adjust_custom_fields_labels_in_translation' ), 1000, 2 );

		// Coming Soon Settings in Products edit page.
		add_action( 'woocommerce_product_after_variable_attributes', array( $this, 'coming_soon_for_variation' ), 1000, 3 );

		// Coming Soon Indication in Products listing in products page.
		add_filter( 'woocommerce_admin_stock_html', array( $this, 'coming_soon_indication_in_products_list' ), 1000, 2 );
	}

	/**
	 * Coming Soon Indicator at products
	 *
	 * @param string      $stock_html
	 * @param \WC_Product $_product
	 * @return string
	 */
	public function coming_soon_indication_in_products_list( $stock_html, $_product ) {
		if ( self::is_product_coming_soon( $_product->get_id() ) ) {
			ob_start();
			?>
			<div style="clear:both;margin:5px 0px;padding:5px;border-radius:10px;background-color:#ffd767;text-align:center;color:#fff;font-weight:bolder;">
				<span><?php esc_html_e( 'Coming Soon', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
			</div>
			<?php
			$stock_html .= ob_get_clean();
		}
		return $stock_html;
	}

	/**
	 * Variation Coming Soon.
	 *
	 * @param boolean $loop
	 * @param array $variation_data
	 * @param \WC_Product $variation
	 * @return void
	 */
	public function coming_soon_for_variation( $loop, $variation_data, $variation ) {
		?>
		<div style="background: #743774;" class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field ' . self::$plugin_info['classes_prefix'] . '-variation-coming-soon-box wc-metabox woocommerce_attribute postbox closed' ); ?>">
			<h3>
				<div class="handlediv"></div>
				<div style="color:#FFF;" class="attribute_name"><?php echo esc_html( 'Variation Coming Soon', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode', '', true ); ?></div>
			</h3>
		</div>
		<?php
	}

	/**
	 * Adjust the Label of Custom Fields in Translation Editor.
	 *
	 * @param string $field_name
	 * @param int    $product_id
	 * @return string
	 */
	public function adjust_custom_fields_labels_in_translation( $field_name, $product_id ) {
		if ( ! empty( self::$custom_fields_labels_for_wpml[ $field_name ] ) ) {
			$field_label = self::$custom_fields_labels_for_wpml[ $field_name ];
			$_product    = wc_get_product( $product_id );
			if ( is_a( $_product, \WC_Product_Variation::class ) ) {
				$field_label . ' #' . $product_id;
			}
			return $field_label;
		}
		return $field_name;
	}

	/**
	 * Admin Assets.
	 *
	 * @return void
	 */
	public function admin_assets() {
		$wp_screen_object = get_current_screen();
		if ( is_object( $wp_screen_object ) && ! empty( $wp_screen_object->id ) && 'product' === $wp_screen_object->id ) {
			wp_enqueue_style( self::$plugin_info['name'] . '-edit-product-styles', self::$plugin_info['url'] . 'assets/dist/css/settings-styles.min.css', self::$plugin_info['version'], 'all' );
			if ( ! wp_script_is( 'jquery' ) ) {
				wp_enqueue_script( 'jquery' );
			}
			if ( ! wp_script_is( 'wp-i18n' ) ) {
				wp_enqueue_script( 'wp-i18n' );
			}
			wp_enqueue_media();
			wp_enqueue_editor();
			wp_enqueue_code_editor(
				array(
					'type' => 'text/html',
				)
			);
			wp_enqueue_script( self::$plugin_info['name'] . '-single-product-actions', self::$plugin_info['url'] . 'assets/dist/js/single-product-actions.min.js', array( 'jquery', 'wp-i18n' ), self::$plugin_info['version'], true );
			wp_localize_script(
				self::$plugin_info['name'] . '-single-product-actions',
				self::$plugin_info['localize_var'],
				array(
					'ajaxUrl'        => admin_url( 'admin-ajax.php' ),
					'nonce'          => wp_create_nonce( self::$plugin_info['name'] . '-nonce' ),
					'prefix'         => self::$plugin_info['name'],
					'classes_prefix' => self::$plugin_info['classes_prefix'],
				)
			);
		}
	}

	/**
	 * Coming Soon Tab in Edit page of single product.
	 *
	 * @param array $tabs
	 * @return array
	 */
	public function coming_soon_tab_in_single_product( $tabs ) {
		$tabs[ self::$plugin_info['name'] . '-coming-soon-settings-tab' ] = array(
			'label'    => esc_html__( 'Coming Soon [GrandPlugins]', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
			'target'   => self::$plugin_info['name'] . '-coming-soon-settings-tab',
			'class'    => array(),
			'priority' => 60,
			'icon'     => 'dashicons-clock',
		);

		return $tabs;
	}

	/**
	 * Coming Soon Settings in Product Edit Page.
	 *
	 * @return void
	 */
	public function coming_soon_tab_in_single_product_settings() {
		global $post, $thepostid, $product_object;
		if ( ! $thepostid || ! $product_object || is_wp_error( $product_object ) ) {
			return;
		}
		$coming_soon_settings = self::get_settings( $thepostid );
		?>

		<div id="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-settings-tab' ); ?>" class="panel woocommerce_options_panel" >
			<div class="options_group">
			<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['name'] . '-coming-soon-status',
						'value'       => $coming_soon_settings['status'],
						'label'       => esc_html__( 'Coming Soon Mode', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Enable coming soon mode', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_textarea_input(
					array(
						'id'          => self::$plugin_info['name'] . '-coming-soon-text',
						'value'       => $coming_soon_settings['coming_soon_text'],
						'label'       => esc_html__( 'Coming Soon Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'class'       => self::$plugin_info['name'] . '-texteditor',
						'description' => esc_html__( 'It will be shown in single product page after product short description', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . '<br/><br/>' .
						'<span class="' . esc_attr( self::$plugin_info['classes_prefix'] . '-pro-shortcode-field' ) . '" >' . esc_html__( 'Shortcode: ', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . ' [' . self::$plugin_info['classes_prefix'] . '-coming-soon-text]'
						. self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode', '', true ) . '</span>',
					)
				);

				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['name'] . '-keep-purchasable',
						'value'       => $coming_soon_settings['keep_purchasable'],
						'label'       => esc_html__( 'Keep it Purchasable ?', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . '<span>' . self::$core->new_keyword( 'New', true ) . '</span>',
						'description' => esc_html__( 'Keep the product purchasable in coming soon mode.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);

				woocommerce_wp_text_input(
					array(
						'id'          => self::$plugin_info['name'] . '-arrival-time',
						'type'        => 'datetime-local',
						'value'       => $coming_soon_settings['arrival_time'],
						'label'       => esc_html__( 'Arrival Time', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Remaining time until arrival is calculated based on the site\'s timezone', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'desc_tip'    => true,
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['name'] . '-show-countdown',
						'value'       => $coming_soon_settings['show_countdown'],
						'label'       => esc_html__( 'Show Countdown', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Show the arrival time countdown', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . ' <br/><br/>' .
						'<span class="' . esc_attr( self::$plugin_info['classes_prefix'] . '-pro-shortcode-field' ) . '" >' . esc_html__( 'Countdown Shortcode ', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . ':   [' . self::$plugin_info['classes_prefix'] . '-coming-soon-countdown]'
						. self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode', '', true ) . '</span>',
					)
				);
				?>
				<p class="<?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-shortcode-field' ); ?> form-field <?php echo esc_attr( self::$plugin_info['name'] . '-hide-countdown-divisions' ); ?>">
					<label><?php esc_html_e( 'Countdown Divisions', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></label>
					<span style="display:block;">
						<span><?php esc_html_e( 'Hide Seconds', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<input disabled style="margin-right:7px !important;" class="checkbox" type="checkbox" id="<?php echo esc_attr( self::$plugin_info['name'] . '-hide-countdown-seconds' ); ?>" />
					</span>
					<span style="display:block;">
						<span><?php esc_html_e( 'Hide Minutes', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<input disabled style="margin-right:7px !important;" class="checkbox" type="checkbox" id="<?php echo esc_attr( self::$plugin_info['name'] . '-hide-countdown-minutes' ); ?>" />
					</span>
					<span style="display:block;">
						<span><?php esc_html_e( 'Hide Hours', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<input disabled style="margin-right:7px !important;" class="checkbox" type="checkbox" id="<?php echo esc_attr( self::$plugin_info['name'] . '-hide-countdown-hours' ); ?>" />
					</span>
					<span class="description"><?php esc_html_e( 'Choose to hide a time division from the arrival time countdown', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
				</p>
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['name'] . '-hide-price',
						'value'       => $coming_soon_settings['hide_price'],
						'label'       => esc_html__( 'Hide Price', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Hide the product price', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['name'] . '-disable-backorders',
						'value'       => $coming_soon_settings['disable_backorders'],
						'label'       => esc_html__( 'Disable Backorders', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Disable purchasing the product in backorder.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				// Pro.
				woocommerce_wp_checkbox(
					array(
						'id'            => self::$plugin_info['classes_prefix'] . '-pro-field-1',
						'wrapper_class' => self::$plugin_info['classes_prefix'] . '-pro-field',
						'class'         => 'disabled',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'label'         => esc_html__( 'Auto Enable', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description'   => esc_html__( 'Auto enable the product for purchase when the arrival time is over [ requires "Arrival Time" ]', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product', '', true ),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-2',
						'wrapper_class' => self::$plugin_info['classes_prefix'] . '-pro-field',
						'class'         => 'disabled',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'label'         => esc_html__( 'Auto Email', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description'   => esc_html__( 'Send email automatically when the product arrival time is over [ requires "Arrival Time" and "Auto Enable" ]', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product', '', true ),
					)
				);
			?>
				<!-- Custom Badge -->
				<p class="form-field gpls-wcsamm-coming-soon-for-woocommerce-custom-badge_field <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>">
					<label for="gpls-wcsamm-coming-soon-for-woocommerce-custom-badge"><?php esc_html_e( 'Custom Badge', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></label>
					<span class="badge-icons-toggler">
						<input type="checkbox" class="disabled" disabled="disabled">
						<button class="badge-icons-toggle button-primary disabled" disabled="disabled"><?php esc_html_e( 'Available Icons', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></button>
						<span class="description"><?php esc_html_e( 'Custom coming soon badge', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span> |
						<span class="description"><?php printf( esc_html( 'Coming Soon Badge Shortcode: [%s]' ), self::$plugin_info['classes_prefix'] . '-coming-soon-badge' ); ?></span>
						<span class="description"><?php self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product' ); ?></span>
					</span>
				</p>

			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>">
				<h3><?php echo ( esc_html( 'Pro Features' ) . self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product', '', true ) ); ?></h3>
				<?php
				woocommerce_wp_checkbox(
					array(
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-3',
						'class'       => 'disabled',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'label'       => esc_html__( 'Show Subscription', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Display the subscription form ', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . ' <br/> ' . esc_html__( 'Subscription form Shortcode ', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . ':   [' . self::$plugin_info['classes_prefix'] . '-subscription-form-shortcode]',
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-4',
						'class'         => 'disabled',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'label'       => esc_html__( 'Custom Title', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Custom Title for the Subscription Form', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-5',
						'class'         => 'disabled',
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'label'         => esc_html__( 'Custom Form Shortcode', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description'   => esc_html__( 'Add custom subscription form shortcode. leave it blank for using the default form', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'desc_tip'      => true,
					)
				);
				?>
			</div>
			<div class="options_group <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>">
				<h4 class="heading-title"><?php esc_html_e( 'Coming Soon Ended Email', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h4>
				<?php
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-6',
						'class'         => 'disabled',
						'label'       => esc_html__( 'Email Subject', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Custom email subject for this product, available placeholders: {site_title}, {site_url}, {site_address}, {product_title}, {product_image}, {product_stock}, {product_link_start}, {product_link_end}', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'desc_tip'    => true,

					)
				);
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-7',
						'class'         => 'disabled',
						'label'       => esc_html__( 'Email Heading', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Custom email heading for this product, available placeholders: {site_title}, {site_url}, {site_address}, {product_title}, {product_image}, {product_stock}, {product_link_start}, {product_link_end}', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'desc_tip'    => true,
					)
				);
				woocommerce_wp_textarea_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'id'          => self::$plugin_info['classes_prefix'] . '-pro-field-8',
						'label'       => esc_html__( 'Email Body', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'class'       => self::$plugin_info['name'] . '-texteditor disabled',
						'rows'        => 100,
						'description' => esc_html__( 'Custom email Body for this product, available placeholders: {site_title}, {site_url}, {site_address}, {product_title}, {product_image}, {product_stock}, {product_link_start}, {product_link_end}', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'desc_tip'    => true,
					)
				);
				?>
			</div>
			<!-- Out of Stock Sync -->
			<div class="options_group <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>">
				<h4 class="heading-title"><?php esc_html_e( 'Out of Stock - Coming Soon Sync', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?><span><?php self::$core->new_keyword( 'New', false ); ?></span></h4>
				<?php
				woocommerce_wp_checkbox(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-stock-status',
						'value'       => 'no',
						'label'       => esc_html__( 'Enable', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Enable coming soon mode and stock sync. When the product is out of stock, coming soon mode will be enabled automatically.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-stock-arrival-time-days',
						'value'       => 0,
						'type'        => 'number',
						'label'       => esc_html__( 'Arrival duration days', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-stock-arrival-time-hours',
						'value'       => 0,
						'type'        => 'number',
						'label'       => esc_html__( 'Arrival duration hours', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-stock-arrival-time-minutes',
						'value'       => 0,
						'type'        => 'number',
						'label'       => esc_html__( 'Arrival duration minutes', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				woocommerce_wp_checkbox(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-stock-auto-enable',
						'value'       => 'no',
						'label'       => esc_html__( 'Set auto enable', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'The "Auto Enable" option above will be enabled with coming soon mode.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				?>
			</div>
			<!-- Auto ReStock -->
			<div class="options_group <?php echo esc_attr( self::$plugin_info['classes_prefix'] . '-pro-field' ); ?>">
				<h4 class="heading-title"><?php esc_html_e( 'Auto Restock', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?><span><?php self::$core->new_keyword( 'New', false ); ?></span></h4>
				<?php
				woocommerce_wp_checkbox(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-restock-status',
						'value'       => 'no',
						'label'       => esc_html__( 'Enable', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'Enable filling the product stock automatically after coming soon arrival time passes and the product is auto enabled.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'disabled'    => true,
					)
				);
				woocommerce_wp_text_input(
					array(
						'custom_attributes' => array(
							'disabled' => 'disabled',
						),
						'class'       => 'disabled',
						'id'          => self::$plugin_info['name'] . '-restock-qty',
						'value'       => 0,
						'type'        => 'number',
						'label'       => esc_html__( 'Restock Quantity', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
						'description' => esc_html__( 'The stock will be set with this value automatically when the product auto enabled from coming soon mode.', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
					)
				);
				?>
			</div>
			<?php do_action( self::$plugin_info['name'] . '-coming-soon-product-fields', $thepostid ); ?>
		</div>
		<?php
	}

		/**
		 * Save Coming Soon Product Settings.
		 *
		 * @param object $product Product Object.
		 *
		 * @return void
		 */
	public function save_settings( $product ) {
		// phpcs:disable WordPress.Security.NonceVerification.Missing
		$settings = self::$default_settings;
		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-coming-soon-status' ] ) ) {
			$settings['status'] = 'yes';
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-arrival-time' ] ) ) {
			$settings['arrival_time'] = sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '-arrival-time' ] ) );
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-coming-soon-text' ] ) ) {
			$settings['coming_soon_text'] = wp_kses_post( $_POST[ self::$plugin_info['name'] . '-coming-soon-text' ] );
		} else {
			$settings['coming_soon_text'] = '';
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-hide-price' ] ) ) {
			$settings['hide_price'] = 'yes';
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-keep-purchasable' ] ) ) {
			$settings['keep_purchasable'] = 'yes';
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-disable-backorders' ] ) ) {
			$settings['disable_backorders'] = 'yes';
		}

		if ( ! empty( $_POST[ self::$plugin_info['name'] . '-show-countdown' ] ) ) {
			$settings['show_countdown'] = sanitize_text_field( wp_unslash( $_POST[ self::$plugin_info['name'] . '-show-countdown' ] ) );
		}

		foreach ( $settings as $setting_name => $setting_value ) {
			update_post_meta( $product->get_id(), self::$plugin_info['name'] . '-' . $setting_name, $setting_value );
		}

		if ( 'yes' === $settings['status'] ) {
			self::update_coming_soon_list( $product->get_id() );
		} else {
			self::update_coming_soon_list( $product->get_id(), 'remove' );
		}
	}

		/**
		 * Render Coming Soon Products List.
		 *
		 * @param array  $block_attributes
		 * @param string $content
		 * @param object $block
		 * @return void
		 */
	public function coming_soon_products_list_admin_tab( $wp_admin_bar ) {
		if ( ! current_user_can( 'edit_posts' ) ) {
			return;
		}
		$coming_soon_products = self::get_coming_soon_list();
		$coming_soon_count    = 0;
		if ( ! empty( $coming_soon_products ) ) {
			$wp_admin_bar->add_node(
				array(
					'id'    => self::$plugin_info['classes_prefix'] . '-coming-soon-products-list-admin-bar-menu',
					'title' => esc_html__( 'Coming Soon Products [GrandPlugins]', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . '&nbsp;' . '<span class="awaiting-mod count-' . esc_attr( $coming_soon_count ) . '"><span class="pending-count" aria-hidden="true">' . esc_attr( $coming_soon_count ) . '</span></span>',
				)
			);

			$GLOBALS[ self::$plugin_info['name'] . '-bypass-coming-soon-badge' ] = true;

			foreach ( $coming_soon_products as $product_id ) {
				$_product = wc_get_product( $product_id );
				if ( ! $_product || ! self::is_product_coming_soon( $product_id ) ) {
					self::update_coming_soon_list( $product_id, 'remove' );
					continue;
				}
				// WPML comp: Pypass WPML translations.
				global $sitepress;
				if ( $sitepress ) {
					if ( self::wpml_is_translation( $product_id, 'post_' . get_post_type( $product_id ) ) ) {
						continue;
					}
				}

				$product_title = $_product->get_title();
				$arrival_time  = self::get_settings( $product_id, 'arrival_time' );

				if ( $arrival_time ) {
					$arrival_time = date( 'F jS, Y, g:i A', strtotime( $arrival_time ) );
				}
				$product_edit_link = get_edit_post_link( $product_id );
				$products_list     = array(
					'thumb'        => $_product->get_image( 'thumbnail' ),
					'title'        => $product_title,
					'arrival_time' => $arrival_time,
					'edit_link'    => $product_edit_link,
				);
				ob_start();
				?>
				<div class="coming-soon-item">
					<!-- Thumb -->
					<div class="thumb">
					<?php echo wp_kses_post( $products_list['thumb'] ); ?>
					</div>
					<!-- Product edit link -->
					<div class="edit-link">
						<a target="_blank" href="<?php echo esc_url( $products_list['edit_link'] ); ?>">
						<?php echo esc_html( $products_list['title'] ); ?>
						</a>
					</div>
					<!-- Arrival Time -->
					<?php
					if ( $products_list['arrival_time'] ) :
						?>
					<div class="arrival-time">
						<span><?php echo esc_html( $products_list['arrival_time'] ); ?></span>
					</div>
							<?php
						endif;
					?>
				</div>
					<?php
					$list_item = ob_get_clean();
					$wp_admin_bar->add_node(
						array(
							'id'     => self::$plugin_info['classes_prefix'] . '-coming-soon-products-list-' . $product_id,
							'parent' => self::$plugin_info['classes_prefix'] . '-coming-soon-products-list-admin-bar-menu',
							'title'  => $list_item,
						)
					);
					$coming_soon_count += 1;
			}
			$wp_admin_bar->add_node(
				array(
					'id'    => self::$plugin_info['classes_prefix'] . '-coming-soon-products-list-admin-bar-menu',
					'title' => esc_html__( 'Coming Soon Products', 'gpls-wcsamm-coming-soon-for-woocommerce' ) . '&nbsp;' . '<span class="awaiting-mod count-' . esc_attr( $coming_soon_count ) . '"><span class="pending-count" aria-hidden="true">' . esc_attr( $coming_soon_count ) . '</span></span>',
				)
			);
			$admin_bar_menu_id = '#wp-admin-bar-' . esc_attr( self::$plugin_info['classes_prefix'] . '-coming-soon-products-list-admin-bar-menu' );
			unset( $GLOBALS[ self::$plugin_info['name'] . '-bypass-coming-soon-badge' ] );
			?>
			<style type="text/css" >
			<?php echo esc_attr( $admin_bar_menu_id ); ?> .ab-submenu {
				overflow-y: scroll;
				max-height: 500px;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> img {
				width: 40px;
				height: 40px;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> li {
				line-height: 1;
				margin:10px 5px !important;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .ab-item {
				height: auto !important;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .coming-soon-item {
				display: flex;
				justify-content: start;
				align-items: flex-start;
				flex-direction: row;

			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .coming-soon-item .thumb {
				width: 40px;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .coming-soon-item .arrival-time {
				margin-left: auto;
				background: #0072ff;
				border-radius: 10px;
				color: #FFF;
				padding: 0px 8px;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .awaiting-mod {
				display: inline-block;
				vertical-align: baseline;
				box-sizing: border-box;
				margin: 1px 0 -1px 2px;
				padding: 0 5px;
				min-width: 18px;
				height: 18px;
				border-radius: 9px;
				background-color: #d63638;
				color: #fff;
				font-size: 11px;
				line-height: 1.6;
				text-align: center;
				z-index: 26;
			}
				<?php echo esc_attr( $admin_bar_menu_id ); ?> .pending-count {
				line-height: 1.3 !important;
				font-size: inherit;
			}
			</style>
				<?php
		}
	}

}

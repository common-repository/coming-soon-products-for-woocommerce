<?php
/**
 * Handles the Coming Soon State of WooCommerce Products.
 *
 * @category class
 * @package  ComingSoon
 */

namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

use DateInterval;
use DateTime;
use DateTimeImmutable;
use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;

/**
 * Coming Soon Class
 */
class ComingSoon {

	/**
	 * Core Object.
	 *
	 * @var object
	 */
	public static $core;

	/**
	 * Plugin Info Object.
	 *
	 * @var array
	 */
	public static $plugin_info;

	/**
	 * Coming Soon Default Settings.
	 *
	 * @var array
	 */
	public static $default_settings;

	/**
	 * List of coming soon products List Settings Key.
	 *
	 * @var string
	 */
	public static $coming_soon_hub_key;

	/**
	 * Custom Fields to Labels Mapping for WPML Translation Editor.
	 *
	 * @var array
	 */
	protected static $custom_fields_labels_for_wpml = array();

	 /**
	  * Constructor
	  *
	  * @param object $core Core Object.
	  * @param object $plugin_info Plugin Info.
	  */
	public function __construct( $core, $plugin_info ) {
		self::$core                = $core;
		self::$plugin_info         = $plugin_info;
		self::$coming_soon_hub_key = self::$plugin_info['name'] . '-coming-soon-products-list-settings';

		self::init_settings();
	}

	/**
	 * Initialize the Settings.
	 *
	 * @return void
	 */
	public static function init_settings() {
		self::$default_settings              = array(
			'status'                    => 'no',
			'coming_soon_text'           => Settings::get_settings( 'general', 'coming_soon_text' ),
			'hide_price'                => 'no',
			'keep_purchasable'          => 'no',
			'disable_backorders'        => 'no',
			'arrival_time'              => '',
			'show_countdown'            => 'no',
		);
		self::$default_settings              = apply_filters( self::$plugin_info['name'] . '-coming-soon-product-default-settings', self::$default_settings );
		self::$custom_fields_labels_for_wpml = array(
			self::$plugin_info['name'] . '-coming_soon_text' => esc_html__( 'Coming Soon Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ),
		);
	}

	/**
	 * Get Product Coming Soon Settings.
	 *
	 * @param int $product_id Product ID.
	 * @return array|false|string
	 */
	public static function get_settings( $product_id, $custom_key = null ) {
		$settings = self::$default_settings;
		if ( ! is_null( $custom_key ) && ! in_array( $custom_key, array_keys( $settings ) ) ) {
			return false;
		}
		if ( is_null( $custom_key ) ) {
			$saved_settings = array();
			foreach ( self::$default_settings as $setting_name => $setting_value ) {
				$val = maybe_unserialize( get_metadata_raw( 'post', $product_id, self::$plugin_info['name'] . '-' . $setting_name, true ) );
				if ( ! is_null( $val ) ) {
					$saved_settings[ $setting_name ] = $val;
				}
			}
			return array_replace_recursive( self::$default_settings, $saved_settings );
		} else {
			return maybe_unserialize( get_post_meta( $product_id, self::$plugin_info['name'] . '-' . $custom_key, true ) );
		}
	}

	/**
	 * Reset product Arrival Time.
	 *
	 * @param int $product_id
	 * @return void
	 */
	public static function reset_arrival_time( $product_id ) {
		self::update_setting( $product_id, 'arrival_time', '' );
	}

	/**
	 * Update Coming Soon Setting key.
	 *
	 * @param int        $product_id Product ID.
	 * @param string     $key Key Name.
	 * @param string|int $value Key Value.
	 * @return void
	 */
	public static function update_setting( $product_id, $key, $value ) {
		update_post_meta( $product_id, self::$plugin_info['name'] . '-' . $key, $value );
	}

	/**
	 * Check only if the coming soon status is enabled or not.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	public static function is_product_coming_soon_enabled( $product_id ) {
		$status = self::get_settings( $product_id, 'status' );
		if ( ! empty( $status ) && ( 'yes' === $status ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Is the product in Coming Soon Mode.
	 *
	 * @param int $product_id Product ID.
	 * @return boolean
	 */
	public static function is_product_coming_soon( $product_id ) {
		$status = self::get_settings( $product_id, 'status' );
		if ( ! empty( $status ) && ( 'yes' === $status ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Keep the product purchasable in coming soon mode.
	 *
	 * @param int $product_id
	 * @return boolean
	 */
	public static function is_keep_product_purchasable( $product_id ) {
		return ( 'yes' === self::get_settings( $product_id, 'keep_purchasable' ) );
	}

	/**
	 * Check if backorders are disabled or the product grants that.
	 *
	 * @param int $product_id Product ID.
	 * @return boolean
	 */
	public static function disable_backorders( $product_id ) {
		// 1) if it's disabled, return false directly.
		$_product           = wc_get_product( $product_id );
		$disable_backorders = self::get_settings( $product_id, 'disable_backorders' );
		if ( ! empty( $disable_backorders ) && ( 'yes' === $disable_backorders ) ) {
			return true;
		}
		// 2) return false only if the product allows any type of backorders.
		if ( ( 'onbackorder' === $_product->get_stock_status() ) || $_product->backorders_allowed() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check if the product is unpurchasable based on the coming soon options and backorder options.
	 *
	 * @param int $product_id Product ID.
	 * @return boolean
	 */
	public static function is_product_unpurchasable( $product_id ) {
		return ( self::is_product_coming_soon( $product_id ) && self::disable_backorders( $product_id ) && ! self::is_keep_product_purchasable( $product_id ) );
	}

	/**
	 * Get list of coming soon products.
	 *
	 * @return array
	 */
	public static function get_coming_soon_list() {
		return get_option( self::$coming_soon_hub_key, array() );
	}

	/**
	 * Update coming soon list.
	 *
	 * @param int    $product_id
	 * @param string $action 'add' - 'remove'
	 * @return void
	 */
	public static function update_coming_soon_list( $product_id, $action = 'add' ) {
		$coming_soon_list = self::get_coming_soon_list();
		$_product         = wc_get_product( $product_id );
		if ( 'add' === $action ) {
			if ( ! in_array( $product_id, $coming_soon_list ) ) {
				$coming_soon_list[] = $product_id;
			}
			// WPML Comp: Get all translations of the post to add them there.
			global $sitepress;
			if ( $sitepress ) {
				$trid = $sitepress->get_element_trid( $product_id, 'post_' . get_post_type( $product_id ) );
				if ( $trid ) {
					$translations = $sitepress->get_element_translations( $trid, 'post_' . get_post_type( $product_id ) );
					if ( ! empty( $translations ) && is_array( $translations ) ) {
						foreach ( $translations as $translation_code => $translation_data ) {
							$element_id = absint( $translation_data->element_id );
							if ( ! in_array( $element_id, $coming_soon_list ) ) {
								$coming_soon_list[] = $element_id;
							}
						}
					}
				}
			}
		} elseif ( 'remove' === $action ) {
			$index = array_search( $product_id, $coming_soon_list );
			if ( false !== $index ) {
				unset( $coming_soon_list[ $index ] );
			}
			// WPML Comp: Get all translations of the post to remove them from there.
			global $sitepress;
			if ( $sitepress ) {
				$trid = $sitepress->get_element_trid( $product_id, 'post_' . get_post_type( $product_id ) );
				if ( $trid ) {
					$translations = $sitepress->get_element_translations( $product_id, 'post_' . get_post_type( $product_id ) );
					if ( ! empty( $translations ) && is_array( $translations ) ) {
						foreach ( $translations as $translation_code => $translation_data ) {
							$element_id = absint( $translation_data->element_id );
							$index      = array_search( $element_id, $coming_soon_list );
							if ( false !== $index ) {
								unset( $coming_soon_list[ $index ] );
							}
						}
					}
				}
			}
		}
		update_option( self::$coming_soon_hub_key, $coming_soon_list, true );
	}

	/**
	 * Is the arrival time countdown should be shown.
	 *
	 * @param int $product_id Product ID.
	 * @return boolean
	 */
	public static function is_show_arrival_time_countdown( $product_id ) {
		$status = self::get_settings( $product_id, 'show_countdown' );
		if ( ! empty( $status ) && ( 'yes' === $status ) ) {
			return true;
		}
		return false;
	}

	/**
	 * Is the product Arrival Time has passed.
	 *
	 * @param int $product_id Product ID.
	 * @return boolean
	 */
	public static function is_product_arrival_time_passed( $product_id ) {
		$arrival_time_settings = self::get_settings( $product_id, 'arrival_time' );
		$current_time          = current_datetime()->getTimestamp();
		if ( empty( $arrival_time_settings ) ) {
			return false;
		}
		$arrival_time = DateTime::createFromFormat( 'Y-m-d\TH:i', $arrival_time_settings, wp_timezone() );
		if ( false === $arrival_time ) {
			return false;
		}
		$arrival_time = $arrival_time->getTimestamp();
		return ( $current_time > $arrival_time );
	}

	/**
	 * Check if the product is a translation.
	 *
	 * @param int    $element_id
	 * @param string $element_type
	 * @return boolean
	 */
	public static function wpml_is_translation( $element_id, $element_type ) {
		global $sitepress, $wpdb;
		if ( ! $sitepress ) {
			return;
		}
		$query  = "
		SELECT
			trid, language_code, source_language_code
		FROM
			{$wpdb->prefix}icl_translations
		WHERE
			element_id=%d
		AND
			element_type=%s
		";
		$result = $wpdb->get_row( $wpdb->prepare( $query, array( $element_id, $element_type ) ), \ARRAY_A );

		if ( ! $result || ! is_array( $result ) ) {
			return false;
		}

		return is_null( $result['source_language_code'] ) ? false : true;

	}

	/**
	 * Kses Data.
	 *
	 * @param string $data
	 * @return string
	 */
	public function gpls_kses_post( $data ) {
		add_filter( 'safe_style_css', array( $this, 'filter_style_styles' ), PHP_INT_MAX, 1 );
	 	$result = wp_kses_post( $data );
		remove_filter( 'safe_style_css', array( $this, 'filter_style_styles' ), PHP_INT_MAX );
		return $result;
	}

	/**
	 * Filter Style.
	 *
	 * @param array $styles
	 * @return array
	 */
	public function filter_style_styles( $styles ) {
		$styles[] = 'display';
		return $styles;
	}

	/**
	 * Check if this is the current active theme.
	 *
	 * @param string $theme_slug
	 * @return boolean
	 */
	protected function is_theme_active( $theme_slug ) {
		return ( get_template() === $theme_slug );
	}

	/**
	 * Is Porto THEME.
	 *
	 * @return boolean
	 */
	protected static function is_porto_theme() {
		return defined( 'PORTO_DIR' );
	}

}

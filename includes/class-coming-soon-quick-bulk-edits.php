<?php
/**
 * Handles the Coming Soon Quick and Bulk Products Edits.
 *
 * @category class
 * @package  ComingSoon
 */

namespace GPLSCore\GPLS_PLUGIN_WCSAMM;

use GPLSCore\GPLS_PLUGIN_WCSAMM\ComingSoon;

/**
 * Coming Soon Backend - Bulk and Quick Edits Class.
 */
class ComingSoonBulkQuickEdits extends ComingSoon {

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
	 * Hooks.
	 */
	private function hooks() {

		// Coming Soon Switch in Bulk and Quick Edit.
		add_action( 'bulk_edit_custom_box', array( $this, 'coming_soon_switch_in_bulk_edit' ), PHP_INT_MAX, 2 );
		add_action( 'quick_edit_custom_box', array( $this, 'coming_soon_switch_in_quick_edit' ), PHP_INT_MAX, 2 );
		add_action( 'manage_product_posts_custom_column', array( $this, 'products_table_columns' ), PHP_INT_MAX, 2 );
	}

	/**
	 * Coming Soon Switch in Bulk Edit.
	 *
	 * @return void
	 */
	public function coming_soon_switch_in_bulk_edit( $column_name, $post_type ) {
		if ( 'price' !== $column_name || 'product' !== $post_type ) {
			return;
		}
		$this->coming_soon_bulk_quick_fields();
		$this->coming_soon_bulk_quick_assets();
	}

	/**
	 * Coming Soon Switch in Quick Edit.
	 *
	 * @return void
	 */
	public function coming_soon_switch_in_quick_edit( $column_name, $post_type ) {
		if ( 'price' !== $column_name || 'product' !== $post_type ) {
			return;
		}
		$this->coming_soon_bulk_quick_fields( 'quick' );
	}

	/**
	 * Coming Soon Bulk and Quick Assets.
	 */
	private function coming_soon_bulk_quick_assets() {
		?>
		<script>
			(function($) {
				$(function(e) {
					$( '#wpbody' ).on(
						'change',
						'#<?php echo esc_attr( self::$plugin_info['prefix'] . '-fields-bulk' ); ?> .inline-edit-group .change_to',
						function() {
							if ( 0 < $( this ).val() ) {
								$( this ).closest( 'div' ).find( '.change-input' ).show();
							} else {
								$( this ).closest( 'div' ).find( '.change-input' ).hide();
							}
						}
					);

					// Quick Edit Click.
					$('#the-list').on( 'click', '.editinline', function(e) {
						let btn        = $(this);
						var productRow = $( this ).closest( 'tr' );
						var productID  = productRow.attr( 'id' );
						productID      = productID.replace( 'post-', '' );
						setTimeout( (e) => {
							let quickEditBox   = $('#edit-' + productID );
							let comingSoonText = quickEditBox.find( '.<?php echo esc_attr( self::$plugin_info['name'] . '-texteditor' ); ?>' );
							var newID          = '<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text-quick-live' ); ?>';

							// Set Values.
							let inlineDiv = $('#<?php echo esc_attr( self::$plugin_info['prefix'] ); ?>-inline-' + productID );
							quickEditBox.find('[name="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-status' ); ?>"]').prop( 'checked', inlineDiv.find('.<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-status' ); ?>').text() === 'yes' );
							quickEditBox.find('[name="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text' ); ?>"]').html( inlineDiv.find('.<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text' ); ?>').html() );
							quickEditBox.find('[name="<?php echo esc_attr( self::$plugin_info['name'] . '-hide-price' ); ?>"]').prop( 'checked', inlineDiv.find('.<?php echo esc_attr( self::$plugin_info['name'] . '-hide-price' ); ?>').text() === 'yes' );
							quickEditBox.find('[name="<?php echo esc_attr( self::$plugin_info['name'] . '-show-countdown' ); ?>"]').prop( 'checked', inlineDiv.find('.<?php echo esc_attr( self::$plugin_info['name'] . '-show-countdown' ); ?>').text() === 'yes' );
							quickEditBox.find('[name="<?php echo esc_attr( self::$plugin_info['name'] . '-arrival-time' ); ?>"]').val( inlineDiv.find('.<?php echo esc_attr( self::$plugin_info['name'] . '-arrival-time' ); ?>').text() );

							comingSoonText.prop( 'id', newID );
							wp.editor.remove( newID );
							wp.editor.initialize(
								newID,
								{
									tinymce: {
										height: 250,
										wpautop: true,
										theme: 'modern',
										plugins : 'charmap, colorpicker, compat3x, directionality, hr, image, lists, media, paste, tabfocus, textcolor, WordPress, wpautoresize, wpdialogs, wpeditimage, wpemoji, wpgallery, wplink, wptextpattern, wpview',
										tabfocus_elements: ': prev ,: next' ,
										toolbar1: 'bold, hr, link, fontselect, outdent, indent, undo, redo, forecolor, alignjustify, charmap, pastetext fontsizeselect, formatselect, italic, numlist, bullist, alignleft, aligncenter, alignright, link, unlink, fontname, fontsize, fontsize_class, samp, div, h1, h2, h3, h4, h5, h6, p. code, underline, wp_help',
										setup: function( editor ) {
											editor.on( 'change', function() {
												editor.save();
											});
										},
										wpeditimage_html5_captions: true,
										contextmenu: "link image imagetools spellchecker",
										paste_webkit_styles: 'font-weight font-style color',
										preview_styles: 'background-color font-family font-size font-weight font-style text-decoration text-transform',
										resize: 'vertical',
										fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 36px 48px 60px',
										menubar: 'file edit insert view format tools help',
										statusbar: true,
										paste_as_text: true
									},
									quicktags: true,
									mediaButtons: true
								}
							);
						}, 300 );
					});

					$('.bulkactions .action').on( 'click', (e) => {
						// Bulk Edit Click.
						let bulkID = '<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text-bulk' ); ?>';
						// wp.editor.remove( bulkID );
						wp.editor.initialize(
							bulkID,
							{
								tinymce: {
									height: 250,
									wpautop: true,
									theme: 'modern',
									plugins : 'charmap, colorpicker, compat3x, directionality, hr, image, lists, media, paste, tabfocus, textcolor, WordPress, wpautoresize, wpdialogs, wpeditimage, wpemoji, wpgallery, wplink, wptextpattern, wpview',
									tabfocus_elements: ': prev ,: next' ,
									toolbar1: 'bold, hr, link, fontselect, outdent, indent, undo, redo, forecolor, alignjustify, charmap, pastetext fontsizeselect, formatselect, italic, numlist, bullist, alignleft, aligncenter, alignright, link, unlink, fontname, fontsize, fontsize_class, samp, div, h1, h2, h3, h4, h5, h6, p. code, underline, wp_help',
									setup: function( editor ) {
										editor.on( 'change', function() {
											editor.save();
										});
									},
									wpeditimage_html5_captions: true,
									contextmenu: "link image imagetools spellchecker",
									paste_webkit_styles: 'font-weight font-style color',
									preview_styles: 'background-color font-family font-size font-weight font-style text-decoration text-transform',
									resize: 'vertical',
									fontsize_formats: '8px 10px 12px 14px 16px 18px 24px 36px 48px 60px',
									menubar: 'file edit insert view format tools help',
									statusbar: true,
									paste_as_text: true
								},
								quicktags: true,
								mediaButtons: true
							}
						);
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Coming Soon Bulk and Edit Fields.
	 */
	private function coming_soon_bulk_quick_fields( $context = 'bulk' ) {
		?>
		<fieldset id="<?php echo esc_attr( self::$plugin_info['prefix'] . '-fields-' . ( 'bulk' === $context ? 'bulk' : 'quick' ) ); ?>" class="<?php echo esc_attr( self::$plugin_info['prefix'] . '-pro-field' ); ?> inline-edit-col-left" style="margin-top:20px;">
			<div class="inline-edit-col" style="background-color:#EEE;padding:5px;">
				<h4 style="padding:0px 8px;"><?php esc_html_e( 'Coming Soon Mode [GrandPlugins]', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?> <?php self::$core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode' ); ?></h4>
				<!-- Enable Coming Soon Mode -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Coming Soon Mode', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-status' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="checkbox" class="checkbox <?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-status' ); ?>">
						<span><?php esc_html_e( 'Enable / Disable Coming Soon Mode', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
				<!-- Coming Soon Text -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Coming Soon Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;width:100%;max-width:100%;clear:both;' : '' ); ?>">
						<textarea disabled class="<?php echo esc_attr( self::$plugin_info['name'] . '-texteditor' . ( 'bulk' === $context ? '-bulk' : '' ) ); ?>" id="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text' . ( 'quick' === $context ? '-quick' : '-bulk' ) ); ?>" ><?php esc_html_e( 'Coming Soon', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></textarea>
					</label>
				</div>
				<!-- Coming Soon Arrival Time -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Arrival Time', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-arrival-time' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="datetime-local" value="" class="short" name="<?php echo esc_attr( self::$plugin_info['name'] . '-arrival-time' ); ?>" >
						<span><?php esc_html_e( 'Coming Soon Arrival Time', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
				<!-- Coming Soon Show Count Down -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Show Countdown', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-show-countdown' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="checkbox" class="checkbox" >
						<span><?php esc_html_e( 'Show / Hide CountDown', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
				<!-- Coming Soon Hide Price -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Hide Price', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-hide-price' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="checkbox" class="checkbox" >
						<span><?php esc_html_e( 'Hide the product price', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
				<!-- Coming Soon Auto Email -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Auto Email', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-auto-email' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="checkbox" class="checkbox" >
						<span><?php esc_html_e( 'Send email automatically when the product arrival time is over [ requires "Arrival Time" and "Auto Enable" ]', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
				<!-- Coming Soon Show Subscription Form -->
				<div class="inline-edit-group wp-clearfix" style="margin: 5px;box-shadow: 0 .125rem .25rem rgba(0,0,0,.075)!important;padding: 5px;background: #FFF;">
					<label class="alignleft">
						<span class="title" style="width:10em;"><?php esc_html_e( 'Subscription Form', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
						<?php if ( 'bulk' === $context ) : ?>
						<span class="input-text-wrap" style="margin-left:10em;">
							<select disabled class="change_to change_<?php echo esc_attr( self::$plugin_info['name'] . '-show-subscription-form' ); ?>">
								<option value=""><?php esc_html_e( '— No change —', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
								<option value="1"><?php esc_html_e( 'Change to:', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></option>
							</select>
						</span>
						<?php endif; ?>
					</label>
					<label class="change-input" style="<?php echo esc_attr( 'bulk' === $context ? 'display:none;' : '' ); ?>">
						<input disabled type="checkbox" class="checkbox" >
						<span><?php esc_html_e( 'Show subscription form', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
					</label>
				</div>
			</div>
		</fieldset>
		<?php
	}


	/**
	 * Products List Columns.
	 */
	public function products_table_columns( $column, $product_id ) {
		if ( 'name' !== $column ) {
			return;
		}
		$coming_soon_settings = self::get_settings( $product_id );
		?>
		<div class="hidden" id="<?php echo esc_attr( self::$plugin_info['prefix'] . '-inline-' . absint( $product_id ) ); ?>">
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-status' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-coming-soon-text' ); ?>"></div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-hide-price' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-show-countdown' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-arrival-time' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-auto-enable' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-auto-email' ); ?>">no</div>
			<div class="<?php echo esc_attr( self::$plugin_info['name'] . '-show-subscription-form' ); ?>">no</div>
		</div>
		<?php
	}

}

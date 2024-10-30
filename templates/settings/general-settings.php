<?php
use GPLSCore\GPLS_PLUGIN_WCSAMM\Settings;

?>
<div class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-countdown-settings-wrapper' ); ?>">
	<div class="container-fluid">
		<!-- Colors -->
		<div class="row border p-3 colors">
			<div class="col-12">
				<div class="settings-list row">
					<div class="loop-wrapper col-12 my-3 p-3 bg-white shadow-sm">
						<div class="container-fluid border">
							<!-- Coming Soon Text -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Coming soon section position', 'woo-coming-soon-products' ); ?><span class="ms-1"><?php $core->new_keyword( 'New', false ); ?></span></h6>
									</div>
									<div class="col-md-9">
										<div class="settings-field-section my-3 shadow-sm p-3">
											<?php Settings::single_product_placement_actions_select( $plugin_info['name'] . '[general][single_placement_position]', $general_settings['single_placement_position'] ); ?>
											<small class="d-block mt-3"><?php esc_html_e( 'Action hook to place the coming soon section [ coming soon text - coming soon countdown - subscription form ] in single product page', 'woo-coming-soon-products' ); ?></small>
										</div>
										<div class="settings-field-section my-3 shadow-sm p-3">
											<input type="number" value="<?php echo esc_attr( $general_settings['single_placement_position_priority'] ); ?>" name="<?php echo esc_attr( $plugin_info['name'] . '[general][single_placement_position_priority]' ); ?>">
											<small class="d-block mt-3"><?php esc_html_e( 'Action hook priority', 'woo-coming-soon-products' ); ?></small>
										</div>
										<div class="coming-soon-section-wrapper my-3 shadow-sm p-3">
											<h3><?php esc_html_e( 'Coming soon section shortcode', 'woo-coming-soon-products' ); ?></h3>
											<small class="ms-1 ml-1"><?php echo esc_html( ' [ coming soon text - coming soon countdown ]' ); ?></small>
											<small class="d-block ms-1 ml-1 my-2 text-muted"><?php esc_html_e( 'Use this shortcode in case the coming soon section doesn\'t appear using the hooks selection above. This happens if you are using a blocks-based theme or other themes that manipulate the WooCommerce standard single product template structure.', 'woo-coming-soon-products' ); ?></small>
											<div class="coming-soon-section">
												<code class="d-inline-block my-3"><?php echo esc_html( '[' . str_replace( '-', '_', self::$plugin_info['classes_prefix'] . '-coming-soon-section' ) . ']' ); ?></code>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<div class="col-12">
				<div class="settings-list row">
					<div class="loop-wrapper col-12 my-3 p-3 bg-white shadow-sm">
						<div class="container-fluid border">
							<!-- Coming Soon Text -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'General Coming Soon Text', 'woo-coming-soon-products' ); ?><span class="ms-1"><?php $core->new_keyword( 'New', false ); ?></span></h6>
									</div>
									<div class="col-md-9">
										<textarea id="<?php echo esc_attr( $plugin_info['name'] . '-coming-soon-text' ); ?>" type="text" class="<?php echo esc_attr( $plugin_info['name'] . '-texteditor' ); ?>" name="<?php echo esc_attr( $plugin_info['name'] . '[general][coming_soon_text]' ); ?>" ><?php echo wp_kses_post( $general_settings['coming_soon_text'] ); ?></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
					<input type="hidden" name="<?php echo esc_attr( $plugin_info['name'] . '-general-settings-nonce' ); ?>" value="<?php echo esc_attr( wp_create_nonce( $plugin_info['name'] . '-general-settings-nonce' ) ); ?>">
				</div>
			</div>
		</div>
	</div>
</div>
<?php
$core->default_footer_section();

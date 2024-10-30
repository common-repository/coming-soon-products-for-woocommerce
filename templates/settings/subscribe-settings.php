<?php
use GPLSCorePro\GPLS_PLUGIN_WCSAMM\Settings;

?>
<div class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-countdown-settings-wrapper' ); ?>">
	<div class="container-fluid">
		<div class="row border p-3 colors <?php echo esc_attr( $plugin_info['classes_prefix'] . '-pro-field' ); ?>">
			<div class="col-12">
				<div class="settings-list row">
					<div class="loop-wrapper col-12 my-3 p-3 bg-white shadow-sm">
						<div class="container-fluid border">
                            <h3 class="mt-3"><?php esc_html_e( 'Subscribe form settings', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?><?php $core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode', '', false ); ?></h3>
							<!-- Subscription Form Title -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Subscription Form Title', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<textarea disabled id="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-subscribe-title' ); ?>" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-subscribe-title' ); ?>" ></textarea>
									</div>
								</div>
							</div>
							<!-- Subscription Form Placeholder -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Subscription Form Placeholder', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<input disabled type="text" class="regular-text" >
									</div>
								</div>
							</div>
							<!-- After Submit Text -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'After Submit Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
										<span><?php esc_html_e( 'This text will appear after the Subscription form is submitted', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></span>
									</div>
									<div class="col-md-9">
										<textarea disabled id="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-post-subscribe-text' ); ?>" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-post-subscribe-text' ); ?>" ></textarea>
									</div>
								</div>
							</div>
							<!-- Submit Button Text -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Submit Button Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<input disabled type="text" class="regular-text" >
									</div>
								</div>
							</div>
							<!-- Submit Button Background -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Submit Button Background', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<input disabled type="text" class="regular-text <?php echo esc_attr( $plugin_info['classes_prefix'] . '-color-picker' ); ?>" >
									</div>
								</div>
							</div>
							<!-- Submit Button Color -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Submit Button Color', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<input disabled type="text" class="regular-text <?php echo esc_attr( $plugin_info['classes_prefix'] . '-color-picker' ); ?>" >
									</div>
								</div>
							</div>
							<!-- Consent Text -->
							<div class="loop-coming-soon-text-wrapper settings-group my-4 py-4 col-md-12">
								<div class="row">
									<div class="col-md-3">
										<h6><?php esc_html_e( 'Consent Text', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?></h6>
									</div>
									<div class="col-md-9">
										<textarea disabled id="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-consent-text' ); ?>" class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-consent-text' ); ?>" cols="100" rows="10"></textarea>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

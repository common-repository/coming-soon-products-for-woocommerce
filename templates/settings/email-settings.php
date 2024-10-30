<?php
use GPLSCorePro\GPLS_PLUGIN_WCSAMM\ComingSoonBackend;
use GPLSCorePro\GPLS_PLUGIN_WCSAMM\ComingSoonEmails;
use GPLSCorePro\GPLS_PLUGIN_WCSAMM\Settings;

?>
<div class="<?php echo esc_attr( $plugin_info['classes_prefix'] . '-emails-list-settings-wrapper' ); ?>">
	<div class="container-fluid">
		<!-- Colors -->
		<div class="row border p-3 colors">
			<div class="col-12">
				<div class="settings-list row <?php echo esc_attr( $plugin_info['classes_prefix'] . '-pro-field' ); ?>">
					<div class="loop-wrapper col-12 my-3 p-3 bg-white shadow-sm">
						<div class="container-fluid border">
							<h3 class="mt-3"><?php esc_html_e( 'Subscribed Emails', 'gpls-wcsamm-coming-soon-for-woocommerce' ); ?><?php $core->pro_btn( '', 'Pro →', 'd-gpls-premium-btn-wave-product d-gpls-premium-btn-wave-product-shortcode', '', false ); ?></h3>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

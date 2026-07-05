<?php
/**
 * Plugin Name:       Smart Stock Predictor
 * Plugin URI:        https://profiles.wordpress.org/sudipta2470/
 * Description:       Predict possible WooCommerce product stockouts using recent sales trends and current inventory levels.
 * Requires at least: 6.0
 * Requires PHP:      7.4
 * Version:           1.0.0
 * Author:            Sudipta Das
 * Author URI:        https://profile-nine-jet.vercel.app/
 * License:           MIT
 * License URI:       https://opensource.org/licenses/MIT
 * Text Domain:       smart-stock-predictor
 * Domain Path:       /languages
 * Requires Plugins: woocommerce
 */

if ( ! defined( 'WPINC' ) ) {
	die; // Absolute security gate
}

class Smart_Stock_Predictor_Free {

	public function __construct() {
		// Hook early into plugins_loaded to check dependencies
		add_action( 'plugins_loaded', array( $this, 'check_dependencies' ) );
		
	}

	/**
	 * Verify if required plugins are active
	 */
	public function check_dependencies() {
		// Check if WooCommerce class exists or the plugin is active
		if ( ! class_exists( 'WooCommerce' ) ) {
			// Show admin notice to the user
			add_action( 'admin_notices', array( $this, 'render_missing_wc_notice' ) );
			return; // Stop initialization
		}

		// WooCommerce is active, proceed to run the plugin safely
		$this->init_plugin();
	}

	public function init_plugin() {
		load_plugin_textdomain( 'smart-stock-predictor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Notice displaying the WooCommerce dependency warning
	 */
	public function render_missing_wc_notice() {
		?>
		<div class="ssp-notice notice-error is-dismissible">
			<p>
				<?php 
				echo wp_kses_post( 
					sprintf(
						/* translators: %s: Search term or link text */
						__( '<strong>Smart Stock Predictor</strong> requires %s to be installed and active.', 'smart-stock-predictor' ),
						'<a href="' . esc_url( admin_url( 'plugin-install.php?tab=search&s=woocommerce' ) ) . '">WooCommerce</a>'
					) 
				); 
				?>
			</p>
		</div>
		<?php
	}

	public function create_admin_menu() {
		add_menu_page(
			__( 'Smart Stock Predictor', 'smart-stock-predictor' ),
			__( 'Smart Predictor', 'smart-stock-predictor' ),
			'manage_options',
			'smart-stock-predictor',
			array( $this, 'render_admin_dashboard' ),
			'dashicons-chart-line',
			6
		);
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_smart-stock-predictor' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'ssp-free-style', plugins_url( 'assets/css/admin-style.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'ssp-free-script', plugins_url( 'assets/js/admin-script.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
	}

	public function render_admin_dashboard() {
		$dir_path = plugin_dir_path( __FILE__ );

		if ( file_exists( $dir_path . 'includes/dashboard/class-product-listing.php' ) ) {
			include $dir_path . 'includes/dashboard/class-product-listing.php';
			new SSP_Product_Listing();
		}
	}
}

new Smart_Stock_Predictor_Free();

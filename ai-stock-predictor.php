<?php
/**
 * Plugin Name:       AI Stock Predictor
 * Plugin URI:        https://profile-nine-jet.vercel.app/
 * Description:       A starter kit to predict stock trends using machine learning concepts.
 * Version:           1.0.0
 * Author:            Sudipta Das
 * Author URI:        https://profile-nine-jet.vercel.app/
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 * Text Domain:       ai-stock-predictor
 * Domain Path:       /languages
 */

if ( ! defined( 'WPINC' ) ) {
	die; // Absolute security gate
}

class AI_Stock_Predictor_Free {

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
		load_plugin_textdomain( 'ai-stock-predictor', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
		add_action( 'admin_menu', array( $this, 'create_admin_menu' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
	}

	/**
	 * Notice displaying the WooCommerce dependency warning
	 */
	public function render_missing_wc_notice() {
		?>
		<div class="notice notice-error is-dismissible">
			<p>
				<?php 
				echo wp_kses_post( 
					sprintf(
						/* translators: %s: Search term or link text */
						__( '<strong>AI Stock Predictor</strong> requires %s to be installed and active.', 'ai-stock-predictor' ),
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
			__( 'AI Stock Predictor', 'ai-stock-predictor' ),
			__( 'AI Predictor', 'ai-stock-predictor' ),
			'manage_options',
			'ai-stock-predictor',
			array( $this, 'render_admin_dashboard' ),
			'dashicons-chart-line',
			6
		);
	}

	public function enqueue_admin_assets( $hook ) {
		if ( 'toplevel_page_ai-stock-predictor' !== $hook ) {
			return;
		}
		wp_enqueue_style( 'aisp-free-style', plugins_url( 'assets/css/admin-style.css', __FILE__ ), array(), '1.0.0' );
		wp_enqueue_script( 'aisp-free-script', plugins_url( 'assets/js/admin-script.js', __FILE__ ), array( 'jquery' ), '1.0.0', true );
	}

	public function render_admin_dashboard() {
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
			<p><?php esc_html_e( 'Welcome to the AI Stock Predictor Dashboard.', 'ai-stock-predictor' ); ?></p>
		</div>
		<?php
	}
}

new AI_Stock_Predictor_Free();

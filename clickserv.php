<?php
/**
 * Plugin Name: ClickServ
 * Description: ClickServ is the leading WooCommerce plugin that allows you to sync with your SAGE accounting
 * Text Domain: clickserv-plugin
 * Version: 0.1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

class ClickServ_Velocity {
	/**
	 * Construct the plugin.
	 */
	private $stores = ["woocommerce"];		
	private static $instance;
	private $clikservMenu;

	public function __construct() {
		add_action( 'admin_init', array( $this, 'check_version' ) );
		if(!$this->compatible_versions())
		return;
		
		$this->id = "ClickServ_Velocity";
		$this->method_title       = __( 'Integration Demo', 'ClickServ_Velocity' );
		$this->method_description = __( 'A plugin that helps you link and manage your SageOne account and WooCommerce store.', 'ClickServ_Velocity' );
		
		add_filter( 'cron_schedules', array($this, 'clickserv_add_schedule') );
		add_action( 'plugins_loaded', array( $this, 'init_clickserv' ) );
		add_action( 'admin_init', array( $this, 'clickserv_assets' ) );		
		register_activation_hook( __FILE__, array($this, 'clickserv_run_on_activate') );
		register_deactivation_hook( __FILE__, array($this, 'clickserv_run_on_deactivate') ); 
	}

	function check_version() {
		if ( !$this->compatible_versions() ) {		
			if ( is_plugin_active( plugin_basename( __FILE__ ) ) ) {		
				deactivate_plugins( plugin_basename( __FILE__ ) );		
				add_action( 'admin_notices', array( $this, 'disabled_notice' ) );		
				if ( isset( $_GET['activate'] ) ) {		
						unset( $_GET['activate'] );		
				}		
			}		
		}		
	}

	function disabled_notice() {
		echo '<strong>' . esc_html__( 'Please check the error messages below to enable ClickServ', 'clickserv-plugin' ) . '</strong>';
	}
	
	public function compatible_versions() {
		$isCompatible = true;
		if(class_exists( 'WC_Integration' ) ){
				if( version_compare( $GLOBALS['wp_version'], '4.4', '<' )){
					add_action( 'admin_notices', array($this, 'clickserv_wrong_wp_error') );
					$isCompatible = false;			
				}
				if( version_compare( $GLOBALS['woocommerce']->version, '3.5', '<' )){
					add_action( 'admin_notices', array($this, 'clickserv_wrong_wc_error') );
					$isCompatible = false;			
				} 
				remove_action( 'admin_notices', array($this, 'clickserv_no_wc_error'));
		} else {
			add_action( 'admin_notices', array($this, 'clickserv_no_wc_error'));	 
			$isCompatible = false;	
		}
		return $isCompatible;
	}

	static function GetInstance()
	{          
		if (!isset(self::$instance))
		{
			self::$instance = new self();
		}
		return self::$instance;
	}
	
	public function clickserv_add_schedule( $schedules ) {
		// add a 'weekly' schedule to the existing set
		$schedules['custom'] = array(
			'interval' => 60,
			'display' => __('1 Minute')
		);
		return $schedules;			
	}
	
	/**
	 * Activation Functions
	 **/
	function clickserv_run_on_activate() {
		if ( !$this->compatible_versions() ) {
			deactivate_plugins( plugin_basename( __FILE__ ) );
		}
	} // end run_on_activate()
		
	public function clickserv_run_on_deactivate() {
		remove_submenu_page( $this->stores[0], 'clickserv-submenu-page'); 
	} // end run_on_deactivate()
		
	public function register_clickserv_submenu_page() {
		$this->clikservMenu = add_submenu_page( $this->stores[0], 'ClickServ', 'ClickServ', 'manage_options', 'clickserv-submenu-page', array($this,'clickserv_submenu_page_callback') ); 
	}
	// Error Messages
	public function clickserv_wrong_wc_error() {?>
    <div class="notice notice-error">
        <p><?php _e( 'Clickserv needs <b>WooCommerce version 3.5</b> and upwards in order to work. <strong>Please update your WooCommerce</strong>', 'clickserv_plugin'); ?></p>
    </div>
		<?php 
	}
	public function clickserv_wrong_wp_error() {?>
    <div class="notice notice-error">
				<p><?php _e( 'Clickserv needs <b>Wordpress version 4.4</b> and upwards in order to work. <strong>Please update your Wordpress</strong>', 'clickserv_plugin'); ?></p>
    </div>
		<?php 
	}
	public function clickserv_no_wc_error() {?>
    <div class="notice notice-error">
				<p><?php _e( 'Clickserv needs WooCommerce. <strong>Please install and activate WooCommerce</strong>', 'clickserv_plugin'); ?></p>
    </div>
		<?php 
	}

	function clickserv_assets() {
		wp_register_style('ClickServ_Velocity_Style', plugins_url('includes/styles/clickserv.css',__FILE__ ));
		wp_enqueue_style('ClickServ_Velocity_Style');
		wp_register_script( 'ClickServ_Velocity_Script', plugins_url('includes/scripts/clickserv.js',__FILE__ ));
		wp_enqueue_script('ClickServ_Velocity_Script');
	}
	
	
	/**
	 * Initialize the plugin.
	 */
	function init_clickserv() {	
		include_once 'includes/clikserv_redirects.php';
		// Register the integration.
		add_filter('woocommerce_integrations', array( $this, 'add_integration' ) );
		add_action('admin_menu', array($this, 'register_clickserv_submenu_page'),99);
	}
	function clickserv_submenu_page_callback() {
		// TODO, this needs to be gotten from and API call and saved to DB
		$registered = true;
		if($registered === true){
			ClickservRedirects::adminPage();
		} else {			
			ClickservRedirects::registerPage();
		}
	}
	
	/**
	 * Add a new integration to WooCommerce.
	 */
	public function add_integration( $integrations ) {
		$integrations[] = 'ClickServ_Velocity';
		return $integrations;
	}
}
	$ClickServ_Velocity = ClickServ_Velocity::GetInstance();
	$ClickServ_Velocity->init_clickserv();
?>
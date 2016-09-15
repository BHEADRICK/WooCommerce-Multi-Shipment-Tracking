<?php
/*
Plugin Name: WooCommerce Multi-Shipment Tracking
Plugin URI:
Description:
Version: 1.0.0
Author: Author Name
Author URI: https://authorurl.com
 License: GNU General Public License v3.0
 License URI: http://www.gnu.org/licenses/gpl-3.0.html

*/


// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WooCommerceMultiShipmentTracking {

	/*--------------------------------------------*
	 * Constants
	 *--------------------------------------------*/
	const name = 'WooCommerce Multi-Shipment Tracking';
	const slug = 'woocommerce-multi-shipment-tracking';

	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( $this, 'install_woocommerce_multi_shipment_tracking' ) );

		//Hook up to the init action
		add_action( 'init', array( $this, 'init_woocommerce_multi_shipment_tracking' ) );
	}

	/**
	 * Runs when the plugin is activated
	 */


	/**
	 * Runs when the plugin is initialized
	 */
	function init_woocommerce_multi_shipment_tracking() {
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();

		// Register the shortcode [my_shortcode]
		add_shortcode( 'my_shortcode', array( $this, 'render_shortcode' ) );

		if ( is_admin() ) {
			//this will run when in the WordPress admin
		} else {
			//this will run when on the frontend
		}

		/*
		 * TODO: Define custom functionality for your plugin here
		 *
		 * For more information:
		 * http://codex.wordpress.org/Plugin_API#Hooks.2C_Actions_and_Filters
		 */
		add_action( 'cmb2_admin_init', array( $this, 'cmb2_box' ) );
//		add_filter( 'your_filter_here', array( $this, 'filter_callback_method_name' ) );


	}

	public function cmb2_box(){
	    $prefix = 'multiship_';

        $cmb = new_cmb2_box( array(
            'id'            => $prefix . 'metabox',
            'title'         => __( 'Shipments', 'cmb2' ),
            'object_types'  => array( 'shop_order', ), // Post type
            // 'show_on_cb' => 'yourprefix_show_if_front_page', // function should return a bool value
             'context'    => 'side',
             'priority'   => 'high',
             'show_names' => true, // Show field names on the left
            // 'cmb_styles' => false, // false to disable the CMB stylesheet
            // 'closed'     => true, // true to keep the metabox closed by default
            // 'classes'    => 'extra-class', // Extra cmb2-wrap classes
            // 'classes_cb' => 'yourprefix_add_some_classes', // Add classes through a callback.
        ) );

        $group_field_id = $cmb->add_field( array(
            'id'          => '_packages',
            'type'        => 'group',
            'description' => __( 'Generates reusable form entries', 'cmb2' ),
            // 'repeatable'  => false, // use false if you want non-repeatable group
            'options'     => array(
                'group_title'   => __( 'Package {#}', 'cmb2' ), // since version 1.1.4, {#} gets replaced by row number
                'add_button'    => __( 'Add Another Package', 'cmb2' ),
                'remove_button' => __( 'Remove Package', 'cmb2' ),
                'sortable'      => true, // beta
                // 'closed'     => true, // true to have the groups closed by default
            ),
        ) );

        $cmb->add_group_field( $group_field_id, array(
            'name' => 'Tracking #',
            'id'   => 'tracking',
            'type' => 'text',
//             'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ) );   $cmb->add_group_field( $group_field_id, array(
            'name' => 'Carrier',
            'id'   => 'carrier',
            'type' => 'select',
            'options'=>[
                'ups'=>'UPS',
                'fedex'=>'Fedex',
                'usps'=>'US Mail'
            ]
//             'repeatable' => true, // Repeatable fields are supported w/in repeatable groups (for most types)
        ) );

        $cmb->add_group_field( $group_field_id, array(
            'name' => 'Package Images',
            'id'   => 'image',
            'type' => 'file_list',
            'preview_size'=>['100','100']
        ) );

    }

	function action_callback_method_name() {
		// TODO define your action method here
	}

	function filter_callback_method_name() {
		// TODO define your filter method here
	}

	function render_shortcode($atts) {
		// Extract the attributes
		extract(shortcode_atts(array(
			'attr1' => 'foo', //foo is a default value
			'attr2' => 'bar'
			), $atts));
		// you can now access the attribute values using $attr1 and $attr2
	}

	/**
	 * Registers and enqueues stylesheets for the administration panel and the
	 * public facing site.
	 */
	private function register_scripts_and_styles() {
		if ( is_admin() ) {
			$this->load_file( self::slug . '-admin-script', '/js/admin.js', true );
			$this->load_file( self::slug . '-admin-style', '/css/admin.css' );
		} else {
			$this->load_file( self::slug . '-script', '/js/script.js', true );
			$this->load_file( self::slug . '-style', '/css/style.css' );
		} // end if/else
	} // end register_scripts_and_styles

	/**
	 * Helper function for registering and enqueueing scripts and styles.
	 *
	 * @name	The 	ID to register with WordPress
	 * @file_path		The path to the actual file
	 * @is_script		Optional argument for if the incoming file_path is a JavaScript source file.
	 */
	private function load_file( $name, $file_path, $is_script = false ) {

		$url = plugins_url($file_path, __FILE__);
		$file = plugin_dir_path(__FILE__) . $file_path;

		if( file_exists( $file ) ) {
			if( $is_script ) {

				wp_enqueue_script($name, $url, array('jquery'), false, true ); //depends on jquery
			} else {

				wp_enqueue_style( $name, $url );
			} // end if
		} // end if

	} // end load_file

} // end class
new WooCommerceMultiShipmentTracking();

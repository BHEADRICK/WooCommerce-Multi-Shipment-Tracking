<?php
/*
Plugin Name: WooCommerce Multi-Shipment Tracking
Plugin URI:
Description: Adds the ability to include multiple tracking numbers on the order as well as multiple shipment photos
to be included with the Order Complete Email

Version: 1.0.0
Author: Bryan Headrick
Author URI: https://catmanstudios.com
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

	var $carriers;
	/**
	 * Constructor
	 */
	function __construct() {
		//register an activation hook for the plugin
		register_activation_hook( __FILE__, array( $this, 'install_woocommerce_multi_shipment_tracking' ) );

		//Hook up to the init action
		add_action( 'init', array( $this, 'init_woocommerce_multi_shipment_tracking' ) );
        add_action('woocommerce_email_order_meta', array($this, 'email_content'), 10, 4);
        add_action( 'woocommerce_order_actions', array( $this, 'order_complete_action' ) );
        add_action( 'woocommerce_order_action_pw_order_shipped', array( $this, 'process_order_shipped' ) );
        add_action('woocommerce_view_order', [$this,'order_tracking_details']);

	}

	private function get_carrier_titles(){

	    $carrier_titles = [''=>'Select Carrier'];

	    foreach($this->carriers as $key=>$carrier){
	        $carrier_titles[$key] = $carrier['title'];
        }
        return $carrier_titles;

    }

    private function get_carrier_name($carrier_type){
	    return $this->carriers[$carrier_type]['title'];
    }

    private function get_carrier_url($carrier_type, $tracking){
        $url = $this->carriers[$carrier_type]['url'];

        return str_replace('[tracking_no]', $tracking, $url);
    }

    public function order_tracking_details($order_id, $email = false){
        $tracking = get_post_meta($order_id, '_packages', true);

        if($tracking && is_array($tracking)):


            echo '<h3>Tracking</h3>
<table id="tracking" class="td" style="width: 100%; font-family: \'Helvetica Neue\', Helvetica, Roboto, Arial, sans-serif; color: #737373; border: 1px solid #e4e4e4;">';
            if(count($tracking)>0){
                echo '<tr></tr><th>Package</th><th class="tracking">Tracking#</th><th class="track-link">Track</th></tr>';
                foreach($tracking  as $ix=>$package){
                    $url = $this->get_carrier_url($package['carrier'], $package['tracking']);
                    echo '<tr>
		<td>
		Package #'.($ix+1).' of '.count($tracking) .'
</td><td class="tracking">'. $package['tracking'].' (' . $this->get_carrier_name($package['carrier']) . ')</td>
<td class="tracking-link"><a target="_blank" href="'.$url. '">'. ($email?$url:'Track') .' </a></td>';

echo '</tr>';

                    if(isset($package['desc'])){
                        echo '<tr ><td colspan="3">'.$package['desc'].'</td></tr>';
                    }
                }
            }



            echo '</table>';

            echo '<p><small>*If the tracking link is not working, your tracking information may need additional time to upload into the carrier\'s system. Please check back or retry tomorrow</small><p/>';
        endif;
    }

	public function email_content( $order, $sent_to_admin, $plain_text, $email ){

      $this->order_tracking_details($order->get_id(), true);
    }

    public function order_complete_action($actions){
        $actions['pw_order_shipped'] = __( 'Mark order as Shipped (Completed)', self::slug );
        return $actions;

    }
    public function process_order_shipped($order){
        $order->update_status('wc-completed', 'Order Shipped');
        do_action( 'woocommerce_order_action_send_email_customer_invoice' , $order );
        $mailer = WC()->mailer();

        $email_to_send = 'customer_completed_order';

        $mails = $mailer->get_emails();

        if ( ! empty( $mails ) ) {
            foreach ( $mails as $mail ) {
                if ( $mail->id == $email_to_send ) {
                    $mail->trigger( $order->get_id(), $order );
                    $order->add_order_note( sprintf( __( '%s email notification manually sent.', 'woocommerce' ), $mail->title ), false, true );
                }
            }
        }
    }

	/**
	 * Runs when the plugin is activated
	 */


	/**
	 * Runs when the plugin is initialized
	 */
	function init_woocommerce_multi_shipment_tracking() {

        $carriers =  ['ups'=>[
            'title'=>'UPS',
            'url'=> 'http://wwwapps.ups.com/WebTracking/track?track=yes&trackNums=[tracking_no]'],
            'fedex'=>['url'=>'http://www.fedex.com/Tracking?tracknumbers=[tracking_no]',
                'title'=>'Fedex'
            ],
            'usps'=>
                ['url'=>'http://trkcnfrm1.smi.usps.com/PTSInternetWeb/InterLabelInquiry.do?origTrackNum=[tracking_no]',
                    'title'=>'US Mail'],
            'averitt'=>[
                'url'=>'https://www.averittexpress.com/trackLTLById.avrt?serviceType=LTL&resultsPageTitle=LTL+Tracking+by+PRO+and+BOL&trackPro=[tracking_no]',
                'title'=>'Averitt Express'
            ],
            'xpo'=>[
                'url'=>'http://www.xpo.com/tracking/[tracking_no]/0/CON_WAY',
                'title'=>'XPO'
            ],
            'fedex_small'=>[
                'url'=>'https://www.fedex.com/apps/fedextrack/?tracknumbers=[tracking_no]&cntry_code=us',
                'title'=>'Fedex Small Parcel'
            ],
            'saia'=>[
                'url'=>'http://www.saia.com/Tracing/AjaxProstatusByPro.aspx?&PRONum1=[tracking_no]',
                'title'=>'Saia'
            ]
        ];
            $this->carriers = apply_filters('multi_shipment_tracking_carriers',$carriers, 10);
		// Setup localization
		load_plugin_textdomain( self::slug, false, dirname( plugin_basename( __FILE__ ) ) . '/lang' );
		// Load JavaScript and stylesheets
		$this->register_scripts_and_styles();



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
            'description' => __( 'List of Packages for order', 'cmb2' ),
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

        ) );   $cmb->add_group_field( $group_field_id, array(
            'name' => 'Carrier',
            'id'   => 'carrier',
            'type' => 'select',
            'options'=>$this->get_carrier_titles()

        ) );

        $cmb->add_group_field( $group_field_id, array(
            'name' => 'Package Images',
            'id'   => 'image',
            'type' => 'file_list',
            'preview_size'=>['100','100']
        ) );

        $cmb->add_group_field($group_field_id, [
            'name'=>'Package Description',
            'id'=>'desc',
            'type'=>'text'
        ]);

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
		} 
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

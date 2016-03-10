<?php
/**
 * @class         WC_Split_Orer
 * @since         
 * @package       WooCommerce Split Order
 * @license       http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

if ( ! class_exists( 'WC_Split_Order' ) ) :

/**
 * WC_Split_Order Class.
 */
class WC_Split_Order {

	/**
	 * Original Order
	 *
	 * @access private
	 * @var    WC_Order
	 */
	private $orignal_order;

	/**
	 * Current User
	 *
	 * @access private
	 * @var    WC_Customer
	 */
	private $current_user;

    /**
     * Line Items
     *
     * @access private
     * @var    array
     */
    private $line_items;

    /**
     * New Order ID
     *
     * @access private
     * @var    int
     */
    private $newOrderID;

	/**
     * Class constructor
     *
     * @access public
     * @param 
     */
    public function __construct( $order_id, $line_items ) {
    	$this->original_order = wc_get_order( $order_id );
    	$this->current_user = wp_get_current_user();
    	$this->line_items = $line_items;

    	$this->createOrder();
    	$this->updateOrderHeader();
    	$this->updateShippingFlds();
    	$this->updateBillingFlds();
    }

	/**
     * Create Order
     *
     * @access 	private
     * @param 	void
     * @return 	bool
     */
    private function createOrder() {
		//1 Create Order
	    $order_data =  array(
	        'post_type'     => 'shop_order',
	        'post_title'    => sprintf( __( 'Auto-Ship Order &ndash; %s', 'woocommerce' ), strftime( _x( '%b %d, %Y @ %I:%M %p', 'Order date parsed by strftime', 'woocommerce' ) ) ),
	        'post_status'   => 'publish',
	        'ping_status'   => 'closed',
	        'post_excerpt'  => 'Auto-Ship Order based on original order ' . $this->original_order->id,
	        'post_author'   => $this->current_user->ID,
	        'post_password' => uniqid( 'order_' )   // Protects the post just in case
	    );

	    $this->newOrderID = wp_insert_post( $order_data, true );

	    if ( is_wp_error( $this->newOrderID ) ) {
		   return false;
		}

		return true;
    }

   	/**
     * Create Order
     *
     * @access 	private
     * @param 	void
     * @return 	void
     */
   	private function updateOrderHeader() {
   		$original_order_id = $this->original_order->id;
		update_post_meta( $this->newOrderID, '_order_shipping',         get_post_meta($original_order_id, '_order_shipping', true) );
        update_post_meta( $this->newOrderID, '_order_discount',         get_post_meta($original_order_id, '_order_discount', true) );
        update_post_meta( $this->newOrderID, '_cart_discount',          get_post_meta($original_order_id, '_cart_discount', true) );
        update_post_meta( $this->newOrderID, '_order_tax',              get_post_meta($original_order_id, '_order_tax', true) );
        update_post_meta( $this->newOrderID, '_order_shipping_tax',     get_post_meta($original_order_id, '_order_shipping_tax', true) );
        update_post_meta( $this->newOrderID, '_order_total',            get_post_meta($original_order_id, '_order_total', true) );

        update_post_meta( $this->newOrderID, '_order_key',              'wc_' . apply_filters('woocommerce_generate_order_key', uniqid('order_') ) );
        update_post_meta( $this->newOrderID, '_customer_user',          get_post_meta($original_order_id, '_customer_user', true) );
        update_post_meta( $this->newOrderID, '_order_currency',         get_post_meta($original_order_id, '_order_currency', true) );
        update_post_meta( $this->newOrderID, '_prices_include_tax',     get_post_meta($original_order_id, '_prices_include_tax', true) );
        update_post_meta( $this->newOrderID, '_customer_ip_address',    get_post_meta($original_order_id, '_customer_ip_address', true) );
        update_post_meta( $this->newOrderID, '_customer_user_agent',    get_post_meta($original_order_id, '_customer_user_agent', true) );
   	}

   	/**
     * Update billing address fields
     *
     * @access 	private
     * @param 	void
     * @return 	void
     */
   	private function updateBillingFlds() {
   		$original_order_id = $this->original_order->id;
   		update_post_meta( $this->newOrderID, '_billing_city',           get_post_meta($original_order_id, '_billing_city', true));
        update_post_meta( $this->newOrderID, '_billing_state',          get_post_meta($original_order_id, '_billing_state', true));
        update_post_meta( $this->newOrderID, '_billing_postcode',       get_post_meta($original_order_id, '_billing_postcode', true));
        update_post_meta( $this->newOrderID, '_billing_email',          get_post_meta($original_order_id, '_billing_email', true));
        update_post_meta( $this->newOrderID, '_billing_phone',          get_post_meta($original_order_id, '_billing_phone', true));
        update_post_meta( $this->newOrderID, '_billing_address_1',      get_post_meta($original_order_id, '_billing_address_1', true));
        update_post_meta( $this->newOrderID, '_billing_address_2',      get_post_meta($original_order_id, '_billing_address_2', true));
        update_post_meta( $this->newOrderID, '_billing_country',        get_post_meta($original_order_id, '_billing_country', true));
        update_post_meta( $this->newOrderID, '_billing_first_name',     get_post_meta($original_order_id, '_billing_first_name', true));
        update_post_meta( $this->newOrderID, '_billing_last_name',      get_post_meta($original_order_id, '_billing_last_name', true));
        update_post_meta( $this->newOrderID, '_billing_company',        get_post_meta($original_order_id, '_billing_company', true));
   	}

   	/**
   	 * Update shipping address fields
   	 *
   	 * @access 	private
   	 * @param 	void
   	 * @return 	void
   	 */
   	private function updateShippingFlds() {
   		$original_order_id = $this->original_order->id;
   		update_post_meta( $this->newOrderID, '_shipping_country',       get_post_meta($original_order_id, '_shipping_country', true));
        update_post_meta( $this->newOrderID, '_shipping_first_name',    get_post_meta($original_order_id, '_shipping_first_name', true));
        update_post_meta( $this->newOrderID, '_shipping_last_name',     get_post_meta($original_order_id, '_shipping_last_name', true));
        update_post_meta( $this->newOrderID, '_shipping_company',       get_post_meta($original_order_id, '_shipping_company', true));
        update_post_meta( $this->newOrderID, '_shipping_address_1',     get_post_meta($original_order_id, '_shipping_address_1', true));
        update_post_meta( $this->newOrderID, '_shipping_address_2',     get_post_meta($original_order_id, '_shipping_address_2', true));
        update_post_meta( $this->newOrderID, '_shipping_city',          get_post_meta($original_order_id, '_shipping_city', true));
        update_post_meta( $this->newOrderID, '_shipping_state',         get_post_meta($original_order_id, '_shipping_state', true));
        update_post_meta( $this->newOrderID, '_shipping_postcode',      get_post_meta($original_order_id, '_shipping_postcode', true));
   	}

   	/**
   	 * Add line items
   	 *
   	 * @access 	private
   	 * @param 	void
   	 * @return 	void
   	 */
   	private function addLineItems() {
   		foreach( $this->original_order->get_items() as $originalOrderItem ) {

            $itemName = $originalOrderItem['name'];
            $qty = $originalOrderItem['qty'];
            $lineTotal = $originalOrderItem['line_total'];
            $lineTax = $originalOrderItem['line_tax'];
            $productID = $originalOrderItem['product_id'];

            $item_id = wc_add_order_item( $order_id, array(
                'order_item_name'       => $itemName,
                'order_item_type'       => 'line_item'
            ) );

            wc_add_order_item_meta( $item_id, '_qty', $qty );
            //wc_add_order_item_meta( $item_id, '_tax_class', $_product->get_tax_class() );
            wc_add_order_item_meta( $item_id, '_product_id', $productID );
            //wc_add_order_item_meta( $item_id, '_variation_id', $values['variation_id'] );
            wc_add_order_item_meta( $item_id, '_line_subtotal', wc_format_decimal( $lineTotal ) );
            wc_add_order_item_meta( $item_id, '_line_total', wc_format_decimal( $lineTotal ) );
            wc_add_order_item_meta( $item_id, '_line_tax', wc_format_decimal( '0' ) );
            wc_add_order_item_meta( $item_id, '_line_subtotal_tax', wc_format_decimal( '0' ) );

        }
   	}
}

endif;
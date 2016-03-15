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
	 *	Original Order ID
	 *	integer
	 */
	private $original_order_id;

	/** 
	 *	Original Order 
	 *	WC_Order
	 */
	private $original_order;

	/** 
	 *	Line Items
	 *	Array
	 */
	private $line_items;

	/** 
	 *	Line Items
	 *	Array
	 */
	public $newOrderID;

	/**
     * Class constructor
     */
    public function __construct( $order_id, $line_items ) {

    	// Initialize class var
    	$this->original_order_id = $order_id;
    	$this->line_items = $line_items;

    	// Create WC_Order Obj for original order
    	$this->original_order = wc_get_order( $this->original_order_id );

    	// Main Function
    	$this->Main();
    }

   	/**
     * Main
     * @param  void
     * @return bool
     */
    private function Main() {

    	// Duplicate Order ( Make a new order exactly same as original one )
    	$this->duplicateOrder();

    	// Update New Order ( Remove Items which are not included in new order, Update Items' qty )
    	$this->updateNewOrder();

    	// Update Original Order ( Remove Items which are not included in original order, Update Items' qty )
    	$this->updateOriginalOrder();

    	return true;
    }

    /**
     * Update New Order
     * @param  void
     * @return bool
     */
    private function updateNewOrder() {

    	$newOrderItems = array();

    	$newOrder = wc_get_order( $this->newOrderID );
    	
    	foreach ( $this->line_items as $key => $element ) {
    		
    		// $element[0] : product_id, $element[2] : qty
    		$newOrderItems[$element[1]] = $element[2];
    		
    	}

    	foreach ( $newOrder->get_items() as $key => $newItem) {
    		
    		$newItem_orderID = $key;
    		$newItem_prodID  = $newItem['product_id'];

    		if ( ! isset($newOrderItems[$newItem_prodID]) ) {

    			// Remove this item
    			wc_delete_order_item($newItem_orderID);
    		}
 			else {

 				// Update this item's qty
 				$curProduct = new WC_Product( $newItem_prodID );
 				$newOrder->update_product( $newItem_orderID, $curProduct, array( "qty" => $newOrderItems[$newItem_prodID] ) );
 			}
    		
    	}

    	$newOrder->calculate_totals();

    	return true;
    }

    /**
     * Update Original Order
     * @param  void
     * @return bool
     */
    private function updateOriginalOrder() {

    	$newOrderItems = array();
    	
    	foreach ( $this->line_items as $key => $element ) {
    		
    		// $element[0] : order_item_id, $element[2] : qty
    		$newOrderItems[$element[0]] = $element[2];
    		
    	}

    	foreach ( $this->original_order->get_items() as $key => $originalItem ) {

    		$originalItem_orderID = $key; // $key : order_item_id
    		$originalItem_qty 	  = $originalItem['qty'];

    		if ( isset($newOrderItems[$originalItem_orderID]) ) {
    			if ( $originalItem_qty == $newOrderItems[$originalItem_orderID] ) {

    				// Remove this item
    				wc_delete_order_item($originalItem_orderID);
    			} else {

    				// Update this item's qty
    				$curProduct = new WC_Product( $originalItem['product_id'] );
    				$newQtyVal	= $originalItem_qty - $newOrderItems[$originalItem_orderID];
 					$this->original_order->update_product( $originalItem_orderID, $curProduct, array( "qty" => $newQtyVal ) );
    			}    			
    		}
   
    	}

    	$this->original_order->calculate_totals();

    	return true;
    }

	/**
     * Duplicate Order
     * @param  void
     * @return bool
     */
    private function duplicateOrder() {

    	// Create Order ; post
    	$this->createOrder();
    	
    	// Add Order Data ; postmeta
    	$this->duplicateOrderData();
    	
    	// Duplicate Items - line_item, shipping and coupon ; order_item, order_itemmeta
    	$this->duplicateLineItems();
    	$this->duplicateShippingItems();
    	$this->duplicateCouponItems();

    	return true;
    }

    /**
     * Create Order
     * @param  void
     * @return bool
     */
    private function createOrder() {

    	$original_PostData = get_post($this->original_order_id, "ARRAY_A");
    	unset($original_PostData['ID']);
    	
    	$new_PostData = $original_PostData;

    	$this->newOrderID = wp_insert_post( $new_PostData, true );

    	if ( is_wp_error( $this->newOrderID ) ) {
    		return false;
    	}

    	return true;
    }

    /**
     * Duplicate Post Meta Data, Update Order Number
     * @param  void
     * @return bool
     */
    private function duplicateOrderData() {

    	$new_MetaData = get_metadata( "post", $this->original_order_id );

    	foreach ( $new_MetaData as $key => $value ) {

    		// $key : post meta key, $value[0] : post meta value
    		update_metadata( "post", $this->newOrderID, $key, $value[0] );
    	
    	}

    	$this->updateOrderNumber();

    	return true;
    }

    /**
     * Duplicate Line Items
     * @param  void
     * @return bool
     */
    private function duplicateLineItems() {

    	foreach ( $this->original_order->get_items() as $originalOrderItem) {
    		$itemName 	 = $originalOrderItem['name'];
            $itemQty 	 = $originalOrderItem['qty'];
            $tax_class 	 = $originalOrderItem['tax_class'];
            $productID	 = $originalOrderItem['product_id'];
            $variationID = $originalOrderItem['variation_id'];
            $subtotal    = $originalOrderItem['line_subtotal'];
            $line_total  = $originalOrderItem['line_total'];
            $subttl_tax  = $originalOrderItem['line_subtotal_tax'];
            $line_tax 	 = $originalOrderItem['line_tax'];
            $line_tax_d  = $originalOrderItem['line_tax_data'];

            $item_id = wc_add_order_item( $this->newOrderID, array(
                'order_item_name'       => $itemName,
                'order_item_type'       => 'line_item'
            ) );

            wc_add_order_item_meta( $item_id, '_qty', $itemQty );
            wc_add_order_item_meta( $item_id, '_tax_class', $tax_class );
            wc_add_order_item_meta( $item_id, '_product_id', $productID );
            wc_add_order_item_meta( $item_id, '_variation_id', $variationID );
            wc_add_order_item_meta( $item_id, '_line_subtotal', $subtotal );
            wc_add_order_item_meta( $item_id, '_line_total', $line_total );
            wc_add_order_item_meta( $item_id, '_line_subtotal_tax', $subttl_tax );
            wc_add_order_item_meta( $item_id, '_line_tax', $line_tax );
            wc_add_order_item_meta( $item_id, '_line_tax_data', $line_tax_d );
    	}

    	return true;
    }

    /**
     * Duplicate Shipping Items
     * @param  void
     * @return bool
     */
    private function duplicateShippingItems() {

 		$original_order_shipping_items = $this->original_order->get_items('shipping');
        
        foreach ( $original_order_shipping_items as $original_order_shipping_item ) {

            $item_id = wc_add_order_item( $this->newOrderID, array(
                'order_item_name' => $original_order_shipping_item['name'],
                'order_item_type' => 'shipping'
            ) );

            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, 'method_id', $original_order_shipping_item['method_id'] );
                wc_add_order_item_meta( $item_id, 'cost', wc_format_decimal( $original_order_shipping_item['cost'] ) );
            }
        }

        return true;
    }

    /**
     * Duplicate Coupon Items
     * @param  void
     * @return bool
     */
    private function duplicateCouponItems() {

 		$original_order_coupons = $this->original_order->get_items('coupon');

        foreach ( $original_order_coupons as $original_order_coupon ) {
            $item_id = wc_add_order_item( $this->newOrderID, array(
                'order_item_name' => $original_order_coupon['name'],
                'order_item_type' => 'coupon'
            ) );
            
            if ( $item_id ) {
                wc_add_order_item_meta( $item_id, 'discount_amount', $original_order_coupon['discount_amount'] );
            }
        }

        return true;
    }

    /**
     * Update Original/New Orders' _order_number to show #XXX-X
     * @param  array
     * @return bool
     */
    private function updateOrderNumber() {

    	// _child_count is a custom meta field that shows whether it is a parent or not
    	$child_count = get_post_meta( $this->original_order_id, '_child_count', true );
		
		// Check if the original order is parent & if the original order is child
		
		if ( empty( $child_count ) ) {	

			// If the original order is not parent
			
			// _parent_id is a custom meta field that shows whether it is child order or not
			$parent_id = get_post_meta( $this->original_order_id, '_parent_id', true );

			if ( empty( $parent_id ) ) {

				// If the original order is not child

				// Change original order to parent by adding custom field "_child_count"
				update_post_meta( $this->original_order_id, '_child_count', '2' );
				// Change original order's "_order_number" to XXX-1
				update_post_meta( $this->original_order_id, '_order_number', $this->original_order_id . "-1" );

				// Change new order's "_order_number" to XXX-2
				update_post_meta( $this->newOrderID, '_order_number', $this->original_order_id . "-2" );
				// Change new order to child by adding custom field "_parent_id"
				update_post_meta( $this->newOrderID, '_parent_id', $this->original_order_id );
			} 
			else {

				// If the original order is child

				// Get order number index from parent id
				$child_count = get_post_meta( $parent_id, '_child_count', true );

				// Update parent order's child count for next child's order number
				update_post_meta( $parent_id, '_child_count', $child_count + 1 );

				// Set current order's _order_number 
				update_post_meta( $this->newOrderID, '_order_number', $parent_id . '-' . ( $child_count + 1 ) );
			}
		}
		else {

			// If the order is primary

            // Increase primary order's _child_count value for next child order
			update_post_meta( $this->original_order_id, '_child_count', $child_count + 1 );

            // Set new order's _order_number to parent_id - child_count+1
			update_post_meta( $this->newOrderID, '_order_number', $this->original_order_id . '-' . ( $child_count + 1 ) );
            // Set new order's _parent_id to primary's id
			update_post_meta( $this->newOrderID, '_parent_id', $this->original_order_id );
            // As new order has all meta fields same as primary order, it has _child_count. So it should remove it.
			delete_post_meta( $this->newOrderID, '_child_count' );
		}

    	return true;
    }

}

endif;
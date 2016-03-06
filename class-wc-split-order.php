<?php
/**
 * @class         WSC_Country_Sales
 * @since         1.4
 * @package       WooCommerce Sales by Country
 * @subpackage    Base class
 * @author        MH Mithu <mail@mithu.me>
 * @link          https://github.com/mhmithu
 * @license       http://www.gnu.org/licenses/gpl-3.0.html
 *
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly
}

if ( ! class_exists( 'WC_Admin_Dashboard' ) ) :

/**
 * WC_Split_Order Class.
 */
class WC_Split_Order {

	/**
	 * Order Item Data
	 *
	 * @access public
	 * @var    array
	 */
	public $order_items;

	/**
     * Class constructor
     *
     * @access public
     * @param 
     */
    public function __construct( $post ) {

      	$this->order_items = array();
      	$order = new WC_Order( $post );
	    $items = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item['product_id'];
			$product_name = $item['name'];
			array_push( $this->order_items, array($product_id, $product_name) );
		}

		// Enqueue script hook
		$this->wso_enqueue();
    }

    public function output_metabox_content() {
?>
    	<a href="#TB_inline?width=550&height=480&inlineId=split_modal" class="thickbox button">Split Order</a>		
<?php
    	$this->generate_modal_content();
    }

    /**
     * Generate Modal Window Content
     *
     * @access public
     * @return 
     */
    public function generate_modal_content() {
?>
		<div id="split_modal" style="display: none;">
			<div class="list-wrapper">
			    <div class="list1">
			        <select id="parent-order" multiple>
<?php
					foreach( $this->order_items as $item ) {
						echo "<option value='" . $item[0] . "'>" . $item[1] . "</option>";
					}				
?>
		       		</select>
			    </div>
			    <div class="move-btn-wrapper">
			        <a class="button toright">></a>
			        <a class="button toleft"><</a>
			    </div>
			    <div class="list2">
			        <select id="child-order" multiple>
			        </select>
			    </div>
			    <a class="button modal-close">Close</a>
			</div>
		</div>
<?php
    }

    /**
     * Plugin script/style enqueue function
     *
     * @access public
     * @return void
     */
    public function wso_enqueue() {
    	wp_enqueue_style( 'ws-style', plugins_url( 'wso_style.css', __FILE__ ) );
        wp_enqueue_script( 'wso-script', plugins_url( 'wso_script.js', __FILE__ ), array(), '1.0.0', true);
    }

}

endif;
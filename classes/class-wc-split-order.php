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
     * Order ID
     *
     * @access public
     * @var    integer
     */
    public $order_ID;

	/**
     * Class constructor
     *
     * @access public
     * @param 
     */
    public function __construct( $post ) {

        $this->order_ID = absint( $post->ID );
      	$this->order_items = array();
      	$order = new WC_Order( $post );
	    $items = $order->get_items();
		foreach ( $items as $item ) {
			$product_id = $item['product_id'];
			$product_name = $item['name'];
            $product_qty = $item['qty'];
			array_push( $this->order_items, array($product_id, $product_name, $product_qty) );
		}

		// Enqueue script hook
		$this->wso_enqueue();

		// add action save_post
		add_action( 'save_post', array( $this, 'splitCurrentOrder' ), 1000, 1 );
    }

    public function output_metabox_content() {
?>
	   <a href="#TB_inline?width=600&height=550&inlineId=split-order-modal" class="button thickbox open-modal">Split Order</a>   
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
        <div id="split-order-modal" style="display:none;">
            <div class="modal-window-wrapper">
                <table class="itemlist-wrapper">
                    <tbody>
                        <tr>
                            <td>Add?</td>
                            <td>Product Name</td>
                            <td>Quantity</td>
                        </tr>
                        <?php
                            foreach ( $this->order_items as $item )
                            {
                                echo "<tr data-id='" . $item[0] . "'>";
                                echo "<td><input type='checkbox' class='flag_add'></td>";
                                echo "<td>" . $item[1] . "</td>";
                                echo "<td><input type='number' min='1' class='order-item-qty' max='" . $item[2] . "'></td>";
                                echo "</tr>";
                            }
                        ?>
                    </tbody>
                </table>
                <div class="button-wrapper" data-id="<?php echo $this->order_ID; ?>">
                    <a class="button button-primary split-order">Split Order</a>
                    <a class="button close-modal">Cancel</a>
                </div>
            </div>
        </div>
<?php
    }

    /**
     * Split Current Order into Origin one and child one 
     *
     * @access public
     * @return void
     */
    public function splitCurrentOrder() {
    
    }

    /**
     * Plugin script/style enqueue function
     *
     * @access public
     * @return void
     */
    public function wso_enqueue() {
        wp_enqueue_style( 'ws-style-custom', plugins_url( 'css/wso_style.css', dirname(__FILE__) ) );
        wp_enqueue_script( 'wso-script-main', plugins_url( 'js/wso_script.js', dirname(__FILE__) ), array(), '1.0.0', true);
    }

}

endif;
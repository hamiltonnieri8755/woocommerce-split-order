<a href="#TB_inline?width=600&height=550&inlineId=split-order-modal" class="button thickbox open-modal">Split Order</a> 
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
                        echo "<tr data-id='" . $item[0] . "' data-product_id='" . $item[1] . "'>";
                        echo "<td><input type='checkbox' class='flag_add'></td>";
                        echo "<td>" . $item[2] . "</td>";
                        echo "<td><input type='number' min='1' class='order-item-qty' max='" . $item[3] . "'></td>";
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
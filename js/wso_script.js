jQuery(".open-modal").click( function () {

	jQuery(".order-item-qty").each( function () {
		jQuery(this).val(jQuery(this).attr("max"));
		jQuery(this).css( "display", "none");
	})

	jQuery(".flag_add").prop("checked", false);

});

function onQtyChange() {

	var curVal = parseInt( jQuery(this).val() );
	var minVal = parseInt( jQuery(this).attr('min') );
	var maxVal = parseInt( jQuery(this).attr('max') );

	if ( curVal > maxVal ) {
		alert("Please input qty under max value " + maxVal );
		jQuery(this).val( maxVal );
	}
	if ( curVal < minVal ) {
		alert("Please input qty above min value " + minVal );
		jQuery(this).val( minVal );
	}

}
jQuery(".order-item-qty").keyup( onQtyChange );

function onAddCheckboxChange() {

	if ( jQuery(this).prop("checked") )	{
		jQuery(this).parent().parent().find(".order-item-qty").css( "display", "block" );
	}
	else {
		jQuery(this).parent().parent().find(".order-item-qty").css( "display", "none" );
	}
}
jQuery(".flag_add").change( onAddCheckboxChange );

jQuery(".close-modal").click( function () {
	tb_remove();
});

jQuery(".split-order").click( function () {

	var newOrderData = [];
	var isNull = true;
	var newOrderItemCnt = 0;
	var totalItemCnt = 0;
	
	jQuery(".itemlist-wrapper .order-item-qty").each( function () { totalItemCnt += parseInt( jQuery(this).attr('max') ); })

	jQuery(".itemlist-wrapper tr").each( function () {

		if ( jQuery(this).find(".flag_add").prop("checked") )
		{
			var orderItemID = jQuery(this).data("id");
			var productID	= jQuery(this).data("product_id");
			var curVal 		= jQuery(this).find(".order-item-qty").val();

			newOrderData.push( [orderItemID, productID, curVal] );

			isNull = false; 

			newOrderItemCnt += parseInt( curVal );
		}
	})

	if ( isNull == true ) {
		alert("Please select at least one product");
		return;
	}
	if ( totalItemCnt == newOrderItemCnt ) {
		alert("Please do not select all products");
		return;
	}

	var orderData = {};
	orderData["order_id"] = jQuery(this).parent().data("id");
	orderData.line_items = newOrderData;

	console.log(orderData);

	jQuery.post( ajaxurl, { 'action':'split_order', 'orderData' : orderData }, function (result) {
		console.log(result);
	});

});

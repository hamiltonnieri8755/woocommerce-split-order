jQuery(".toleft").click(function () {
	jQuery(".list1 select").find(':selected').remove().appendTo(jQuery(".list2 select"));
});

jQuery(".toright").click(function () {
	jQuery(".list2 select").find(':selected').remove().appendTo(jQuery(".list1 select"));
});

jQuery(".modal-close").click(function () {
	tb_remove();
})
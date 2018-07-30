(function($) {
	window.Refund = {
		emptyRefundMessage: 'Empty refund',
		
		change: function(e) {
			var num = $(this).attr('name').match(/\d+/)[0];

			var price_element = $('#completion-form input[name="item[' + num + '][unit_price]"]');
			var quantity_element = $('#completion-form input[name="item[' + num + '][quantity]"]');
			
			var quantity = quantity_element.val();
			var rest_quantity = quantity_element.attr('max') - quantity;

			var full_price = parseFloat(price_element.attr('max')) + parseFloat(price_element.attr('min'));
			var reduction = parseFloat(price_element.val());

			var total = (rest_quantity * reduction) + (quantity * full_price);
			
			$('#completion-form input[name="item[' + num + '][total]"]').val(total.toFixed(2));

			Refund.calculateTotalTotal();
		},
		
		setEmptyError: function(message) {
			Refund.emptyRefundMessage = message;
		},

		fullRefund: function() {
			$('#completion-form input[name$="[quantity]"]').each(function() {
				$(this).val($(this).attr('max'));
				$(this).change();
			});
		},

		submit: function(e) {
			if($('#line-item-total').html() == '0.00') {
				alert(Refund.emptyRefundMessage);
				e.preventDefault();
			}
		},

		calculateTotalTotal: function() {
			$('#line-item-total').html(0);
			var total = 0
			$('#completion-form input[type=text]').each(function() {
				if($(this).attr('name').indexOf('total') !== -1) {
					total += parseFloat($(this).val());
				}
			});
			$('#line-item-total').html(total.toFixed(2));
		},

		reset: function() {
			$('#completion-form input[name$="[quantity]"]').each(function() {
				$(this).val($(this).attr('min'));
				$(this).change(); 
			});
		},

		init: function() {
			$("#completion-form input[type=number]").on("change", Refund.change);
			$("#full-refund").on("click", Refund.fullRefund);
			$("#completion-form").on("submit", Refund.submit);
			$("#completion-form").on("reset", Refund.reset);
			Refund.calculateTotalTotal();
		}
	}
})(jQuery);
(function($) {
	window.PostFinanceCheckout = {
		handler : null,
		methodConfigurationId : null,
		running : false,

		initialized : function() {
			$('#button-confirm').removeAttr('disabled');
			$('#postfinancecheckout-iframe-spinner').hide();
			$('#postfinancecheckout-iframe-container').show();
			$('#button-confirm').click(function(event) {
				PostFinanceCheckout.handler.validate();
				$('#button-confirm').attr('disabled', 'disabled');
			});
		},

		submit : function() {
			if(!PostFinanceCheckout.running) {
				PostFinanceCheckout.running = true;
				$.getJSON('index.php?route=extension/payment/postfinancecheckout_'
						+ PostFinanceCheckout.methodConfigurationId + '/confirm', '', function(data,
						status, jqXHR) {
					if (data.status) {
						PostFinanceCheckout.handler.submit();
					} else {
						alert(data.message);
						$('#button-confirm').removeAttr('disabled');
					}
					PostFinanceCheckout.running = false;
				});
			}
		},

		validated : function(result) {
			if (result.success) {
				PostFinanceCheckout.submit();
			} else {
				$('#button-confirm').removeAttr('disabled');
			}
		},

		init : function(methodConfigurationId) {
			if (typeof window.IframeCheckoutHandler === 'undefined') {
				setTimeout(function() {
					PostFinanceCheckout.init(methodConfigurationId);
				}, 500);
			} else {
				PostFinanceCheckout.methodConfigurationId = methodConfigurationId;
				PostFinanceCheckout.handler = window
						.IframeCheckoutHandler(methodConfigurationId);
				PostFinanceCheckout.handler.setInitializeCallback(this.initialized);
				PostFinanceCheckout.handler.setValidationCallback(this.validated);
				PostFinanceCheckout.handler.create('postfinancecheckout-iframe-container');
			}
		}
	}
})(jQuery);
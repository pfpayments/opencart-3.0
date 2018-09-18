(function($) {
	window.PostFinanceCheckout = {
		handler : null,
		methodConfigurationId : null,
		running : false,
		initCalls : 0,
		initMaxCalls : 10,

		initialized : function() {
			$('#button-confirm').removeAttr('disabled');
			$('#postfinancecheckout-iframe-spinner').hide();
			$('#postfinancecheckout-iframe-container').show();
			$('#button-confirm').click(function(event) {
				PostFinanceCheckout.handler.validate();
				$('#button-confirm').attr('disabled', 'disabled');
			});
		},

		fallback : function(methodConfigurationId) {
			PostFinanceCheckout.methodConfigurationId = methodConfigurationId;
			$('#button-confirm').click(PostFinanceCheckout.submit);
			$('#button-confirm').removeAttr('disabled');
			$('#postfinancecheckout-iframe-spinner').toggle();
		},

		submit : function() {
			if (!PostFinanceCheckout.running) {
				PostFinanceCheckout.running = true;
				$.getJSON('index.php?route=extension/payment/postfinancecheckout_'
						+ PostFinanceCheckout.methodConfigurationId
						+ '/confirm', '', function(data, status, jqXHR) {
					if (data.status) {
						if(PostFinanceCheckout.handler) {
							PostFinanceCheckout.handler.submit();
						}
						else {
							window.location.assign(data.redirect);
						}
					}
					else {
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
			PostFinanceCheckout.initCalls++;
			if (typeof window.IframeCheckoutHandler === 'undefined') {
				if (PostFinanceCheckout.initCalls < PostFinanceCheckout.initMaxCalls) {
					setTimeout(function() {
						PostFinanceCheckout.init(methodConfigurationId);
					}, 500);
				} else {
					PostFinanceCheckout.fallback(methodConfigurationId);
				}
			} else {
				PostFinanceCheckout.methodConfigurationId = methodConfigurationId;
				PostFinanceCheckout.handler = window
						.IframeCheckoutHandler(methodConfigurationId);
				PostFinanceCheckout.handler
						.setInitializeCallback(this.initialized);
				PostFinanceCheckout.handler
						.setValidationCallback(this.validated);
				PostFinanceCheckout.handler
						.create('postfinancecheckout-iframe-container');
			}
		}
	}
})(jQuery);
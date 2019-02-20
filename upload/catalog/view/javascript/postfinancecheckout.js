(function($) {
	window.PostFinanceCheckout = {
		handler : null,
		methodConfigurationId : null,
		running : false,
		initCalls : 0,
		initMaxCalls : 10,
		confirmationButtonSources: ['#button-confirm', '#journal-checkout-confirm-button'],

		initialized : function() {
			$('#postfinancecheckout-iframe-spinner').hide();
			$('#postfinancecheckout-iframe-container').show();
			PostFinanceCheckout.enableConfirmButton();
			$('#button-confirm').click(function(event) {
				PostFinanceCheckout.handler.validate();
				PostFinanceCheckout.disableConfirmButton();
			});
		},

		fallback : function(methodConfigurationId) {
			PostFinanceCheckout.methodConfigurationId = methodConfigurationId;
			$('#button-confirm').click(PostFinanceCheckout.submit);
			$('#postfinancecheckout-iframe-spinner').toggle();
			PostFinanceCheckout.enableConfirmButton();
		},
		
		reenable: function() {
			PostFinanceCheckout.enableConfirmButton();
			if($('html').hasClass('quick-checkout-page')) { // modifications do not work for js
				triggerLoadingOff();
			}
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
						PostFinanceCheckout.reenable();
					}
					PostFinanceCheckout.running = false;
				});
			}
		},

		validated : function(result) {
			if (result.success) {
				PostFinanceCheckout.submit();
			} else {
				PostFinanceCheckout.reenable();
			}
		},

		init : function(methodConfigurationId) {
			PostFinanceCheckout.initCalls++;
			PostFinanceCheckout.disableConfirmButton();
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
		},
		
		enableConfirmButton : function() {
			for(var i = 0; i < PostFinanceCheckout.confirmationButtonSources.length; i++) {
				var button = $(PostFinanceCheckout.confirmationButtonSources[i]);
				if(button.length) {
					button.removeAttr('disabled');
				}
			}
		},
		
		disableConfirmButton : function() {
			for(var i = 0; i < PostFinanceCheckout.confirmationButtonSources.length; i++) {
				var button = $(PostFinanceCheckout.confirmationButtonSources[i]);
				if(button.length) {
					button.attr('disabled', 'disabled');
				}
			}
		}
	}
})(jQuery);
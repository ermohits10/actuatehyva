require(['jquery', 'jquery/ui', 'jquery/validate', 'mage/translate'],function(jQuery){
	jQuery(document).ready(function() {
		// this file gets loaded on all Magento configuration menus, so make sure we are in the right place!
		var pcl_check = setInterval(function() {
			if (jQuery('#cc_uk_main_options_frontend_accesstoken').length) {
				clearInterval(pcl_check)
				jQuery.validator.addMethod('pcl-token-format', function(value, element) {
					// allow empty field
					if (value == '') return true;

					// attempt to correct typos
					value = value.toLowerCase().replace(/-{2,}/g, "-").replace(/^-|-$|[^a-f0-9-]/g, "");
					jQuery(element).val(value);
		
					// validate token format
					let patt = /(?!xxxxx)^[a-f0-9?]{5}?(-[a-f0-9]{5}){3}?$/;
					if (patt.test(value) || value == '') {
						return this.optional(element) || patt.test(value);
					}
					return false;
				}, 'Please check your access token is formatted correctly or <a href="https://account.craftyclicks.co.uk/#/signup">sign up for a token here</a>.');
			}
		}, 200)
	});
});

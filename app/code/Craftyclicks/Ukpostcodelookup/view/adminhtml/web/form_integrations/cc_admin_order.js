/*
// This is a collection of JavaScript code to allow easy integration of
// the Crafty Clicks postcode / address finder functionality into Magento 2
//
// Provided by www.CraftyClicks.co.uk
//
// Requires standard CraftyClicks JS - tested with v4.9.3
//
// If you copy/use/modify this code - please keep this
// comment header in place
//
// Copyright (c) 2009-2018 Crafty Clicks (http://www.craftyclicks.com)
//
// This code relies on jQuery, you must have a reasonably recent version loaded
// in your template. Magento should include it as standard.
//
// If you need any help, contact support@craftyclicks.co.uk - we will help!
//
// Plugin version: magento2-uk 1.1.5
**********************************************************************************/

function CraftyClicksMagento2Class() {
	this.current_setup = 'initial';

	// initial page setup
	this.initial_setup =function() {
		// move fields
		jQuery('#' + this.fields.company_id).closest('.field').before(jQuery('#' + this.fields.country_id).closest('.field'));
		jQuery('#' + this.fields.company_id).closest('.field').before(jQuery('#' + this.fields.postcode_id).closest('.field'));
	}

	this.setup_for_uk = function() {
		// check if we need to do anything
		if ('uk' != this.current_setup) {
			// do the magic for UK

			// add placeholder
			jQuery('#' + this.fields.postcode_id).attr('placeholder', crafty_cfg.txt.search_placeholder);

			// check if button already exists
			if (jQuery('#cp_button_id').length == 0) {
				jQuery('#'+this.fields.postcode_id).wrap('<div class="search-bar"></div>');

				// add button after postcode input
				var tmp_html = '<button id="cp_button_id" type="button" class="action primary"><span>' + crafty_cfg.txt.search_buttontext + '</span></button>';
				jQuery('#'+this.fields.postcode_id).after(tmp_html);

				// add click event listener to button
				var el = document.getElementById('cp_button_id');
				if (el != null) {
					el.addEventListener('click', this.button_clicked.bind(this));
				}

				// add search-list and mage-error after search-bar
				jQuery('#'+this.fields.postcode_id).closest('.search-bar').after('<div id="cp_result_display" class="search-list"></div>');

				jQuery('#'+this.fields.postcode_id).closest('.field').addClass('search-container type_3')
			} else {
				jQuery('#cp_button_id').show();
				jQuery('#cp_result_display').show();
				jQuery('#' + this.fields.postcode_id).closest('.field').addClass("search-container type_3");
			}
		}

		this.current_setup = 'uk';
	}

	this.setup_for_non_uk = function() {
		// check if we need to do anything
		if ('non_uk' != this.current_setup) {
			// hide button and result box if they exist
			if (jQuery('#cp_button_id').length == 1) {
				jQuery('#cp_button_id').hide();
			}

			if (jQuery('#cp_result_display').length == 1) {
				jQuery('#cp_result_display').hide();
			}

			jQuery('#cp_result_display').children('select').remove();

			this.current_setup = 'non_uk';
		}
	}

	// declare our add lookup function, passing in the fields info from Step 4
	this.add_lookup = function(setup) {
		// Step 5 - make sure we only do add_lookup once per instance
		if (jQuery('#'+setup.fields.postcode_id).attr('cc-applied') == "1") {
			return
		}
		cp_obj = CraftyPostcodeCreate(); // instantiate library magic
		this.cp_obj = cp_obj;
		// config
		this.fields = setup.fields;
		this.misc = setup.misc;

		// the set method is shorthand and is equivalent to cp_obj.config['configItem'] = configValue;
		cp_obj.set("result_elem_id", "cp_result_display");
		cp_obj.set("form", "");
		cp_obj.set("elem_company", this.fields.company_id);
		cp_obj.set("elem_street1", this.fields.address_1_id);
		cp_obj.set("elem_street2", this.fields.address_2_id);
		cp_obj.set("elem_postcode", this.fields.postcode_id);
		cp_obj.set("elem_town", this.fields.town_id);
		cp_obj.set("elem_country", this.fields.country_id);

		if (crafty_cfg.advanced.county_data !== 'none') {
			cp_obj.set("elem_county", this.fields.county_id);
		}

		cp_obj.set("container_elem", this.misc.container_elem);

		cp_obj.set("access_token", crafty_cfg.key);
		cp_obj.set("res_autoselect", "0");
		cp_obj.set("single_res_autoselect" , 0);
		cp_obj.set("max_lines" , 1);

		if (crafty_cfg.advanced.county_data == 'traditional') {
			cp_obj.set("traditional_county" , 1);
		} else if (crafty_cfg.advanced.county_data == 'former_postal') {
			cp_obj.set("traditional_county" , 0);
		}

		cp_obj.set("busy_img_url" , null);
		cp_obj.set("hide_result" , false);
		cp_obj.set("on_result_ready", this.result_ready.bind(this));
		cp_obj.set("on_result_selected", this.result_selected.bind(this));
		cp_obj.set("on_error", this.result_error.bind(this));
		cp_obj.set("first_res_line", '---- please select your address ----');
		cp_obj.set("err_msg1", crafty_cfg.error_msg['0001']);
		cp_obj.set("err_msg2", crafty_cfg.error_msg['0002']);
		cp_obj.set("err_msg3", crafty_cfg.error_msg['0003']);
		cp_obj.set("err_msg4", crafty_cfg.error_msg['0004']);
		cp_obj.set("max_width", "100%");

		// do initial setup
		this.initial_setup();

		// do setup for current country
		this.country_changed();

		// listen for country changes
		var countryEl = document.getElementById(this.fields.country_id);
		countryEl.addEventListener('change', this.country_changed.bind(this));

		jQuery('#'+setup.fields.postcode_id).attr('cc-applied', '1');
	}

	this.country_changed = function(e) {
		// show postcode lookup for:
		// "GB" UK
		// "JE" Jersey
		// "GG" Guernsey
		// "IM" Isle of Man
		var curr_country = jQuery('#' + this.fields.country_id).val();
		if ('GB' == curr_country || 'JE' == curr_country || 'GG' == curr_country || 'IM' == curr_country) {
			this.setup_for_uk();
		} else {
			this.setup_for_non_uk();
		}
	}

	this.button_clicked = function(e) {
		this.cp_obj.doLookup();
	}

	this.result_ready = function() {
		jQuery('#crafty_postcode_lookup_result_option1').addClass('admin__control-select');
		jQuery('#cp_result_display').removeClass('admin__field-error');
	}

	this.result_selected = function() {
		// update country field if user selects address from GG/JE, etc.
		switch(jQuery('#' + this.fields.postcode_id).val().substring(0,2)){
			case "GY":
				jQuery('#' + this.fields.country_id).val("GG");
				break;
			case "JE":
				jQuery('#' + this.fields.country_id).val("JE");
				break;
			case "IM":
				jQuery('#' + this.fields.country_id).val("IM");
				break;
			default:
				jQuery('#' + this.fields.country_id).val("GB");
				break;
		}

		if (crafty_cfg.advanced.county_data == 'none') {
			jQuery('#'+this.fields.county_id).val('')
		}

		// trigger change event on address fields to satisfy magento required fields
		jQuery('#'+this.fields.company_id).trigger('change');
		jQuery('#'+this.fields.address_1_id).trigger('change');
		jQuery('#'+this.fields.address_2_id).trigger('change');
		jQuery('#'+this.fields.postcode_id).trigger('change');
		jQuery('#'+this.fields.town_id).trigger('change');
		jQuery('#'+this.fields.county_id).trigger('change');
		jQuery('#'+this.fields.country_id).trigger('change');
	}

	this.result_error = function() {
		jQuery('#cp_result_display').addClass('admin__field-error');
	}
}


activate_cc = function() {
	if (jQuery('#edit_form').find('#postcode').length == 1) {
		// Step 3 - now that we know this form exists, create an instance from our constructor
		var cc1 = new CraftyClicksMagento2Class();
		// Step 4 - run the add_lookup method on our instance and pass it field id's
		cc1.add_lookup({
			"fields": {
				"company_id":			"company",
				"address_1_id":		"street0",
				"address_2_id":		"street1",
				"postcode_id":		"postcode",
				"town_id":				"city",
				"county_id":			"region",
				"country_id":			"country_id",
			},
			"misc": {
				"container_id":	"edit_form"
			}
		});
	}
}

requirejs(['jquery'], function($) {
	jQuery(document).ready(function() {
		// Step 1 - check crafty is enabled
		if (crafty_cfg.enabled) {
			// Step 2 - page takes a while to load, so continuously check for form
			setInterval(activate_cc, 200);
		}
	});
});
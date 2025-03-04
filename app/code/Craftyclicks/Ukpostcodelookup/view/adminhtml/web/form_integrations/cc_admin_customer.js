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
		jQuery('#' + this.fields.company_id).closest('.admin__field').before(jQuery('#' + this.fields.country_id).closest('.admin__field'));
		jQuery('#' + this.fields.company_id).closest('.admin__field').before(jQuery('#' + this.fields.postcode_id).closest('.admin__field'));
	}

	this.setup_for_uk = function() {
		// check if we need to do anything
		if ('uk' != this.current_setup) {
			// do the magic for UK

			// add placeholder
			jQuery('#' + this.fields.postcode_id).attr('placeholder', crafty_cfg.txt.search_placeholder);

			// check if button already exists
			if (jQuery('#' + this.misc.prefix + '_cp_button_id').length == 0) {
				jQuery('#'+this.fields.postcode_id).wrap('<div class="search-bar"></div>');

				// add button after postcode input
				var tmp_html = '<button id="' + this.misc.prefix + '_cp_button_id" type="button" class="action primary"><span>' + crafty_cfg.txt.search_buttontext + '</span></button>';
				jQuery('#'+this.fields.postcode_id).after(tmp_html);

				// add click event listener to button
				var el = document.getElementById(this.misc.prefix + '_cp_button_id');
				if (el != null) {
					el.addEventListener('click', this.button_clicked.bind(this));
				}

				jQuery('#'+this.fields.postcode_id).closest('.search-bar').after('<div id="' + this.misc.prefix + '_cp_result_display" class="search-list"></div>');
				jQuery('#'+this.fields.postcode_id).closest('.admin__field').addClass('search-container type_3')
				jQuery('.search-container').css({width: "auto", display: "block"})

			} else {
				jQuery('#' + this.misc.prefix + '_cp_button_id').show();
				jQuery('#' + this.misc.prefix + '_cp_result_display').show();
				jQuery('#' + this.fields.postcode_id).closest('.admin__field').addClass("search-container type_3");
				// move postcode input back inside search bar
				jQuery('#'+this.fields.postcode_id).closest('.admin__field').find('.search-bar').prepend(jQuery('#'+this.fields.postcode_id))
			}
		}

		jQuery('#cc' + this.misc.index + '_cp_result_display').css('min-width', '200px');

		this.current_setup = 'uk';
	}

	this.setup_for_non_uk = function() {
		// check if we need to do anything
		if ('non_uk' != this.current_setup) {
			// hide button and result box if they exist
			if (jQuery('#' + this.misc.prefix + '_cp_button_id').length == 1) {
				jQuery('#' + this.misc.prefix + '_cp_button_id').hide();
			}

			if (jQuery('#' + this.misc.prefix + '_cp_result_display').length == 1) {
				jQuery('#' + this.misc.prefix + '_cp_result_display').hide();
			}

			jQuery('#' + this.misc.prefix + '_cp_result_display').children('select').remove();

			// revert postcode field to default state
			// move input up
			var postcode = '#'+this.fields.postcode_id
			var formControl = jQuery(postcode).closest('.admin__field-control')
			formControl.prepend(jQuery(postcode))

			// remove placeholder
			jQuery(postcode).attr('placeholder', '')

			// remove search-container and type3 classes
			jQuery(postcode).closest('.admin__field').removeClass('search-container')
			jQuery(postcode).closest('.admin__field').removeClass('type_3')

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
		cp_obj.set("result_elem_id", this.misc.prefix + "_cp_result_display");
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

	this.button_clicked = function() {
		this.cp_obj.doLookup();
	}

	this.result_ready = function() {
		jQuery('#crafty_postcode_lookup_result_option' + this.misc.index).addClass('admin__control-select');
		jQuery('#cc' + this.misc.index + '_cp_result_display').removeClass('admin__field-error');
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
		// apply magento error styling
		jQuery('#cc' + this.misc.index + '_cp_result_display').addClass('admin__field-error');
	}
}

activate_cc = function() {
	// Step 3 - get all postcode elements so we can identify the forms
	var postcode_elements = jQuery('[name="postcode"]');

	postcode_elements.each(function(index) {
		if (postcode_elements.eq(index).attr('cc-applied') !== '1') {
			var form = postcode_elements.eq(index).closest('fieldset');

			// check if all form elements exist correctly
			// the way this form loads, initially region and other fields might be missing
			if(!(
				0 != form.find('[name="company"]').length &&
				0 != form.find('[name="street[0]"]').length &&
				0 != form.find('[name="street[1]"]').length &&
				0 != form.find('[name="postcode"]').length &&
				0 != form.find('[name="city"]').length &&
				0 != form.find('[name="region"]').length &&
				0 != form.find('select[name="region_id"]').length &&
				0 != form.find('select[name="country_id"]').length
			)){
				// if anything is missing (some parts get loaded in a second ajax pass)
				return;
			}

			cc_index++;
			cc_objects[cc_index] = new CraftyClicksMagento2Class();
			cc_objects[cc_index].add_lookup({
				"fields": {
					"company_id":			form.find('[name="company"]').attr('id'),
					"address_1_id":		form.find('[name="street[0]"]').attr('id'),
					"address_2_id":		form.find('[name="street[1]"]').attr('id'),
					"postcode_id":		form.find('[name="postcode"]').attr('id'),
					"town_id":				form.find('[name="city"]').attr('id'),
					"county_id":			form.find('[name="region"]').attr('id'),
					"country_id":			form.find('select[name="country_id"]').attr('id')
				},
				"misc": {
					"prefix": ("cc" + cc_index),
					"index":	cc_index
				}
			});

		}
	});
}

// Step 0 - use an incrementer since this page may contain many forms
var cc_index = 0;
var cc_objects = [];
requirejs(['jquery'], function($) {
	jQuery(document).ready(function() {
		// Step 1 - check crafty is enabled
		if (crafty_cfg.enabled) {
			// Step 2 - page takes a while to load, so continuously check for form
			setInterval(activate_cc, 200);
		}
	});
});
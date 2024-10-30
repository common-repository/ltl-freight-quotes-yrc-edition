jQuery(document).ready(function () {
    // Weight threshold for LTL freight
    en_weight_threshold_limit();

    jQuery("#yrc_residential").closest('tr').addClass("yrc_residential");
    jQuery("#avaibility_auto_residential").closest('tr').addClass("avaibility_auto_residential");
    jQuery("#avaibility_lift_gate").closest('tr').addClass("avaibility_lift_gate");
    jQuery("#yrc_liftgate").closest('tr').addClass("yrc_liftgate");
    jQuery("#yrc_quotes_liftgate_delivery_as_option").closest('tr').addClass("yrc_quotes_liftgate_delivery_as_option");

    // Cuttoff Time
    jQuery("#yrc_freight_shipment_offset_days").closest('tr').addClass("yrc_freight_shipment_offset_days_tr");
    jQuery("#all_shipment_days_yrc").closest('tr').addClass("all_shipment_days_yrc_tr");
    jQuery(".yrc_shipment_day").closest('tr').addClass("yrc_shipment_day_tr");
    jQuery("#yrc_freight_order_cut_off_time").closest('tr').addClass("yrc_freight_cutt_off_time_ship_date_offset");
    var yrc_current_time = en_yrc_admin_script.yrc_freight_order_cutoff_time;
    if (yrc_current_time == '') {

        jQuery('#yrc_freight_order_cut_off_time').wickedpicker({
            now: '',
            title: 'Cut Off Time',
        });
    } else {
        jQuery('#yrc_freight_order_cut_off_time').wickedpicker({

            now: yrc_current_time,
            title: 'Cut Off Time'
        });
    }

    var delivery_estimate_val = jQuery('input[name=yrc_delivery_estimates]:checked').val();
    if (delivery_estimate_val == 'dont_show_estimates') {
        jQuery("#yrc_freight_order_cut_off_time").prop('disabled', true);
        jQuery("#yrc_freight_shipment_offset_days").prop('disabled', true);
        jQuery("#yrc_freight_shipment_offset_days").css("cursor", "not-allowed");
        jQuery("#yrc_freight_order_cut_off_time").css("cursor", "not-allowed");
    } else {
        jQuery("#yrc_freight_order_cut_off_time").prop('disabled', false);
        jQuery("#yrc_freight_shipment_offset_days").prop('disabled', false);
        // jQuery("#yrc_freight_order_cut_off_time").css("cursor", "auto");
        jQuery("#yrc_freight_order_cut_off_time").css("cursor", "");
    }

    jQuery("input[name=yrc_delivery_estimates]").change(function () {
        var delivery_estimate_val = jQuery('input[name=yrc_delivery_estimates]:checked').val();
        if (delivery_estimate_val == 'dont_show_estimates') {
            jQuery("#yrc_freight_order_cut_off_time").prop('disabled', true);
            jQuery("#yrc_freight_shipment_offset_days").prop('disabled', true);
            jQuery("#yrc_freight_order_cut_off_time").css("cursor", "not-allowed");
            jQuery("#yrc_freight_shipment_offset_days").css("cursor", "not-allowed");
        } else {
            jQuery("#yrc_freight_order_cut_off_time").prop('disabled', false);
            jQuery("#yrc_freight_shipment_offset_days").prop('disabled', false);
            jQuery("#yrc_freight_order_cut_off_time").css("cursor", "auto");
            jQuery("#yrc_freight_shipment_offset_days").css("cursor", "auto");
        }
    });

    /*
     * Uncheck Week days Select All Checkbox
     */
    jQuery(".yrc_shipment_day").on('change load', function () {

        var checkboxes = jQuery('.yrc_shipment_day:checked').length;
        var un_checkboxes = jQuery('.yrc_shipment_day').length;
        if (checkboxes === un_checkboxes) {
            jQuery('.all_shipment_days_yrc').prop('checked', true);
        } else {
            jQuery('.all_shipment_days_yrc').prop('checked', false);
        }
    });

    /*
     * Select All Shipment Week days
     */

    var all_int_checkboxes = jQuery('.all_shipment_days_yrc');
    if (all_int_checkboxes.length === all_int_checkboxes.filter(":checked").length) {
        jQuery('.all_shipment_days_yrc').prop('checked', true);
    }

    jQuery(".all_shipment_days_yrc").change(function () {
        if (this.checked) {
            jQuery(".yrc_shipment_day").each(function () {
                this.checked = true;
            });
        } else {
            jQuery(".yrc_shipment_day").each(function () {
                this.checked = false;
            });
        }
    });


    //** End: Order Cut Off Time

    /**
     * Offer lift gate delivery as an option and Always include residential delivery fee
     * @returns {undefined}
     */

    jQuery(".checkbox_fr_add").on("click", function () {
        var id = jQuery(this).attr("id");
        if (id == "yrc_liftgate") {
            jQuery("#yrc_quotes_liftgate_delivery_as_option").prop({checked: false});
            jQuery("#en_woo_addons_liftgate_with_auto_residential").prop({checked: false});

        } else if (id == "yrc_quotes_liftgate_delivery_as_option" ||
            id == "en_woo_addons_liftgate_with_auto_residential") {
            jQuery("#yrc_liftgate").prop({checked: false});
        }
    });


    var url = get_url_vars_yrc_freight()["tab"];
    if (url === 'yrc_quotes') {
        jQuery('#footer-left').attr('id', 'wc-footer-left');
    }
    /*
    * Add err class on connection settings page
    */
    jQuery('.connection_section_class_yrc input[type="text"]').each(function () {
        if (jQuery(this).parent().find('.err').length < 1) {
            jQuery(this).after('<span class="err"></span>');
        }
    });

    /*
     * Show Note Message on Connection Settings Page
     */
    if (jQuery('#wc_settings_yrc_plugin_licence_key').length > 0) {
        jQuery('.connection_section_class_yrc .form-table').before("<div class='warning-msg'><p>Note! You must have a YRC account to use this application. If you don't have one, contact YRC at 1-800-610-6500.</p></div>");
    }
    /*
    * Add maxlength Attribute on Handling Fee Quote Setting Page
    */

    jQuery("#yrc_handling_fee").attr('maxlength', '9');


    /*
     * Add Title To Connection Setting Fields
     */
    jQuery('#wc_settings_yrc_userid').attr('title', 'Username');
    jQuery('#wc_settings_yrc_password').attr('title', 'Password');
    jQuery('#wc_settings_yrc_busid').attr('title', 'Business ID');
    jQuery('#wc_settings_yrc_plugin_licence_key').attr('title', 'Plugin License Key ');

    /*
     * Add Title To Qoutes Setting Fields
     */

    jQuery('#yrc_label_as').attr('title', 'Label As');
    jQuery('#yrc_handling_fee').attr('title', 'Handling Fee / Markup');

    jQuery(".connection_section_class_yrc .button-primary").click(function () {
        var has_err = true;
        jQuery(".connection_section_class_yrc tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');

            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';

            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }
    });

    jQuery(".connection_section_class_yrc .woocommerce-save-button").before('<a href="javascript:void(0)" class="button-primary yrc_test_connection">Test connection</a>');

    /*
     * YRC Test connection Form Valdating ajax Request
     */

    jQuery('.yrc_test_connection').click(function (e) {
        var has_err = true;
        jQuery(".connection_section_class_yrc tbody input[type='text']").each(function () {
            var input = jQuery(this).val();
            var response = validateString(input);
            var errorText = jQuery(this).attr('title');
            var optional = jQuery(this).data('optional');

            var errorElement = jQuery(this).parent().find('.err');
            jQuery(errorElement).html('');

            optional = (optional === undefined) ? 0 : 1;
            errorText = (errorText != undefined) ? errorText : '';

            if ((optional == 0) && (response == false || response == 'empty')) {
                errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
                jQuery(errorElement).html(errorText);
            }
            has_err = (response != true && optional == 0) ? false : has_err;
        });
        var input = has_err;
        if (input === false) {
            return false;
        }

        var postForm = {
            'action': 'yrc_action',
            'yrc_userid': jQuery('#wc_settings_yrc_userid').val(),
            'yrc_password': jQuery('#wc_settings_yrc_password').val(),
            'yrc_busid': jQuery('#wc_settings_yrc_busid').val(),
            'yrc_plugin_license': jQuery('#wc_settings_yrc_plugin_licence_key').val(),
            'yrc_rates_based': jQuery("input[name=yrc_rates_based]:checked").val(),
        };

        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: postForm,
            dataType: 'json',

            beforeSend: function () {
                jQuery(".connection_save_button").remove();
                jQuery('#wc_settings_yrc_userid').css('background', 'rgba(255, 255, 255, 1) url("' + en_yrc_admin_script.plugins_url + '/ltl-freight-quotes-yrc-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_yrc_password').css('background', 'rgba(255, 255, 255, 1) url("' + en_yrc_admin_script.plugins_url + '/ltl-freight-quotes-yrc-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_yrc_busid').css('background', 'rgba(255, 255, 255, 1) url("' + en_yrc_admin_script.plugins_url + '/ltl-freight-quotes-yrc-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                jQuery('#wc_settings_yrc_plugin_licence_key').css('background', 'rgba(255, 255, 255, 1) url("' + en_yrc_admin_script.plugins_url + '/ltl-freight-quotes-yrc-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
            },
            success: function (data) {
                jQuery('#wc_settings_yrc_userid').css('background', '#fff');
                jQuery('#wc_settings_yrc_password').css('background', '#fff');
                jQuery('#wc_settings_yrc_busid').css('background', '#fff');
                jQuery('#wc_settings_yrc_plugin_licence_key').css('background', '#fff');

                jQuery(".yrc_success_message").remove();
                jQuery(".yrc_error_message").remove();
                jQuery("#message").remove();

                if (data.message === "success") {
                    jQuery('.warning-msg').before('<div class="notice notice-success yrc_success_message"><p><strong>Success! The test resulted in a successful connection.</strong></p></div>');
                } else if (data.message !== "failure" && data.message !== "success") {
                    jQuery('.warning-msg').before('<div class="notice notice-error yrc_error_message"><p>Error!  ' + data.message + ' </p></div>');
                } else {
                    jQuery('.warning-msg').before('<div class="notice notice-error yrc_error_message"><p>Error! Please verify credentials and try again.</p></div>');
                }
            }
        });
        e.preventDefault();
    });
    // fdo va
    jQuery('#fd_online_id_yrc').click(function (e) {
        var postForm = {
            'action': 'yrc_fd',
            'company_id': jQuery('#freightdesk_online_id').val(),
            'disconnect': jQuery('#fd_online_id_yrc').attr("data")
        }
        var id_lenght = jQuery('#freightdesk_online_id').val();
        var disc_data = jQuery('#fd_online_id_yrc').attr("data");
        if(typeof (id_lenght) != "undefined" && id_lenght.length < 1) {
            jQuery(".yrc_error_message").remove();
            jQuery('.user_guide_fdo').before('<div class="notice notice-error yrc_error_message"><p><strong>Error!</strong> FreightDesk Online ID is Required.</p></div>');
            return;
        }
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: postForm,
            beforeSend: function () {
                jQuery('#freightdesk_online_id').css('background', 'rgba(255, 255, 255, 1) url("' + en_yrc_admin_script.plugins_url + '/ltl-freight-quotes-yrc-edition/warehouse-dropship/wild/assets/images/processing.gif") no-repeat scroll 50% 50%');
                },
            success: function (data_response) {
                if(typeof (data_response) == "undefined"){
                    return;
                }
                var fd_data = JSON.parse(data_response);
                jQuery('#freightdesk_online_id').css('background', '#fff');
                jQuery(".yrc_error_message").remove();
                if((typeof (fd_data.is_valid) != 'undefined' && fd_data.is_valid == false) || (typeof (fd_data.status) != 'undefined' && fd_data.is_valid == 'ERROR')) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error yrc_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'SUCCESS') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-success yrc_success_message"><p><strong>Success! ' + fd_data.message + '</strong></p></div>');
                    window.location.reload(true);
                }else if(typeof (fd_data.status) != 'undefined' && fd_data.status == 'ERROR') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error yrc_error_message"><p><strong>Error! ' + fd_data.message + '</strong></p></div>');
                }else if (fd_data.is_valid == 'true') {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error yrc_error_message"><p><strong>Error!</strong> FreightDesk Online ID is not valid.</p></div>');
                } else if (fd_data.is_valid == 'true' && fd_data.is_connected) {
                    jQuery('.user_guide_fdo').before('<div class="notice notice-error yrc_error_message"><p><strong>Error!</strong> Your store is already connected with FreightDesk Online.</p></div>');

                } else if (fd_data.is_valid == true && fd_data.is_connected == false && fd_data.redirect_url != null) {
                    window.location = fd_data.redirect_url;
                } else if (fd_data.is_connected == true) {
                    jQuery('#con_dis').empty();
                    jQuery('#con_dis').append('<a href="#" id="fd_online_id_yrc" data="disconnect" class="button-primary">Disconnect</a>')
                }
            }
        });
        e.preventDefault();
    });

    /*
     * YRC Qoute Settings Tabs Validation
     */

    jQuery('.quote_section_class_yrc .woocommerce-save-button').on('click', function () {
        jQuery(".updated").hide();
        jQuery('.error').remove();
        var handling_fee1 = jQuery('#yrc_handling_fee').val();
        var yrc_handling_fee2 = jQuery('#yrc_handling_fee2').val();
        var handling_fee_regex = /^(-?[0-9]{1,4}%?)$|(\.[0-9]{1,2})%?$/;

        if (handling_fee1 != '' && !handling_fee_regex.test(handling_fee1)) {
            jQuery("#mainform .quote_section_class_yrc").prepend('<div id="message" class="error inline yrc_handlng_fee_error"><p><strong>Handling fee format should be 100.20 or 10%.</strong></p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.yrc_handlng_fee_error').position().top
            });
            jQuery("#yrc_handling_fee").css({'border-color': '#e81123'});
            return false;
        }
        if (yrc_handling_fee2 != '' && !handling_fee_regex.test(yrc_handling_fee2)) {
            jQuery("#mainform .quote_section_class_yrc").prepend('<div id="message" class="error inline yrc_handlng_fee2_error"><p><strong>Handling fee 2 format should be 100.20 or 10%.</strong></p></div>');
            jQuery('html, body').animate({
                'scrollTop': jQuery('.yrc_handlng_fee2_error').position().top
            });
            jQuery("#yrc_handling_fee2").css({'border-color': '#e81123'});
            return false;
        }
    });

});

// Weight threshold for LTL freight
if (typeof en_weight_threshold_limit != 'function') {
    function en_weight_threshold_limit() {
        // Weight threshold for LTL freight
        jQuery("#en_weight_threshold_lfq").keypress(function (e) {
            if (String.fromCharCode(e.keyCode).match(/[^0-9]/g) || !jQuery("#en_weight_threshold_lfq").val().match(/^\d{0,3}$/)) return false;
        });

        jQuery('#en_plugins_return_LTL_quotes').on('change', function () {
            if (jQuery('#en_plugins_return_LTL_quotes').prop("checked")) {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'contents');
            } else {
                jQuery('tr.en_weight_threshold_lfq').css('display', 'none');
            }
        });

        jQuery("#en_plugins_return_LTL_quotes").closest('tr').addClass("en_plugins_return_LTL_quotes_tr");
        // Weight threshold for LTL freight
        var weight_threshold_class = jQuery("#en_weight_threshold_lfq").attr("class");
        jQuery("#en_weight_threshold_lfq").closest('tr').addClass("en_weight_threshold_lfq " + weight_threshold_class);

        // Weight threshold for LTL freight is empty
        if (jQuery('#en_weight_threshold_lfq').length && !jQuery('#en_weight_threshold_lfq').val().length > 0) {
            jQuery('#en_weight_threshold_lfq').val(150);
        }
    }
}

// Update plan
if (typeof en_update_plan != 'function') {
    function en_update_plan(input) {
        let action = jQuery(input).attr('data-action');
        jQuery.ajax({
            type: "POST",
            url: ajaxurl,
            data: {action: action},
            success: function (data_response) {
                window.location.reload(true);
            }
        });
    }
}

/*
         * YRC Form Validating Inputs
         */
function validateInput(form_id) {
    var has_err = true;
    jQuery(form_id + " input[type='text']").each(function () {
        var input = jQuery(this).val();
        var response = validateString(input);
        var errorText = jQuery(this).attr('title');
        var optional = jQuery(this).data('optional');

        var errorElement = jQuery(this).parent().find('.err');
        jQuery(errorElement).html('');

        optional = (optional === undefined) ? 0 : 1;
        errorText = (errorText != undefined) ? errorText : '';

        if ((optional == 0) && (response == false || response == 'empty')) {
            errorText = (response == 'empty') ? errorText + ' is required.' : 'Invalid input.';
            jQuery(errorElement).html(errorText);
        }
        has_err = (response != true && optional == 0) ? false : has_err;
    });
    return has_err;
}

/*
 * YRC Validating Numbers
 */
function isValidNumber(value, noNegative) {
    if (typeof (noNegative) === 'undefined') noNegative = false;
    var isValidNumber = false;
    var validNumber = (noNegative == true) ? parseFloat(value) >= 0 : true;
    if ((value == parseInt(value) || value == parseFloat(value)) && (validNumber)) {
        if (value.indexOf(".") >= 0) {
            var n = value.split(".");
            if (n[n.length - 1].length <= 2) {
                isValidNumber = true;
            } else {
                isValidNumber = 'decimal_point_err';
            }
        } else {
            isValidNumber = true;
        }
    }
    return isValidNumber;
}

/*
 * YRC Validating String
 */
function validateString(string) {
    if (string == '')
        return 'empty';
    else
        return true;

}

/**
 * Read a page's GET URL variables and return them as an associative array.
 */
function get_url_vars_yrc_freight() {
    var vars = [], hash;
    var hashes = window.location.href.slice(window.location.href.indexOf('?') + 1).split('&');
    for (var i = 0; i < hashes.length; i++) {
        hash = hashes[i].split('=');
        vars.push(hash[0]);
        vars[hash[0]] = hash[1];
    }
    return vars;
}
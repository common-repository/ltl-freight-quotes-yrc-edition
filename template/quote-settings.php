<?php
/**
 * YRC WooComerce | Qoute Settings Page
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * YRC WooComerce | Qoute Settings Page
 */
class YRC_Quote_Settings
{
    /**
     * Quote Setting From Fields
     * @return array
     */
    function yrc_quote_settings_tab()
    {
        // Cuttoff Time
        $yrc_disable_cutt_off_time_ship_date_offset = "";
        $yrc_cutt_off_time_package_required = "";
        $yrc_disable_show_delivery_estimates = "";
        $yrc_show_delivery_estimates_required = "";

        //  Check the cutt of time & offset days plans for disable input fields
        $yrc_action_cutOffTime_shipDateOffset = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'yrc_cutt_off_time');
        if (is_array($yrc_action_cutOffTime_shipDateOffset)) {
            $yrc_disable_cutt_off_time_ship_date_offset = "disabled_me";
            $yrc_cutt_off_time_package_required = apply_filters('yrc_quotes_plans_notification_link', $yrc_action_cutOffTime_shipDateOffset);
        }
        // check the delivery estimate option plan
        $yrc_show_delivery_estimates = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'yrc_show_delivery_estimates');
        if (is_array($yrc_show_delivery_estimates)) {
            $yrc_disable_show_delivery_estimates = "disabled_me";
            $yrc_show_delivery_estimates_required = apply_filters('yrc_quotes_plans_notification_link', $yrc_show_delivery_estimates);
        }

        $ltl_enable = get_option('en_plugins_return_LTL_quotes');
        $weight_threshold_class = $ltl_enable == 'yes' ? 'show_en_weight_threshold_lfq' : 'hide_en_weight_threshold_lfq';
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

        echo '<div class="quote_section_class_yrc">';
        $settings = array(
            'section_title_quote' => array(
                'title' => __('Quote Settings ', 'woocommerce_yrc_quote'),
                'type' => 'title',
                'desc' => '',
                'id' => 'yrc_section_title_quote'
            ),

            'label_as_yrc' => array(
                'name' => __('Label As ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style"> What the user sees during checkout, e.g. "LTL Freight". If left blank, "Freight" will display as the shipping method.</span>',
                'id' => 'yrc_label_as'
            ),

            'price_sort_yrc' => array(
                'name' => __("Don't sort shipping methods by price  ", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'By default, the plugin will sort all shipping methods by price in ascending order.',
                'id' => 'shipping_methods_do_not_sort_by_price'
            ),
            //** Start Delivery Estimate Options - Cuttoff Time
            'service_yrc_estimates_title' => array(
                'name' => __('Delivery Estimate Options ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'desc' => $yrc_show_delivery_estimates_required,
                'id' => 'service_yrc_estimates_title'
            ),
            'yrc_show_delivery_estimates_options_radio' => array(
                'name' => __("", 'woocommerce-settings-yrc'),
                'type' => 'radio',
                'default' => 'dont_show_estimates',
                'options' => array(
                    'dont_show_estimates' => __("Don't display delivery estimates.", 'woocommerce'),
                    'delivery_days' => __("Display estimated number of days until delivery.", 'woocommerce'),
                    'delivery_date' => __("Display estimated delivery date.", 'woocommerce'),
                ),
                'id' => 'yrc_delivery_estimates',
                'class' => $yrc_disable_show_delivery_estimates . ' yrc_dont_show_estimate_option',
            ),
            //** End Delivery Estimate Options
            //**Start: Cut Off Time & Ship Date Offset
            'cutOffTime_shipDateOffset_yrc_freight' => array(
                'name' => __('Cut Off Time & Ship Date Offset ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => $yrc_cutt_off_time_package_required,
                'id' => 'yrc_freight_cutt_off_time_ship_date_offset'
            ),
            'orderCutoffTime_yrc_freight' => array(
                'name' => __('Order Cut Off Time ', 'woocommerce-settings-yrc_freight_freight_orderCutoffTime'),
                'type' => 'text',
                'placeholder' => '-- : -- --',
                'desc' => 'Enter the cut off time (e.g. 2.00) for the orders. Orders placed after this time will be quoted as shipping the next business day.',
                'id' => 'yrc_freight_order_cut_off_time',
                'class' => $yrc_disable_cutt_off_time_ship_date_offset,
            ),
            'shipmentOffsetDays_yrc_freight' => array(
                'name' => __('Fullfillment Offset Days ', 'woocommerce-settings-yrc_freight_shipment_offset_days'),
                'type' => 'text',
                'desc' => 'The number of days the ship date needs to be moved to allow the processing of the order.',
                'placeholder' => 'Fullfillment Offset Days, e.g. 2',
                'id' => 'yrc_freight_shipment_offset_days',
                'class' => $yrc_disable_cutt_off_time_ship_date_offset,
            ),
            'all_shipment_days_yrc' => array(
                'name' => __("What days do you ship orders?", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Select All',
                'class' => "all_shipment_days_yrc $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'all_shipment_days_yrc'
            ),
            'monday_shipment_day_yrc' => array(
                'name' => __("", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Monday',
                'class' => "yrc_shipment_day $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'monday_shipment_day_yrc'
            ),
            'tuesday_shipment_day_yrc' => array(
                'name' => __("", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Tuesday',
                'class' => "yrc_shipment_day $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'tuesday_shipment_day_yrc'
            ),
            'wednesday_shipment_day_yrc' => array(
                'name' => __("", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Wednesday',
                'class' => "yrc_shipment_day $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'wednesday_shipment_day_yrc'
            ),
            'thursday_shipment_day_yrc' => array(
                'name' => __("", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Thursday',
                'class' => "yrc_shipment_day $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'thursday_shipment_day_yrc'
            ),
            'friday_shipment_day_yrc' => array(
                'name' => __("", 'woocommerce-settings-yrc_quotes'),
                'type' => 'checkbox',
                'desc' => 'Friday',
                'class' => "yrc_shipment_day $yrc_disable_cutt_off_time_ship_date_offset",
                'id' => 'friday_shipment_day_yrc'
            ),
            'yrc_show_delivery_estimate' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce-settings-yrc_quotes'),
                'desc' => '',
                'id' => 'yrc_show_delivery_estimates',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),
            //**End: Cut Off Time & Ship Date Offset

            'accessorial_quoted_yrc' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce_yrc_quote'),
                'desc' => '',
                'id' => 'woocommerce_accessorial_quoted_yrc',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),

            'accessorial_quoted_yrc' => array(
                'title' => __('', 'woocommerce'),
                'name' => __('', 'woocommerce_yrc_quote'),
                'desc' => '',
                'id' => 'woocommerce_yrc_accessorial_quoted',
                'css' => '',
                'default' => '',
                'type' => 'title',
            ),

            'residential_delivery_options_label' => array(
                'name' => __('Residential Delivery', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'residential_delivery_options_label'
            ),

            'accessorial_residential_delivery_yrc' => array(
                'name' => __('Always quote as residential delivery ', 'woocommerce_yrc_quote'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce_yrc_quote'),
                'id' => 'yrc_residential',
                'class' => 'accessorial_service yrcCheckboxClass',
            ),

//          Auto-detect residential addresses notification
            'avaibility_auto_residential' => array(
                'name' => __('Auto-detect residential addresses', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_auto_residential'
            ),

            'liftgate_delivery_options_label' => array(
                'name' => __('Lift Gate Delivery ', 'woocommerce-settings-en_woo_addons_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'id' => 'liftgate_delivery_options_label'
            ),

            'accessorial_liftgate_delivery_yrc' => array(
                'name' => __('Always quote lift gate delivery ', 'woocommerce_yrc_quote'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce_yrc_quote'),
                'id' => 'yrc_liftgate',
                'class' => 'accessorial_service yrcCheckboxClass checkbox_fr_add',
            ),

            'yrc_quotes_liftgate_delivery_as_option' => array(
                'name' => __('Offer lift gate delivery as an option ', 'woocommerce-settings-fedex_freight'),
                'type' => 'checkbox',
                'desc' => __('', 'woocommerce-settings-fedex_freight'),
                'id' => 'yrc_quotes_liftgate_delivery_as_option',
                'class' => 'accessorial_service checkbox_fr_add yrcCheckboxClass',
            ),

//          Use my liftgate notification
            'avaibility_lift_gate' => array(
                'name' => __('Always include lift gate delivery when a residential address is detected', 'woocommerce-settings-wwe_small_packages_quotes'),
                'type' => 'text',
                'class' => 'hidden',
                'desc' => "Click <a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/'>here</a> to add the Residential Address Detection module. (<a target='_blank' href='https://eniture.com/woocommerce-residential-address-detection/#documentation'>Learn more</a>)",
                'id' => 'avaibility_lift_gate'
            ),

            'handing_fee_markup_yrc' => array(
                'name' => __('Handling Fee 1 / Markup ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.</span>',
                'id' => 'yrc_handling_fee'
            ),

            'handing_fee_markup_yrc2' => array(
                'name' => __('Handling Fee 2 / Markup ', 'woocommerce_yrc_quote2'),
                'type' => 'text',
                'desc' => '<span class="desc_text_style">Amount excluding tax. Enter an amount, e.g 3.75, or a percentage, e.g, 5%. Leave blank to disable.</span>',
                'id' => 'yrc_handling_fee2'
            ),

            'allow_other_plugins_yrc' => array(
                'name' => __('Show WooCommerce Shipping Options ', 'woocommerce_yrc_quote'),
                'type' => 'select',
                'default' => '3',
                'desc' => __('<span class="desc_text_style">Enabled options on WooCommerce Shipping page are included in quote results.</span>', 'woocommerce_yrc_quote'),
                'id' => 'yrc_allow_other_plugins',
                'options' => array(
                    'yes' => __('YES', 'YES'),
                    'no' => __('NO', 'NO'),
                )
            ),

            'return_YRC_quotes' => array(
                'name' => __("Return LTL quotes when an order parcel shipment weight exceeds the weight threshold ", 'woocommerce-settings-yrc_quetes'),
                'type' => 'checkbox',
                'desc' => '<span class="desc_text_style">When checked, the LTL Freight Quote will return quotes when an order’s total weight exceeds the weight threshold (the maximum permitted by WWE and UPS), even if none of the products have settings to indicate that it will ship LTL Freight. To increase the accuracy of the returned quote(s), all products should have accurate weights and dimensions. </span>',
                'id' => 'en_plugins_return_LTL_quotes',
                'class' => 'yrcCheckboxClass'
            ),
            // Weight threshold for LTL freight
            'en_weight_threshold_lfq' => [
                'name' => __('Weight threshold for LTL Freight Quotes ', 'woocommerce-settings-wwe_freight'),
                'type' => 'text',
                'default' => $weight_threshold,
                'class' => $weight_threshold_class,
                'id' => 'en_weight_threshold_lfq'
            ],
            'section_end_quote' => array(
                'type' => 'sectionend',
                'id' => 'yrc_quote_section_end'
            )
        );
        return $settings;
    }
}

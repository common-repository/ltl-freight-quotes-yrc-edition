<?php

/**
 * YRC WooComerce | Get YRC LTL Quotes Rate Class
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * YRC WooComerce | Get YRC LTL Quotes Rate Class
 */
class YRC_Get_Shipping_Quotes extends Yrc_Liftgate_As_Option
{

    public $en_wd_origin_array;
    public $InstorPickupLocalDelivery;
    public $quote_settings;
    // Excluded accessorials
    public $recent_quote_settings;
    public $en_accessorial_excluded;

    function __construct()
    {
        $this->quote_settings = array();
        $this->recent_quote_settings = array();
    }

    /**
     * Create Shipping Package
     * @param $packages
     * @return array
     */
    function yrc_shipping_array($packages, $package_plugin = '')
    {
        // FDO
        $EnYrcFdo = new EnYrcFdo();
        $en_fdo_meta_data = array();

        $destinationAddressYrc = $this->destinationAddressYrc();
        $residential_detecion_flag = get_option("en_woo_addons_auto_residential_detecion_flag");

        $index = 'ltl-freight-quotes-yrc-edition/ltl-freight-quotes-yrc-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = $plugin_info[$index]['Version'];

        $products = $product_name = array();
        $hazardous = array();
        foreach ($packages['items'] as $item) {
            $product_name[] = $item['product_name'];
            $products[] = $item['products'];
        }

        // Cuttoff Time
        $shipment_week_days = "";
        $order_cut_off_time = "";
        $shipment_off_set_days = "";
        $modify_shipment_date_time = "";
        $store_date_time = "";
        $yrc_delivery_estimates = get_option('yrc_delivery_estimates');
        $yrc_show_delivery_estimates = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'yrc_show_delivery_estimates');
        $shipment_week_days = $this->yrc_shipment_week_days();
        if ($yrc_delivery_estimates == 'delivery_days' || $yrc_delivery_estimates == 'delivery_date' && !is_array($yrc_show_delivery_estimates)) {
            $order_cut_off_time = $this->quote_settings['orderCutoffTime'];
            $shipment_off_set_days = $this->quote_settings['shipmentOffsetDays'];
            $modify_shipment_date_time = ($order_cut_off_time != '' || $shipment_off_set_days != '' || (is_array($shipment_week_days) && count($shipment_week_days) > 0)) ? 1 : 0;
            $store_date_time = $today = date('Y-m-d H:i:s', current_time('timestamp'));
        }

        $domain = yrc_quotes_get_domain();

        $this->en_wd_origin_array = (isset($packages['origin'])) ? $packages['origin'] : array();

        // FDO
        $en_fdo_meta_data = $EnYrcFdo->en_cart_package($packages);

        // Version numbers
        $plugin_versions = $this->en_version_numbers();

        $post_data = array(
            // Version numbers
            'plugin_version' => $plugin_versions["en_current_plugin_version"],
            'wordpress_version' => get_bloginfo('version'),
            'woocommerce_version' => $plugin_versions["woocommerce_plugin_version"],

            'licence_key' => get_option('wc_settings_yrc_plugin_licence_key'),
            'plugin_version' => $plugin_version,
            'sever_name' => $this->yrc_parse_url($domain),
            'carrierName' => 'yrc',
            'carrier_mode' => 'pro',
            'plateform' => 'WordPress',
            'ServiceClass' => 'STD',
            'userId' => get_option('wc_settings_yrc_userid'),
            'password' => get_option('wc_settings_yrc_password'),
            'busId' => get_option('wc_settings_yrc_busid'),
            'suspend_residential' => get_option('suspend_automatic_detection_of_residential_addresses'),
            'residential_detecion_flag' => $residential_detecion_flag,
            'RequestOption' => 'Rate',
            'senderCity' => $packages['origin']['city'],
            'senderState' => $packages['origin']['state'],
            'senderZip' => $packages['origin']['zip'],
            'sender_origin' => $packages['origin']['location'] . ": " . $packages['origin']['city'] . ", " . $packages['origin']['state'] . " " . $packages['origin']['zip'],
            'product_name' => $product_name,
            'products' => $products,
            'senderCountryCode' => $this->yrc_get_country_code($packages['origin']['country']),
            'receiverCity' => $destinationAddressYrc['city'],
            'receiverState' => $destinationAddressYrc['state'],
            'receiverZip' => str_replace(' ', '', $destinationAddressYrc['zip']),
            'receiverCountryCode' => $this->yrc_get_country_code($destinationAddressYrc['country']),

            'commdityDetails' => array(
                'handlingUnitDetails' => array(
                    'wsHandlingUnit' => $this->yrc_get_line_items($packages),
                ),
            ),

            // FDO
            'en_fdo_meta_data' => $en_fdo_meta_data,

            // Dimension
            'liftGateAsAnOption' => $this->recent_quote_settings['liftgate_delivery_option'] == "yes" ? true : false,
            'dimWeightBaseAccount' => isset($this->recent_quote_settings['yrc_rates_based']) && $this->recent_quote_settings['yrc_rates_based'] == 'dimension' ? true : false,
            // Cuttoff Time
            'modifyShipmentDateTime' => $modify_shipment_date_time,
            'OrderCutoffTime' => $order_cut_off_time,
            'shipmentOffsetDays' => $shipment_off_set_days,
            'storeDateTime' => $store_date_time,
            'shipmentWeekDays' => $shipment_week_days,
        );

        if (get_option('yrc_quotes_store_type') == "1") {
            // Hazardous Material
            $hazardous_material = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'hazardous_material');
            if (!is_array($hazardous_material)) {
                (isset($packages['hazardousMaterial']) && $packages['hazardousMaterial'] == 'yes') ? $post_data['accessorial'][] = 'HAZM' : '';
                ($packages['hazardousMaterial'] == 'yes') ? $hazardous[] = 'H' : '';

                // FDO
                $post_data['en_fdo_meta_data'] = array_merge($post_data['en_fdo_meta_data'], $EnYrcFdo->en_package_hazardous($packages, $en_fdo_meta_data));
            }
        } else {
            ($packages['hazardousMaterial'] == 'yes') ? $post_data['accessorial'][] = 'HAZM' : '';

            // FDO
            $post_data['en_fdo_meta_data'] = array_merge($post_data['en_fdo_meta_data'], $EnYrcFdo->en_package_hazardous($packages, $en_fdo_meta_data));
        }

        $post_data = apply_filters("en_woo_addons_carrier_service_quotes_request", $post_data, en_woo_plugin_yrc_quotes);

        // In-store pickup and local delivery
        $instore_pickup_local_devlivery_action = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'instore_pickup_local_devlivery');
        if (!is_array($instore_pickup_local_devlivery_action)) {
            $post_data = apply_filters('en_yrc_wd_standard_plans', $post_data, $post_data['receiverZip'], $this->en_wd_origin_array, $package_plugin);
        }

        $post_data['hazardous'] = $hazardous;

        ($this->recent_quote_settings['liftgate_delivery'] == 'yes') ? $post_data['accessorial'][] = 'LFTD' : '';
        ($this->recent_quote_settings['residential_delivery'] == 'yes') ? $post_data['accessorial'][] = 'HOMD' : '';

        $post_data = $this->yrc_update_carrier_service($post_data);

        // Standard Packaging
        // Configure standard plugin with pallet packaging addon
        $post_data = apply_filters('en_pallet_identify', $post_data);

        // Eniture debug mood
        do_action("eniture_debug_mood", "Build Query", http_build_query($post_data));
        do_action("eniture_debug_mood", "YRC Features", get_option('eniture_plugin_10'));
        do_action("eniture_debug_mood", "Quotes Request (YRC)", $post_data);

        return $post_data;
    }

    /**
     * @return shipment days of a week  - Cuttoff time
     */
    public function yrc_shipment_week_days()
    {
        $shipment_days_of_week = array();

        if (get_option('all_shipment_days_yrc') == 'yes') {
            return $shipment_days_of_week;
        }

        if (get_option('monday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 1;
        }
        if (get_option('tuesday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 2;
        }
        if (get_option('wednesday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 3;
        }
        if (get_option('thursday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 4;
        }
        if (get_option('friday_shipment_day_yrc') == 'yes') {
            $shipment_days_of_week[] = 5;
        }

        return $shipment_days_of_week;
    }

    /**
     * Return version numbers
     * @return int
     */
    function en_version_numbers()
    {
        if (!function_exists('get_plugins'))
            require_once(ABSPATH . 'wp-admin/includes/plugin.php');

        $plugin_folder = get_plugins('/' . 'woocommerce');
        $plugin_file = 'woocommerce.php';
        $wc_plugin = (isset($plugin_folder[$plugin_file]['Version'])) ? $plugin_folder[$plugin_file]['Version'] : "";
        $get_plugin_data = get_plugin_data(YRC_MAIN_FILE);
        $plugin_version = (isset($get_plugin_data['Version'])) ? $get_plugin_data['Version'] : '';

        $versions = array(
            "woocommerce_plugin_version" => $wc_plugin,
            "en_current_plugin_version" => $plugin_version
        );

        return $versions;
    }

    /**
     * YRC Line Items
     * @param $packages
     * @return string
     */
    function yrc_get_line_items($packages)
    {
        $lineItem = array();
        foreach ($packages['items'] as $item) {
            // Standard Packaging
            $ship_as_own_pallet = isset($item['ship_as_own_pallet']) && $item['ship_as_own_pallet'] == 'yes' ? 1 : 0;
            $vertical_rotation_for_pallet = isset($item['vertical_rotation_for_pallet']) && $item['vertical_rotation_for_pallet'] == 'yes' ? 1 : 0;
            $counter = (isset($item['variantId']) && $item['variantId'] > 0) ? $item['variantId'] : $item['productId'];
            $nmfc_num = (isset($item['nmfc_number'])) ? $item['nmfc_number'] : '';
            $lineItem[$counter] = array(
                'lineItemHeight' => $item['productHeight'],
                'lineItemLength' => $item['productLength'],
                'lineItemWidth' => $item['productWidth'],
                'lineItemClass' => $item['productClass'],
                'lineItemWeight' => $item['productWeight'],
                'piecesOfLineItem' => $item['productQty'],
                'lineItemPackageCode' => 'PLT',
               // 'lineItemNMFC' => $nmfc_num,
                'hazmatInd' => $item['hazmat'] == 'yes' ? 'Y' : 'N',

                // Shippable handling units
                'lineItemPalletFlag' => $item['lineItemPalletFlag'],
                'lineItemPackageType' => $item['lineItemPackageType'],

                // Standard Packaging
                'shipPalletAlone' => $ship_as_own_pallet,
                'vertical_rotation' => $vertical_rotation_for_pallet
            );
            $lineItem[$counter] = apply_filters('en_fdo_carrier_service', $lineItem[$counter], $item);
        }
        return $lineItem;
    }

    /**
     * Get YRC Country Code
     * @param $sCountryName
     * @return string
     */
    function yrc_get_country_code($sCountryName)
    {
        switch (trim($sCountryName)) {
            case 'CN':
                $sCountryName = "CAN";
                break;
            case 'CA':
                $sCountryName = "CAN";
                break;
            case 'CAN':
                $sCountryName = "CAN";
                break;
            case 'US':
                $sCountryName = "USA";
                break;
            case 'USA':
                $sCountryName = "USA";
                break;
        }
        return $sCountryName;
    }

    function destinationAddressYrc()
    {
        $yrc_woo_obj = new YRC_Woo_Update_Changes();

        $freight_zipcode = (strlen(WC()->customer->get_shipping_postcode()) > 0) ? WC()->customer->get_shipping_postcode() : $yrc_woo_obj->yrc_postcode();
        $freight_state = (strlen(WC()->customer->get_shipping_state()) > 0) ? WC()->customer->get_shipping_state() : $yrc_woo_obj->yrc_getState();
        $freight_country = (strlen(WC()->customer->get_shipping_country()) > 0) ? WC()->customer->get_shipping_country() : $yrc_woo_obj->yrc_getCountry();
        $freight_city = (strlen(WC()->customer->get_shipping_city()) > 0) ? WC()->customer->get_shipping_city() : $yrc_woo_obj->yrc_getCity();
        return array(
            'city' => $freight_city,
            'state' => $freight_state,
            'zip' => $freight_zipcode,
            'country' => $freight_country
        );
    }

    /**
     * Get Nearest Address If Multiple Warehouses
     * @param $warehous_list
     * @param $receiverZipCode
     * @return Warehouse Address
     */
    function yrc_multi_warehouse($warehous_list, $receiverZipCode)
    {
        if (count($warehous_list) == 1) {
            $warehous_list = reset($warehous_list);
            return $this->yrc_origin_array($warehous_list);
        }

        $yrc_distance_request = new Get_yrc_quotes_distance();
        $accessLevel = "MultiDistance";
        $response_json = $yrc_distance_request->yrc_quotes_get_distance($warehous_list, $accessLevel, $this->destinationAddressYrc());
        $response_json = json_decode($response_json);
        $min_dist_origin = isset($response_json->origin_with_min_dist) ? $response_json->origin_with_min_dist : '';

        return $this->yrc_origin_array($min_dist_origin);
    }

    /**
     * Create Origin Array
     * @param $origin
     * @return Warehouse Address Array
     */
    function yrc_origin_array($origin)
    {
//      In-store pickup and local delivery
        if (has_filter("en_yrc_wd_origin_array_set")) {
            return apply_filters("en_yrc_wd_origin_array_set", $origin);
        }
        return array('locationId' => $origin->id, 'zip' => $origin->zip, 'city' => $origin->city, 'state' => $origin->state, 'location' => $origin->location, 'country' => $origin->country);
    }

    /**
     * Refine URL
     * @param $domain
     * @return Domain URL
     */
    function yrc_parse_url($domain)
    {
        $domain = trim($domain);
        $parsed = parse_url($domain);

        if (empty($parsed['scheme'])) {
            $domain = 'http://' . ltrim($domain, '/');
        }

        $parse = parse_url($domain);
        $refinded_domain_name = $parse['host'];
        $domain_array = explode('.', $refinded_domain_name);

        if (in_array('www', $domain_array)) {
            $key = array_search('www', $domain_array);
            unset($domain_array[$key]);
            if(phpversion() < 8) {
                $refinded_domain_name = implode($domain_array, '.'); 
            }else {
                $refinded_domain_name = implode('.', $domain_array);
            }
        }
        return $refinded_domain_name;
    }

    /**
     * Curl Request To Get Quotes
     * @param $request_data
     * @return json/array
     */
    function yrc_get_web_quotes($request_data)
    {
        // check response from session
        $currentData = md5(json_encode($request_data));
        $requestFromSession = WC()->session->get('previousRequestData');
        $requestFromSession = ((is_array($requestFromSession)) && (!empty($requestFromSession))) ? $requestFromSession : array();

        if (isset($requestFromSession[$currentData]) && (!empty($requestFromSession[$currentData]))) {
            $this->InstorPickupLocalDelivery = (isset(json_decode($requestFromSession[$currentData])->InstorPickupLocalDelivery) ? json_decode($requestFromSession[$currentData])->InstorPickupLocalDelivery : NULL);
            // Eniture debug mood
            do_action("eniture_debug_mood", "Plugin Features (YRC)", get_option('eniture_plugin_10'));
            do_action("eniture_debug_mood", "Quotes Response Session (YRC)", json_decode($requestFromSession[$currentData]));
            return $this->parse_yrc_output($requestFromSession[$currentData], $request_data);
        }

        if (is_array($request_data) && count($request_data) > 0) {
            $yrc_curl_obj = new YRC_Curl_Request();
            $output = $yrc_curl_obj->yrc_get_curl_response(YRC_FREIGHT_DOMAIN_HITTING_URL . '/index.php', $request_data);
            $response = json_decode($output);
            // Set response in session
            if (isset($response->q))  {
                if (isset($response->autoResidentialSubscriptionExpired) &&
                    ($response->autoResidentialSubscriptionExpired == 1)) {
                    $flag_api_response = "no";
                    $request_data['residential_detecion_flag'] = $flag_api_response;
                }
                $currentData = md5(json_encode($request_data));
                $requestFromSession[$currentData] = $output;
                WC()->session->set('previousRequestData', $requestFromSession);
            }

//          Eniture debug mood
            do_action("eniture_debug_mood", "Plugin Features (YRC)", get_option('eniture_plugin_10'));
            do_action("eniture_debug_mood", "Quotes Response (YRC)", json_decode($output));

            $response = json_decode($output);

            $this->InstorPickupLocalDelivery = (isset($response->InstorPickupLocalDelivery) ? $response->InstorPickupLocalDelivery : NULL);

            return $this->parse_yrc_output($output, $request_data);
        }
    }

    /**
     * Accessoarials excluded
     * @param $excluded
     * @return array
     */
    public function en_accessorial_excluded($excluded)
    {
        return array_merge($excluded, $this->en_accessorial_excluded);
    }

    /**
     * Get Shipping Array For Single Shipment
     * @param $output
     * @return Single Quote Array
     */
    function parse_yrc_output($output, $request_data)
    {
        $result = json_decode($output);

        // FDO
        $en_fdo_meta_data = (isset($request_data['en_fdo_meta_data'])) ? $request_data['en_fdo_meta_data'] : '';
        if (isset($result->fdo_handling_unit)) {
            $en_fdo_meta_data['handling_unit_details'] = $result->fdo_handling_unit;
        }

        // Cuttoff Time
        $delivery_estimates = (isset($result->q->totalTransitTimeInDays)) ? $result->q->totalTransitTimeInDays : '';
        $delivery_time_stamp = (isset($result->q->deliveryDate)) ? $result->q->deliveryDate : '';

        // Excluded accessoarials
        $excluded = false;
        $this->quote_settings = $this->recent_quote_settings;
        if (isset($result->q->liftgateExcluded) && $result->q->liftgateExcluded == 1) {
            $this->quote_settings['liftgate_delivery'] = 'no';
            $this->quote_settings['liftgate_delivery_option'] = 'no';
            $this->quote_settings['liftgate_resid_delivery'] = "no";
            $this->quote_settings['residential_delivery'] = "no";
            $this->en_accessorial_excluded = ['liftgateResidentialExcluded'];
            add_filter('en_accessorial_excluded', [$this, 'en_accessorial_excluded'], 10, 1);
            $en_fdo_meta_data['accessorials']['residential'] = false;
            $en_fdo_meta_data['accessorials']['liftgate'] = false;
            $excluded = true;
        }

        // Pallet
        $products = (isset($request_data['products'])) ? $request_data['products'] : [];

        // Pallet
        $standard_packaging = (isset($result->standardPackagingData->response)) ? json_decode(json_encode($result->standardPackagingData->response), true) : [];

        if (isset($standard_packaging['pallets_packed']) && !empty($standard_packaging['pallets_packed'])) {
            foreach ($standard_packaging['pallets_packed'] as $bins_packed_key => $bins_packed_value) {
                $bin_items = (isset($bins_packed_value['items'])) ? $bins_packed_value['items'] : [];
                foreach ($bin_items as $bin_items_key => $bin_items_value) {
                    $bin_item_id = (isset($bin_items_value['id'])) ? $bin_items_value['id'] : '';
                    $get_product_name = (isset($products[$bin_item_id])) ? $products[$bin_item_id] : '';
                    if (isset($standard_packaging['pallets_packed'][$bins_packed_key]['items'][$bin_items_key])) {
                        $standard_packaging['pallets_packed'][$bins_packed_key]['items'][$bin_items_key]['product_name'] = $get_product_name;
                    }
                }
            }
        }

        // Standard Packaging
        $standard_packaging = (!isset($standard_packaging['response']) && !empty($standard_packaging)) ? ['response' => $standard_packaging] : $standard_packaging;
        $accessorials = array();

        ($this->quote_settings['liftgate_delivery'] == "yes") ? $accessorials[] = "L" : "";
        ($this->quote_settings['residential_delivery'] == "yes") ? $accessorials[] = "R" : "";
        (is_array($request_data['hazardous']) && !empty($request_data['hazardous'])) ? $accessorials[] = "H" : "";

        $label_sufex_arr = $this->filter_label_sufex_array_yrc($result);
        if (isset($result->q) && empty($result->q->error) && !empty($result->q->BodyMain->RateQuote->RatedCharges->TotalCharges) && $this->quote_settings['yrc_rates_based'] == 'freight') {
            $quoteId = isset($result->q->BodyMain->RateQuote->QuoteId) &&
            !empty($result->q->BodyMain->RateQuote->QuoteId) ? $result->q->BodyMain->RateQuote->QuoteId : '';

            $meta_data['service_type'] = 'Freight';
            $meta_data['accessorials'] = json_encode($accessorials);
            $meta_data['sender_origin'] = $request_data['sender_origin'];
            $meta_data['product_name'] = json_encode($request_data['product_name']);
            $meta_data['quote_id'] = $quoteId;
            $meta_data['standard_packaging'] = json_encode($standard_packaging);

            $quotes = array(
                'id' => 'yrc_' . $meta_data['service_type'],
                'cost' => $result->q->BodyMain->RateQuote->RatedCharges->TotalCharges / 100,
                'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                'transit_time' => $result->q->BodyMain->RateQuote->Delivery->StandardDays,
                // Cuttoff Time
                'delivery_estimates' => $delivery_estimates,
                'delivery_time_stamp' => $delivery_time_stamp,
                'label_sfx_arr' => $label_sufex_arr,
                'meta_data' => $meta_data,
                'markup' => $this->quote_settings['handling_fee'],
                'markup2' => $this->quote_settings['handling_fee2'],
                'surcharges' => (isset($result->q->BodyMain->RateQuote->LineItem)) ? $this->update_parse_yrc_output($result->q->BodyMain->RateQuote->LineItem) : 0,
            );

            // FDO
            $en_fdo_meta_data['rate'] = $quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

//          To Identify Auto Detect Residential address detected Or Not                        
            $quotes = apply_filters("en_woo_addons_web_quotes", $quotes, en_woo_plugin_yrc_quotes);
            $label_sufex = (isset($quotes['label_sufex'])) ? $quotes['label_sufex'] : array();
            $label_sufex = $this->label_R_yrc_view($label_sufex);
            $quotes['label_sufex'] = $label_sufex;

            in_array('R', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

//          When Lift Gate As An Option Enabled
            if (($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                $service = $quotes;
                $quotes['id'] .= "WL";

                (isset($quotes['label_sufex']) &&
                    (!empty($quotes['label_sufex']))) ?
                    array_push($quotes['label_sufex'], "L") : // IF
                    $quotes['label_sufex'] = array("L");       // ELSE

                // FDO
                $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                $quotes['append_label'] = " with lift gate delivery ";

                $liftgate_charge = (isset($service['surcharges']['LFTD'])) ? $service['surcharges']['LFTD'] : 0;
                $service['cost'] = (isset($service['cost'])) ? $service['cost'] - $liftgate_charge : 0;
                (!empty($service)) && (in_array("R", $service['label_sufex'])) ? $service['label_sufex'] = array("R") : $service['label_sufex'] = array();

                $simple_quotes = $service;

                // FDO
                if (isset($simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                    $simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'] = $service['cost'];
                }
            } elseif ($excluded) {
                // Excluded accessoarials
                $simple_quotes = $quotes;
            }

        } elseif ((isset($result->q) && empty($result->q->errors) && isset($this->quote_settings['yrc_rates_based']) && $this->quote_settings['yrc_rates_based'] == 'dimension' && isset($result->q->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges) && !empty($result->q->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges)) ||
            (isset($result->quotesWithoutLiftGate) && !empty($result->quotesWithoutLiftGate) && isset($this->quote_settings['yrc_rates_based']) && $this->quote_settings['yrc_rates_based'] == 'dimension' && empty($result->quotesWithoutLiftGate->error))) {
            $quotes = [];
            if (isset($result->q) && empty($result->q->errors) && isset($this->quote_settings['yrc_rates_based']) && $this->quote_settings['yrc_rates_based'] == 'dimension' && isset($result->q->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges) && !empty($result->q->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges)) {

                $quoteId = isset($result->q->pageRoot->bodyMain->rateQuote->quoteId) &&
                !empty($result->q->pageRoot->bodyMain->rateQuote->quoteId) ? $result->q->pageRoot->bodyMain->rateQuote->quoteId : '';
                $meta_data['service_type'] = 'Freight';
                $meta_data['accessorials'] = json_encode($accessorials);
                $meta_data['sender_origin'] = $request_data['sender_origin'];
                $meta_data['product_name'] = json_encode($request_data['product_name']);
                $meta_data['quote_id'] = $quoteId;
                $meta_data['standard_packaging'] = json_encode($standard_packaging);

                $timestamp = strtotime($result->q->pageRoot->bodyMain->rateQuote->delivery->deliveryDate);
                $new_date_format = date('Y-m-d', $timestamp);
                $quotes = array(
                    'id' => 'yrc_' . $meta_data['service_type'],
                    'cost' => $result->q->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges / 100,
                    'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                    'transit_time' => $new_date_format,
                    // Cuttoff Time
                    'delivery_estimates' => $delivery_estimates,
                    'delivery_time_stamp' => $delivery_time_stamp,
                    'label_sfx_arr' => $label_sufex_arr,
                    'meta_data' => $meta_data,
                    'markup' => $this->quote_settings['handling_fee'],
                    'markup2' => $this->quote_settings['handling_fee2'],
                    'surcharges' => (isset($result->q->pageRoot->bodyMain->rateQuote->lineItem)) ? $this->update_parse_yrc_output($result->q->pageRoot->bodyMain->rateQuote->lineItem) : 0,
                );
            }
            // FDO
            $en_fdo_meta_data['rate'] = $quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

//          To Identify Auto Detect Residential address detected Or Not
            $quotes = apply_filters("en_woo_addons_web_quotes", $quotes, en_woo_plugin_yrc_quotes);
            $label_sufex = (isset($quotes['label_sufex'])) ? $quotes['label_sufex'] : array();
            $label_sufex = $this->label_R_yrc_view($label_sufex);

            if (isset($result->quotesWithoutLiftGate)) {
                $label_sufex[] = 'L';
                $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
            }

            $quotes['label_sufex'] = $label_sufex;

            in_array('R', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

            //LiftGate as An Option
            if (isset($result->quotesWithoutLiftGate) && ($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                $quoteId = isset($result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->quoteId) &&
                !empty($result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->quoteId) ? $result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->quoteId : '';

                $meta_data = [];
                $meta_data['service_type'] = 'freight_without_liftgate';
                $meta_data['accessorials'] = json_encode($accessorials);
                $meta_data['sender_origin'] = $request_data['sender_origin'];
                $meta_data['product_name'] = json_encode($request_data['product_name']);
                $meta_data['quote_id'] = $quoteId;
                $meta_data['standard_packaging'] = json_encode($standard_packaging);

                $timestamp = strtotime($result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->delivery->deliveryDate);
                $new_date_format = date('Y-m-d', $timestamp);
                $simple_quotes = array(
                    'id' => 'yrc_with_out_liftgate',
                    'cost' => $result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->ratedCharges->totalCharges / 100,
                    'label' => (strlen($this->quote_settings['label']) > 0) ? $this->quote_settings['label'] : 'Freight',
                    'transit_time' => $new_date_format,
                    // Cuttoff Time
                    'delivery_estimates' => $delivery_estimates,
                    'delivery_time_stamp' => $delivery_time_stamp,
                    'label_sfx_arr' => $label_sufex_arr,
                    'meta_data' => $meta_data,
                    'markup' => $this->quote_settings['handling_fee'],
                    'markup2' => $this->quote_settings['handling_fee2'],
                    'surcharges' => (isset($result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->lineItem)) ? $this->update_parse_yrc_output($result->quotesWithoutLiftGate->pageRoot->bodyMain->rateQuote->lineItem) : 0,
                );
                // FDO
                $en_fdo_meta_data['rate'] = $simple_quotes;
                if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                    unset($en_fdo_meta_data['rate']['meta_data']);
                }
                $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
                $simple_quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

                $simple_quotes = apply_filters("en_woo_addons_web_quotes", $simple_quotes, en_woo_plugin_yrc_quotes);
                $label_sufex = (isset($simple_quotes['label_sufex'])) ? $simple_quotes['label_sufex'] : array();
                $label_sufex = $this->label_R_yrc_view($label_sufex);

                if (in_array('L', $label_sufex)) {
                    $label_sufex_fliped = array_flip($label_sufex);
                    unset($label_sufex_fliped['L']);
                    $label_sufex = array_flip($label_sufex_fliped);
                }

                $simple_quotes['label_sufex'] = $label_sufex;

                // FDO
                (!empty($simple_quotes)) && (in_array("R", $simple_quotes['label_sufex'])) ? $simple_quotes['label_sufex'] = array("R") : $simple_quotes['label_sufex'] = array();

            } elseif ($excluded) {
                // Excluded accessoarials
                $simple_quotes = $quotes;
            }
        } else {
            return [];
            $meta_data['service_type'] = 'no_quotes';
            $meta_data['accessorials'] = json_encode($accessorials);
            $meta_data['sender_origin'] = $request_data['sender_origin'];
            $meta_data['product_name'] = json_encode($request_data['product_name']);
            $meta_data['quote_id'] = '';
            $meta_data['standard_packaging'] = json_encode([]);

            $quotes = array(
                'id' => 'yrc_' . $meta_data['service_type'],
                'cost' => 0,
                'label' => '',
                'label_sfx_arr' => $label_sufex_arr,
                'meta_data' => $meta_data,
                'markup' => $this->quote_settings['handling_fee'],
                'markup2' => $this->quote_settings['handling_fee2'],
                'surcharges' => [],
            );

            // FDO
            $en_fdo_meta_data['rate'] = $quotes;
            if (isset($en_fdo_meta_data['rate']['meta_data'])) {
                unset($en_fdo_meta_data['rate']['meta_data']);
            }
            $en_fdo_meta_data['quote_settings'] = $this->quote_settings;
            $quotes['meta_data']['en_fdo_meta_data'] = $en_fdo_meta_data;

//          To Identify Auto Detect Residential address detected Or Not
            $quotes = apply_filters("en_woo_addons_web_quotes", $quotes, en_woo_plugin_yrc_quotes);
            $label_sufex = (isset($quotes['label_sufex'])) ? $quotes['label_sufex'] : array();
            $label_sufex = $this->label_R_yrc_view($label_sufex);
            $quotes['label_sufex'] = $label_sufex;

            in_array('R', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['residential'] = true : '';
            ($this->quote_settings['liftgate_resid_delivery'] == "yes") && (in_array("R", $label_sufex)) && in_array('L', $label_sufex_arr) ? $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true : '';

//          When Lift Gate As An Option Enabled
            if (($this->quote_settings['liftgate_delivery_option'] == "yes") &&
                (($this->quote_settings['liftgate_resid_delivery'] == "yes") && (!in_array("R", $label_sufex)) ||
                    ($this->quote_settings['liftgate_resid_delivery'] != "yes"))) {
                $service = $quotes;
                $quotes['id'] .= "WL";

                (isset($quotes['label_sufex']) &&
                    (!empty($quotes['label_sufex']))) ?
                    array_push($quotes['label_sufex'], "L") : // IF
                    $quotes['label_sufex'] = array("L");       // ELSE

                // FDO
                $quotes['meta_data']['en_fdo_meta_data']['accessorials']['liftgate'] = true;
                $quotes['append_label'] = " with lift gate delivery ";

                $service['cost'] = 0;
                (!empty($service)) && (in_array("R", $service['label_sufex'])) ? $service['label_sufex'] = array("R") : $service['label_sufex'] = array();

                $simple_quotes = $service;

                // FDO
                if (isset($simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'])) {
                    $simple_quotes['meta_data']['en_fdo_meta_data']['rate']['cost'] = $service['cost'];
                }
            }
        }

        (!empty($simple_quotes)) ? $quotes['simple_quotes'] = $simple_quotes : "";

        return $quotes;
    }

    /**
     * Return YRC LTL In-store Pickup Array
     */
    function yrc_ltl_return_local_delivery_store_pickup()
    {
        return $this->InstorPickupLocalDelivery;
    }

    /**
     * check "R" in array
     * @param array type $label_sufex
     * @return array type
     */
    public function label_R_yrc_view($label_sufex)
    {
        if ($this->quote_settings['residential_delivery'] == 'yes' && (in_array("R", $label_sufex))) {
            $label_sufex = array_flip($label_sufex);
            unset($label_sufex['R']);
            $label_sufex = array_keys($label_sufex);

        }

        return $label_sufex;
    }

}
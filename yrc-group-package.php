<?php
/**
 * YRC WooComerce | Get Shipping Package Class
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * YRC WooComerce | Get Shipping Package Class
 */
class YRC_Shipping_Get_Package
{
    // Micro Warehouse
    public $products = [];
    public $dropship_location_array = [];
    public $warehouse_products = [];
    public $destination_Address_yrc;
    public $origin;
    // Images for FDO
    public $en_fdo_image_urls = [];

    /**
     * hasLTLShipment
     * @var int
     */
    public $hasLTLShipment = 0;

    /**
     * Errors
     * @var varchar
     */
    public $errors = [];
    public $ValidShipments = 0;

    public $ValidShipmentsArrYrc = [];

    /**
     * Grouping For Shipments
     * @param $package
     * @param $yrc_res_inst
     * @param $freight_zipcode
     * @return Shipment Grouped Array
     * @global $wpdb
     */
    function group_yrc_shipment($package, $yrc_res_inst, $freight_zipcode)
    {
        if (empty($freight_zipcode)) {
            return [];
        }
        global $wpdb;
        $weight = 0;
        $dimensions = 0;
        $yrc_freight_class = "";
        $yrc_enable = false;
        $validShipmentForLtl = '';

        $counter = 0;

        // Micro Warehouse
        $YRC_Get_Shipping_Quotes = new YRC_Get_Shipping_Quotes();
        $this->destination_Address_yrc = $YRC_Get_Shipping_Quotes->destinationAddressYrc();
        // threshold
        $weight_threshold = get_option('en_weight_threshold_lfq');
        $weight_threshold = isset($weight_threshold) && $weight_threshold > 0 ? $weight_threshold : 150;

        $wc_settings_wwe_ignore_items = get_option("en_ignore_items_through_freight_classification");
        $en_get_current_classes = strlen($wc_settings_wwe_ignore_items) > 0 ? trim(strtolower($wc_settings_wwe_ignore_items)) : '';
        $en_get_current_classes_arr = strlen($en_get_current_classes) > 0 ? array_map('trim', explode(',', $en_get_current_classes)) : [];

        // Standard Packaging
        $en_ppp_pallet_product = apply_filters('en_ppp_existence', false);

        $flat_rate_shipping_addon = apply_filters('en_add_flat_rate_shipping_addon', false);
        foreach ($package['contents'] as $item_id => $values) {
            $_product = $values['data'];

            // Images for FDO
            $this->en_fdo_image_urls($values, $_product);

            // Flat rate pricing
            $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
            $en_flat_rate_price = get_post_meta($product_id, 'en_flat_rate_price', true);
            if ($flat_rate_shipping_addon && isset($en_flat_rate_price) && strlen($en_flat_rate_price) > 0) {
                continue;
            }

            // Get product shipping class
            $en_ship_class = strtolower($values['data']->get_shipping_class());
            if (in_array($en_ship_class, $en_get_current_classes_arr)) {
                continue;
            }

            // Shippable handling units
            $values = apply_filters('en_shippable_handling_units_request', $values, $values, $_product);
            $shippable = [];
            if (isset($values['shippable']) && !empty($values['shippable'])) {
                $shippable = $values['shippable'];
            }

            // Standard Packaging
            $ppp_product_pallet = [];
            $values = apply_filters('en_ppp_request', $values, $values, $_product);
            if (isset($values['ppp']) && !empty($values['ppp'])) {
                $ppp_product_pallet = $values['ppp'];
            }
            $ship_as_own_pallet = $vertical_rotation_for_pallet = 'no';
            if (!$en_ppp_pallet_product) {
                $ppp_product_pallet = [];
            }
            extract($ppp_product_pallet);

            $p_height = str_replace( array( "'",'"' ),'',$_product->get_height());
            $p_width = str_replace( array( "'",'"' ),'',$_product->get_width());
            $p_length = str_replace( array( "'",'"' ),'',$_product->get_length());
            $height = is_numeric($p_height) ? $p_height : 0;
            $width = is_numeric($p_width) ? $p_width : 0;
            $length = is_numeric($p_length) ? $p_length : 0;
            $height = ceil(wc_get_dimension($height, 'in'));
            $width = ceil(wc_get_dimension($width, 'in'));
            $length = ceil(wc_get_dimension($length, 'in'));
            $product_weight = round(wc_get_weight($_product->get_weight(), 'lbs'), 2);
            $weight = $product_weight * $values['quantity'];
            $dimensions = (($length * $values['quantity']) * $width * $height);
            $freightClass = $_product->get_shipping_class(); // it define either product marked as ltl or not
            if ($freightClass == 'ltl_freight') {
                $ltl_freight_class = $freightClass;
            }
            $locationId = 0;
            (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
            $locations_list = $this->yrc_get_locations_list($post_id);
            $origin_address = $yrc_res_inst->yrc_multi_warehouse($locations_list, $freight_zipcode);

            $locationId = (isset($origin_address['id'])) ? $origin_address['id'] : $origin_address['locationId'];

            // Micro Warehouse
            (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id = $_product->get_id();
            $this->products[] = $post_id;

            $yrc_package[$locationId]['origin'] = $origin_address;
            $getFreightClassAndHazardous = $this->yrc_get_freight_class_hazardous($_product, $values['variation_id'], $values['product_id']);

            //hazardous_material
            $hm_plan = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'hazardous_material');
            $hm_status = (!is_array($hm_plan) && $getFreightClassAndHazardous['hazardous_material'] == 'yes') ? TRUE : FALSE;

            ($getFreightClassAndHazardous["freightClass_ltl_gross"] == 'Null') ? $getFreightClassAndHazardous["freightClass_ltl_gross"] = "" : "";

            // Shippable handling units
            $lineItemPalletFlag = $lineItemPackageCode = $lineItemPackageType = '0';
            extract($shippable);

            $product_title = str_replace(array("'", '"'), '', $_product->get_title());
            $en_items = [
                'productId' => $_product->get_id(),
                'product_name' => $values['quantity'] . " x " . $product_title,
                'products' => $product_title,
                'productQty' => $values['quantity'],
                'productName' => $product_title,
                'productPrice' => $_product->get_price(),
                'productWeight' => $product_weight,
                'productLength' => $length,
                'productWidth' => $width,
                'productHeight' => $height,
                'productClass' => $getFreightClassAndHazardous["freightClass_ltl_gross"],
                'freightClass' => $freightClass,
                'hazmat' => $getFreightClassAndHazardous['hazardous_material'],

                'hazardousMaterial' => $hm_status,
                'hazardous_material' => $hm_status,
                'productType' => ($_product->get_type() == 'variation') ? 'variant' : 'simple',
                'productSku' => $_product->get_sku(),
                'actualProductPrice' => $_product->get_price(),
                'attributes' => $_product->get_attributes(),
                'variantId' => ($_product->get_type() == 'variation') ? $_product->get_id() : '',

                // Shippable handling units
                'lineItemPalletFlag' => $lineItemPalletFlag,
                'lineItemPackageCode' => $lineItemPackageCode,
                'lineItemPackageType' => $lineItemPackageType,

                // Standard Packaging
                'ship_as_own_pallet' => $ship_as_own_pallet,
                'vertical_rotation_for_pallet' => $vertical_rotation_for_pallet
            ];

            // Hook for flexibility adding to package
            $en_items = apply_filters('en_group_package', $en_items, $values, $_product);
            // NMFC Number things
            $en_items = $this->en_group_package($en_items, $values, $_product);
            // Micro Warehouse
            $items[$post_id] = $en_items;

            if (!empty($origin_address)) {
                $locationId = (isset($origin_address['id'])) ? $origin_address['id'] : $origin_address['locationId'];
                $yrc_package[$locationId]['origin'] = $origin_address;
                if (!$_product->is_virtual()) {

                    $yrc_package[$locationId]['items'][$counter] = $en_items;

                    $validateProductParamsRtrn = $this->validateProductParams($yrc_package[$locationId]['items'][$counter]);
                    (isset($validateProductParamsRtrn) && ($validateProductParamsRtrn === 1)) ? $validShipmentForLtl = 1 : "";
                    $yrc_package[$locationId]['items'][$counter]['validForLtl'] = $validateProductParamsRtrn;

                }

            }

            $yrc_enable = $this->get_yrc_enable($_product);

            // Micro Warehouse
            $items_shipment[$post_id] = $yrc_enable;

            $exceedWeight = get_option('en_plugins_return_LTL_quotes');
            $yrc_package[$locationId]['shipment_weight'] = isset($yrc_package[$locationId]['shipment_weight']) ? $yrc_package[$locationId]['shipment_weight'] + $weight : $weight;
            $yrc_package[$locationId]['hazardousMaterial'] = isset($yrc_package[$locationId]['hazardousMaterial']) && $yrc_package[$locationId]['hazardousMaterial'] == 'yes' ? $yrc_package[$locationId]['hazardousMaterial'] : $getFreightClassAndHazardous["hazardous_material"];

            $yrc_package[$locationId]['validShipmentForLtl'] = $validShipmentForLtl;
            (isset($validShipmentForLtl) && ($validShipmentForLtl === 1)) ? $this->ValidShipments = 1 : "";

            $smallPluginExist = 0;
            $calledMethod = [];
            $eniturePluigns = json_decode(get_option('EN_Plugins'));

            if (!empty($eniturePluigns)) {
                foreach ($eniturePluigns as $enIndex => $enPlugin) {
                    $freightSmallClassName = 'WC_' . $enPlugin;

                    if (!in_array($freightSmallClassName, $calledMethod)) {
                        if (class_exists($freightSmallClassName)) {
                            $smallPluginExist = 1;
                        }
                        $calledMethod[] = $freightSmallClassName;
                    }
                }
            }

            if ($yrc_enable == true || ($yrc_package[$locationId]['shipment_weight'] > $weight_threshold && $exceedWeight == 'yes')) {
                $yrc_package[$locationId]['yrc'] = 1;
                $this->hasLTLShipment = 1;
                $this->ValidShipmentsArrYrc[] = "ltl_freight";
            } elseif (isset($yrc_package[$locationId]['yrc'])) {
                $yrc_package[$locationId]['yrc'] = 1;
                $this->hasLTLShipment = 1;
                $this->ValidShipmentsArrYrc[] = "ltl_freight";
            } elseif ($smallPluginExist == 1) {
                $yrc_package[$locationId]['small'] = 1;
                $this->ValidShipmentsArrYrc[] = "small_shipment";
            } else {
                $this->ValidShipmentsArrYrc[] = "no_shipment";
            }

            $counter++;
        }

        // Eniture debug mood
        // Micro Warehouse
        $eniureLicenceKey = get_option('wc_settings_yrc_plugin_licence_key');
        $yrc_package = apply_filters('en_micro_warehouse', $yrc_package, $this->products, $this->dropship_location_array, $this->destination_Address_yrc, $this->origin, $smallPluginExist, $items, $items_shipment, $this->warehouse_products, $eniureLicenceKey, 'yrc');
        do_action("eniture_debug_mood", "Product Detail (YRC)", $yrc_package);
        return $yrc_package;
    }

    /**
     * Set images urls | Images for FDO
     * @param array type $en_fdo_image_urls
     * @return array type
     */
    public function en_fdo_image_urls_merge($en_fdo_image_urls)
    {
        return array_merge($this->en_fdo_image_urls, $en_fdo_image_urls);
    }

    /**
     * Get images urls | Images for FDO
     * @param array type $values
     * @param array type $_product
     * @return array type
     */
    public function en_fdo_image_urls($values, $_product)
    {
        $product_id = (isset($values['variation_id']) && $values['variation_id'] > 0) ? $values['variation_id'] : $_product->get_id();
        $gallery_image_ids = $_product->get_gallery_image_ids();
        foreach ($gallery_image_ids as $key => $image_id) {
            $gallery_image_ids[$key] = $image_id > 0 ? wp_get_attachment_url($image_id) : '';
        }

        $image_id = $_product->get_image_id();
        $this->en_fdo_image_urls[$product_id] = [
            'product_id' => $product_id,
            'image_id' => $image_id > 0 ? wp_get_attachment_url($image_id) : '',
            'gallery_image_ids' => $gallery_image_ids
        ];

        add_filter('en_fdo_image_urls_merge', [$this, 'en_fdo_image_urls_merge'], 10, 1);
    }

    /**
     *
     * @param type $productData
     * @return int
     */

    function validateProductParams($productData)
    {
        if ((!isset($productData['freightClass']) || $productData['freightClass'] != "ltl_freight")) {
            return 0;
        }
        return 1;

    }

    /**
     * Check enable_dropship and get Locations list
     * @param $post_id
     * @return Shipping Class
     * @global $wpdb
     */
    function yrc_get_locations_list($post_id)
    {
        global $wpdb;

        $locations_list = [];

        (isset($values['variation_id']) && $values['variation_id'] > 0) ? $post_id = $values['variation_id'] : $post_id;
        $enable_dropship = get_post_meta($post_id, '_enable_dropship', true);
        if ($enable_dropship == 'yes') {
            $get_loc = get_post_meta($post_id, '_dropship_location', true);
            if ($get_loc == '') {
                // Micro Warehouse
                $this->warehouse_products[] = $post_id;
                return array('error' => 'Fedex Freight dp location not found!');
            }

            // Multi Dropship
            $multi_dropship = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'multi_dropship');

            if (is_array($multi_dropship)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'dropship' LIMIT 1"
                );
            } else {
                $get_loc = ($get_loc !== '') ? maybe_unserialize($get_loc) : $get_loc;
                $get_loc = is_array($get_loc) ? implode(" ', '", $get_loc) : $get_loc;
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE id IN ('" . $get_loc . "')"
                );
            }

            // Micro Warehouse
            $this->multiple_dropship_of_prod($locations_list, $post_id);
            $eniture_debug_name = "Dropships";
        }

        if (empty($locations_list)) {
            // Multi Warehouse
            $multi_warehouse = apply_filters('yrc_quotes_quotes_plans_suscription_and_features', 'multi_warehouse');
            if (is_array($multi_warehouse)) {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse' LIMIT 1"
                );
            } else {
                $locations_list = $wpdb->get_results(
                    "SELECT * FROM " . $wpdb->prefix . "warehouse WHERE location = 'warehouse'"
                );
            }

            // Micro Warehouse
            $this->warehouse_products[] = $post_id;
            $eniture_debug_name = "Warehouses";

        }

        do_action("eniture_debug_mood", "Quotes $eniture_debug_name (s)", $locations_list);
        return $locations_list;
    }

    // Micro Warehouse
    public function multiple_dropship_of_prod($locations_list, $post_id)
    {
        $post_id = (string)$post_id;

        foreach ($locations_list as $key => $value) {
            $dropship_data = $this->address_array($value);

            $this->origin["D" . $dropship_data['zip']] = $dropship_data;
            $dropship_location_array_data = (isset($this->dropship_location_array["D" . $dropship_data['zip']])) ? $this->dropship_location_array["D" . $dropship_data['zip']] : [];
            if (!in_array($post_id, $dropship_location_array_data)) {
                $this->dropship_location_array["D" . $dropship_data['zip']][] = $post_id;
            }
        }

    }

    // Micro Warehouse
    public function address_array($value)
    {
        $dropship_data = [];

        $dropship_data['locationId'] = (isset($value->id)) ? $value->id : "";
        $dropship_data['zip'] = (isset($value->zip)) ? $value->zip : "";
        $dropship_data['city'] = (isset($value->city)) ? $value->city : "";
        $dropship_data['state'] = (isset($value->state)) ? $value->state : "";
        // Origin terminal address
        $dropship_data['address'] = (isset($value->address)) ? $value->address : "";
        // Terminal phone number
        $dropship_data['phone_instore'] = (isset($value->phone_instore)) ? $value->phone_instore : "";
        $dropship_data['location'] = (isset($value->location)) ? $value->location : "";
        $dropship_data['country'] = (isset($value->country)) ? $value->country : "";
        $dropship_data['enable_store_pickup'] = (isset($value->enable_store_pickup)) ? $value->enable_store_pickup : "";
        $dropship_data['fee_local_delivery'] = (isset($value->fee_local_delivery)) ? $value->fee_local_delivery : "";
        $dropship_data['suppress_local_delivery'] = (isset($value->suppress_local_delivery)) ? $value->suppress_local_delivery : "";
        $dropship_data['miles_store_pickup'] = (isset($value->miles_store_pickup)) ? $value->miles_store_pickup : "";
        $dropship_data['match_postal_store_pickup'] = (isset($value->match_postal_store_pickup)) ? $value->match_postal_store_pickup : "";
        $dropship_data['checkout_desc_store_pickup'] = (isset($value->checkout_desc_store_pickup)) ? $value->checkout_desc_store_pickup : "";
        $dropship_data['enable_local_delivery'] = (isset($value->enable_local_delivery)) ? $value->enable_local_delivery : "";
        $dropship_data['miles_local_delivery'] = (isset($value->miles_local_delivery)) ? $value->miles_local_delivery : "";
        $dropship_data['match_postal_local_delivery'] = (isset($value->match_postal_local_delivery)) ? $value->match_postal_local_delivery : "";
        $dropship_data['checkout_desc_local_delivery'] = (isset($value->checkout_desc_local_delivery)) ? $value->checkout_desc_local_delivery : "";

        $dropship_data['sender_origin'] = $dropship_data['location'] . ": " . $dropship_data['city'] . ", " . $dropship_data['state'] . " " . $dropship_data['zip'];

        return $dropship_data;
    }

    /**
     * Get Freight Class and Hazardous Material Checkbox
     * @param $_product
     * @param $variation_id
     * @param $product_id
     * @return Shipping Class
     */
    function yrc_get_freight_class_hazardous($_product, $variation_id, $product_id)
    {
        if ($_product->get_type() == 'variation') {
            $hazardous_material = get_post_meta($variation_id, '_hazardousmaterials', true);
            $variation_class = get_post_meta($variation_id, '_ltl_freight_variation', true);

            //get_parent condition for Freight class
            if ($variation_class == 'get_parent') {
                $variation_class = get_post_meta($product_id, '_ltl_freight', true);
                $freightClass_ltl_gross = $variation_class;
            } else {
                if ($variation_class > 0) {
                    $freightClass_ltl_gross = get_post_meta($variation_id, '_ltl_freight_variation', true);
                } else {
                    $freightClass_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
                }

                if(empty($freightClass_ltl_gross)){
                    $freightClass_ltl_gross = get_post_meta($_product->get_parent_id(), '_ltl_freight', true);
                }
            }

        } else {
            $hazardous_material = get_post_meta($_product->get_id(), '_hazardousmaterials', true);
            $freightClass_ltl_gross = get_post_meta($_product->get_id(), '_ltl_freight', true);
        }
        $aDataArr = array(
            'freightClass_ltl_gross' => $freightClass_ltl_gross,
            'hazardous_material' => $hazardous_material
        );

        return $aDataArr;
    }

    /**
     * Get YRC Enable or not
     * @param $_product
     * @return Shipping Class
     */
    function get_yrc_enable($_product)
    {
        if ($_product->get_type() == 'variation') {
            $ship_class_id = $_product->get_shipping_class_id();

            if ($ship_class_id == 0) {
                $parent_data = $_product->get_parent_data();
                $get_parent_term = get_term_by('id', $parent_data['shipping_class_id'], 'product_shipping_class');
                $get_shipping_result = (isset($get_parent_term->slug)) ? $get_parent_term->slug : '';

            } else {
                $get_shipping_result = $_product->get_shipping_class();
            }

            $yrc_enable = ($get_shipping_result && $get_shipping_result == 'ltl_freight') ? true : false;
        } else {
            $get_shipping_result = $_product->get_shipping_class();
            $yrc_enable = ($get_shipping_result == 'ltl_freight') ? true : false;
        }

        return $yrc_enable;
    }

    /**
     * Grouping For Shipment Quotes
     * @param $quotes
     * @param $handlng_fee
     * @return Total Cost
     */
    function yrc_grouped_quotes($quotes, $handlng_fee)
    {
        $totalPrice = 0;
        $grandTotal = 0;
        $grandTotalWdoutLiftGate = 0;
        $label_sfx_arr = "";
        $freight = [];

        if (count($quotes) > 0 && !empty($quotes)) {
            foreach ($quotes as $multiValues) {
                if (isset($multiValues['cost']) && !empty($multiValues['cost'])) {
                    $totalPriceLiftGate = (isset($multiValues['surcharges']['LFTD'])) ? $multiValues['surcharges']['LFTD'] : 0;

                    if ($handlng_fee != '') {
                        $grandTotal += $this->yrc_parse_handeling_fee($handlng_fee, $multiValues['cost']);
                        $grandTotalWdoutLiftGate += $this->yrc_parse_handeling_fee($handlng_fee, ($multiValues['cost'] - $totalPriceLiftGate));
                    } else {
                        $grandTotal += $multiValues['cost'];
                        $grandTotalWdoutLiftGate += $multiValues['cost'] - $totalPriceLiftGate;
                    }

                    (isset($multiValues['label_sfx_arr'])) ? $label_sfx_arr = $multiValues['label_sfx_arr'] : '';
                } else {
                    $this->errors = 'no quotes return';
                    continue;
                }
            }
        }

        $freight = array(
            'totals' => $grandTotal,
            'label_sfx_arr' => $label_sfx_arr,
            'grandTotalWdoutLiftGate' => $grandTotalWdoutLiftGate,
        );
        return $freight;
    }

    /**
     * Grouping For Small Quotes
     * @param $smallQuotes
     * @return Total Cost
     */
    function yrc_get_small_packages_cost($smallQuotes)
    {
        $result = [];
        $minCostArr = [];

        if (isset($smallQuotes) && count($smallQuotes) > 0) {
            foreach ($smallQuotes as $smQuotes) {
                $CostArr = [];
                if (!isset($smQuotes['error'])) {
                    foreach ($smQuotes as $smQuote) {
                        $CostArr[] = $smQuote['cost'];
                        $result['error'] = false;
                    }
                    $minCostArr[] = (count($CostArr) > 0) ? min($CostArr) : "";
                } else {
                    $result['error'] = !isset($result['error']) ? true : $result['error'];
                }
            }
            $result['price'] = (isset($minCostArr) && count($minCostArr) > 0) ? min($minCostArr) : "";
        } else {
            $result['error'] = false;
            $result['price'] = 0;
        }
        return $result;
    }

    /**
     * Calculate Handling Fee
     * @param $handlng_fee
     * @param $cost
     * @return int
     */
    function yrc_parse_handeling_fee($handlng_fee, $cost)
    {
        $pos = strpos($handlng_fee, '%');
        if ($pos > 0) {
            $rest = substr($handlng_fee, $pos);
            $exp = explode($rest, $handlng_fee);
            $get = $exp[0];
            $percnt = $get / 100 * $cost;
            $grandTotal = $cost + $percnt;
        } else {
            $grandTotal = $cost + $handlng_fee;
        }
        return $grandTotal;
    }

    /**
     * Get the product nmfc number
     */
    public function en_group_package($item, $product_object, $product_detail)
    {
        $en_nmfc_number = $this->en_nmfc_number($product_object, $product_detail);
        $item['nmfc_number'] = $en_nmfc_number;
        return $item;
    }

    /**
     * Get product shippable unit enabled
     */
    public function en_nmfc_number($product_object, $product_detail)
    {
        $post_id = (isset($product_object['variation_id']) && $product_object['variation_id'] > 0) ? $product_object['variation_id'] : $product_detail->get_id();
        return get_post_meta($post_id, '_nmfc_number', true);
    }
}

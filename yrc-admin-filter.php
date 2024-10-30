<?php
/**
 * YRC WooComerce Admin Settings | Plugin Shipping Settings  
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */ 
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
 * Add YRC Method In WOO Method List
 * @param $methods
 * @return string
 */
function add_yrc_logistics( $methods ) 
{
    $methods['yrc'] = 'YRC_Freight_Shipping_Class';
    return $methods;
}

/**
 * Add Woo Setting Tab For YRC
 * @param $settings
 * @return Woo Settings
 */
function yrc_shipping_sections( $settings ) 
{
    include( 'yrc-tab-class.php' );
    return $settings;
}

/**
 *  Hide Other Shipping Options
 * @param $available_methods
 * @return avalable methods
 */
function yrc_hide_shipping( $available_methods ) 
{
    $forceShowMethods = apply_filters('force_show_methods',array());

    if ( get_option( 'yrc_allow_other_plugins' ) == 'no' && (!empty($forceShowMethods)) && (!in_array("valid_third_party", $forceShowMethods))) {
        if ( count( $available_methods ) > 0 ) {
            $yrc_rates         = array();
            $plugins_array     = array();
            $eniture_plugins   = get_option('EN_Plugins');
            if( $eniture_plugins ){ 
                $plugins_array = json_decode( $eniture_plugins );
            }
            foreach ( $available_methods as $index => $method ) {
                if ( !($method->method_id == 'speedship' || $method->method_id == 'ltl_shipping_method' || in_array( $method->method_id, $plugins_array )) ) {
                    unset($available_methods[$index]);
                }
            }
        }
    } 
    return $available_methods;
}

/**
 * Filter For CSV Import
 */
if (!function_exists('en_import_dropship_location_csv')) {

    /**
     * Import drop ship location CSV
     * @param $data
     * @param $this
     * @return array
     */
    function en_import_dropship_location_csv($data, $parseData)
    {
        $_product_freight_class = $_product_freight_class_variation = '';
        $_dropship_location = $locations = [];
        foreach ($data['meta_data'] as $key => $metaData) {
            $location = explode(',', trim($metaData['value']));
            switch ($metaData['key']) {
                // Update new columns
                case '_product_freight_class':
                    $_product_freight_class = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_product_freight_class_variation':
                    $_product_freight_class_variation = trim($metaData['value']);
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_nickname':
                    $locations[0] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_zip_code':
                    $locations[1] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_city':
                    $locations[2] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_state':
                    $locations[3] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location_country':
                    $locations[4] = $location;
                    unset($data['meta_data'][$key]);
                    break;
                case '_dropship_location':
                    $_dropship_location = $location;
            }
        }

        // Update new columns
        if (strlen($_product_freight_class) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight',
                'value' => $_product_freight_class,
            ];
        }

        // Update new columns
        if (strlen($_product_freight_class_variation) > 0) {
            $data['meta_data'][] = [
                'key' => '_ltl_freight_variation',
                'value' => $_product_freight_class_variation,
            ];
        }

        if (!empty($locations) || !empty($_dropship_location)) {
            if (isset($locations[0]) && is_array($locations[0])) {
                foreach ($locations[0] as $key => $location_arr) {
                    $metaValue = [];
                    if (isset($locations[0][$key], $locations[1][$key], $locations[2][$key], $locations[3][$key])) {
                        $metaValue[0] = $locations[0][$key];
                        $metaValue[1] = $locations[1][$key];
                        $metaValue[2] = $locations[2][$key];
                        $metaValue[3] = $locations[3][$key];
                        $metaValue[4] = $locations[4][$key];
                        $dsId[] = en_serialize_dropship($metaValue);
                    }
                }
            } else {
                $dsId[] = en_serialize_dropship($_dropship_location);
            }

            $sereializedLocations = maybe_serialize($dsId);
            $data['meta_data'][] = [
                'key' => '_dropship_location',
                'value' => $sereializedLocations,
            ];
        }
        return $data;
    }

    add_filter('woocommerce_product_importer_parsed_data', 'en_import_dropship_location_csv', '99', '2');
}

/**
 * Serialize drop ship
 * @param $metaValue
 * @return string
 * @global $wpdb
 */

if (!function_exists('en_serialize_dropship')) {
    function en_serialize_dropship($metaValue)
    {
        global $wpdb;
        $dropship = (array)reset($wpdb->get_results(
            "SELECT id
                        FROM " . $wpdb->prefix . "warehouse WHERE nickname='$metaValue[0]' AND zip='$metaValue[1]' AND city='$metaValue[2]' AND state='$metaValue[3]' AND country='$metaValue[4]'"
        ));

        $dropship = array_map('intval', $dropship);

        if (empty($dropship['id'])) {
            $data = en_csv_import_dropship_data($metaValue);
            $wpdb->insert(
                $wpdb->prefix . 'warehouse', $data
            );

            $dsId = $wpdb->insert_id;
        } else {
            $dsId = $dropship['id'];
        }

        return $dsId;
    }
}

/**
 * Filtered Data Array
 * @param $metaValue
 * @return array
 */
if (!function_exists('en_csv_import_dropship_data')) {
    function en_csv_import_dropship_data($metaValue)
    {
        return array(
            'city' => $metaValue[2],
            'state' => $metaValue[3],
            'zip' => $metaValue[1],
            'country' => $metaValue[4],
            'location' => 'dropship',
            'nickname' => (isset($metaValue[0])) ? $metaValue[0] : "",
        );
    }
}
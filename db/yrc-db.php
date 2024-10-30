<?php
/**
 * YRC WooComerce | Create warehouse database table
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Create warehouse database table
 * @global $wpdb
 */
function create_yrc_wh_db($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            global $wpdb;
            $warehouse_table = $wpdb->prefix . "warehouse";
            if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
                $origin = 'CREATE TABLE ' . $warehouse_table . '(
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    city varchar(200) NOT NULL,
                    state varchar(200) NOT NULL,
                    address varchar(255) NOT NULL,
                    phone_instore varchar(255) NOT NULL,
                    zip varchar(200) NOT NULL,
                    country varchar(200) NOT NULL,
                    location varchar(200) NOT NULL,
                    nickname varchar(200) NOT NULL,
                    enable_store_pickup VARCHAR(255) NOT NULL,
                    miles_store_pickup VARCHAR(255) NOT NULL ,
                    match_postal_store_pickup VARCHAR(255) NOT NULL ,
                    checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                    enable_local_delivery VARCHAR(255) NOT NULL ,
                    miles_local_delivery VARCHAR(255) NOT NULL ,
                    match_postal_local_delivery VARCHAR(255) NOT NULL ,
                    checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                    fee_local_delivery VARCHAR(255) NOT NULL ,
                    suppress_local_delivery VARCHAR(255) NOT NULL,
                        PRIMARY KEY  (id) )';
                dbDelta($origin);
            }
            add_option('yrc_db_version', '1.0');

            $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
            if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {
                $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                    . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));

            }
            // Origin terminal address
            yrc_update_warehouse();
            restore_current_blog();
        }

    } else {
        global $wpdb;
        $warehouse_table = $wpdb->prefix . "warehouse";
        if ($wpdb->query("SHOW TABLES LIKE '" . $warehouse_table . "'") === 0) {
            $origin = 'CREATE TABLE ' . $warehouse_table . '(
                    id mediumint(9) NOT NULL AUTO_INCREMENT,
                    city varchar(200) NOT NULL,
                    state varchar(200) NOT NULL,
                    address varchar(255) NOT NULL,
                    phone_instore varchar(255) NOT NULL,
                    zip varchar(200) NOT NULL,
                    country varchar(200) NOT NULL,
                    location varchar(200) NOT NULL,
                    nickname varchar(200) NOT NULL,
                    enable_store_pickup VARCHAR(255) NOT NULL,
                    miles_store_pickup VARCHAR(255) NOT NULL ,
                    match_postal_store_pickup VARCHAR(255) NOT NULL ,
                    checkout_desc_store_pickup VARCHAR(255) NOT NULL ,
                    enable_local_delivery VARCHAR(255) NOT NULL ,
                    miles_local_delivery VARCHAR(255) NOT NULL ,
                    match_postal_local_delivery VARCHAR(255) NOT NULL ,
                    checkout_desc_local_delivery VARCHAR(255) NOT NULL ,
                    fee_local_delivery VARCHAR(255) NOT NULL ,
                    suppress_local_delivery VARCHAR(255) NOT NULL,
                        PRIMARY KEY  (id) )';
            dbDelta($origin);
        }
        add_option('yrc_db_version', '1.0');

        $myCustomer = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'enable_store_pickup'");
        if (!(isset($myCustomer->Field) && $myCustomer->Field == 'enable_store_pickup')) {
            $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN enable_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_store_pickup VARCHAR(255) NOT NULL , "
                . "ADD COLUMN enable_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN miles_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN match_postal_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN checkout_desc_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN fee_local_delivery VARCHAR(255) NOT NULL , "
                . "ADD COLUMN suppress_local_delivery VARCHAR(255) NOT NULL", $warehouse_table));

        }
        // Origin terminal address
        yrc_update_warehouse();
    }

}

/**
 * Update warehouse
 */
function yrc_update_warehouse()
{
    // Origin terminal address
    global $wpdb;
    $warehouse_table = $wpdb->prefix . "warehouse";
    $warehouse_address = $wpdb->get_row("SHOW COLUMNS FROM " . $warehouse_table . " LIKE 'phone_instore'");
    if (!(isset($warehouse_address->Field) && $warehouse_address->Field == 'phone_instore')) {
        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN address VARCHAR(255) NOT NULL", $warehouse_table));
        // Terminal phone number
        $wpdb->query(sprintf("ALTER TABLE %s ADD COLUMN phone_instore VARCHAR(255) NOT NULL", $warehouse_table));
    }
}

/**
 * Create LTL Class
 */
function create_yrc_ltl_freight_class($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            if (!function_exists('create_ltl_class')) {
                wp_insert_term(
                    'LTL Freight', 'product_shipping_class', array(
                        'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                        'slug' => 'ltl_freight'
                    )
                );
            }
            restore_current_blog();
        }

    } else {
        if (!function_exists('create_ltl_class')) {
            wp_insert_term(
                'LTL Freight', 'product_shipping_class', array(
                    'description' => 'The plugin is triggered to provide an LTL freight quote when the shopping cart contains an item that has a designated shipping class. Shipping class? is a standard WooCommerce parameter not to be confused with freight class? or the NMFC classification system.',
                    'slug' => 'ltl_freight'
                )
            );
        }
    }
}

/**
 * Add Option For YRC
 */
function create_yrc_option($network_wide = null)
{
    if ( is_multisite() && $network_wide ) {

        foreach (get_sites(['fields'=>'ids']) as $blog_id) {
            switch_to_blog($blog_id);
            $eniture_plugins = get_option('EN_Plugins');
            if (!$eniture_plugins)
                add_option('EN_Plugins', json_encode(array('yrc')));
            else {
                $plugins_array = json_decode($eniture_plugins);
                if (!in_array('yrc', $plugins_array)) {
                    array_push($plugins_array, 'yrc');
                    update_option('EN_Plugins', json_encode($plugins_array));
                }
            }
            restore_current_blog();
        }

    } else {
        $eniture_plugins = get_option('EN_Plugins');
        if (!$eniture_plugins)
            add_option('EN_Plugins', json_encode(array('yrc')));
        else {
            $plugins_array = json_decode($eniture_plugins);
            if (!in_array('yrc', $plugins_array)) {
                array_push($plugins_array, 'yrc');
                update_option('EN_Plugins', json_encode($plugins_array));
            }
        }
    }

}

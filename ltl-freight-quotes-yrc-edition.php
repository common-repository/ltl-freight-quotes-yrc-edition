<?php
/**
 * Plugin Name:    LTL Freight Quotes - YRC Edition
 * Plugin URI:     https://eniture.com/products/
 * Description:    Dynamically retrieves your negotiated shipping rates from YRC Freight and displays the results in the WooCommerce shopping cart.
 * Version:        3.1.8
 * Author:         Eniture Technology
 * Author URI:     http://eniture.com/
 * Text Domain:    eniture-technology
 * License:        GPL version 2 or later - http://www.eniture.com/
 * WC requires at least: 5.7
 * WC tested up to: 7.5.1
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

define('YRC_FREIGHT_DOMAIN_HITTING_URL', 'https://ws027.eniture.com');
define('YRC_FDO_HITTING_URL', 'https://freightdesk.online/api/updatedWoocomData');
define('YRC_MAIN_FILE', __FILE__);

// Define reference
function en_yrc_freight_plugin($plugins)
{
    $plugins['lfq'] = (isset($plugins['lfq'])) ? array_merge($plugins['lfq'], ['yrc' => 'YRC_Freight_Shipping_Class']) : ['yrc' => 'YRC_Freight_Shipping_Class'];
    return $plugins;
}

add_filter('en_plugins', 'en_yrc_freight_plugin');

if (!function_exists('en_woo_plans_notification_PD')) {
    function en_woo_plans_notification_PD($product_detail_options)
    {
        $eniture_plugins_id = 'eniture_plugin_';

        for ($en = 1; $en <= 25; $en++) {
            $settings = get_option($eniture_plugins_id . $en);

            if (isset($settings) && (!empty($settings)) && (is_array($settings))) {
                $plugin_detail = current($settings);
                $plugin_name = (isset($plugin_detail['plugin_name'])) ? $plugin_detail['plugin_name'] : "";

                foreach ($plugin_detail as $key => $value) {

                    if ($key != 'plugin_name') {
                        $action = $value === 1 ? 'enable_plugins' : 'disable_plugins';

                        $product_detail_options[$key][$action] = (isset($product_detail_options[$key][$action]) && strlen($product_detail_options[$key][$action]) > 0) ? ", $plugin_name" : "$plugin_name";
                    }

                }

            }

        }

        return $product_detail_options;
    }

    add_filter('en_woo_plans_notification_action', 'en_woo_plans_notification_PD', 10, 1);
}

if (!function_exists('en_woo_plans_notification_message')) {
    function en_woo_plans_notification_message($enable_plugins, $disable_plugins)
    {
        $enable_plugins = (strlen($enable_plugins) > 0) ? "$enable_plugins: <b> Enabled</b>. " : "";
        $disable_plugins = (strlen($disable_plugins) > 0) ? " $disable_plugins: Upgrade to <b>Standard Plan to enable</b>." : "";
        return $enable_plugins . "<br>" . $disable_plugins;
    }

    add_filter('en_woo_plans_notification_message_action', 'en_woo_plans_notification_message', 10, 2);
}

if (!function_exists('is_plugin_active')) {
    require_once(ABSPATH . 'wp-admin/includes/plugin.php');
}

if (!is_plugin_active('woocommerce/woocommerce.php')) {
    add_action('admin_notices', 'yrc_wc_avaibility_error');
}
/**
 * Check WooCommerce installlation
 */
function yrc_wc_avaibility_error()
{
    $class = "error";
    $message = "LTL Freight Quotes - YRC Edition is enabled, but not effective. It requires WooCommerce in order to work, please <a target='_blank' href='https://wordpress.org/plugins/woocommerce/installation/'>Install</a> WooCommerce Plugin. Reactivate LTL Freight Quotes - YRC Edition plugin to create LTL shipping class.";
    echo "<div class=\"$class\"> <p>$message</p></div>";
}

add_action('admin_init', 'yrc_check_wc_version');
/**
 * Check WooCommerce version compatibility
 */
function yrc_check_wc_version()
{
    $woo_version = yrc_wc_version_number();
    $version = '2.6';
    if (!version_compare($woo_version, $version, ">=")) {
        add_action('admin_notices', 'wc_version_incompatibility_yrc');
    }
}

/**
 * Check WooCommerce version incompatibility
 */
function wc_version_incompatibility_yrc()
{
    ?>
    <div class="notice notice-error">
        <p>
            <?php
            _e('LTL Freight Quotes - YRC Edition plugin requires WooCommerce version 2.6 or higher to work. Functionality may not work properly.', 'wwe-woo-version-failure');
            ?>
        </p>
    </div>
    <?php
}

/**
 * WooCommerce version
 * @return version
 */
function yrc_wc_version_number()
{
    $plugin_folder = get_plugins('/' . 'woocommerce');
    $plugin_file = 'woocommerce.php';

    if (isset($plugin_folder[$plugin_file]['Version'])) {
        return $plugin_folder[$plugin_file]['Version'];
    } else {
        return NULL;
    }
}

if (in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins'))) || is_plugin_active_for_network('woocommerce/woocommerce.php')) {

    add_action('admin_enqueue_scripts', 'yrc_admin_script');

    /**
     * Load scripts for YRC
     */
    function yrc_admin_script()
    {
        // Cuttoff Time
        wp_register_style('yrc_wickedpicker_style', plugin_dir_url(__FILE__) . 'css/wickedpicker.min.css', false, '1.0.0');
        wp_register_script('yrc_wickedpicker_script', plugin_dir_url(__FILE__) . 'js/wickedpicker.js', false, '1.0.0');
        wp_enqueue_style('yrc_wickedpicker_style');
//        wp_enqueue_style('yrc_style');
        wp_enqueue_script('yrc_wickedpicker_script');

        wp_register_style('yrc-style', plugin_dir_url(__FILE__) . '/css/yrc-style.css', false, '1.1.1');
        wp_enqueue_style('yrc-style');
    }

    /**
     * Inlude Plugin Files
     */

    require_once 'order/rates/order-rates.php';
    require_once 'order/en-order-export.php';
    require_once 'order/en-order-widget.php';
    require_once 'fdo/en-fdo.php';

    require_once('warehouse-dropship/wild-delivery.php');
    require_once('warehouse-dropship/get-distance-request.php');
    // Origin terminal address
    add_action('admin_init', 'yrc_update_warehouse');

    require_once('product/en-product-detail.php');

    require_once 'template/csv-export.php';
    add_action('admin_enqueue_scripts', 'en_yrc_script');

    /**
     * Load Front-end scripts for xpo
     */
    function en_yrc_script()
    {
        wp_enqueue_script('jquery');
        wp_enqueue_script('en_yrc_script', plugin_dir_url(__FILE__) . 'js/en-yrc.js', array(), '1.0.6');
        wp_localize_script('en_yrc_script', 'en_yrc_admin_script', array(
            'plugins_url' => plugins_url(),
            'allow_proceed_checkout_eniture' => trim(get_option("allow_proceed_checkout_eniture")),
            'prevent_proceed_checkout_eniture' => trim(get_option("prevent_proceed_checkout_eniture")),
            // Cuttoff Time
            'yrc_freight_order_cutoff_time' => get_option("yrc_freight_order_cut_off_time"),
        ));
    }

    require_once('yrc-liftgate-as-option.php');
    require_once('yrc-test-connection.php');
    require_once('yrc-shipping-class.php');
    require_once('db/yrc-db.php');
    require_once('yrc-admin-filter.php');
    require_once('yrc-group-package.php');
    require_once('yrc-carrier-service.php');
    require_once('template/connection-settings.php');
    require_once('template/quote-settings.php');
    require_once('yrc-wc-update-change.php');
    require_once('yrc-curl-class.php');

    require_once('standard-package-addon/standard-package-addon.php');
    require_once 'update-plan.php';
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');

    /**
     * YRC Freight Activation Hook
     */
    register_activation_hook(__FILE__, 'create_yrc_ltl_freight_class');
    register_activation_hook(__FILE__, 'create_yrc_wh_db');
    register_activation_hook(__FILE__, 'create_yrc_option');
    register_activation_hook(__FILE__, 'old_store_yrc_ltl_dropship_status');
    register_activation_hook(__FILE__, 'yrc_quotes_activate_hit_to_update_plan');
    register_deactivation_hook(__FILE__, 'yrc_quotes_deactivate_hit_to_update_plan');


    /**
     * yrc plugin update now
     */
    function en_yrc_update_now()
    {
        $index = 'ltl-freight-quotes-yrc-edition/ltl-freight-quotes-yrc-edition.php';
        $plugin_info = get_plugins();
        $plugin_version = (isset($plugin_info[$index]['Version'])) ? $plugin_info[$index]['Version'] : '';
        $update_now = get_option('en_yrc_update_now');

        if ($update_now != $plugin_version) {
            if (!function_exists('yrc_quotes_activate_hit_to_update_plan')) {
                require_once(__DIR__ . '/update-plan.php');
            }

            yrc_quotes_activate_hit_to_update_plan();
            old_store_yrc_ltl_dropship_status();
            create_yrc_wh_db();
            create_yrc_option();
            create_yrc_ltl_freight_class();
            update_option('en_yrc_update_now', $plugin_version);
        }
    }

    add_action('init', 'en_yrc_update_now');

    /**
     * YRC Action And Filters
     */

    add_action('woocommerce_shipping_init', 'yrc_logistics_init');
    add_filter('woocommerce_shipping_methods', 'add_yrc_logistics');
    add_filter('woocommerce_get_settings_pages', 'yrc_shipping_sections');
    add_filter('woocommerce_package_rates', 'yrc_hide_shipping', 99);
    add_filter('woocommerce_shipping_calculator_enable_city', '__return_true');

    add_filter('plugin_action_links', 'yrc_logistics_add_action_plugin', 10, 5);
    /**
     * YRC action links
     * @staticvar $plugin
     * @param $actions
     * @param $plugin_file
     * @return links array settings
     */
    function yrc_logistics_add_action_plugin($actions, $plugin_file)
    {
        static $plugin;
        if (!isset($plugin))
            $plugin = plugin_basename(__FILE__);
        if ($plugin == $plugin_file) {
            $settings = array('settings' => '<a href="admin.php?page=wc-settings&tab=yrc_quotes">' . __('Settings', 'General') . '</a>');
            $site_link = array('support' => '<a href="https://support.eniture.com" target="_blank">Support</a>');
            $actions = array_merge($settings, $actions);
            $actions = array_merge($site_link, $actions);
        }
        return $actions;
    }

    add_filter('woocommerce_cart_no_shipping_available_html', 'yrc_cart_html_message');
    /**
     * No Quotes Cart Message
     */
    function yrc_cart_html_message()
    {
        echo "<div><p>There are no shipping methods available. Please double check your address, or contact us if you need any help.</p></div>";
    }

}

define("en_woo_plugin_yrc_quotes", "yrc_quotes");

add_action('wp_enqueue_scripts', 'en_ltl_yrc_frontend_checkout_script');
/**
 * Load Frontend scripts for yrc
 */
function en_ltl_yrc_frontend_checkout_script()
{
    wp_enqueue_script('jquery');
    wp_enqueue_script('en_ltl_yrc_frontend_checkout_script', plugin_dir_url(__FILE__) . 'front/js/en-yrc-checkout.js', array(), '1.0.1');
    wp_localize_script('en_ltl_yrc_frontend_checkout_script', 'frontend_script', array(
        'pluginsUrl' => plugins_url(),
    ));
}

/**
 * Get Host
 * @param type $url
 * @return type
 */
if (!function_exists('getHost')) {
    function getHost($url)
    {
        $parseUrl = parse_url(trim($url));
        if (isset($parseUrl['host'])) {
            $host = $parseUrl['host'];
        } else {
            $path = explode('/', $parseUrl['path']);
            $host = $path[0];
        }
        return trim($host);
    }
}

/**
 * Get Domain Name
 */
if (!function_exists('yrc_quotes_get_domain')) {
    function yrc_quotes_get_domain()
    {
        global $wp;
        $url = home_url($wp->request);
        return getHost($url);
    }
}


/**
 * Plans Common Hooks
 */
add_filter('yrc_quotes_quotes_plans_suscription_and_features', 'yrc_quotes_quotes_plans_suscription_and_features', 1);

function yrc_quotes_quotes_plans_suscription_and_features($feature)
{
    $package = get_option('yrc_quotes_packages_quotes_package');


    $features = array
    (
        'instore_pickup_local_devlivery' => array('3'),
        // Cuttoff Time
        'yrc_cutt_off_time' => array('2', '3'),
        'yrc_show_delivery_estimates' => array('1', '2', '3'),
        'hazardous_material' => array('2', '3')
    );

    if (get_option('yrc_quotes_store_type') == "1") {
        $features['multi_warehouse'] = array('2', '3');
        $features['multi_dropship'] = array('', '0', '1', '2', '3');
    }
    if (get_option('en_old_user_dropship_status') == "0" && get_option('yrc_quotes_store_type') == "0") {

        $features['multi_dropship'] = array('', '0', '1', '2', '3');
    }
    if (get_option('en_old_user_warehouse_status') === "0" && get_option('yrc_quotes_store_type') == "0") {

        $features['multi_warehouse'] = array('2', '3');
    }

    return (isset($features[$feature]) && (in_array($package, $features[$feature]))) ? TRUE : ((isset($features[$feature])) ? $features[$feature] : '');
}

add_filter('yrc_quotes_plans_notification_link', 'yrc_quotes_plans_notification_link', 1);

function yrc_quotes_plans_notification_link($plans)
{
    $plans = is_array($plans) ? $plans : array();
    $plan = isset($plans) && !empty($plans) ? current($plans) : '';
    $plan_to_upgrade = "";
    switch ($plan) {
        case 2:
            $plan_to_upgrade = "<a href='http://eniture.com/plan/woocommerce-yrc-ltl-freight/' class='plan_color' target='_blank'>Standard Plan required</a>";
            break;
        case 3:
            $plan_to_upgrade = "<a href='http://eniture.com/plan/woocommerce-yrc-ltl-freight/' target='_blank'>Advanced Plan required</a>";
            break;
    }

    return $plan_to_upgrade;
}

/**
 *
 * old customer check dropship status on plugin update
 */
function old_store_yrc_ltl_dropship_status()
{
    global $wpdb;

//  Check total no. of dropships on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $count_query = "select count(*) from $table_name where location = 'dropship' ";
    $num = $wpdb->get_var($count_query);

    if (get_option('en_old_user_dropship_status') == "0" && get_option('yrc_quotes_store_type') == "0") {

        $dropship_status = ($num > 1) ? 1 : 0;

        update_option('en_old_user_dropship_status', "$dropship_status");
    } elseif (get_option('en_old_user_dropship_status') == "" && get_option('yrc_quotes_store_type') == "0") {
        $dropship_status = ($num == 1) ? 0 : 1;

        update_option('en_old_user_dropship_status', "$dropship_status");
    }

//  Check total no. of warehouses on plugin updation
    $table_name = $wpdb->prefix . 'warehouse';
    $warehouse_count_query = "select count(*) from $table_name where location = 'warehouse' ";
    $warehouse_num = $wpdb->get_var($warehouse_count_query);

    if (get_option('en_old_user_warehouse_status') == "0" && get_option('yrc_quotes_store_type') == "0") {

        $warehouse_status = ($warehouse_num > 1) ? 1 : 0;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    } elseif (get_option('en_old_user_warehouse_status') == "" && get_option('yrc_quotes_store_type') == "0") {
        $warehouse_status = ($warehouse_num == 1) ? 0 : 1;

        update_option('en_old_user_warehouse_status', "$warehouse_status");
    }

}
// fdo va
add_action('wp_ajax_nopriv_yrc_fd', 'yrc_fd_api');
add_action('wp_ajax_yrc_fd', 'yrc_fd_api');
/**
 * UPS AJAX Request
 */
function yrc_fd_api()
{
    $store_name = yrc_quotes_get_domain();
    $company_id = $_POST['company_id'];
    $data = [
        'plateform'  => 'wp',
        'store_name' => $store_name,
        'company_id' => $company_id,
        'fd_section' => 'tab=yrc_quotes&section=section-4',
    ];
    if (is_array($data) && count($data) > 0) {
        if($_POST['disconnect'] != 'disconnect') {
            $url =  'https://freightdesk.online/validate-company';
        }else {
            $url = 'https://freightdesk.online/disconnect-woo-connection';
        }
        $response = wp_remote_post($url, [
                'method' => 'POST',
                'timeout' => 60,
                'redirection' => 5,
                'blocking' => true,
                'body' => $data,
            ]
        );
        $response = wp_remote_retrieve_body($response);
    }
    if($_POST['disconnect'] == 'disconnect') {
        $result = json_decode($response);
        if ($result->status == 'SUCCESS') {
            update_option('en_fdo_company_id_status', 0);
        }
    }
    echo $response;
    exit();
}
add_action('rest_api_init', 'en_rest_api_init_status_yrc');
function en_rest_api_init_status_yrc()
{
    register_rest_route('fdo-company-id', '/update-status', array(
        'methods' => 'POST',
        'callback' => 'en_yrc_fdo_data_status',
        'permission_callback' => '__return_true'
    ));
}

/**
 * Update FDO coupon data
 * @param array $request
 * @return array|void
 */
function en_yrc_fdo_data_status(WP_REST_Request $request)
{
    $status_data = $request->get_body();
    $status_data_decoded = json_decode($status_data);
    if (isset($status_data_decoded->connection_status)) {
        update_option('en_fdo_company_id_status', $status_data_decoded->connection_status);
        update_option('en_fdo_company_id', $status_data_decoded->fdo_company_id);
    }
    return true;
}

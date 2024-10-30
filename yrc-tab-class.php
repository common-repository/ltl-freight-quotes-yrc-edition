<?php
/**
 * YRC WooComerce | Setting Tab Class
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * YRC WooComerce | Setting Tab Class
 */
class YRC_Freight_Settings extends WC_Settings_Page
{
    /**
     * Setting Tab Class Constructor
     */
    public function __construct()
    {
        $this->id = 'yrc_quotes';
        add_filter('woocommerce_settings_tabs_array', array($this, 'add_settings_tab'), 50);
        add_action('woocommerce_sections_' . $this->id, array($this, 'output_sections'));
        add_action('woocommerce_settings_' . $this->id, array($this, 'output'));
        add_action('woocommerce_settings_save_' . $this->id, array($this, 'save'));
    }

    /**
     * YRC Setting Tab For WooCommerce
     * @param $settings_tabs
     * @return seetings
     */
    public function add_settings_tab($settings_tabs)
    {
        $settings_tabs[$this->id] = __('YRC Freight', 'woocommerce_yrc_quote');
        return $settings_tabs;
    }

    /**
     * YRC Setting Sections
     * @return array
     */
    public function get_sections()
    {
        $sections = array(
            '' => __('Connection Settings', 'woocommerce_yrc_quote'),
            'section-1' => __('Quote Settings', 'woocommerce_yrc_quote'),
            'section-2' => __('Warehouses', 'woocommerce_yrc_quote'),
            'section-3' => __('User Guide', 'woocommerce_yrc_quote'),
            // fdo va
            'section-4' => __('FreightDesk Online', 'woocommerce_yrc_quote'),
            'section-5' => __('Validate Addresses', 'woocommerce_yrc_quote')
        );

        $sections = apply_filters('en_woo_addons_sections', $sections, en_woo_plugin_yrc_quotes);
        // Standard Packaging
        $sections = apply_filters('en_woo_pallet_addons_sections', $sections, en_woo_plugin_yrc_quotes);
        return apply_filters('woocommerce_get_sections_' . $this->id, $sections);
    }

    /**
     * YRC Warehouse Tab
     */
    public function yrc_warehouse()
    {
        require_once 'warehouse-dropship/wild/warehouse/warehouse_template.php';
        require_once 'warehouse-dropship/wild/dropship/dropship_template.php';
    }

    /**
     * YRC User Guide Tab
     */
    public function yrc_user_guide()
    {
        include_once('template/guide.php');
    }

    /**
     * YRC Pages Initialize
     * @param $section
     * @return array
     */
    public function get_settings($section = null)
    {
        ob_start();
        switch ($section) {
            case 'section-0' :
                $settings = YRC_Connection_Settings::yrc_con_setting();
                break;
            case 'section-1':
                $yrc_quote_Settings = new YRC_Quote_Settings();
                $settings = $yrc_quote_Settings->yrc_quote_settings_tab();
                break;
            case 'section-2' :
                $this->yrc_warehouse();
                $settings = array();
                break;
            case 'section-3' :
                $this->yrc_user_guide();
                $settings = array();
                break;
            // fdo va
            case 'section-4' :
                $this->freightdesk_online_section();
                $settings = [];
                break;

            case 'section-5' :
                $this->validate_addresses_section();
                $settings = [];
                break;

            default:
                $yrc_con_settings = new YRC_Connection_Settings();
                $settings = $yrc_con_settings->yrc_con_setting();

                break;
        }

        $settings = apply_filters('en_woo_addons_settings', $settings, $section, en_woo_plugin_yrc_quotes);
        // Standard Packaging
        $settings = apply_filters('en_woo_pallet_addons_settings', $settings, $section, en_woo_plugin_yrc_quotes);
        $settings = $this->avaibility_addon($settings);
        return apply_filters('woocommerce_yrc_quote', $settings, $section);
    }

    /**
     * avaibility_addon
     * @param array type $settings
     * @return array type
     */
    function avaibility_addon($settings)
    {
        if (is_plugin_active('residential-address-detection/residential-address-detection.php')) {
            unset($settings['avaibility_lift_gate']);
            unset($settings['avaibility_auto_residential']);
        }

        return $settings;
    }

    /**
     * YRC Settings Pages Output
     * @global $current_section
     */
    public function output()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        WC_Admin_Settings::output_fields($settings);
    }

    /**
     * YRC Save Settings
     * @global $current_section
     */
    public function save()
    {
        global $current_section;
        $settings = $this->get_settings($current_section);
        // Cuttoff Time
        if (isset($_POST['yrc_freight_order_cut_off_time']) && $_POST['yrc_freight_order_cut_off_time'] != '') {
            $time_24_format = $this->yrc_get_time_in_24_hours($_POST['yrc_freight_order_cut_off_time']);
            $_POST['yrc_freight_order_cut_off_time'] = $time_24_format;
        }
        WC_Admin_Settings::save_fields($settings);
    }

    /**
     * Cuttoff Time
     * @param $timeStr
     * @return false|string
     */
    public function yrc_get_time_in_24_hours($timeStr)
    {
        $cutOffTime = explode(' ', $timeStr);
        $hours = $cutOffTime[0];
        $separator = $cutOffTime[1];
        $minutes = $cutOffTime[2];
        $meridiem = $cutOffTime[3];
        $cutOffTime = "{$hours}{$separator}{$minutes} $meridiem";
        return date("H:i", strtotime($cutOffTime));
    }
    // fdo va
    /**
     * FreightDesk Online section
     */
    public function freightdesk_online_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/freightdesk-online-section.php';
    }

    /**
     * Validate Addresses Section
     */
    public function validate_addresses_section()
    {
        include_once plugin_dir_path(__FILE__) . 'fdo/validate-addresses-section.php';
    }
}

return new YRC_Freight_Settings();
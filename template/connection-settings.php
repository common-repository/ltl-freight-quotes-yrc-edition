<?php
/**
 * YRC WooComerce | YRC Test connection HTML Form
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

/**
 * YRC WooComerce | YRC Test connection HTML Form
 */
class YRC_Connection_Settings
{
    /**
     * YRC Test connection Settings
     * @return array
     */
    public function yrc_con_setting()
    {
        echo '<div class="connection_section_class_yrc">';
        $settings = array(
            'section_title_yrc' => array(
                'name' => __('', 'woocommerce_yrc_quote'),
                'type' => 'title',
                'desc' => '<br> ',
                'id' => 'wc_settings_yrc_title_section_connection',
            ),

            'busid_yrc' => array(
                'name' => __('Business ID ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => __('', 'woocommerce_yrc_quote'),
                'id' => 'wc_settings_yrc_busid'
            ),

            'userid_yrc' => array(
                'name' => __('Username ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => __('', 'woocommerce_yrc_quote'),
                'id' => 'wc_settings_yrc_userid'
            ),

            'password_yrc' => array(
                'name' => __('Password ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => __('', 'woocommerce_yrc_quote'),
                'id' => 'wc_settings_yrc_password'
            ),

            'plugin_licence_key_yrc' => array(
                'name' => __('Plugin License Key ', 'woocommerce_yrc_quote'),
                'type' => 'text',
                'desc' => __('Obtain a Plugin License Key from <a href="https://eniture.com/woocommerce-yrc-ltl-freight/" target="_blank" >eniture.com </a>', 'woocommerce_yrc_quote'),
                'id' => 'wc_settings_yrc_plugin_licence_key'
            ),

            'yrc_rates_based' => array(
                'name' => __('YRC rates my freight based on weight and...', 'woocommerce_yrc_quote'),
                'type' => 'radio',
                'id' => 'yrc_rates_based',
                'options' => array(
                    'freight' => __('Freight class', 'woocommerce'),
                    'dimension' => __('Dimensions', 'woocommerce'),
                ),
                'default' => 'freight',
            ),

            'section_end_yrc' => array(
                'type' => 'sectionend',
                'id' => 'wc_settings_yrc_plugin_licence_key'
            ),
        );
        return $settings;
    }
}
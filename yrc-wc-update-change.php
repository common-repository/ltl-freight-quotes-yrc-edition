<?php
/**
 * YRC WooComerce | Class for new and old functions
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if ( ! defined( 'ABSPATH' ) ) {
    exit; 
}

/**
* YRC WooCommerce Class for new and old functions
*/
class YRC_Woo_Update_Changes 
{
    /**
     * Version
     * @var int
     */
    public $WooVersion;

    /**
     * Class constructor
     */
    function __construct() 
    {
        if (!function_exists('get_plugins'))
           require_once( ABSPATH . 'wp-admin/includes/plugin.php' );

        $plugin_folder     = get_plugins('/' . 'woocommerce');
        $plugin_file       = 'woocommerce.php';
        $this->WooVersion  = $plugin_folder[$plugin_file]['Version'];
    }

    /**
     * WooCommerce Customer's PostCode
     * @return string
     */
    function yrc_postcode()
    { 
        $sPostCode = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sPostCode = WC()->customer->get_postcode();
                break;
            case ($this->WooVersion >= '3.0'):
                $sPostCode = WC()->customer->get_billing_postcode();
                break;

            default:
                break;
        }
        return $sPostCode;
    }

    /**
     * WooCommerce Customer's State
     * @return string
     */
    function yrc_getState()
    { 
        $sState = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sState = WC()->customer->get_state();
                break;
            case ($this->WooVersion >= '3.0'):
                $sState = WC()->customer->get_billing_state();
                break;

            default:
                break;
        }
        return $sState;
    }

    /**
     * WooCommerce Customer's State
     * @return string
     */
    function yrc_getCity()
    { 
        $sCity = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCity = WC()->customer->get_city();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCity = WC()->customer->get_billing_city();
                break;

            default:
                break;
        }
        return $sCity;
    }

    /**
     * WooCommerce Customer's Country
     * @return string
     */
    function yrc_getCountry()
    { 
        $sCountry = "";
        switch ($this->WooVersion) 
        {  
            case ($this->WooVersion <= '2.7'):
                $sCountry = WC()->customer->get_country();
                break;
            case ($this->WooVersion >= '3.0'):
                $sCountry = WC()->customer->get_billing_country();
                break;

            default:
                break;
        }
        return $sCountry;
    }

}
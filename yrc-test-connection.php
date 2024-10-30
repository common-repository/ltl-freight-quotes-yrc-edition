<?php
/**
 * YRC WooComerce | Test connection AJAX Request
 * @package     Woocommerce YRC Edition
 * @author      <https://eniture.com/>
 * @copyright   Copyright (c) 2017, Eniture
 */
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_nopriv_yrc_action', 'yrc_test_submit');
add_action('wp_ajax_yrc_action', 'yrc_test_submit');
/**
 * Test connection AJAX Request
 */
function yrc_test_submit()
{
    $domain = yrc_quotes_get_domain();

    $data = array(
        'licence_key' => (isset($_POST['yrc_plugin_license'])) ? sanitize_text_field($_POST['yrc_plugin_license']) : "",
        'sever_name' => $domain,
        'carrierName' => 'yrc',
        'plateform' => 'WordPress',
        'carrier_mode' => 'test',
        'userId' => (isset($_POST['yrc_userid'])) ? sanitize_text_field($_POST['yrc_userid']) : "",
        'password' => (isset($_POST['yrc_password'])) ? sanitize_text_field($_POST['yrc_password']) : "",
        'busId' => (isset($_POST['yrc_busid'])) ? sanitize_text_field($_POST['yrc_busid']) : "",
    );

    $yrc_rates_based = (isset($_POST['yrc_rates_based'])) ? sanitize_text_field($_POST['yrc_rates_based']) : "";
    ($yrc_rates_based == "dimension") ? $data['dimWeightBaseAccount'] = '1' : "";

    $yrc_curl_obj = new YRC_Curl_Request();
    $sResponseData = $yrc_curl_obj->yrc_get_curl_response(YRC_FREIGHT_DOMAIN_HITTING_URL . '/index.php', $data);
    $sResponseData = json_decode($sResponseData);

    if (isset($sResponseData->severity) && $sResponseData->severity == 'SUCCESS') {
        $sResult = array('message' => "success");
    } else if (isset($sResponseData->q->BodyHead)) {
        $sResult = array('message' => "success");
    } elseif (isset($sResponseData->error) || $sResponseData->q->error == 1) {
        if (isset($sResponseData->error) && !empty($sResponseData->error)) {
            if (isset($sResponseData->error_desc) && strlen($sResponseData->error_desc) > 0) {
                $sResult = $sResponseData->error_desc;
            } else {
                $sResult = $sResponseData->error;
            }
        } else {
            $sResult = (isset($sResponseData->q->error_desc->{0}) && count($sResponseData->q->error_desc->{0}) == 1) ? $sResponseData->q->error_desc->{0} : $sResponseData->q->error_desc;
        }

        $fullstop = (substr($sResult, -1) == '.') ? '' : '.';
        $sResult = array('message' => $sResult . $fullstop);
    } else {
        $sResult = array('message' => "failure");
    }

    echo json_encode($sResult);
    exit();
}

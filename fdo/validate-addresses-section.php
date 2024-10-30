<?php
/**
 * YRC Freightdesk online Template
 *
 * @package     YRC Quotes
 * @author      Eniture-Technology
 */
if (!defined('ABSPATH')) {
    exit;
}
$link = ' <a href="https://validate-addresses.com/" target="_blank">validate-addresses.com</a>';
$link_freight = '<a href="https://validate-addresses.com/" target="_blank">Learn more</a>';
$link_register = '<a href="https://validate-addresses.com/register" target="_blank">click here</a>';
$message = wp_sprintf('<div class="user_guide_fdo">
    <h2>Connect to Validate Addresses.</h2>
    <p>
    Validate Addresses ( %s ) is a cloud-based platform that verifies an order’s address details after the order is placed. It is also the most economical way.
        You won’t be paying to validate an address every time someone enters the checkout process and then abandons the cart. 
        Connect your store to Validate Address and virtually eliminate to avoid spending your time validating addresses. (
        %s )
    </p>

    <div id="message" class="en-coupon-code-div woocommerce-message en-coupon-notice">
        <p>
            <strong>Note!</strong>
            To establish a connection, you must have a Validate Addresses account. If you don’t have one, %s to register.
        </p>
    </div>', $link, $link_freight, $link_register);
echo wp_kses_post($message);
?>




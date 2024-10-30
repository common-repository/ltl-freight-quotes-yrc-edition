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
$link = '<a href="https://freightdesk.online/" target="_blank">freightdesk.online</a>';
$link_freight = '<a href="https://freightdesk.online/" target="_blank">Learn more</a>';
$link_register = '<a href="https://freightdesk.online/register" target="_blank">click here</a>';
$f_desk_online_id = '<a href="https://support.eniture.com/what-is-my-freightdesk-online-id" target="_blank">[ ? ]</a>';
$company_id = get_option('en_fdo_company_id');
if(get_option('en_fdo_company_id_status') != 1) {
    $button_connect = '<a href="javascript:void(0)" id="fd_online_id_yrc" class="button-primary">Connect</a>';
    $id_field = '<input type="text" name="freightdesk_online_id" id="freightdesk_online_id" value="" maxlength="10" placeholder="FreightDesk Online ID">';
    $ulli = ' <p>While connecting, Woocommerce may prompt you to authorize access to:</p>
                        <ul style="list-style-type: disc;margin-left: 16px">
                            <li>Create webhooks</li>
                            <li>View and manage coupons</li>
                            <li>View and manage customers</li>
                            <li>View and manage orders and sales </li>
                            <li>Sales reports</li>
                            <li>View and manage products</li>
                        </ul>';
    $dn = '';
    $dnblock = '';
    $note = '<div id="message" class="en-coupon-code-div woocommerce-message en-coupon-notice">
        <p>
            <strong>Note!</strong>
            To establish a connection, you must have a FreightDesk Online account. If you don’t have one, ' . $link_register . ' to register.
        </p>

    </div>';
    $style = 'padding: 15px 0';

}else {
    if(get_option('en_fdo_company_id_status') == 1) {
        $button_connect = '<a href="javascript:void(0)" data="disconnect" id="fd_online_id_yrc" class="button-primary">Disconnect</a>';
        $id_field = 'Connected to FreightDesk Online using FreightDesk Online Account ID <strong> ' . $company_id . ' <strong> ' . $f_desk_online_id;
        $ulli ='';
        $dn = 'dn';
        $dnblock = 'dnblock';
        $note = '';
        $style = 'padding: 0 0 15px 0';
    }
}
$message = wp_sprintf('<div class="user_guide_fdo">
    <h2>Connect to FreightDesk Online.</h2>
    <p>
    FreightDesk Online (
        %s
        ) is a cloud-based, multi-carrier shipping platform that allows its users to create and manage postal, parcel, and LTL freight shipments. Connect your store to FreightDesk Online and virtually eliminate the need for data entry when shipping orders. (
        %s
        )
    </p>
    %s
    <div class="parent-column">
      <div class="half-column %s">
        <h2>FreightDesk Online ID %s <span style="color:red">*</span></h2>
      </div>
      <div class="half-column %s">', $link, $link_freight, $note, $dn, $f_desk_online_id, $dnblock);
echo wp_kses_post($message);
echo wp_sprintf('<div style="%s">
                            %s
                         </div> 
                         <span id="con_dis"> %s</span>
                         %s
                        </div></div>', $style, $id_field, $button_connect, $ulli);
?>
<?php
/*
Plugin Name: WooCommerce - Country Based Payments
Plugin URI:  https://github.com/Knight-Digital/WooCommerce-Country-Based-Payments
Description: Choose in which country certain payment gateway will be available
Version:     1.0.0
Author:      Knight Digital 
Author URI:  https://knightdigital.se
License:     GPL2
License URI: https://www.gnu.org/licenses/gpl-2.0.html
Domain Path: /languages
Text Domain: wccbp

Forked from the https://wordpress.org/plugins/woocommerce-country-based-payments/ plugin
Original Author: Ivan Paulin - http://ivanpaulin.com
*/

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

// define text domain
define('WCCBP_TEXT_DOMAIN', 'wccbp');


class WoocommerceCountryBasedPayment {

    private $selected_country;

    private $id;
    public function __construct()
    {
        $this->id = 'wccbp';
        add_action('woocommerce_init', array($this, 'loadCustomerCountry'), 10);
        add_action('woocommerce_loaded', array($this, 'loadSettings'));
        add_action('wccbp_update_country', array($this, 'setSelectedCountry'), 10, 1);
        add_action('wccbp_filter_gateways', array($this, 'availablePaymentGateways'), 10, 2);
    }


    /**
     * Load admin settings
     */
    public function loadSettings()
    {
        new WCCBPSettings();
    }

    /**
     * Get customer country
     */
    public function loadCustomerCountry() {
        $this->setSelectedCountry(WC()->customer->get_shipping_country());
    }


    /**
     * Set selected country on Ajax request in checkout process
     * Country code is used.
     *
     */
    public function setSelectedCountry($country = null)
    {
        $country_code = $country !== null ? $country : (isset($_REQUEST['country']) ? $_REQUEST['country'] : false);

        if ($country_code) {
            $this->selected_country = sanitize_text_field($country_code);
        }
    }


    /**
     * List through available payment gateways,
     * check if certain payment gateway is enabled for country,
     * if no, unset it from $payment_gateways array
     *
     * @return array with updated list of available payment gateways
     */
    public function availablePaymentGateways($payment_gateways, $callback = null)
    {
        foreach ($payment_gateways as $gateway) {
            if(get_option($this->id . '_' . $gateway->id) && !in_array($this->selected_country, get_option($this->id . '_' . $gateway->id))) {
                unset($payment_gateways[$gateway->id]);
            }
        }

        // return $payment_gateways;

        if ($callback !== null) {
            $callback($payment_gateways);
        }

        return $payment_gateways;
    }
}

/**
 * Check if WooCommerce is active
 **/
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    require 'includes/admin/WCCBPSettings.php';

    new WoocommerceCountryBasedPayment();
}

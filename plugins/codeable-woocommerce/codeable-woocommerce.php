<?php

/**
 * Plugin Name: Codeable Woocommerce
 * Plugin URI: local
 * description: Alters the woocommerce cart to inflate the cart total by a configurable amount based on a given attribute in the product(s)
 * Author: Spiro Floropoulos
 * Author URI: spirofloropoulos.com
 **/

class MySettingsPage {
    private $options;

    public function __construct() {
        add_action('admin_menu', array($this, 'add_plugin_page'));
        add_action('admin_init', array($this, 'page_init'));
    }

    public function add_plugin_page() {
        add_options_page(
            'CW Config', // The title that will be displayed on the menu
            'Cart Inflation Config', // The title of the settings page
            'manage_options',
            'my-setting-admin',
            array($this, 'create_admin_page')
        );
    }

    /**
     * The core HTML for the admin settings page
     **/
    public function create_admin_page() {
        $this->options = get_option('cw_inflation');
        ?>
        <div class="wrap">
            <form method="post" action="options.php">
            <?php
                settings_fields('cw_options_group');
                do_settings_sections('my-setting-admin');
                submit_button();
            ?>
            </form>
        </div>
        <?php
    }

    public function page_init() {
        register_setting(
            'cw_options_group',
            'cw_inflation', // The name of the registered global option
            array($this, 'sanitize')
        );

        add_settings_section(
            'cw_section',
            'Inflation Settings',
            array($this, 'print_section_info'),
            'my-setting-admin'
        );

        // Field for the percentage
        add_settings_field(
            'percentage',
            'Inflation Percentage (%)',
            array($this, 'cw_inflation_percentage_callback'),
            'my-setting-admin',
            'cw_section'
        );

        // Field for the attribute name to look for in the cart
        add_settings_field(
            'attribute_name',
            'Attribute Name To Apply Inflation',
            array($this, 'cw_inflation_attribute_name_callback'),
            'my-setting-admin',
            'cw_section'
        );
    }

    public function sanitize($input) {
        $new_input = array();
        if (isset($input['percentage'])) {
            // We only want floating point numbers for the purpose of percentages
            $new_input['percentage'] = floatval($input['percentage']);
        }
        if (isset($input['attribute_name'])) {
            // We only want text here
            $new_input['attribute_name'] = sanitize_text_field($input['attribute_name']);
        }
        return $new_input;
    }

    public function print_section_info() {
        print 'Enter configuration settings.';
    }

    public function cw_inflation_percentage_callback() {
        printf(
            '<input type="text" id="percentage" name="cw_inflation[percentage]" value="%s" /><br/><i>For example: 2 or 38.3</i>',
            isset($this->options['percentage']) ? esc_attr($this->options['percentage']) : ''
        );
    }

    public function cw_inflation_attribute_name_callback() {
        printf(
            '<input type="text" id="attribute_name" name="cw_inflation[attribute_name]" value="%s" /><br/><i>Please note that the attribute name must, MUST match the custom attribute name you create/add for individual products in order for this to take effect.</i>',
            isset($this->options['attribute_name']) ? esc_attr($this->options['attribute_name']) : ''
        );
    }
}

if (is_admin()) {
    $my_settings_page = new MySettingsPage();
}

/**
 * Actual cart modifications below
 **/
add_action( 'woocommerce_cart_calculate_fees','woocommerce_custom_surcharge' );
function woocommerce_custom_surcharge() {
    global $woocommerce;

    if (is_admin() && ! defined('DOING_AJAX')) {
        return;
    }

    $cw_options = get_option('cw_inflation');
    $products = $woocommerce->cart->get_cart();
    $inflated = false;
    foreach ($products as $cartProduct => $values) {
        $product = wc_get_product($values['data']->get_id());
        $attributes = $product->get_attributes();
        foreach ($attributes as $attribute) {
            if ($attribute['name'] === $cw_options['attribute_name']) {
                $inflated = true;
            }
        }
    }

    if ($inflated) {
        // $percentage = 0.01;
        $percentage = $cw_options['percentage'] / 100;
        $surcharge = ($woocommerce->cart->cart_contents_total + $woocommerce->cart->shipping_total) * $percentage;
        $woocommerce->cart->add_fee('Surcharge', $surcharge, true, '');
    }
}

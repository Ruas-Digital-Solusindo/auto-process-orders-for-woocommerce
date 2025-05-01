<?php
/**
 * Plugin Name: Auto-process Orders for WooCommerce
 * Plugin URI: https://www.ruasdigital.id/wp-plugin/auto-process-orders-for-woocommerce/
 * Description: Automatically completes WooCommerce orders when customers purchase products at no cost, providing instant access without manual intervention.
 * Version: 1.1.0
 * Author: ruasdigitalid
 * Author URI: https://ruasdigital.id
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Requires Plugins: woocommerce
 * Text Domain: auto-process-orders-for-woocommerce
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit; // Prevent direct access
}

// Load plugin translations
function ruas_load_plugin_textdomain() {
    load_plugin_textdomain( 'auto-process-orders-for-woocommerce', false, basename( dirname( __FILE__ ) ) . '/languages' );
}
add_action( 'plugins_loaded', 'ruas_load_plugin_textdomain' );

// Add submenu under WooCommerce
add_action('admin_menu', 'ruas_add_free_order_settings_menu');
function ruas_add_free_order_settings_menu() {
    add_submenu_page(
        'woocommerce',
        __( 'Free Products Settings', 'auto-process-orders-for-woocommerce' ),
        __( 'Free Products Settings', 'auto-process-orders-for-woocommerce' ),
        'manage_options',
        'ruas-free-order-settings',
        'ruas_free_order_settings_page'
    );
}

// Settings Page
function ruas_free_order_settings_page() {
    $message = '';

    if ( isset( $_POST['ruas_settings_nonce'] ) ) {
        $nonce = sanitize_text_field( wp_unslash( $_POST['ruas_settings_nonce'] ) );

        if ( wp_verify_nonce( $nonce, 'ruas_free_order_settings' ) ) {
            if ( isset( $_POST['ruas_free_order_status'] ) ) {
                update_option( 'ruas_free_order_status', sanitize_text_field( wp_unslash( $_POST['ruas_free_order_status'] ) ) );
                $message = '<div class="ruas-notice"><p>' . esc_html__( 'Settings saved successfully!', 'auto-process-orders-for-woocommerce' ) . '</p></div>';
            }
        }
    }

    $selected_status = get_option( 'ruas_free_order_status', 'completed' );
    $order_statuses = wc_get_order_statuses();
    ?>
    <div class="wrap">
        <div class="ruas-settings-container">
            <div class="ruas-settings-header">
                <h1><?php esc_html_e( 'Free Products Settings', 'auto-process-orders-for-woocommerce' ); ?></h1>
                <p><?php esc_html_e( 'Set automatic status for orders with zero total (free)', 'auto-process-orders-for-woocommerce' ); ?></p>
            </div>

            <?php echo wp_kses_post( $message ); ?>

            <form method="post" class="ruas-settings-form">
                <?php wp_nonce_field( 'ruas_free_order_settings', 'ruas_settings_nonce' ); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="ruas_free_order_status"><?php esc_html_e( 'Automatic Order Status:', 'auto-process-orders-for-woocommerce' ); ?></label></th>
                        <td>
                            <select name="ruas_free_order_status" id="ruas_free_order_status">
                                <?php foreach ( $order_statuses as $key => $label ) : ?>
                                    <option value="<?php echo esc_attr( $key ); ?>" <?php selected( $selected_status, $key ); ?>>
                                        <?php echo esc_html( $label ); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select the status to be automatically applied to free orders.', 'auto-process-orders-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php echo esc_attr__( 'Save Settings', 'auto-process-orders-for-woocommerce' ); ?>">
                </p>
            </form>
        </div>
    </div>
    <?php
}

// Automatically update status for free orders
add_action( 'woocommerce_thankyou', 'ruas_auto_complete_free_orders' );
function ruas_auto_complete_free_orders( $order_id ) {
    if ( ! $order_id ) {
        return;
    }

    $order = wc_get_order( $order_id );
    if ( $order->get_total() == 0 ) {
        $custom_status = get_option( 'ruas_free_order_status', 'completed' );
        $order->update_status( $custom_status );
    }
}

// Enqueue admin CSS
function ruas_enqueue_admin_styles() {
    $screen = get_current_screen();
    if ( isset( $screen->id ) && 'woocommerce_page_ruas-free-order-settings' === $screen->id ) {
        wp_enqueue_style(
            'ruas-admin-style',
            plugin_dir_url( __FILE__ ) . 'css/admin-style.css',
            array(),
            '1.0',
            'all'
        );
    }
}
add_action( 'admin_enqueue_scripts', 'ruas_enqueue_admin_styles' );

// Enqueue admin JS
function ruas_enqueue_admin_scripts() {
    $screen = get_current_screen();
    if ( isset( $screen->id ) && 'woocommerce_page_ruas-free-order-settings' === $screen->id ) {
        wp_enqueue_script(
            'ruas-admin-script',
            plugin_dir_url( __FILE__ ) . 'js/admin-script.js',
            array( 'jquery' ),
            '1.0',
            true
        );
    }
}
add_action( 'admin_enqueue_scripts', 'ruas_enqueue_admin_scripts' );

// Add Settings link on plugin list
add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'ruas_add_settings_link_to_plugin_list' );
function ruas_add_settings_link_to_plugin_list( $links ) {
    $settings_link = '<a href="' . esc_url( admin_url( 'admin.php?page=ruas-free-order-settings' ) ) . '">' . esc_html__( 'Settings', 'auto-process-orders-for-woocommerce' ) . '</a>';
    array_unshift( $links, $settings_link );
    return $links;
}

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

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

// 1️⃣ Tambahkan menu di WooCommerce
add_action('admin_menu', 'ruas_add_free_order_settings_menu');
function ruas_add_free_order_settings_menu() {
    add_submenu_page(
        'woocommerce',
        'Pengaturan Free Order',
        'Free Products Settings',
        'manage_options',
        'ruas-free-order-settings',
        'ruas_free_order_settings_page'
    );
}

// 2️⃣ Halaman Pengaturan
function ruas_free_order_settings_page() {
    $message = '';
    
    // Check for nonce and verify it
    if (isset($_POST['ruas_settings_nonce'])) {
        // Unslash and sanitize the nonce
        $nonce = sanitize_text_field(wp_unslash($_POST['ruas_settings_nonce']));
        
        // Verify the nonce
        if (wp_verify_nonce($nonce, 'ruas_free_order_settings')) {
            if (isset($_POST['ruas_free_order_status'])) {
                // Unslash before sanitizing
                update_option('ruas_free_order_status', sanitize_text_field(wp_unslash($_POST['ruas_free_order_status']))); 
                $message = '<div class="ruas-notice"><p>' . esc_html__( 'Settings saved successfully!', 'auto-process-orders-for-woocommerce' ) . '</p></div>';
            }
        }
    }

    $selected_status = get_option('ruas_free_order_status', 'completed');
    $order_statuses = wc_get_order_statuses();
    ?>
    <div class="wrap">
        <div class="ruas-settings-container">
            <div class="ruas-settings-header">
                <h1><?php esc_html_e( 'Free Products Settings', 'auto-process-orders-for-woocommerce' ); ?></h1>
                <p><?php esc_html_e( 'Set automatic status for orders with zero total (free)', 'auto-process-orders-for-woocommerce' ); ?></p>
            </div>
            
            <?php echo wp_kses_post($message); ?>
            
            <form method="post" class="ruas-settings-form">
                <?php wp_nonce_field('ruas_free_order_settings', 'ruas_settings_nonce'); ?>
                <table class="form-table">
                    <tr>
                        <th><label for="ruas_free_order_status">Status Pesanan Otomatis:</label></th>
                        <td>
                            <select name="ruas_free_order_status" id="ruas_free_order_status">
                                <?php foreach ($order_statuses as $key => $label) : ?>
                                    <option value="<?php echo esc_attr($key); ?>" <?php selected($selected_status, $key); ?>>
                                        <?php echo esc_html($label); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Select the status to be automatically applied to free orders.', 'auto-process-orders-for-woocommerce' ); ?></p>
                        </td>
                    </tr>
                </table>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php esc_attr_e( 'Save Settings', 'auto-process-orders-for-woocommerce' ); ?>">
                </p>
            </form>
        </div>
    </div>
    <?php
}

// 3️⃣ Gunakan Status yang Dipilih untuk Pesanan Gratis
add_action( 'woocommerce_thankyou', 'ruas_auto_complete_free_orders' );
function ruas_auto_complete_free_orders( $order_id ) {
    if ( !$order_id ) return;

    $order = wc_get_order( $order_id );
    if ( $order->get_total() == 0 ) {
        $custom_status = get_option('ruas_free_order_status', 'completed');
        $order->update_status( $custom_status );
    }
}

// 4️⃣ Enqueue CSS dan JS di Halaman Admin
function ruas_enqueue_admin_styles() {
    // Pastikan kita hanya memuat style di halaman pengaturan plugin
    $screen = get_current_screen();
    if ( 'toplevel_page_ruas-free-order-settings' === $screen->id ) {
        wp_enqueue_style(
            'ruas-admin-style', // Nama handle style
            plugin_dir_url( __FILE__ ) . 'css/admin-style.css', // Lokasi file CSS
            array(), // Ketergantungan (tidak ada untuk CSS ini)
            '1.0', // Versi file CSS
            'all' // Media (gunakan 'all' untuk semua layar)
        );
    }
}
add_action( 'admin_enqueue_scripts', 'ruas_enqueue_admin_styles' );

// 5️⃣ Enqueue JS di Halaman Admin
function ruas_enqueue_admin_scripts() {
    // Pastikan hanya memuat JS di halaman pengaturan plugin
    $screen = get_current_screen();
    if ( 'toplevel_page_ruas-free-order-settings' === $screen->id ) {
        wp_enqueue_script(
            'ruas-admin-script', // Nama handle script
            plugin_dir_url( __FILE__ ) . 'admin-script.js', // Lokasi file JS
            array( 'jquery' ), // Ketergantungan (jQuery jika diperlukan)
            '1.0', // Versi file JS
            true // Memuat di footer
        );
    }
}
add_action( 'admin_enqueue_scripts', 'ruas_enqueue_admin_scripts' );

/**
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 */

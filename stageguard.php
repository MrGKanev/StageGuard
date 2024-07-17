<?php
/*
 * Plugin Name: StageGuard
 * Description: Adds a message to the admin panel indicating this is a staging environment and deactivates specific plugins.
 * Version:                 0.0.1
 * Author:                  Gabriel Kanev
 * Author URI:              https://gkanev.com
 * License:                 MIT
 * Requires at least:       6.0
 * Requires PHP:            7.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

// Add an admin notice indicating this is a staging environment.
function staging_env_notice()
{
    echo '<div class="notice notice-error"><p>This website is a <strong>staging environment</strong>.</p></div>';
}
add_action('admin_notices', 'staging_env_notice');

// Deactivate specific plugins in staging environment.
function deactivate_staging_plugins()
{
    $plugins_to_deactivate = array(
        'bunnycdn/bunnycdn.php',
        'redis-cache/redis-cache.php',
        'google-listings-and-ads/google-listings-and-ads.php',
        'metorik-helper/metorik-helper.php',
        'order-sync-with-zendesk-for-woocommerce/order-sync-with-zendesk-for-woocommerce.php',
        'redis-object-cache/redis-object-cache.php',
        'runcloud-hub/runcloud-hub.php',
        'site-kit-by-google/site-kit-by-google.php',
        'super-page-cache-for-cloudflare/super-page-cache-for-cloudflare.php',
        'ups-woocommerce-shipping/ups-woocommerce-shipping.php',
        'woocommerce-shipstation-integration/woocommerce-shipstation.php',
        'wp-opcache/wp-opcache.php'
    );

    foreach ($plugins_to_deactivate as $plugin) {
        if (is_plugin_active($plugin)) {
            deactivate_plugins($plugin);
        }
    }
}
add_action('admin_init', 'deactivate_staging_plugins');

// Add an admin notice when a restricted plugin is attempted to be activated.
function stageguard_activation_notice()
{
    if (isset($_GET['stageguard_activation_error'])) {
        echo '<div class="notice notice-error"><p>This plugin cannot be activated in the staging environment. Please deactivate StageGuard to enable this plugin.</p></div>';
    }
}
add_action('admin_notices', 'stageguard_activation_notice');

// Prevent activation of specific plugins with a custom error message.
function prevent_plugin_activation($plugin)
{
    $plugins_to_deactivate = array(
        'bunnycdn/bunnycdn.php',
        'redis-cache/redis-cache.php',
        'google-listings-and-ads/google-listings-and-ads.php',
        'metorik-helper/metorik-helper.php',
        'order-sync-with-zendesk-for-woocommerce/order-sync-with-zendesk-for-woocommerce.php',
        'redis-object-cache/redis-object-cache.php',
        'runcloud-hub/runcloud-hub.php',
        'site-kit-by-google/site-kit-by-google.php',
        'super-page-cache-for-cloudflare/super-page-cache-for-cloudflare.php',
        'ups-woocommerce-shipping/ups-woocommerce-shipping.php',
        'woocommerce-shipstation-integration/woocommerce-shipstation.php',
        'wp-opcache/wp-opcache.php'
    );

    if (in_array($plugin, $plugins_to_deactivate)) {
        deactivate_plugins($plugin);
        wp_safe_redirect(admin_url('plugins.php?stageguard_activation_error=true'));
        exit;
    }
}
add_action('activate_plugin', 'prevent_plugin_activation', 10, 1);

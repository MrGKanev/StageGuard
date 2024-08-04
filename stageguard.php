<?php
/*
 * Plugin Name: StageGuard
 * Plugin URI: https://github.com/MrGKanev/StageGuard/
 * Description: Adds a message to the admin panel indicating this is a staging environment and manages specific plugins.
 * Version: 0.0.3
 * Author: Gabriel Kanev
 * Author URI: https://gkanev.com
 * License: MIT
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class StageGuard
{
    private static $instance = null;
    private $plugins_to_handle;

    private function __construct()
    {
        $this->load_plugins_to_handle();

        add_action('admin_notices', [$this, 'staging_env_notice']);
        add_action('admin_init', [$this, 'deactivate_staging_plugins']);
        add_action('admin_notices', [$this, 'stageguard_activation_notice']);
        add_action('activate_plugin', [$this, 'prevent_plugin_activation'], 10, 1);
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_plugins_to_handle()
    {
        $this->plugins_to_handle = [
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
            'wp-opcache/wp-opcache.php',
            'headers-security-advanced-hsts-wp/headers-security-advanced-hsts-wp.php',
            'wp-rocket/wp-rocket.php',
            'tidio-live-chat/tidio-live-chat.php',
            'litespeed-cache/litespeed-cache.php',
            'wp-fastest-cache/wpFastestCache.php',
            'phastpress/phastpress.php',
            'w3-total-cache/w3-total-cache.php',
            'wp-optimize/wp-optimize.php',
            'autoptimize/autoptimize.php',
            'nitropack/nitropack.php',
            'wp-sync-db/wp-sync-db.php', // WP Sync DB
            'wp-sync-db-media-files/wp-sync-db-media-files.php', // WP Sync DB Media Files
            'updraftplus/updraftplus.php', // UpdraftPlus - Backup/Restore
        ];
        $this->plugins_to_handle = array_map('trim', $this->plugins_to_handle);
    }

    public function staging_env_notice()
    {
        echo '<div class="notice notice-warning"><p>This website is a <strong>staging environment</strong>.</p></div>';
    }

    public function deactivate_staging_plugins()
    {
        foreach ($this->plugins_to_handle as $plugin) {
            if (is_plugin_active($plugin)) {
                deactivate_plugins($plugin);
            }
        }
    }

    public function stageguard_activation_notice()
    {
        if (isset($_GET['stageguard_activation_error'])) {
            echo '<div class="notice notice-warning is-dismissible"><p>This plugin cannot be activated in the staging environment. Please deactivate StageGuard to enable this plugin.</p></div>';
        }
    }

    public function prevent_plugin_activation($plugin)
    {
        if (in_array($plugin, $this->plugins_to_handle)) {
            deactivate_plugins($plugin);
            wp_safe_redirect(add_query_arg('stageguard_activation_error', 'true', admin_url('plugins.php')));
            exit;
        }
    }
}

$stageguard = StageGuard::get_instance();

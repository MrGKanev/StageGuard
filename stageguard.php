<?php
/*
 * Plugin Name: StageGuard
 * Plugin URI: https://github.com/MrGKanev/StageGuard/
 * Description: Manages staging environment, including Coming Soon mode, search engine visibility, staging indicator, debug mode toggle, and robots.txt modification.
 * Version: 0.0.5
 * Author: Gabriel Kanev (modified by AI assistant)
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
        add_action('init', [$this, 'activate_woocommerce_coming_soon']);
        add_action('init', [$this, 'activate_wordpress_search_engine_visibility']);
        add_action('wp_head', [$this, 'add_staging_indicator']);
        add_action('admin_menu', [$this, 'add_debug_mode_menu']);
        add_action('generate_rewrite_rules', [$this, 'modify_robots_txt']);
        add_filter('robots_txt', [$this, 'custom_robots_txt'], 10, 2);
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
            'bunnycdn/bunnycdn.php', // BunnyCDN
            'redis-cache/redis-cache.php', // Redis Cache
            'google-listings-and-ads/google-listings-and-ads.php', // Google Listings and Ads
            'metorik-helper/metorik-helper.php', // Metorik Helper
            'order-sync-with-zendesk-for-woocommerce/order-sync-with-zendesk-for-woocommerce.php', // Order Sync with Zendesk for WooCommerce
            'redis-object-cache/redis-object-cache.php', // Redis Object Cache
            'runcloud-hub/runcloud-hub.php', // RunCloud Hub
            'google-site-kit/google-site-kit.php', // Site Kit by Google
            'super-page-cache-for-cloudflare/super-page-cache-for-cloudflare.php', // WP Cloudflare Page Cache
            'ups-woocommerce-shipping/ups-woocommerce-shipping.php',
            'woocommerce-shipstation-integration/woocommerce-shipstation.php', // ShipStation
            'wp-opcache/wp-opcache.php', // OPcache
            'headers-security-advanced-hsts-wp/headers-security-advanced-hsts-wp.php', // HSTS
            'wp-rocket/wp-rocket.php', // WP Rocket
            'tidio-live-chat/tidio-live-chat.php', // Tidio Chat
            'litespeed-cache/litespeed-cache.php', // LiteSpeed Cache
            'wp-fastest-cache/wpFastestCache.php', // WP Fastest Cache
            'phastpress/phastpress.php', // PhastPress
            'w3-total-cache/w3-total-cache.php', // W3 Total Cache
            'wp-optimize/wp-optimize.php', // WP Optimize
            'autoptimize/autoptimize.php', // Autoptimize
            'nitropack/nitropack.php', // Nitropack
            'wp-sync-db/wp-sync-db.php', // WP Sync DB
            'wp-sync-db-media-files/wp-sync-db-media-files.php', // WP Sync DB Media Files
            'updraftplus/updraftplus.php', // UpdraftPlus - Backup/Restore
            'mailchimp-for-woocommerce/mailchimp-woocommerce.php', // Mailchimp for WooCommerce
            'bunnycdn/bunnycdn.php'
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

    public function activate_woocommerce_coming_soon()
    {
        if (class_exists('WooCommerce')) {
            update_option('woocommerce_shop_page_display', '');
            update_option('woocommerce_category_archive_display', '');
            update_option('woocommerce_default_catalog_orderby', 'menu_order');
            update_option('woocommerce_placeholder_image', 0);
            update_option('woocommerce_enable_reviews', 'no');
            update_option('woocommerce_enable_review_rating', 'no');
            update_option('woocommerce_enable_ajax_add_to_cart', 'no');
            
            // Set store notice
            update_option('woocommerce_demo_store', 'yes');
            update_option('woocommerce_demo_store_notice', 'Coming Soon! Our store is currently under construction.');
        }
    }

    public function activate_wordpress_search_engine_visibility()
    {
        update_option('blog_public', 0);
    }

    // New method: Add staging indicator
    public function add_staging_indicator()
    {
        echo '<div style="position: fixed; top: 0; left: 0; right: 0; background: #ff6b6b; color: white; text-align: center; padding: 5px; z-index: 9999;">STAGING ENVIRONMENT</div>';
    }

    // New method: Add debug mode toggle
    public function add_debug_mode_menu()
    {
        add_options_page('Debug Mode', 'Debug Mode', 'manage_options', 'stageguard-debug', [$this, 'debug_mode_page']);
    }

    public function debug_mode_page()
    {
        if (isset($_POST['debug_mode'])) {
            $debug_mode = $_POST['debug_mode'] === 'on' ? true : false;
            $this->update_wp_config('WP_DEBUG', $debug_mode);
        }

        $current_debug_mode = defined('WP_DEBUG') && WP_DEBUG;

        echo '<div class="wrap">';
        echo '<h1>Debug Mode</h1>';
        echo '<form method="post">';
        echo '<label for="debug_mode">Enable Debug Mode: </label>';
        echo '<input type="checkbox" id="debug_mode" name="debug_mode" ' . ($current_debug_mode ? 'checked' : '') . '>';
        echo '<p class="submit"><input type="submit" name="submit" id="submit" class="button button-primary" value="Save Changes"></p>';
        echo '</form>';
        echo '</div>';
    }

    private function update_wp_config($constant, $value)
    {
        $config_file = ABSPATH . 'wp-config.php';
        $config_content = file_get_contents($config_file);

        if ($value === true) {
            $replacement = "define('$constant', true);";
        } elseif ($value === false) {
            $replacement = "define('$constant', false);";
        } else {
            $replacement = "define('$constant', '$value');";
        }

        if (preg_match("/define\s*\(\s*['\"]$constant['\"]\s*,/", $config_content)) {
            $config_content = preg_replace("/define\s*\(\s*['\"]$constant['\"]\s*,.*?\);/", $replacement, $config_content);
        } else {
            $config_content .= PHP_EOL . $replacement;
        }

        file_put_contents($config_file, $config_content);
    }

    // New method: Modify robots.txt
    public function modify_robots_txt($wp_rewrite)
    {
        $home_path = get_home_path();
        $robots_file = $home_path . 'robots.txt';

        $content = "User-agent: *\nDisallow: /\n";
        file_put_contents($robots_file, $content);
    }

    public function custom_robots_txt($output, $public)
    {
        $output = "User-agent: *\nDisallow: /\n";
        return $output;
    }
}

$stageguard = StageGuard::get_instance();
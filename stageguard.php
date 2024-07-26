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
        $this->load_settings();

        add_action('admin_notices', [$this, 'staging_env_notice']);
        add_action('admin_init', [$this, 'deactivate_staging_plugins']);
        add_action('admin_notices', [$this, 'stageguard_activation_notice']);
        add_action('activate_plugin', [$this, 'prevent_plugin_activation'], 10, 1);
        add_action('admin_menu', [$this, 'add_settings_page']);
        add_action('admin_init', [$this, 'register_settings']);
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function load_settings()
    {
        $saved_plugins = get_option('stageguard_plugins_to_handle');
        $this->plugins_to_handle = $saved_plugins ? explode("\n", $saved_plugins) : [
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
            'nitropack/nitropack.php'
        ];
        $this->plugins_to_handle = array_map('trim', $this->plugins_to_handle);
    }

    public function staging_env_notice()
    {
        echo '<div class="notice notice-error"><p>This website is a <strong>staging environment</strong>.</p></div>';
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
            echo '<div class="notice notice-error is-dismissible"><p>This plugin cannot be activated in the staging environment. Please deactivate StageGuard to enable this plugin.</p></div>';
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

    public function add_settings_page()
    {
        add_options_page('StageGuard Settings', 'StageGuard', 'manage_options', 'stageguard', [$this, 'render_settings_page']);
    }

    public function register_settings()
    {
        register_setting('stageguard_options', 'stageguard_plugins_to_handle', [
            'sanitize_callback' => [$this, 'sanitize_plugins_list'],
        ]);
    }

    public function sanitize_plugins_list($input)
    {
        $plugins = explode("\n", $input);
        $plugins = array_map('trim', $plugins);
        $plugins = array_filter($plugins);
        return implode("\n", $plugins);
    }

    public function render_settings_page()
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        if (isset($_GET['settings-updated'])) {
            add_settings_error('stageguard_messages', 'stageguard_message', 'Settings Saved', 'updated');
        }

        settings_errors('stageguard_messages');
?>
        <div class="wrap">
            <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
            <form action="options.php" method="post">
                <?php
                settings_fields('stageguard_options');
                do_settings_sections('stageguard_options');
                ?>
                <table class="form-table">
                    <tr valign="top">
                        <th scope="row">Plugins to Handle</th>
                        <td>
                            <textarea name="stageguard_plugins_to_handle" rows="10" cols="50"><?php echo esc_textarea(implode("\n", $this->plugins_to_handle)); ?></textarea>
                            <p class="description">Enter one plugin file path per line.</p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings'); ?>
            </form>
        </div>
<?php
    }
}

$stageguard = StageGuard::get_instance();

<?php
/*
 * Plugin Name: StageGuard
 * Plugin URI: https://github.com/MrGKanev/StageGuard/
 * Description: Manages staging environment, including Coming Soon mode, search engine visibility, staging indicator, debug mode toggle, and robots.txt modification.
 * Version: 0.2.0
 * Author: Gabriel Kanev
 * Author URI: https://gkanev.com
 * License: GPL-2.0 License
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: stageguard
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly.
}

class StageGuard
{
    private static $instance = null;
    private $plugins_to_handle;
    private $log_file;

    private function __construct()
    {
        $this->load_plugins_to_handle();
        $this->log_file = WP_CONTENT_DIR . '/stageguard-log.txt';

        add_action('plugins_loaded', [$this, 'load_textdomain']);
        add_action('admin_notices', [$this, 'staging_env_notice']);
        add_action('admin_init', [$this, 'deactivate_staging_plugins']);
        add_action('admin_notices', [$this, 'stageguard_activation_notice']);
        add_action('activate_plugin', [$this, 'prevent_plugin_activation'], 10, 1);
        add_action('init', [$this, 'activate_woocommerce_coming_soon_mode']);
        add_action('init', [$this, 'activate_wordpress_search_engine_visibility']);
        add_action('wp_head', [$this, 'add_staging_indicator']);
        add_action('admin_menu', [$this, 'add_stageguard_menu']);
        add_action('generate_rewrite_rules', [$this, 'modify_robots_txt']);
        add_filter('robots_txt', [$this, 'custom_robots_txt'], 10, 2);
        add_action('wp_login', [$this, 'log_user_login'], 10, 2);

        // New actions for additional security measures
        add_action('init', [$this, 'password_protect_staging']);
        add_action('init', [$this, 'ip_restrict_staging']);

        // New action for email handling
        add_filter('wp_mail', [$this, 'catch_staging_emails']);

        register_activation_hook(__FILE__, [$this, 'activate']);
        register_deactivation_hook(__FILE__, [$this, 'deactivate']);
    }

    public static function get_instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function load_textdomain()
    {
        load_plugin_textdomain('stageguard', false, dirname(plugin_basename(__FILE__)) . '/languages');
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
        ];
        $this->plugins_to_handle = array_map('trim', $this->plugins_to_handle);
    }

    public function staging_env_notice()
    {
        echo '<div class="notice notice-warning"><p>' . esc_html__('This website is a staging environment.', 'stageguard') . '</p></div>';
    }

    public function deactivate_staging_plugins()
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        foreach ($this->plugins_to_handle as $plugin) {
            if (is_plugin_active($plugin)) {
                deactivate_plugins($plugin);
                $this->log_action(sprintf('Deactivated plugin: %s', $plugin));
            }
        }
    }

    public function stageguard_activation_notice()
    {
        if (isset($_GET['stageguard_activation_error'])) {
            echo '<div class="notice notice-warning is-dismissible"><p>' . esc_html__('This plugin cannot be activated in the staging environment. Please deactivate StageGuard to enable this plugin.', 'stageguard') . '</p></div>';
        }
    }

    public function prevent_plugin_activation($plugin)
    {
        if (!current_user_can('activate_plugins')) {
            return;
        }

        if (in_array($plugin, $this->plugins_to_handle)) {
            deactivate_plugins($plugin);
            $this->log_action(sprintf('Prevented activation of plugin: %s', $plugin));
            wp_safe_redirect(add_query_arg('stageguard_activation_error', 'true', admin_url('plugins.php')));
            exit;
        }
    }

    public function activate_woocommerce_coming_soon_mode()
    {
        if (class_exists('WooCommerce')) {
            if (version_compare(WC()->version, '9.1', '>=')) {
                update_option('woocommerce_coming_soon', 'yes');
                $this->log_action('Activated WooCommerce Coming Soon mode');
            }
        }
    }

    public function activate_wordpress_search_engine_visibility()
    {
        update_option('blog_public', 0);
        $this->log_action('Activated WordPress Search Engine Visibility');
    }

    public function add_staging_indicator()
    {
        echo '<div style="position: fixed; top: 0; left: 0; right: 0; background: #ff6b6b; color: white; text-align: center; padding: 5px; z-index: 9999;">' . esc_html__('STAGING ENVIRONMENT', 'stageguard') . '</div>';
    }

    public function add_stageguard_menu()
    {
        add_options_page(
            __('StageGuard Settings', 'stageguard'),
            __('StageGuard', 'stageguard'),
            'manage_options',
            'stageguard-settings',
            [$this, 'stageguard_settings_page']
        );
    }

    public function stageguard_settings_page()
    {
        if (!current_user_can('manage_options')) {
            wp_die(__('You do not have sufficient permissions to access this page.', 'stageguard'));
        }

        if (isset($_POST['stageguard_settings']) && check_admin_referer('stageguard_settings')) {
            $debug_mode = isset($_POST['debug_mode']);
            $password_protection = isset($_POST['password_protection']);
            $staging_password = sanitize_text_field($_POST['staging_password']);
            $ip_restriction = isset($_POST['ip_restriction']);
            $allowed_ips = sanitize_textarea_field($_POST['allowed_ips']);

            update_option('stageguard_debug_mode', $debug_mode);
            update_option('stageguard_password_protection', $password_protection);
            if (!empty($staging_password)) {
                update_option('stageguard_staging_password', wp_hash_password($staging_password));
            }
            update_option('stageguard_ip_restriction', $ip_restriction);
            update_option('stageguard_allowed_ips', $allowed_ips);

            $this->update_wp_config('WP_DEBUG', $debug_mode);
            $this->log_action('Settings updated');
        }

        $current_debug_mode = get_option('stageguard_debug_mode', true);
        $current_password_protection = get_option('stageguard_password_protection', false);
        $current_ip_restriction = get_option('stageguard_ip_restriction', false);
        $current_allowed_ips = get_option('stageguard_allowed_ips', '');

?>
        <div class="wrap">
            <h1><?php esc_html_e('StageGuard Settings', 'stageguard'); ?></h1>
            <form method="post">
                <?php wp_nonce_field('stageguard_settings'); ?>
                <table class="form-table">
                    <tr>
                        <th scope="row"><?php esc_html_e('Debug Mode', 'stageguard'); ?></th>
                        <td>
                            <label for="debug_mode">
                                <input type="checkbox" id="debug_mode" name="debug_mode" <?php checked($current_debug_mode); ?>>
                                <?php esc_html_e('Enable Debug Mode', 'stageguard'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Password Protection', 'stageguard'); ?></th>
                        <td>
                            <label for="password_protection">
                                <input type="checkbox" id="password_protection" name="password_protection" <?php checked($current_password_protection); ?>>
                                <?php esc_html_e('Enable Password Protection', 'stageguard'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Staging Password', 'stageguard'); ?></th>
                        <td>
                            <input type="password" id="staging_password" name="staging_password">
                            <p class="description"><?php esc_html_e('Enter a new password to update. Leave blank to keep the current password.', 'stageguard'); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('IP Restriction', 'stageguard'); ?></th>
                        <td>
                            <label for="ip_restriction">
                                <input type="checkbox" id="ip_restriction" name="ip_restriction" <?php checked($current_ip_restriction); ?>>
                                <?php esc_html_e('Enable IP Restriction', 'stageguard'); ?>
                            </label>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e('Allowed IPs', 'stageguard'); ?></th>
                        <td>
                            <textarea id="allowed_ips" name="allowed_ips" rows="5" cols="50"><?php echo esc_textarea($current_allowed_ips); ?></textarea>
                            <p class="description"><?php esc_html_e('Enter one IP address per line', 'stageguard'); ?></p>
                        </td>
                    </tr>
                </table>
                <?php submit_button('Save Settings', 'primary', 'stageguard_settings'); ?>
            </form>
        </div>
        <?php
    }

    private function update_wp_config($constant, $value)
    {
        if (!current_user_can('manage_options')) {
            return;
        }

        $wp_config_file = ABSPATH . 'wp-config.php';

        if (!is_writable($wp_config_file)) {
            add_settings_error('stageguard', 'file_not_writable', __('wp-config.php is not writable. Please check file permissions.', 'stageguard'));
            return;
        }

        $config_content = file_get_contents($wp_config_file);
        $value_to_put = $value ? 'true' : 'false';

        if (preg_match("/define\s*\(\s*(['\"])$constant\\1\s*,\s*(.+?)\s*\);/", $config_content)) {
            $config_content = preg_replace(
                "/define\s*\(\s*(['\"])$constant\\1\s*,\s*(.+?)\s*\);/",
                "define('$constant', $value_to_put);",
                $config_content
            );
        } else {
            $config_content .= PHP_EOL . "define('$constant', $value_to_put);";
        }

        if (file_put_contents($wp_config_file, $config_content) === false) {
            add_settings_error('stageguard', 'file_not_updated', __('Failed to update wp-config.php. Please check file permissions.', 'stageguard'));
        }
    }

    public function modify_robots_txt($wp_rewrite)
    {
        $home_path = get_home_path();
        $robots_file = $home_path . 'robots.txt';

        $content = "User-agent: *\nDisallow: /\n";
        file_put_contents($robots_file, $content);
    }

    public function custom_robots_txt($output, $public)
    {
        return "User-agent: *\nDisallow: /\n";
    }

    public function password_protect_staging()
    {
        if (get_option('stageguard_password_protection', false)) {
            if (!is_user_logged_in() && !defined('DOING_CRON')) {
                $this->display_password_form();
            }
        }
    }

    private function display_password_form()
    {
        if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['staging_access_password'])) {
            $submitted_password = $_POST['staging_access_password'];
            $stored_password_hash = get_option('stageguard_staging_password');

            if (wp_check_password($submitted_password, $stored_password_hash)) {
                $this->set_auth_cookie();
                return;
            }
        }

        if (!$this->is_auth_cookie_valid()) {
            header('HTTP/1.1 401 Unauthorized');
        ?>
            <!DOCTYPE html>
            <html>

            <head>
                <title><?php esc_html_e('Staging Site Access', 'stageguard'); ?></title>
            </head>

            <body>
                <h1><?php esc_html_e('Staging Site Access', 'stageguard'); ?></h1>
                <form method="post">
                    <label for="staging_access_password"><?php esc_html_e('Password:', 'stageguard'); ?></label>
                    <input type="password" id="staging_access_password" name="staging_access_password" required>
                    <input type="submit" value="<?php esc_attr_e('Access Staging Site', 'stageguard'); ?>">
                </form>
            </body>

            </html>
<?php
            exit;
        }
    }

    private function set_auth_cookie()
    {
        $expiration = time() + DAY_IN_SECONDS;
        $cookie_value = wp_hash('stageguard_auth_' . get_current_blog_id() . $expiration);
        setcookie('stageguard_auth', $cookie_value, $expiration, COOKIEPATH, COOKIE_DOMAIN, is_ssl(), true);
    }

    private function is_auth_cookie_valid()
    {
        if (!isset($_COOKIE['stageguard_auth'])) {
            return false;
        }

        $cookie_value = $_COOKIE['stageguard_auth'];
        $expiration = time() + DAY_IN_SECONDS;
        $expected_value = wp_hash('stageguard_auth_' . get_current_blog_id() . $expiration);

        return hash_equals($expected_value, $cookie_value);
    }

    public function ip_restrict_staging()
    {
        if (get_option('stageguard_ip_restriction', false)) {
            $allowed_ips = explode("\n", get_option('stageguard_allowed_ips', ''));
            $allowed_ips = array_map('trim', $allowed_ips);
            $current_ip = $_SERVER['REMOTE_ADDR'];

            if (!in_array($current_ip, $allowed_ips) && !is_user_logged_in()) {
                wp_die(__('Access denied. Your IP is not allowed to view this staging site.', 'stageguard'));
            }
        }
    }

    public function log_action($message)
    {
        $timestamp = current_time('mysql');
        $log_message = sprintf("[%s] %s\n", $timestamp, $message);
        file_put_contents($this->log_file, $log_message, FILE_APPEND);
    }

    public function log_user_login($user_login, $user)
    {
        $this->log_action(sprintf('User logged in: %s (ID: %d)', $user_login, $user->ID));
    }

    public function catch_staging_emails($args)
    {
        $this->log_action(sprintf('Email caught: To: %s, Subject: %s', $args['to'], $args['subject']));

        // Prevent the email from being sent
        $args['to'] = 'no-reply@example.com';

        return $args;
    }

    public function activate()
    {
        add_option('stageguard_debug_mode', true);
        add_option('stageguard_password_protection', false);
        add_option('stageguard_ip_restriction', false);
        add_option('stageguard_allowed_ips', '');
        $this->update_wp_config('WP_DEBUG', true);
        $this->log_action('StageGuard activated');
    }

    public function deactivate()
    {
        delete_option('stageguard_debug_mode');
        delete_option('stageguard_password_protection');
        delete_option('stageguard_ip_restriction');
        delete_option('stageguard_allowed_ips');
        $this->log_action('StageGuard deactivated');
    }
}

function stageguard_init()
{
    $stageguard = StageGuard::get_instance();
}
add_action('plugins_loaded', 'stageguard_init');

// WP-CLI Support
if (defined('WP_CLI') && WP_CLI) {
    WP_CLI::add_command('stageguard', 'StageGuardCLI');
}

class StageGuardCLI
{
    /**
     * Toggles debug mode on or off.
     *
     * ## OPTIONS
     *
     * <on|off>
     * : Whether to turn debug mode on or off.
     *
     * ## EXAMPLES
     *
     *     wp stageguard debug_mode on
     *     wp stageguard debug_mode off
     *
     * @when after_wp_load
     */
    public function debug_mode($args)
    {
        if (!isset($args[0])) {
            WP_CLI::error('Please specify either "on" or "off".');
        }

        $value = $args[0] === 'on';
        update_option('stageguard_debug_mode', $value);
        StageGuard::get_instance()->update_wp_config('WP_DEBUG', $value);

        WP_CLI::success('Debug mode has been turned ' . ($value ? 'on' : 'off') . '.');
    }

    /**
     * Displays the StageGuard log.
     *
     * ## OPTIONS
     *
     * [--lines=<number>]
     * : Number of lines to display from the end of the log. Default is 50.
     *
     * ## EXAMPLES
     *
     *     wp stageguard show_log
     *     wp stageguard show_log --lines=100
     *
     * @when after_wp_load
     */
    public function show_log($args, $assoc_args)
    {
        $lines = isset($assoc_args['lines']) ? intval($assoc_args['lines']) : 50;
        $log_file = WP_CONTENT_DIR . '/stageguard-log.txt';

        if (!file_exists($log_file)) {
            WP_CLI::error('Log file does not exist.');
        }

        $log_content = file($log_file);
        $log_content = array_slice($log_content, -$lines);

        foreach ($log_content as $line) {
            WP_CLI::line(trim($line));
        }
    }
}

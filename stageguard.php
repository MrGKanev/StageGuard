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
        echo '<div class="notice notice-warning"><p>This website is a <strong>staging environment</strong>.</p></div>';
    }
    add_action('admin_notices', 'staging_env_notice');

    // Deactivate specific plugins in staging environment.
    function deactivate_staging_plugins()
    {
        $plugins_to_deactivate = array(
            'bunnycdn/bunnycdn.php',
            'redis-cache/redis-cache.php',
        );

        foreach ($plugins_to_deactivate as $plugin) {
            if (is_plugin_active($plugin)) {
                deactivate_plugins($plugin);
            }
        }
    }
    add_action('admin_init', 'deactivate_staging_plugins');

# StageGuard

## Description

StageGuard is a WordPress plugin designed to clearly indicate and manage a staging environment. It provides various features to protect your staging site, prevent accidental emails, and manage plugin activations.

## Features

1. **Staging Environment Indicator**: Displays a prominent red message at the top of all pages indicating that the site is a staging environment.

2. **Password Protection**: Redirects non-logged-in users to the WordPress login page, ensuring that only authenticated users can access the staging site.

3. **IP Restriction**: Allows access only from specified IP addresses.

4. **Plugin Management**: Automatically deactivates specific plugins in the staging environment.

5. **Search Engine Visibility**: Automatically sets the site to discourage search engines from indexing it.

6. **WooCommerce Coming Soon Mode**: Activates WooCommerce Coming Soon mode if WooCommerce is installed.

7. **Email Catching**: Prevents emails from being sent out from the staging environment.

8. **Debug Mode Toggle**: Easily enable or disable WordPress debug mode.

9. **Robots.txt Modification**: Modifies the robots.txt file to disallow all crawlers.

10. **Logging System**: Keeps a log of important actions for debugging and monitoring.

## Deactivated Plugins

StageGuard will deactivate the following plugins:

1. [BunnyCDN](https://wordpress.org/plugins/bunnycdn/)
2. [Redis Cache](https://wordpress.org/plugins/redis-cache/)
3. [Google Listings and Ads](https://wordpress.org/plugins/google-listings-and-ads/)
4. [Metorik Helper](https://wordpress.org/plugins/metorik-helper/)
5. [Order Sync with Zendesk for WooCommerce](https://wordpress.org/plugins/order-sync-with-zendesk-for-woocommerce/)
6. [Redis Object Cache](https://wordpress.org/plugins/redis-object-cache/)
7. [RunCloud Hub](https://wordpress.org/plugins/runcloud-hub/)
8. [Site Kit by Google](https://wordpress.org/plugins/google-site-kit/)
9. [Super Page Cache for Cloudflare](https://wordpress.org/plugins/wp-cloudflare-page-cache/)
10. [WooCommerce - ShipStation Integration](https://wordpress.org/plugins/woocommerce-shipstation-integration/)
11. [WP OPcache](https://wordpress.org/plugins/wp-opcache/)
12. [Headers Security Advanced & HSTS WP](https://wordpress.org/plugins/headers-security-advanced-hsts-wp/)
13. [WP-Rocket](https://wp-rocket.me/)
14. [Tidio Chat](https://wordpress.org/plugins/tidio-live-chat/)
15. [LiteSpeed Cache](https://wordpress.org/plugins/litespeed-cache/)
16. [WP Fastest Cache](https://wordpress.org/plugins/wp-fastest-cache/)
17. [PhastPress](https://wordpress.org/plugins/phastpress/)
18. [W3 Total Cache](https://wordpress.org/plugins/w3-total-cache/)
19. [WP Optimize](https://wordpress.org/plugins/wp-optimize/)
20. [Autoptimize](https://wordpress.org/plugins/autoptimize/)
21. [NitroPack](https://wordpress.org/plugins/nitropack/)
22. [WP Sync DB](https://github.com/wp-sync-db/wp-sync-db)
23. [WP Sync DB Media Files](https://github.com/wp-sync-db/wp-sync-db-media-files)
24. [UpdraftPlus](https://wordpress.org/plugins/updraftplus/)
25. [Mailchimp for WooCommerce](https://wordpress.org/plugins/mailchimp-for-woocommerce/)

## Installation

1. Upload the `stageguard` folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Go to Settings > StageGuard to configure the plugin.

## Configuration

1. **Debug Mode**: Toggle WordPress debug mode on or off.
2. **Password Protection**: Enable to redirect non-logged-in users to the WordPress login page.
3. **IP Restriction**: Enable and specify allowed IP addresses to restrict access to the staging site.
4. **Allowed IPs**: Enter the IP addresses that should have access to the staging site (one per line).

## Viewing Logs

You can view the StageGuard logs in two ways:

1. **Admin Interface**: Go to Settings > StageGuard Logs in the WordPress admin area.
2. **WP-CLI**: Use the command `wp stageguard show_log` to view logs in the terminal.

## WP-CLI Commands

StageGuard supports the following WP-CLI commands:

- `wp stageguard debug_mode <on|off>`: Toggle debug mode on or off.
- `wp stageguard show_log [--lines=<number>]`: Display the StageGuard log. Use the `--lines` option to specify the number of lines to show (default is 50).

## Troubleshooting

If you're having issues with StageGuard, check the following:

1. Ensure that the web server has write permissions to the `wp-content` directory for logging.
2. If you're not seeing the staging indicator, check if your theme is properly loading the `wp_head` action.
3. If password protection isn't working, make sure you're not already logged in to WordPress.

## License

This plugin is licensed under the GPL-2.0 License.

## Support

For support, please open an issue on the [GitHub repository](https://github.com/MrGKanev/StageGuard/).

## Author

Gabriel Kanev
Author URI: <https://gkanev.com>

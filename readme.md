# StageGuard

## Description

StageGuard is a WordPress plugin designed to clearly indicate that a website is running in a staging environment and manage various aspects of the staging setup.

## Features

- Displays a prominent message in the admin panel indicating a staging environment.
- Automatically deactivates specific plugins.
- Prevents activation of certain plugins and provides a custom error message.
- Activates WooCommerce Coming Soon mode (for WooCommerce 9.1 or higher).
- Disables search engine visibility for WordPress.
- Adds a visual indicator on the frontend for staging environments.
- Provides a settings page to toggle Debug Mode.
- Modifies robots.txt to disallow all crawling.
- Offers WP-CLI support for managing debug mode.

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
3. Confirm the activation when prompted.

## Usage

### Admin Settings

Navigate to Settings > StageGuard in the WordPress admin panel to access the StageGuard settings page. Here you can toggle Debug Mode on or off.

### WP-CLI Commands

StageGuard supports the following WP-CLI command:

```
wp stageguard debug_mode <on|off>
```

This command allows you to toggle debug mode on or off from the command line.

## Requirements

- WordPress 6.0 or higher
- PHP 7.4 or higher

## License

This plugin is licensed under the MIT License.

## Author

Gabriel Kanev
Author URI: <https://gkanev.com>

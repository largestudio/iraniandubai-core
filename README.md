# IranianDubai Core

Production-ready core functionality plugin for the IranianDubai website.

## Requirements

- PHP 8.2+
- WordPress 6.7+
- WoodMart 8.5.4 compatible
- Elementor Pro compatible

## Architecture

The plugin uses a lightweight PSR-4 autoloader for the `IDB\` namespace and keeps features in independent modules:

- `IDB\Core` - bootstrap, requirements, module contracts, autoloading.
- `IDB\Admin` - WordPress admin menu and settings.
- `IDB\Blog` - `[idb_blog]` shortcode registration.
- `IDB\Frontend` - blog shortcode rendering.

No active theme files are modified.

## Shortcode

Display latest blog posts with pagination:

```text
[idb_blog]
```

## Admin

After activation, open **IranianDubai** in the WordPress admin menu to verify the settings page.

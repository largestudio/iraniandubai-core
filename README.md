# IranianDubai Core

Production-ready core functionality plugin for the IranianDubai website.

Current release candidate: `1.0.0-rc.1`.

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
- `IDB\Elementor` - Elementor widget integration backed by the shared blog renderer.

No active theme files are modified.

## Shortcode

Display latest blog posts with pagination, category filters, and search:

```text
[idb_blog]
```

Common attributes:

```text
[idb_blog posts="6" columns="2" excerpt="24" pagination="yes"]
```

## Admin

After activation, open **IranianDubai** in the WordPress admin menu to configure blog defaults and import or export plugin settings.

## Release Candidate Notes

- Public blog output is rendered through `IDB\Frontend\BlogRenderer`.
- Queries are built through `IDB\Blog\Query`.
- AJAX behavior progressively enhances existing pagination, filter, and search URLs.
- Plugin options are removed on uninstall.

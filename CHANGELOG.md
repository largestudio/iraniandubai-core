# Changelog

## v1.0.0

### Changed
- Promoted the release candidate to the final v1.0.0 release.
- Completed final pre-release audit for version metadata, lifecycle safety, admin security, AJAX behavior, cache invalidation, and documentation.

## v1.0.0-rc.1

### Added
- AJAX blog pagination, category filters, and search with non-JS fallbacks.
- Blog SEO improvements including card labels, image alt fallbacks, and JSON-LD.
- Safe transient caching for public blog output with post-change invalidation.
- Admin settings import/export tools.

### Changed
- Hardened admin settings validation, bounds, nonces, and capability checks.
- Cleaned release-candidate bootstrap and lifecycle formatting.

## v0.7.3

### Fixed
- Fixed blog pagination for shortcode and Elementor widget
- Fixed admin defaults being ignored by widget/shortcode
- Cleaned blog query flow
- Removed debug leftovers

## v0.7.2

### Added
- Elementor Blog Widget
- Responsive Blog Cards
- Blog Renderer
- Category Filter

### Changed
- Improved Blog UI
- Elementor integration

### Fixed
- WoodMart compatibility
- Blog CSS loading

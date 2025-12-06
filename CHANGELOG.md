# Changelog

All notable changes to BK Auction Manager will be documented in this file.

## [1.3.0] - 2025-12-06

### Changed
- **BREAKING:** Renamed all prefixes from `WCAM_`, `wcam_` to `BK_AUCTION_`, `bk_auction_`
- **BREAKING:** Class names updated: `WP_Community_Auction_Manager` → `BK_Auction_Manager`
- **BREAKING:** Text domain changed to `bk-auction-manager`
- **BREAKING:** CSS classes updated from `.wcam-` to `.bk-auction-`
- **BREAKING:** JavaScript variables updated to use `bkAuction` prefix
- Updated dependency check to look for `BK_Community_Core` class
- Changed dependency check to warning instead of fatal error (allows activation)

### Fixed
- Corrected post type registration `show_in_menu` to integrate with custom admin menu (`bk-auction-dashboard`)
- Fixed dependency check function names (was using wrong plugin prefix)
- Fixed plugin name in error messages
- Plugin now activates successfully even if Core plugin is not installed (shows warning)

## [1.2.0] - 2025-12-06

### Added
- Modern auction management interface
- Real-time bidding system
- User dashboard improvements
- Enhanced notification system

## [1.1.0] - 2025-12-06

### Added
- Initial release with auction management features
- Auction post type
- Bidding system
- User notifications
- Admin settings
- Template system

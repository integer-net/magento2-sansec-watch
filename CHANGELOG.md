# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0]

### Added

- Added policy search in the admin configuration.
- Added sorting for the displayed policy list.
- Added a German (`de_DE`) translation for admin texts.

### Changed

- Collapsed long policy lists by default in the admin UI.
- Hid labels for informational fields that are not editable configuration values.
- Made admin UI strings translatable.

### Fixed

- Added an explicit empty-state message when no policies are available.

## [1.0.12] - 2025-09-23

### Changed

- Memoized policy fetches to avoid repeated requests during a single update flow.
- Disabled inline policies by default.

## [1.0.10] - 2025-02-20

### Fixed

- Sorted policies before calculating the change hash so identical policy sets no longer trigger incorrect update detection.

## [1.0.9] - 2025-02-14

### Added

- Added a status indicator for the report URI configuration.

### Changed

- Added compatibility with `symfony/http-client` 5.x.

### Fixed

- Prevented policies from being loaded when the module is disabled.

## [1.0.7] - 2024-12-02

### Added

- Added an admin action that links directly to the Sansec dashboard.
- Added default button styling for the dashboard shortcut.

## [1.0.6] - 2024-11-30

### Changed

- Made the default Sansec policy fetch parameters configurable.
- Stopped enabling `strict-dynamic` by default.

## [1.0.4] - 2024-11-17

### Fixed

- Resolved policy table names through Magento's `ResourceConnection` to improve compatibility with customized database table names.

## [1.0.3] - 2024-11-17

### Changed

- Added compatibility with `symfony/http-client` 7.

## [1.0.2] - 2024-11-05

### Fixed

- Added support for Magento installations that use database table prefixes.

## [1.0.1] - 2024-10-29

### Changed

- Changed the package license to MIT.

## [1.0.0] - 2024-10-18

### Added

- Initial release of the Magento integration for importing CSP policies from Sansec Watch.
- Added scheduled and manual policy updates, including CLI support.
- Added an admin policy list and backend timestamps for the last checks and updates.
- Added full page cache handling after policy updates.
- Added extension points for customizations.

[1.1.0]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.12...1.1.0
[1.0.12]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.11...1.0.12
[1.0.11]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.10...1.0.11
[1.0.10]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.9...1.0.10
[1.0.9]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.8...1.0.9
[1.0.8]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.7...1.0.8
[1.0.7]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.6...1.0.7
[1.0.6]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.5...1.0.6
[1.0.5]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.4...1.0.5
[1.0.4]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.3...1.0.4
[1.0.3]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.2...1.0.3
[1.0.2]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.1...1.0.2
[1.0.1]: https://github.com/integer-net/magento2-sansec-watch/compare/1.0.0...1.0.1
[1.0.0]: https://github.com/integer-net/magento2-sansec-watch/tag/1.0.0

Don’t let your friends dump git logs into changelogs.
Version 1.0.0
# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.6.0] - 2020-01-17
### Added
- Support for ?limit
- `findModel` supports all types rather than just int now.
- Default `getRouteKey` implementation
- Simple file logging implementation
- Added a `api_domain` to `restful.php` config to override the domain

### Changed
- OPTIONS with ID requested via query string now when using `Builder`
- Made CRUD methods manipulatable by concrete classes

## [1.5.0] - 2020-01-14
### Added
- Added `Builder` class to interface with the API

## [1.4.1] - 2020-01-13
### Changed
- Improved OPTIONS support
- Improved README.md

## [1.4.0] - 2020-01-12
### Added
- Support for OPTIONS, which if the model implements HasLinks will return the JSON _links

## [1.3.2] - 2020-01-11
### Fixed
- Issue-2: Can now paginate HasMany relationship.

## [1.3.1] - 2020-01-11
### Fixed
- Issue-4: Fixed pagination

## [1.3.0] - 2020-01-11
### Added
- Issue-5: Added a view link to relationship responses

## [1.2.0] - 2020-01-11
### Changed
- Changed 'href' in links from a single relative link, to contain relative and absolute keys.
_Not making this a major version bump as package isn't used yet_

## [1.1.0] - 2020-01-10
### Added
- Added _links support and accessing children through show

## [1.0.1] - 2020-01-09
### Fixed
- PHPStan formatting errors

## [1.0.0] - 2019-12-07
### Added
- Initial Release

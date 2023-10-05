# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [0.3.1] - 2023-10-05

### Fixed
- Fixed data type registration for Omeka S 4.0+
- Fixed `subjectValueTotalCount` for Omeka S 4.0+
- Fixed call to `displaySubjectValues` for Omeka S 4.0+
- Fixed taxonomy term selection sidebar

## [0.3.0] - 2023-09-08

### Added
- Added ability to browse all terms (not only terms of a single taxonomy)
- Added ability to search taxonomy and taxonomy terms by code
- Added ability to search taxonomy terms by taxonomy

### Fixed
- Fixed taxonomy terms "Edit all" and "Delete all" actions that were not taking
  into account the selected taxonomy

### For developers
- Added view helper `taxonomySelect`
- Added method `TaxonomyRepresentation::termsUrl`

## [0.2.0] - 2023-08-30

- Added the ability to organize taxonomy terms in a hierarchy
- Fixed unit tests

## [0.1.1] - 2023-06-29

- Fixed access rights for taxonomy and taxonomy term pages

## [0.1.0] - 2023-03-21

Initial release

[0.3.1]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.3.1
[0.3.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.3.0
[0.2.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.2.0
[0.1.1]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.1.1
[0.1.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.1.0

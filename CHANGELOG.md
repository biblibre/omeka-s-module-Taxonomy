# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## Unreleased

- Add user setting to change the default view (list or tree) in the taxonomy
  term selection sidebar

## [0.7.0] - 2023-12-08

- Add Solr transformation to add taxonomy term ancestors to the list of values
  (requires Solr module 0.13.0)

## [0.6.0] - 2023-12-05

- In admin "show-details" sidebar, make the linked resources count clickable
- Show children count on term show page and in show-details sidebar
- Modify admin and site advanced search forms to be able to search resources
  that links to a specific taxonomy term or its descendants.
- On taxonomy term pages, show resources linked to term descendants too
- Admin taxonomy and taxonomy term pages now show links to the corresponding
  sites pages

## [0.5.1] - 2023-11-23

- Fixed AJAX calls when Taxonomy is the only enabled module doing it (enabled
  Laminas ViewJsonStrategy)

## [0.5.0] - 2023-11-20

- Add a site block layout to display a taxonomy term tree
- Fix "Linked resources" sections for all versions of Omeka S >= 3.1

## [0.4.0] - 2023-11-16

- Avoid hitting memory limit when working with large number of taxonomy terms:
  - Taxonomy term select is replaced by a selector similar to the asset
    selector
  - Taxonomy term hierarchy now displays only root terms. Children are loaded
    dynamically when user clicks on a term
- Added a tree view in the taxonomy term selection sidebar
- Added a [Search](https://github.com/biblibre/omeka-s-module-Search) form
  element to be able to search for a taxonomy term

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

[0.7.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.7.0
[0.6.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.6.0
[0.5.1]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.5.1
[0.5.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.5.0
[0.4.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.4.0
[0.3.1]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.3.1
[0.3.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.3.0
[0.2.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.2.0
[0.1.1]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.1.1
[0.1.0]: https://github.com/biblibre/omeka-s-module-Taxonomy/releases/tag/v0.1.0

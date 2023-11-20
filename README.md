# Taxonomy (module for Omeka S)

[Taxonomy] allows to create controlled vocabularies within Omeka S.

Controlled vocabularies (taxonomies) and controlled vocabulary terms (taxonomy
terms) are Omeka S resources, which means:

* they can be linked to items, item sets and media like any other Omeka S
  resources (they can be linked in annotations too);
* they can be described like any other resources (resource template, class,
  properties, annotations);
* they can have a thumbnail.

Taxonomy terms can be arranged in a hierarchy (a taxonomy term can have a
parent term).

[Documentation](https://biblibre.github.io/omeka-s-module-Taxonomy)

## Requirements

* Omeka S >= 3.1

## Quick start

1. Add Taxonomy to Omeka S ([how to add modules to Omeka S](https://omeka.org/s/docs/user-manual/modules/#adding-modules-to-omeka-s))
2. In the admin navigation menu, there will be a `Taxonomies` link. Click there
   to go to the taxonomies list.
3. From there it's possible to create, edit or delete taxonomies. It's also
   possible to list terms of a taxonomy, and then create, edit or delete terms
   for this taxonomy.
4. Once the taxonomies are created, create/modify a resource template and
   include the new data types for the desired properties. Use this resource
   template when taxonomies are needed.

## Features

In addition to the core features, this module provides:

* A site block layout that allows to add a taxonomy term tree in any site page
* A tree view for selecting a term (when linking a taxonomy term to an Omeka S
  resource) as well as a list view with search capabilities and pagination
* A form element for the [Search module](https://github.com/biblibre/omeka-s-module-Search)
  that suggests taxonomy terms while typing.
  Requires Search module >= 0.14.0
* A value formatter for the [Solr module](https://github.com/biblibre/omeka-s-module-Solr)
  that adds term ancestors IDs to the indexed value. This allows to search for
  a taxonomy term and have as results all resources linked to this taxonomy
  term or a descendant of this taxonomy term.
  Requires Solr module >= 0.11.0

## Differences with the Thesaurus module

Taxonomy and [Thesaurus] are similar, but there are a few differences:

* Taxonomy uses distinct resource types while Thesaurus uses items with a
  specific class;
* Taxonomy does not require the skos ontology.

## Sponsors

* Université de Lille

## License

Taxonomy is distributed under the GNU General Public License version 3 (GPLv3).
The full text of this license is given in the `LICENSE` file.

[Taxonomy]: https://github.com/biblibre/omeka-s-module-Taxonomy
[Thesaurus]: https://github.com/Daniel-KM/Omeka-S-module-Thesaurus

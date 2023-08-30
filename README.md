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

## Installation

See general end user documentation for [Installing a
module](http://omeka.org/s/docs/user-manual/modules/#installing-modules)

## Usage

In the admin navigation menu, there will be a `Taxonomies` link. Click there to
go to the taxonomies list. From there it's possible to create, edit or delete
taxonomies. It's also possible to list a taxonomy terms, and then create, edit
or delete terms for this taxonomy.

Once the taxonomies are created, create/modify a resource template and include
the new data types for the desired properties. Use this resource template when
taxonomies are needed.

For more information, see the [full documentation](https://biblibre.github.io/omeka-s-module-Taxonomy).

## Differences with the Thesaurus module

Taxonomy and [Thesaurus] are similar, but there are a few differences:

* Taxonomy uses distinct resource types while Thesaurus uses items with a
  specific class;
* Taxonomy does not require the skos ontology.

## License

Taxonomy is distributed under the GNU General Public License version 3 (GPLv3).
The full text of this license is given in the `LICENSE` file.

[Taxonomy]: https://github.com/biblibre/omeka-s-module-Taxonomy
[Thesaurus]: https://github.com/Daniel-KM/Omeka-S-module-Thesaurus

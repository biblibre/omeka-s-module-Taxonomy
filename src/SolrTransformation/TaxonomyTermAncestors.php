<?php

namespace Taxonomy\SolrTransformation;

use Omeka\Api\Representation\ValueRepresentation;
use Solr\Transformation\AbstractTransformation;
use Taxonomy\Api\Representation\TaxonomyTermRepresentation;

class TaxonomyTermAncestors extends AbstractTransformation
{
    public function getLabel(): string
    {
        return 'Add taxonomy term ancestors'; // @translate
    }

    public function transform(array $values, array $transformationData): array
    {
        $transformedValues = [];

        foreach ($values as $value) {
            $transformedValues[] = $value;

            $resource = null;
            if ($value instanceof ValueRepresentation) {
                $resource = $value->valueResource();
            } elseif ($value instanceof TaxonomyTermRepresentation) {
                $resource = $value;
            }

            if ($resource instanceof TaxonomyTermRepresentation) {
                while ($resource = $resource->parent()) {
                    $transformedValues[] = $resource;
                }
            }
        }

        return $transformedValues;
    }
}

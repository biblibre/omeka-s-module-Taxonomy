<?php

namespace Taxonomy\SolrValueFormatter;

use Solr\ValueFormatter\ValueFormatterInterface;

class TaxonomyTermAncestors implements ValueFormatterInterface
{
    protected $apiManager;

    public function __construct(\Omeka\Api\Manager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    public function getLabel()
    {
        return 'Add taxonomy term ancestors IDs'; // @translate
    }

    public function format($value)
    {
        try {
            $taxonomyTerm = $this->apiManager->read('taxonomy_terms', $value)->getContent();
            $values = [$value];
            while ($taxonomyTerm = $taxonomyTerm->parent()) {
                $values[] = $taxonomyTerm->id();
            }
            return $values;
        } catch (\Omeka\Api\Exception\NotFoundException $e) {
        }

        return $value;
    }
}

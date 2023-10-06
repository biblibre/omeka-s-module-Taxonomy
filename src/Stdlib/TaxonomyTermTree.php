<?php

namespace Taxonomy\Stdlib;

use Omeka\Api\Manager as ApiManager;

class TaxonomyTermTree
{
    protected $apiManager;

    public function setApiManager(ApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    public function getApiManager()
    {
        return $this->apiManager;
    }

    // FIXME delete ?
    public function buildTaxonomyTermTree($taxonomy_id)
    {
        $terms = $this->getApiManager()->search('taxonomy_terms', ['taxonomy_id' => $taxonomy_id])->getContent();

        $termsTreeNodes = [];
        foreach ($terms as $term) {
            $termsTreeNodes[$term->id()] = [
                'term' => $term,
                'parent' => null,
                'children' => [],
            ];
        }

        foreach ($termsTreeNodes as $id => &$termsTreeNodeRef) {
            $parent = $termsTreeNodeRef['term']->parent();
            if ($parent) {
                $termsTreeParentNodeRef = &$termsTreeNodes[$parent->id()];
                $termsTreeParentNodeRef['children'][] = &$termsTreeNodeRef;
                $termsTreeNodeRef['parent'] = &$termsTreeParentNodeRef;
            }
        }

        $rootNodes = array_filter($termsTreeNodes, fn ($node) => is_null($node['parent']));
        $rootNodes = array_values($rootNodes);

        return $rootNodes;
    }
}

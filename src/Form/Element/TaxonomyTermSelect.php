<?php

namespace Taxonomy\Form\Element;

use Laminas\Form\Element\Select;
use Omeka\Api\Manager as ApiManager;
use Taxonomy\Stdlib\TaxonomyTermTree;

class TaxonomyTermSelect extends Select
{
    /**
     * @var ApiManager
     */
    protected $apiManager;

    protected TaxonomyTermTree $taxonomyTermTree;

    /**
     * @param ApiManager $apiManager
     */
    public function setApiManager(ApiManager $apiManager)
    {
        $this->apiManager = $apiManager;
    }

    /**
     * @return ApiManager
     */
    public function getApiManager()
    {
        return $this->apiManager;
    }

    public function setTaxonomyTermTree(TaxonomyTermTree $taxonomyTermTree)
    {
        $this->taxonomyTermTree = $taxonomyTermTree;
    }

    public function getTaxonomyTermTree(): TaxonomyTermTree
    {
        return $this->taxonomyTermTree;
    }

    public function getValueLabel($resource)
    {
        $lang = $this->options['lang'] ?? null;
        return $resource->displayTitle(null, $lang);
    }

    public function getValueOptions()
    {
        $valueOptions = [];

        $taxonomy_id = $this->getOption('taxonomy_id');

        if ($this->getOption('value_options_flattened')) {
            $api = $this->getApiManager();
            $terms = $api->search('taxonomy_terms', ['taxonomy_id' => $taxonomy_id])->getContent();

            // Sort alphabetically by resource label
            $resources = [];
            foreach ($terms as $term) {
                $resources[$this->getValueLabel($term)][] = $term->id();
            }
            ksort($resources);

            foreach ($resources as $label => $ids) {
                foreach ($ids as $id) {
                    $valueOptions[$id] = $label;
                }
            }
        } else {
            $rootNodes = $this->getTaxonomyTermTree()->buildTaxonomyTermTree($taxonomy_id);
            $valueOptions = $this->buildValueOptionsAsTree($rootNodes);
        }

        return $valueOptions;
    }

    protected function buildValueOptionsAsTree($nodes, $depth = 0, $forceDisable = false)
    {
        $valueOptions = [];

        foreach ($nodes as &$nodeRef) {
            $nodeRef['label'] = $this->getValueLabel($nodeRef['term']);
        }
        usort($nodes, fn ($a, $b) => strcmp($a['label'], $b['label']));

        foreach ($nodes as $node) {
            $term = $node['term'];
            $disabled = $forceDisable || $term->id() === $this->getOption('taxonomy_term_id');
            $valueOption = [
                'value' => $term->id(),
                'label' => str_repeat('‒', $depth) . ' ' . $node['label'],
                'disabled' => $disabled,
            ];

            $valueOptions[] = $valueOption;
            $valueOptions = array_merge($valueOptions, $this->buildValueOptionsAsTree($node['children'], $depth + 1, $disabled));
        }

        return $valueOptions;
    }
}

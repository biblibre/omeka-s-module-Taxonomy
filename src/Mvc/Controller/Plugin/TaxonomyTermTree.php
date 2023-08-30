<?php

namespace Taxonomy\Mvc\Controller\Plugin;

use Laminas\Mvc\Controller\Plugin\AbstractPlugin;
use Taxonomy\Stdlib\TaxonomyTermTree as TaxonomyTermTreeService;

class TaxonomyTermTree extends AbstractPlugin
{
    protected TaxonomyTermTreeService $taxonomyTermTree;

    public function __construct(TaxonomyTermTreeService $taxonomyTermTree)
    {
        $this->taxonomyTermTree = $taxonomyTermTree;
    }

    public function __invoke()
    {
        return $this->taxonomyTermTree;
    }
}

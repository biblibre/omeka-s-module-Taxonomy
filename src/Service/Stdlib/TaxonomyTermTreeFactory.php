<?php
namespace Taxonomy\Service\Stdlib;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Taxonomy\Stdlib\TaxonomyTermTree;

class TaxonomyTermTreeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $taxonomyTermTree = new TaxonomyTermTree();
        $taxonomyTermTree->setApiManager($services->get('Omeka\ApiManager'));

        return $taxonomyTermTree;
    }
}

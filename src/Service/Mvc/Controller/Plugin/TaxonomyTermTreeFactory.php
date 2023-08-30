<?php
namespace Taxonomy\Service\Mvc\Controller\Plugin;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TaxonomyTermTreeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $taxonomyTermTree = new \Taxonomy\Mvc\Controller\Plugin\TaxonomyTermTree(
            $services->get('Taxonomy\TaxonomyTermTree')
        );

        return $taxonomyTermTree;
    }
}

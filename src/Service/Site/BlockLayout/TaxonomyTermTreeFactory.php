<?php
namespace Taxonomy\Service\Site\BlockLayout;

use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;
use Taxonomy\Site\BlockLayout\TaxonomyTermTree;

class TaxonomyTermTreeFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new TaxonomyTermTree($services->get('FormElementManager'));
    }
}

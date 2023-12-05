<?php
namespace Taxonomy\Service\View\Helper;

use Taxonomy\View\Helper\TaxonomyTermSelect;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TaxonomyTermSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new TaxonomyTermSelect($services->get('FormElementManager'));
    }
}

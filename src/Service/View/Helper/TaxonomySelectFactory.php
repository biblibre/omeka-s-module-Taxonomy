<?php
namespace Taxonomy\Service\View\Helper;

use Taxonomy\View\Helper\TaxonomySelect;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Interop\Container\ContainerInterface;

class TaxonomySelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        return new TaxonomySelect($services->get('FormElementManager'));
    }
}

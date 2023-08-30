<?php
namespace Taxonomy\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Taxonomy\Form\Element\TaxonomyTermSelect;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TaxonomyTermSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new TaxonomyTermSelect;
        $element->setApiManager($services->get('Omeka\ApiManager'));
        $element->setTaxonomyTermTree($services->get('Taxonomy\TaxonomyTermTree'));
        return $element;
    }
}

<?php
namespace Taxonomy\Service\Form\Element;

use Interop\Container\ContainerInterface;
use Taxonomy\Form\Element\TaxonomySelect;
use Laminas\ServiceManager\Factory\FactoryInterface;

class TaxonomySelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $element = new TaxonomySelect;
        $element->setApiManager($services->get('Omeka\ApiManager'));
        return $element;
    }
}

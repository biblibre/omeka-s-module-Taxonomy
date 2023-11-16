<?php
namespace Taxonomy\Service\SearchFormElement;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Taxonomy\SearchFormElement\TaxonomyTermSelect;

class TaxonomyTermSelectFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $formElementManager = $services->get('FormElementManager');
        $api = $services->get('Omeka\ApiManager');

        $taxonomyTermSelect = new TaxonomyTermSelect($formElementManager, $api);

        return $taxonomyTermSelect;
    }
}

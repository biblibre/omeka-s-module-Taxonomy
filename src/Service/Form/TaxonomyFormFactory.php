<?php
namespace Taxonomy\Service\Form;

use Interop\Container\ContainerInterface;
use Laminas\ServiceManager\Factory\FactoryInterface;
use Taxonomy\Form\TaxonomyForm;

class TaxonomyFormFactory implements FactoryInterface
{
    public function __invoke(ContainerInterface $services, $requestedName, array $options = null)
    {
        $form = new TaxonomyForm;
        $form->setUrlHelper($services->get('ViewHelperManager')->get('Url'));
        $form->setEventManager($services->get('EventManager'));
        return $form;
    }
}

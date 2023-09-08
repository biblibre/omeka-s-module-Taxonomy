<?php
namespace Taxonomy\View\Helper;

use Taxonomy\Form\Element\TaxonomySelect as Select;
use Laminas\Form\Factory;
use Laminas\View\Helper\AbstractHelper;
use Laminas\ServiceManager\ServiceLocatorInterface;

class TaxonomySelect extends AbstractHelper
{
    protected $formElementManager;

    public function __construct(ServiceLocatorInterface $formElementManager)
    {
        $this->formElementManager = $formElementManager;
    }

    /**
     * Render a select menu containing all taxonomies.
     *
     * @param array $spec
     * @return string
     */
    public function __invoke(array $spec = [])
    {
        $spec['type'] = Select::class;
        if (!isset($spec['options']['empty_option'])) {
            $spec['options']['empty_option'] = 'Select taxonomy…'; // @translate
        }
        $factory = new Factory($this->formElementManager);
        $element = $factory->createElement($spec);
        return $this->getView()->formSelect($element);
    }
}

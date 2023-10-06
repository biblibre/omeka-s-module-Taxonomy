<?php
namespace Taxonomy\Form\Element;

use Laminas\Form\Element;
use Laminas\InputFilter\InputProviderInterface;

class TaxonomyTerm extends Element implements InputProviderInterface
{
    public function getInputSpecification()
    {
        return [
            'name' => $this->getName(),
            'required' => false,
            'validators' => [
                [
                    'name' => 'Regex',
                    'options' => ['pattern' => '/^[0-9]+$/'],
                ],
            ],
        ];
    }
}

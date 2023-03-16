<?php
namespace Taxonomy\Form;

use Omeka\Form\ResourceForm;
use Laminas\Form\Element\Text;

class TaxonomyForm extends ResourceForm
{
    public function init()
    {
        $this->add([
            'name' => 'o:code',
            'type' => Text::class,
            'options' => [
                'label' => 'Code', // @translate
                'info' => 'A string that uniquely identifies the taxonomy. Not modifiable after creation', // @translate
            ],
            'attributes' => [
                'id' => 'code',
                'required' => true,
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:code',
            'required' => true,
            'allow_empty' => false,
        ]);

        parent::init();
    }
}

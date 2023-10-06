<?php
namespace Taxonomy\Form;

use Omeka\Form\ResourceForm;
use Laminas\Form\Element\Text;
use Taxonomy\Form\Element\TaxonomyTerm;

class TaxonomyTermForm extends ResourceForm
{
    public function init()
    {
        $this->add([
            'name' => 'o:code',
            'type' => Text::class,
            'options' => [
                'label' => 'Code', // @translate
                'info' => 'A string that uniquely identifies the term among its taxonomy. Not modifiable after creation', // @translate
            ],
            'attributes' => [
                'id' => 'code',
                'required' => true,
            ],
        ]);

        $taxonomy = $this->getOption('taxonomy');
        $taxonomy_term = $this->getOption('taxonomy_term');

        $this->add([
            'name' => 'o:parent',
            'type' => TaxonomyTerm::class,
            'options' => [
                'label' => 'Parent term', // @translate
                'taxonomy_id' => $taxonomy->id(),
                'taxonomy_term_id' => $taxonomy_term ? $taxonomy_term->id() : null,
            ],
        ]);

        $inputFilter = $this->getInputFilter();
        $inputFilter->add([
            'name' => 'o:code',
            'required' => true,
            'allow_empty' => false,
        ]);
        $inputFilter->add([
            'name' => 'o:parent',
            'required' => false,
        ]);

        parent::init();
    }
}

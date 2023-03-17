<?php

namespace Taxonomy\Form;

use Laminas\Form\Fieldset;

class ResourceBatchUpdateTaxonomyFieldset extends Fieldset
{
    public function init()
    {
        $this->setAttribute('data-collection-action', 'replace');

        $this->add([
            'type' => \Omeka\Form\Element\PropertySelect::class,
            'name' => 'replace_property_ids',
            'options' => [
                'label' => 'Replace literal values by taxonomy terms (properties)', // @translate
                'info' => 'Search taxonomy terms by title, and if one match, replace the literal value by a corresponding taxonomy term value. If more than one term match, no modification is made. Only the properties selected here will be modified.', // @translate
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select properties', // @translate
                'id' => 'taxonomy_replace_property_ids',
                'multiple' => true,
            ],
        ]);

        $this->add([
            'type' => Element\TaxonomySelect::class,
            'name' => 'replace_taxonomy_id',
            'options' => [
                'label' => 'Replace literal values by taxonomy terms (taxonomy)', // @translate
                'info' => 'Search taxonomy terms by title, and if one match, replace the literal value by a corresponding taxonomy term value. The search is limited to the selected taxonomy.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-placeholder' => 'Select taxonomy', // @translate
                'id' => 'taxonomy_replace_taxonomy_id',
            ],
        ]);
    }
}

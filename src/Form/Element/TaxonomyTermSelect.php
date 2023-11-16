<?php

namespace Taxonomy\Form\Element;

use Laminas\Form\Element\Select;

class TaxonomyTermSelect extends Select
{
    protected $attributes = [
        'class' => 'taxonomy-term-select',
    ];
}

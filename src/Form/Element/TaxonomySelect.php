<?php

namespace Taxonomy\Form\Element;

use Omeka\Form\Element\AbstractGroupByOwnerSelect;

class TaxonomySelect extends AbstractGroupByOwnerSelect
{
    public function getResourceName()
    {
        return 'taxonomies';
    }

    public function getValueLabel($resource)
    {
        $lang = (isset($this->options['lang']) ? $this->options['lang'] : null);
        return $resource->displayTitle(null, $lang);
    }
}

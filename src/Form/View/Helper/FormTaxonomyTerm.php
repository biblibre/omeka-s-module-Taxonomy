<?php
namespace Taxonomy\Form\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;

class FormTaxonomyTerm extends AbstractHelper
{
    public function __invoke(ElementInterface $element, AssetRepresentation $asset = null)
    {
        return $this->render($element, $asset);
    }

    public function render(ElementInterface $element)
    {
        $view = $this->getView();

        return $view->partial('taxonomy/common/taxonomy-term-form', ['element' => $element]);
    }
}

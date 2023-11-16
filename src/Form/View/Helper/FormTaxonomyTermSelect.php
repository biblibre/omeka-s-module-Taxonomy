<?php
namespace Taxonomy\Form\View\Helper;

use Laminas\Form\View\Helper\AbstractHelper;
use Laminas\Form\ElementInterface;

class FormTaxonomyTermSelect extends AbstractHelper
{
    public function __invoke(ElementInterface $element)
    {
        return $this->render($element);
    }

    public function render(ElementInterface $element)
    {
        $view = $this->getView();

        return $view->partial('taxonomy/common/taxonomy-term-select-form', ['element' => $element]);
    }
}

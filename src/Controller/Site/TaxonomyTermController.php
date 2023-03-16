<?php
namespace Taxonomy\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class TaxonomyTermController extends AbstractActionController
{
    public function showAction()
    {
        $response = $this->api()->read('taxonomy_terms', $this->params('id'));

        $view = new ViewModel;
        $taxonomyTerm = $response->getContent();
        $view->setVariable('taxonomyTerm', $taxonomyTerm);
        $view->setVariable('resource', $taxonomyTerm);
        return $view;
    }
}

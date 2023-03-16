<?php
namespace Taxonomy\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;

class TaxonomyController extends AbstractActionController
{
    public function showAction()
    {
        $response = $this->api()->read('taxonomies', $this->params('id'));

        $view = new ViewModel;
        $taxonomy = $response->getContent();
        $view->setVariable('taxonomy', $taxonomy);
        $view->setVariable('resource', $taxonomy);
        return $view;
    }
}

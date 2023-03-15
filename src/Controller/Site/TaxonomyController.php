<?php
namespace Taxonomy\Controller\Site;

use Omeka\Form\ConfirmForm;
use Omeka\Form\ResourceBatchUpdateForm;
use Omeka\Stdlib\Message;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Taxonomy\Form\TaxonomyForm;

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

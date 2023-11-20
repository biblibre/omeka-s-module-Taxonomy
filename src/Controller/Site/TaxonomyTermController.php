<?php
namespace Taxonomy\Controller\Site;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;
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

    public function browseJstreeAction()
    {
        $query = $this->params()->fromQuery();

        if (!array_key_exists('parent_id', $query)) {
            $query['parent_id'] = null;
        }

        $partialName = 'taxonomy/common/taxonomy-term-jstree-node-text-public';

        $taxonomyTerms = $this->api()->search('taxonomy_terms', $query)->getContent();
        $partialViewHelper = $this->viewHelpers()->get('partial');

        $response = [];
        foreach ($taxonomyTerms as $taxonomyTerm) {
            $response[] = [
                'id' => $taxonomyTerm->id(),
                'text' => $partialViewHelper($partialName, ['taxonomyTerm' => $taxonomyTerm]),
                'children' => $taxonomyTerm->hasChildren(),
            ];
        }

        return new JsonModel($response);
    }
}

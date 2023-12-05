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

    public function linkedValuesAction()
    {
        $taxonomyTerm = $this->api()->read('taxonomy_terms', $this->params('id'))->getContent();

        $partialViewHelper = $this->viewHelpers()->get('partial');
        $query = $this->params()->fromQuery();

        $values = [];
        foreach ($taxonomyTerm->linkedValues($query) as $linkedValue) {
            $resource = $linkedValue->resource();
            $values[] = [
                $partialViewHelper('taxonomy/site/taxonomy-term/linked-values/resource', ['resource' => $resource]),
            ];
        }

        $response = [
            'values' => $values,
            'total' => $taxonomyTerm->linkedValuesCount(),
        ];

        return new JsonModel($response);
    }

    public function linkedValuesGridConfigAction()
    {
        $config = [
            'columns' => [
                $this->translate('Resource'),
            ],
            'server' => [
                'url' => $this->url()->fromRoute('site/taxonomy-term-id', ['action' => 'linked-values'], [], true),
            ],
            'autoWidth' => false,
            'pagination' => [
                'limit' => $this->settings()->get('pagination_per_page', 25),
                'summary' => false,
                'buttonsCount' => 0,
            ],
            'language' => [
                'pagination' => [
                    'previous' => $this->translate('Previous'),
                    'next' => $this->translate('Next'),
                ],
            ],
        ];

        return new JsonModel($config);
    }
}

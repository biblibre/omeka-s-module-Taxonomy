<?php
namespace Taxonomy\Controller;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class TaxonomyTermSearchController extends AbstractActionController
{
    public function searchAction()
    {
        $params = $this->params()->fromQuery();
        $params['page'] = 1;
        $params['per_page'] = 20;
        $params['property'] = [
            [
                'property' => 'dcterms:title',
                'text' => $params['q'],
                'type' => 'in',
            ],
        ];
        $terms = $this->api()->search('taxonomy_terms', $params)->getContent();

        $response = [];
        $partial = $this->viewHelpers()->get('Partial');
        foreach ($terms as $term) {
            $response[] = [
                'id' => $term->id(),
                'title' => $term->displayTitle(),
                'render' => [
                    'option' => $partial('taxonomy/common/taxonomy-term-search/option', ['taxonomyTerm' => $term]),
                ],
            ];
        }

        return new JsonModel($response);
    }
}

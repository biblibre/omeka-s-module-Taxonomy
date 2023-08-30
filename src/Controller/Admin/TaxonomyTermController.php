<?php
namespace Taxonomy\Controller\Admin;

use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Omeka\Form\ConfirmForm;
use Omeka\Form\ResourceBatchUpdateForm;
use Omeka\Stdlib\Message;
use Taxonomy\Form\TaxonomyTermForm;

class TaxonomyTermController extends AbstractActionController
{
    public function searchAction()
    {
        $view = new ViewModel;
        $view->setVariable('query', $this->params()->fromQuery());
        return $view;
    }

    public function addAction()
    {
        $taxonomy_id = $this->params()->fromRoute('taxonomy-id');
        $taxonomy = $this->api()->read('taxonomies', $taxonomy_id)->getContent();

        $form = $this->getForm(TaxonomyTermForm::class, ['taxonomy' => $taxonomy]);
        $form->setAttribute('id', 'add-taxonomy-term');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->create('taxonomy_terms', $data);
                if ($response) {
                    $message = new Message(
                        'Taxonomy term successfully created. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute(null, [], true)),
                            $this->translate('Add another taxonomy term?')
                        ));
                    $message->setEscapeHtml(false);
                    $this->messenger()->addSuccess($message);
                    return $this->redirect()->toUrl($response->getContent()->url());
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('taxonomy', $taxonomy);
        return $view;
    }

    public function editAction()
    {
        $response = $this->api()->read('taxonomy_terms', $this->params('id'));
        $taxonomyTerm = $response->getContent();

        $form = $this->getForm(
            TaxonomyTermForm::class,
            [
                'taxonomy' => $taxonomyTerm->taxonomy(),
                'taxonomy_term' => $taxonomyTerm,
            ]
        );
        $form->setAttribute('id', 'edit-taxonomy-term');

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('taxonomy', $taxonomyTerm->taxonomy());
        $view->setVariable('taxonomyTerm', $taxonomyTerm);
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->update('taxonomy_terms', $this->params('id'), $data);
                if ($response) {
                    $this->messenger()->addSuccess('Taxonomy term successfully updated'); // @translate
                    return $this->redirect()->toUrl($response->getContent()->url());
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $view;
    }

    public function browseAction()
    {
        $taxonomy_id = $this->params()->fromRoute('taxonomy-id');
        $taxonomy = $this->api()->read('taxonomies', $taxonomy_id)->getContent();

        $this->setBrowseDefaults('created');

        $query = $this->params()->fromQuery();
        $query['taxonomy_id'] = $taxonomy->id();
        $response = $this->api()->search('taxonomy_terms', $query);
        $this->paginator($response->getTotalResults());

        $formDeleteSelected = $this->getForm(ConfirmForm::class);
        $formDeleteSelected->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete'], true));
        $formDeleteSelected->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteSelected->setAttribute('id', 'confirm-delete-selected');

        $formDeleteAll = $this->getForm(ConfirmForm::class);
        $formDeleteAll->setAttribute('action', $this->url()->fromRoute(null, ['action' => 'batch-delete-all'], true));
        $formDeleteAll->setButtonLabel('Confirm Delete'); // @translate
        $formDeleteAll->setAttribute('id', 'confirm-delete-all');
        $formDeleteAll->get('submit')->setAttribute('disabled', true);

        $view = new ViewModel;
        $taxonomyTerms = $response->getContent();
        $view->setVariable('taxonomy', $taxonomy);
        $view->setVariable('taxonomyTerms', $taxonomyTerms);
        $view->setVariable('resources', $taxonomyTerms);
        $view->setVariable('formDeleteSelected', $formDeleteSelected);
        $view->setVariable('formDeleteAll', $formDeleteAll);
        return $view;
    }

    public function browseHierarchyAction()
    {
        $taxonomy_id = $this->params()->fromRoute('taxonomy-id');
        $taxonomy = $this->api()->read('taxonomies', $taxonomy_id)->getContent();

        $rootNodes = $this->taxonomyTermTree()->buildTaxonomyTermTree($taxonomy_id);

        $view = new ViewModel;
        $view->setVariable('taxonomy', $taxonomy);
        $view->setVariable('rootNodes', $rootNodes);

        return $view;
    }

    public function showAction()
    {
        $response = $this->api()->read('taxonomy_terms', $this->params('id'));

        $view = new ViewModel;
        $taxonomyTerm = $response->getContent();
        $view->setVariable('taxonomyTerm', $taxonomyTerm);
        $view->setVariable('resource', $taxonomyTerm);
        return $view;
    }

    public function showDetailsAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('taxonomy_terms', $this->params('id'));
        $taxonomyTerm = $response->getContent();
        $values = $taxonomyTerm->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('resource', $taxonomyTerm);
        $view->setVariable('values', json_encode($values));
        return $view;
    }

    public function sidebarSelectAction()
    {
        $this->setBrowseDefaults('created');

        $taxonomy_id = $this->params()->fromRoute('taxonomy-id');
        $taxonomy = $this->api()->read('taxonomies', $taxonomy_id)->getContent();
        $query = $this->params()->fromQuery();
        $query['taxonomy_id'] = $taxonomy->id();

        $response = $this->api()->search('taxonomy_terms', $query);
        $this->paginator($response->getTotalResults());

        $view = new ViewModel;
        $view->setVariable('taxonomyTerms', $response->getContent());
        $view->setVariable('searchValue', $this->params()->fromQuery('search'));
        $view->setTerminal(true);
        return $view;
    }

    public function deleteConfirmAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('taxonomy_terms', $this->params('id'));
        $taxonomyTerm = $response->getContent();
        $values = $taxonomyTerm->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $taxonomyTerm);
        $view->setVariable('resourceLabel', 'taxonomy term'); // @translate
        $view->setVariable('partialPath', 'taxonomy/admin/taxonomy-term/show-details');
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('values', json_encode($values));
        return $view;
    }

    public function deleteAction()
    {
        $taxonomyTerm = $this->api()->read('taxonomy_terms', $this->params('id'))->getContent();

        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('taxonomy_terms', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Taxonomy term successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute(
            'admin/taxonomy-term',
            [
                'action' => 'browse',
                'taxonomy-id' => $taxonomyTerm->taxonomy()->id(),
            ]
        );
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one taxonomy term to batch delete.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('taxonomy_terms', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Taxonomy terms successfully deleted'); // @translate
            }
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    public function batchDeleteAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchDelete', [
                'resource' => 'taxonomy_terms',
                'query' => $query,
            ]);
            $this->messenger()->addSuccess('Deleting taxonomy terms. This may take a while.'); // @translate
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    /**
     * Batch update selected item sets.
     */
    public function batchEditAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one taxonomy term to batch edit.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // TODO Create TaxonomyTermBatchUpdateForm ?
        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'taxonomyTerm']);
        $form->setAttribute('id', 'batch-edit-taxonomy-term');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                foreach ($data as $collectionAction => $properties) {
                    $this->api($form)->batchUpdate('taxonomy_terms', $resourceIds, $properties, [
                        'continueOnError' => true,
                        'collectionAction' => $collectionAction,
                        'detachEntities' => false,
                    ]);
                }

                $this->messenger()->addSuccess('Taxonomy terms successfully edited'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $resources = [];
        foreach ($resourceIds as $resourceId) {
            $resources[] = $this->api()->read('taxonomy_terms', $resourceId)->getContent();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resources', $resources);
        $view->setVariable('query', []);
        $view->setVariable('count', null);
        return $view;
    }

    /**
     * Batch update all item sets returned from a query.
     */
    public function batchEditAllAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // Derive the query, removing limiting and sorting params.
        $query = json_decode($this->params()->fromPost('query', []), true);
        unset($query['submit'], $query['page'], $query['per_page'], $query['limit'],
            $query['offset'], $query['sort_by'], $query['sort_order']);
        $count = $this->api()->search('taxonomy_terms', ['limit' => 0] + $query)->getTotalResults();

        // TODO Create TaxonomyBatchUpdateForm ?
        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'taxonomyTerm']);
        $form->setAttribute('id', 'batch-edit-taxonomy-term');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchUpdate', [
                    'resource' => 'taxonomy_terms',
                    'query' => $query,
                    'data' => $data['replace'] ?? [],
                    'data_remove' => $data['remove'] ?? [],
                    'data_append' => $data['append'] ?? [],
                ]);

                $this->messenger()->addSuccess('Editing taxonomy terms. This may take a while.'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setTemplate('taxonomy/admin/taxonomy-term/batch-edit.phtml');
        $view->setVariable('form', $form);
        $view->setVariable('resources', []);
        $view->setVariable('query', $query);
        $view->setVariable('count', $count);
        return $view;
    }
}

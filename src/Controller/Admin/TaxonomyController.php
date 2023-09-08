<?php
namespace Taxonomy\Controller\Admin;

use Omeka\Form\ConfirmForm;
use Omeka\Form\ResourceBatchUpdateForm;
use Omeka\Stdlib\Message;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\ViewModel;
use Taxonomy\Form\TaxonomyForm;
use Taxonomy\Form\TaxonomyTermForm;

class TaxonomyController extends AbstractActionController
{
    public function searchAction()
    {
        $view = new ViewModel;
        $view->setVariable('query', $this->params()->fromQuery());
        return $view;
    }

    public function addAction()
    {
        $form = $this->getForm(TaxonomyForm::class);
        $form->setAttribute('id', 'add-taxonomy');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->create('taxonomies', $data);
                if ($response) {
                    $message = new Message(
                        'Taxonomy successfully created. %s', // @translate
                        sprintf(
                            '<a href="%s">%s</a>',
                            htmlspecialchars($this->url()->fromRoute(null, [], true)),
                            $this->translate('Add another taxonomy?')
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
        return $view;
    }

    public function addTermAction()
    {
        $taxonomy = $this->api()->read('taxonomies', $this->params('id'))->getContent();

        $form = $this->getForm(TaxonomyTermForm::class, ['taxonomy' => $taxonomy]);
        $form->setAttribute('id', 'add-taxonomy-term');
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $data['o:taxonomy'] = ['o:id' => $taxonomy->id()];
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
        $form = $this->getForm(TaxonomyForm::class);
        $form->setAttribute('id', 'edit-taxonomy');
        $response = $this->api()->read('taxonomies', $this->params('id'));
        $taxonomy = $response->getContent();

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('taxonomy', $taxonomy);
        if ($this->getRequest()->isPost()) {
            $data = $this->params()->fromPost();
            $data = $this->mergeValuesJson($data);
            $form->setData($data);
            if ($form->isValid()) {
                $response = $this->api($form)->update('taxonomies', $this->params('id'), $data);
                if ($response) {
                    $this->messenger()->addSuccess('Taxonomy successfully updated'); // @translate
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
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('taxonomies', $this->params()->fromQuery());
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
        $taxonomies = $response->getContent();
        $view->setVariable('taxonomies', $taxonomies);
        $view->setVariable('resources', $taxonomies);
        $view->setVariable('formDeleteSelected', $formDeleteSelected);
        $view->setVariable('formDeleteAll', $formDeleteAll);
        return $view;
    }

    public function showAction()
    {
        $response = $this->api()->read('taxonomies', $this->params('id'));

        $view = new ViewModel;
        $taxonomy = $response->getContent();
        $view->setVariable('taxonomy', $taxonomy);
        $view->setVariable('resource', $taxonomy);
        return $view;
    }

    public function showDetailsAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('taxonomies', $this->params('id'));
        $taxonomy = $response->getContent();
        $values = $taxonomy->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('resource', $taxonomy);
        $view->setVariable('values', json_encode($values));
        return $view;
    }

    public function sidebarSelectAction()
    {
        $this->setBrowseDefaults('created');
        $response = $this->api()->search('taxonomies', $this->params()->fromQuery());
        $this->paginator($response->getTotalResults());

        $view = new ViewModel;
        $view->setVariable('taxonomies', $response->getContent());
        $view->setVariable('searchValue', $this->params()->fromQuery('search'));
        $view->setTerminal(true);
        return $view;
    }

    public function deleteConfirmAction()
    {
        $linkTitle = (bool) $this->params()->fromQuery('link-title', true);
        $response = $this->api()->read('taxonomies', $this->params('id'));
        $taxonomy = $response->getContent();
        $values = $taxonomy->valueRepresentation();

        $view = new ViewModel;
        $view->setTerminal(true);
        $view->setTemplate('common/delete-confirm-details');
        $view->setVariable('resource', $taxonomy);
        $view->setVariable('resourceLabel', 'taxonomy'); // @translate
        $view->setVariable('partialPath', 'taxonomy/admin/taxonomy/show-details');
        $view->setVariable('linkTitle', $linkTitle);
        $view->setVariable('values', json_encode($values));
        return $view;
    }

    public function deleteAction()
    {
        if ($this->getRequest()->isPost()) {
            $form = $this->getForm(ConfirmForm::class);
            $form->setData($this->getRequest()->getPost());
            if ($form->isValid()) {
                $response = $this->api($form)->delete('taxonomies', $this->params('id'));
                if ($response) {
                    $this->messenger()->addSuccess('Taxonomy successfully deleted'); // @translate
                }
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }
        return $this->redirect()->toRoute(
            'admin/taxonomy',
            ['action' => 'browse'],
            true
        );
    }

    public function batchDeleteAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one taxonomy to batch delete.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $form = $this->getForm(ConfirmForm::class);
        $form->setData($this->getRequest()->getPost());
        if ($form->isValid()) {
            $response = $this->api($form)->batchDelete('taxonomies', $resourceIds, [], ['continueOnError' => true]);
            if ($response) {
                $this->messenger()->addSuccess('Taxonomies successfully deleted'); // @translate
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
                'resource' => 'taxonomies',
                'query' => $query,
            ]);
            $this->messenger()->addSuccess('Deleting taxonomies. This may take a while.'); // @translate
        } else {
            $this->messenger()->addFormErrors($form);
        }
        return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
    }

    /**
     * Batch update selected taxonomies
     */
    public function batchEditAction()
    {
        if (!$this->getRequest()->isPost()) {
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        $resourceIds = $this->params()->fromPost('resource_ids', []);
        if (!$resourceIds) {
            $this->messenger()->addError('You must select at least one taxonomy to batch edit.'); // @translate
            return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
        }

        // TODO Create TaxonomyBatchUpdateForm ?
        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'taxonomy']);
        $form->setAttribute('id', 'batch-edit-taxonomy');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                foreach ($data as $collectionAction => $properties) {
                    $this->api($form)->batchUpdate('taxonomies', $resourceIds, $properties, [
                        'continueOnError' => true,
                        'collectionAction' => $collectionAction,
                        'detachEntities' => false,
                    ]);
                }

                $this->messenger()->addSuccess('Taxonomies successfully edited'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $resources = [];
        foreach ($resourceIds as $resourceId) {
            $resources[] = $this->api()->read('taxonomies', $resourceId)->getContent();
        }

        $view = new ViewModel;
        $view->setVariable('form', $form);
        $view->setVariable('resources', $resources);
        $view->setVariable('query', []);
        $view->setVariable('count', null);
        return $view;
    }

    /**
     * Batch update all taxonomies returned from a query.
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
        $count = $this->api()->search('taxonomies', ['limit' => 0] + $query)->getTotalResults();

        // TODO Create TaxonomyBatchUpdateForm ?
        $form = $this->getForm(ResourceBatchUpdateForm::class, ['resource_type' => 'taxonomy']);
        $form->setAttribute('id', 'batch-edit-taxonomy');
        if ($this->params()->fromPost('batch_update')) {
            $data = $this->params()->fromPost();
            $form->setData($data);

            if ($form->isValid()) {
                $data = $form->preprocessData();

                $job = $this->jobDispatcher()->dispatch('Omeka\Job\BatchUpdate', [
                    'resource' => 'taxonomies',
                    'query' => $query,
                    'data' => $data['replace'] ?? [],
                    'data_remove' => $data['remove'] ?? [],
                    'data_append' => $data['append'] ?? [],
                ]);

                $this->messenger()->addSuccess('Editing taxonomies. This may take a while.'); // @translate
                return $this->redirect()->toRoute(null, ['action' => 'browse'], true);
            } else {
                $this->messenger()->addFormErrors($form);
            }
        }

        $view = new ViewModel;
        $view->setTemplate('taxonomy/admin/taxonomy/batch-edit.phtml');
        $view->setVariable('form', $form);
        $view->setVariable('resources', []);
        $view->setVariable('query', $query);
        $view->setVariable('count', $count);
        return $view;
    }
}

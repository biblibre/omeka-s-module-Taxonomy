<?php

namespace Taxonomy;

use Composer\Semver\Comparator;
use Omeka\Module\AbstractModule;
use Laminas\EventManager\Event;
use Laminas\EventManager\SharedEventManagerInterface;
use Laminas\Mvc\MvcEvent;
use Laminas\ServiceManager\ServiceLocatorInterface;

class Module extends AbstractModule
{
    public function onBootstrap(MvcEvent $event)
    {
        parent::onBootstrap($event);

        $services = $this->getServiceLocator();
        $acl = $services->get('Omeka\Acl');
        $acl->allow(null, 'Taxonomy\Api\Adapter\TaxonomyAdapter');
        $acl->allow(null, 'Taxonomy\Api\Adapter\TaxonomyTermAdapter');
        $acl->allow(null, 'Taxonomy\Entity\Taxonomy', 'read');
        $acl->allow(null, 'Taxonomy\Entity\TaxonomyTerm', 'read');
        $acl->allow(null, 'Taxonomy\Controller\Site\Taxonomy');
        $acl->allow(null, 'Taxonomy\Controller\Site\TaxonomyTerm');

        $api = $services->get('Omeka\ApiManager');
        $dataTypeManager = $services->get('Omeka\DataTypeManager');

        $taxonomies = $api->search('taxonomies')->getContent();
        foreach ($taxonomies as $taxonomy) {
            $dataTypeName = sprintf('resource:taxonomy-term:%s', $taxonomy->code());
            $dataTypeManager->configure([
                'factories' => [
                    $dataTypeName => Service\DataType\ResourceTaxonomyTermFactory::class,
                ],
            ]);
        }
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');

        $conn->exec(<<<SQL
            CREATE TABLE taxonomy_term (
                id INT NOT NULL,
                taxonomy_id INT NOT NULL,
                parent_id INT DEFAULT NULL,
                code VARCHAR(255) NOT NULL,
                INDEX IDX_C7ED653A9557E6F6 (taxonomy_id),
                INDEX IDX_C7ED653A727ACA70 (parent_id),
                UNIQUE INDEX UNIQ_TAXONOMY_ID_CODE (taxonomy_id, code),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $conn->exec(<<<SQL
            CREATE TABLE taxonomy (
                id INT NOT NULL,
                code VARCHAR(255) NOT NULL,
                UNIQUE INDEX UNIQ_FD12B83D77153098 (code),
                PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);

        $conn->exec(<<<SQL
            ALTER TABLE taxonomy_term
            ADD CONSTRAINT FK_C7ED653A9557E6F6
            FOREIGN KEY (taxonomy_id) REFERENCES taxonomy (id)
            ON DELETE CASCADE
        SQL);

        $conn->exec(<<<SQL
            ALTER TABLE taxonomy_term
            ADD CONSTRAINT FK_C7ED653A727ACA70
            FOREIGN KEY (parent_id) REFERENCES taxonomy_term (id)
            ON DELETE SET NULL
        SQL);

        $conn->exec(<<<SQL
            ALTER TABLE taxonomy_term
            ADD CONSTRAINT FK_C7ED653ABF396750
            FOREIGN KEY (id) REFERENCES resource (id)
            ON DELETE CASCADE
        SQL);
        $conn->exec(<<<SQL
            ALTER TABLE taxonomy
            ADD CONSTRAINT FK_FD12B83DBF396750
            FOREIGN KEY (id) REFERENCES resource (id)
            ON DELETE CASCADE
        SQL);
    }

    public function upgrade($oldVersion, $newVersion, ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');

        if (Comparator::lessThan($oldVersion, '0.2.0')) {
            $conn->exec(<<<SQL
                ALTER TABLE taxonomy_term
                ADD COLUMN parent_id INT DEFAULT NULL AFTER taxonomy_id
            SQL);
            $conn->exec(<<<SQL
                ALTER TABLE taxonomy_term
                ADD INDEX IDX_C7ED653A727ACA70 (parent_id)
            SQL);
            $conn->exec(<<<SQL
                ALTER TABLE taxonomy_term
                ADD CONSTRAINT FK_C7ED653A727ACA70
                FOREIGN KEY (parent_id) REFERENCES taxonomy_term (id)
                ON DELETE SET NULL
            SQL);
        }
    }

    public function uninstall(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');

        $conn->exec(<<<SQL
            UPDATE value JOIN resource ON (value.value_resource_id = resource.id)
            SET value.value = resource.title,
                value.type = 'literal',
                value.value_resource_id = NULL
            WHERE value.type = 'resource:taxonomy'
               OR value.type LIKE 'resource:taxonomy-term:%'
        SQL);
        $conn->exec('DROP TABLE taxonomy_term');
        $conn->exec('DROP TABLE taxonomy');
    }

    public function attachListeners(SharedEventManagerInterface $sharedEventManager)
    {
        $sharedEventManager->attach('*', 'view.layout', [$this, 'onViewLayout']);
        $sharedEventManager->attach('Omeka\Controller\Admin\Module', 'view.details', [$this, 'onAdminModuleViewDetails']);
        $sharedEventManager->attach('*', 'data_types.value_annotating', [$this, 'onDataTypesValueAnnotating']);
        $sharedEventManager->attach('*', 'csv_import.config', [$this, 'onCsvImportConfig']);
        $sharedEventManager->attach('Omeka\Form\ResourceBatchUpdateForm', 'form.add_elements', [$this, 'onResourceBatchUpdateFormAddElements']);
        $sharedEventManager->attach('Omeka\Form\ResourceBatchUpdateForm', 'form.add_input_filters', [$this, 'onResourceBatchUpdateFormAddInputFilters']);
        $sharedEventManager->attach('*', 'api.hydrate.post', [$this, 'onApiHydratePost']);
        $sharedEventManager->attach('*', 'api.preprocess_batch_update', [$this, 'onApiPreprocessBatchUpdate']);

        $resourceControllers = [
            'Omeka\Controller\Admin\Item',
            'Omeka\Controller\Admin\ItemSet',
            'Omeka\Controller\Admin\Media',
            'Omeka\Controller\Site\Item',
            'Omeka\Controller\Site\ItemSet',
            'Omeka\Controller\Site\Media',
        ];
        foreach ($resourceControllers as $controller) {
            $sharedEventManager->attach($controller, 'view.advanced_search', [$this, 'onViewAdvancedSearch']);
            $sharedEventManager->attach($controller, 'view.search.filters', [$this, 'onViewSearchFilters']);
        }

        $resourceAdapters = [
            'Omeka\Api\Adapter\ItemAdapter',
            'Omeka\Api\Adapter\ItemSetAdapter',
            'Omeka\Api\Adapter\MediaAdapter',
        ];
        foreach ($resourceAdapters as $adapter) {
            $sharedEventManager->attach($adapter, 'api.search.query', [$this, 'onApiSearchQuery']);
        }

        $sharedEventManager->attach('Omeka\Form\UserForm', 'form.add_elements', [$this, 'onUserFormAddElements']);
        $sharedEventManager->attach('Omeka\Form\UserForm', 'form.add_input_filters', [$this, 'onUserFormAddInputFilters']);
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function onViewLayout(Event $event)
    {
        $view = $event->getTarget();
        if ($view->params()->fromRoute('__ADMIN__')) {
            $view->headLink()->appendStylesheet($view->assetUrl('css/jstree.css', 'Omeka'));
            $view->headLink()->appendStylesheet($view->assetUrl('css/taxonomy.css', 'Taxonomy'));

            $view->headScript()->appendFile($view->assetUrl('vendor/jstree/jstree.min.js', 'Omeka'));
            $view->headScript()->appendFile($view->assetUrl('js/taxonomy-term-tree.js', 'Taxonomy'));
            $view->headScript()->appendFile($view->assetUrl('js/resource-form.js', 'Taxonomy'));
        }
    }

    public function onDataTypesValueAnnotating(Event $event)
    {
        $services = $this->getServiceLocator();
        $translator = $services->get('MvcTranslator');
        $api = $services->get('Omeka\ApiManager');

        $dataTypes = $event->getParam('data_types');
        $taxonomies = $api->search('taxonomies')->getContent();
        foreach ($taxonomies as $taxonomy) {
            $dataTypes[] = sprintf('resource:taxonomy-term:%s', $taxonomy->code());
        }
        $event->setParam('data_types', $dataTypes);
    }

    public function onCsvImportConfig(Event $event)
    {
        $config = $event->getParam('config');

        $config['data_types']['resource:taxonomy'] = [
            'label' => 'Taxonomy (by ID)', // @translate
            'adapter' => 'resource',
        ];

        $services = $this->getServiceLocator();
        $translator = $services->get('MvcTranslator');
        $api = $services->get('Omeka\ApiManager');

        $taxonomies = $api->search('taxonomies')->getContent();
        foreach ($taxonomies as $taxonomy) {
            $name = sprintf('resource:taxonomy-term:%s', $taxonomy->code());
            $config['data_types'][$name] = [
                'label' => sprintf($translator->translate('Taxonomy term: %s (by ID)'), $taxonomy->displayTitle()),
                'adapter' => 'resource',
            ];
        }
        $event->setParam('config', $config);
    }

    public function onAdminModuleViewDetails(Event $event)
    {
        $module = $event->getParam('entity');
        if ('Taxonomy' !== $module->getId()) {
            return;
        }

        $view = $event->getTarget();

        echo '<strong>' . $view->translate('Warning:') . "</strong>\n";
        echo $view->translate('Uninstalling this module will permanently delete all taxonomies, taxonomy terms, and will reset to the "literal" data type all values linked to taxonomies or taxonomy terms');
    }

    public function onResourceBatchUpdateFormAddElements(Event $event)
    {
        $form = $event->getTarget();

        $form->add([
            'type' => \Omeka\Form\Element\PropertySelect::class,
            'name' => 'taxonomy_replace_property_ids',
            'options' => [
                'label' => 'Replace literal values by taxonomy terms (properties)', // @translate
                'info' => 'Search taxonomy terms by title, and if one match, replace the literal value by a corresponding taxonomy term value. If more than one term match, no modification is made. Only the properties selected here will be modified.', // @translate
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-collection-action' => 'replace',
                'data-placeholder' => 'Select properties', // @translate
                'id' => 'taxonomy_replace_property_ids',
                'multiple' => true,
            ],
        ]);

        $form->add([
            'type' => Form\Element\TaxonomySelect::class,
            'name' => 'taxonomy_replace_taxonomy_id',
            'options' => [
                'label' => 'Replace literal values by taxonomy terms (taxonomy)', // @translate
                'info' => 'Search taxonomy terms by title, and if one match, replace the literal value by a corresponding taxonomy term value. The search is limited to the selected taxonomy.', // @translate
                'empty_option' => '',
            ],
            'attributes' => [
                'class' => 'chosen-select',
                'data-collection-action' => 'replace',
                'data-placeholder' => 'Select taxonomy', // @translate
                'id' => 'taxonomy_replace_taxonomy_id',
            ],
        ]);
    }

    public function onResourceBatchUpdateFormAddInputFilters(Event $event)
    {
        $inputFilter = $event->getParam('inputFilter');

        $inputFilter->add([
            'name' => 'taxonomy_replace_property_ids',
            'required' => false,
        ]);
        $inputFilter->add([
            'name' => 'taxonomy_replace_taxonomy_id',
            'required' => false,
        ]);
    }

    public function onApiHydratePost(Event $event)
    {
        $entity = $event->getParam('entity');
        if (!$entity instanceof \Omeka\Entity\Resource) {
            return;
        }

        $request = $event->getParam('request');
        $data = $request->getContent();

        $propertyIds = $data['taxonomy_replace_property_ids'] ?? [];
        $taxonomyId = $data['taxonomy_replace_taxonomy_id'] ?? null;
        if ($propertyIds && $taxonomyId) {
            $em = $this->getServiceLocator()->get('Omeka\EntityManager');
            $logger = $this->getServiceLocator()->get('Omeka\Logger');

            $taxonomy = $em->find(Entity\Taxonomy::class, $taxonomyId);

            foreach ($entity->getValues() as $value) {
                if ('literal' !== $value->getType()) {
                    continue;
                }
                if (!in_array($value->getProperty()->getId(), $propertyIds)) {
                    continue;
                }

                $terms = $em->getRepository(Entity\TaxonomyTerm::class)->findBy([
                    'title' => $value->getValue(),
                    'taxonomy' => $taxonomy,
                ]);

                if (count($terms) == 1) {
                    $term = reset($terms);
                    $value->setType('resource:taxonomy-term:' . $taxonomy->getCode());
                    $value->setValue(null);
                    $value->setValueResource($term);
                } elseif (count($terms) > 1) {
                    $logger->warn(sprintf(
                        'Taxonomy: Found more than one term titled "%s" in taxonomy %s',
                        $value->getValue(),
                        $taxonomy->getCode()
                    ));
                }
            }
        }
    }

    public function onApiPreprocessBatchUpdate(Event $event)
    {
        $request = $event->getParam('request');
        $rawData = $request->getContent();
        $data = $event->getParam('data');

        if (isset($rawData['taxonomy_replace_property_ids'])) {
            $data['taxonomy_replace_property_ids'] = $rawData['taxonomy_replace_property_ids'];
        }
        if (isset($rawData['taxonomy_replace_taxonomy_id'])) {
            $data['taxonomy_replace_taxonomy_id'] = $rawData['taxonomy_replace_taxonomy_id'];
        }

        $event->setParam('data', $data);
    }

    public function onViewAdvancedSearch(Event $event)
    {
        $query = $event->getParam('query');
        $resourceType = $event->getParam('resourceType');
        $partials = $event->getParam('partials');

        $taxonomyPartials = [
            'taxonomy/common/advanced-search/taxonomy-linked-to-term',
        ];

        // Insert partials before the "Sort" partial, if it exists
        $sortPartialIndex = array_search('common/advanced-search/sort', $partials);
        if ($sortPartialIndex) {
            array_splice($partials, $sortPartialIndex, 0, $taxonomyPartials);
        } else {
            $partials = array_merge($partials, $taxonomyPartials);
        }

        $event->setParam('partials', $partials);
    }

    public function onViewSearchFilters(Event $event)
    {
        $translator = $this->getServiceLocator()->get('MvcTranslator');
        $api = $this->getServiceLocator()->get('Omeka\ApiManager');

        $query = $event->getParam('query');
        $filters = $event->getParam('filters');

        if (!empty($query['taxonomy_linked_to_term'])) {
            $filterLabel = $translator->translate('Linked to taxonomy term');
            try {
                $taxonomyTerm = $api->read('taxonomy_terms', $query['taxonomy_linked_to_term'])->getContent();
                $filters[$filterLabel][] = $taxonomyTerm->title();
            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                $filters[$filterLabel][] = $query['taxonomy_linked_to_term'];
            }
        }

        if (!empty($query['taxonomy_linked_to_term_or_descendants'])) {
            $taxonomyTermId = $query['taxonomy_linked_to_term_or_descendants'];
            $filterLabel = $translator->translate('Linked to taxonomy term or descendants');
            try {
                $taxonomyTerm = $api->read('taxonomy_terms', $taxonomyTermId)->getContent();
                $filters[$filterLabel][] = $taxonomyTerm->title();
            } catch (\Omeka\Api\Exception\NotFoundException $e) {
                $filters[$filterLabel][] = $taxonomyTermId;
            }
        }

        $event->setParam('filters', $filters);
    }

    public function onApiSearchQuery(Event $event)
    {
        $qb = $event->getParam('queryBuilder');
        $request = $event->getParam('request');

        $query = $request->getContent();
        if (!empty($query['taxonomy_linked_to_term'])) {
            $alias = 'taxonomy_linked_to_term_values';
            $qb->innerJoin('omeka_root.values', $alias);
            $qb->andWhere($qb->expr()->eq("$alias.valueResource", $query['taxonomy_linked_to_term']));
        }

        if (!empty($query['taxonomy_linked_to_term_or_descendants'])) {
            $taxonomyTermId = (int) $query['taxonomy_linked_to_term_or_descendants'];
            $apiAdapterManager = $this->getServiceLocator()->get('Omeka\ApiAdapterManager');
            $taxonomyTermAdapter = $apiAdapterManager->get('taxonomy_terms');

            $descendantsIds = $taxonomyTermAdapter->getDescendantsIds($taxonomyTermId);

            $alias = 'taxonomy_linked_to_term_or_descendants_values';
            $qb->innerJoin('omeka_root.values', $alias);
            $qb->andWhere($qb->expr()->in(
                "$alias.valueResource",
                [$taxonomyTermId, ...$descendantsIds]
            ));
        }
    }

    public function onUserFormAddElements(Event $event)
    {
        $userSettings = $this->getServiceLocator()->get('Omeka\Settings\User');

        $form = $event->getTarget();

        $settingsFieldset = $form->get('user-settings');
        $element_groups = $settingsFieldset->getOption('element_groups');
        if ($element_groups) {
            $element_groups['taxonomy'] = 'Taxonomy'; // @translate
            $settingsFieldset->setOption('element_groups', $element_groups);
        }

        $userId = $form->getOption('user_id');

        $settingsFieldset->add([
            'type' => \Laminas\Form\Element\Select::class,
            'name' => 'taxonomy_sidebar_default_view',
            'options' => [
                'element_group' => 'taxonomy',
                'label' => 'Taxonomy sidebar default view', // @translate
                'info' => 'View to display by default when selecting a taxonomy term to attach to a resource', // @translate
                'value_options' => [
                    'list' => 'List view', // @translate
                    'tree' => 'Tree view', // @translate
                ],
            ],
            'attributes' => [
                'id' => 'taxonomy_sidebar_default_view',
                'value' => $userId ? $userSettings->get('taxonomy_sidebar_default_view', 'list', $userId) : 'list',
            ],
        ]);
    }

    public function onUserFormAddInputFilters(Event $event)
    {
        $inputFilter = $event->getParam('inputFilter');

        $inputFilter->get('user-settings')->add([
            'name' => 'taxonomy_sidebar_default_view',
            'required' => false,
        ]);
    }
}

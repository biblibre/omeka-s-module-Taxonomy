<?php

namespace Taxonomy;

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

        $api = $services->get('Omeka\ApiManager');
        $dataTypeManager = $services->get('Omeka\DataTypeManager');

        $taxonomies = $api->search('taxonomies')->getContent();
        foreach ($taxonomies as $taxonomy) {
            $dataTypeName = sprintf('resource:taxonomy-term:%s', $taxonomy->code());
            $dataTypeManager->setFactory($dataTypeName, Service\DataType\ResourceTaxonomyTermFactory::class);
        }
    }

    public function install(ServiceLocatorInterface $serviceLocator)
    {
        $conn = $serviceLocator->get('Omeka\Connection');

        $conn->exec(<<<SQL
            CREATE TABLE taxonomy_term (
                id INT NOT NULL,
                taxonomy_id INT NOT NULL,
                code VARCHAR(255) NOT NULL,
                INDEX IDX_C7ED653A9557E6F6 (taxonomy_id),
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
    }

    public function getConfig()
    {
        return require __DIR__ . '/config/module.config.php';
    }

    public function onViewLayout(Event $event)
    {
        $view = $event->getTarget();
        if ($view->params()->fromRoute('__ADMIN__')) {
            $view->headLink()->appendStylesheet($view->assetUrl('css/taxonomy.css', 'Taxonomy'));
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
}

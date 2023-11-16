<?php

namespace Taxonomy\SearchFormElement;

use Laminas\Form\FormElementManager;
use Laminas\View\Renderer\PhpRenderer;
use Omeka\Api\Manager as ApiManager;
use Search\Api\Representation\SearchPageRepresentation;
use Search\FormElement\SearchFormElementInterface;
use Search\Query;
use Taxonomy\Form\Element\TaxonomySelect;

class TaxonomyTermSelect implements SearchFormElementInterface
{
    protected $formElementManager;

    protected $api;

    public function __construct(FormElementManager $formElementManager, ApiManager $api)
    {
        $this->formElementManager = $formElementManager;
        $this->api = $api;
    }

    public function getLabel(): string
    {
        return 'Taxonomy term'; // @translate
    }

    public function getConfigForm(SearchPageRepresentation $searchPage, PhpRenderer $view, array $formElementData): string
    {
        $labelInput = new \Laminas\Form\Element\Text('label');
        $labelInput->setLabel('Label'); // @translate
        $labelInput->setValue($formElementData['label'] ?? '');
        $labelInput->setAttribute('data-field-data-key', 'label');
        $labelInput->setAttribute('required', true);

        $availableFacetFields = $searchPage->index()->availableFacetFields();
        $fieldNameSelect = new \Laminas\Form\Element\Select('field_name');
        $fieldNameSelect->setLabel('Field'); // @translate
        $fieldNameSelect->setOption('info', 'Field to use for filtering. It should be a facet field.'); // @translate
        $fieldNameSelect->setValueOptions(array_column($availableFacetFields, 'label', 'name'));
        $fieldNameSelect->setValue($formElementData['field_name'] ?? '');
        $fieldNameSelect->setAttribute('data-field-data-key', 'field_name');
        $fieldNameSelect->setAttribute('required', true);

        $taxonomySelect = $this->formElementManager->get(TaxonomySelect::class);
        $taxonomySelect->setName('taxonomy_id');
        $taxonomySelect->setLabel('Taxonomy'); // @translate
        $taxonomySelect->setValue($formElementData['taxonomy_id'] ?? '');
        $taxonomySelect->setAttribute('data-field-data-key', 'taxonomy_id');
        $taxonomySelect->setAttribute('required', true);

        $configForm = $view->formRow($labelInput);
        $configForm .= $view->formRow($fieldNameSelect);
        $configForm .= $view->formRow($taxonomySelect);

        return $configForm;
    }

    public function isRepeatable(): bool
    {
        return true;
    }

    public function getForm(SearchPageRepresentation $searchPage, PhpRenderer $view, array $data, array $formElementData): string
    {
        $name = $this->getParamName($formElementData);
        $select = $this->formElementManager->get(\Taxonomy\Form\Element\TaxonomyTermSelect::class);
        $select->setName($name);
        $select->setLabel($formElementData['label']);
        $select->setValue($data[$name] ?? '');
        $select->setEmptyOption('');
        $select->setOption('taxonomy_id', $formElementData['taxonomy_id']);
        $select->setAttribute('multiple', true);

        if (!empty($data[$name])) {
            $taxonomyTerms = $this->api->search('taxonomy_terms', ['id' => $data[$name]])->getContent();
            $valueOptions = array_map(fn ($term) => ['value' => $term->id(), 'label' => $term->title()], $taxonomyTerms);
            $select->setValueOptions($valueOptions);
            $select->setValue($data[$name]);
        }

        return $view->formRow($select);
    }

    public function applyToQuery(Query $query, array $data, array $formElementData): void
    {
        $name = $this->getParamName($formElementData);

        if (!empty($data[$name])) {
            $query->addFacetFilter($formElementData['field_name'], $data[$name]);
        }
    }

    protected function getParamName(array $formElementData): string
    {
        return 'taxonomy_term_' . $formElementData['field_name'] . '_' . $formElementData['taxonomy_id'];
    }
}

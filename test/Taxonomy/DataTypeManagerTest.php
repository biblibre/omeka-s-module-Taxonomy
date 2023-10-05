<?php

namespace Taxonomy\Test;

class DataTypeManagerTest extends TestCase
{
    protected $taxonomy;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = $this->api()->create('taxonomies', ['o:code' => 'DATA_TYPE_TEST'])->getContent();
        $this->application = null;
    }

    public function tearDown(): void
    {
        $this->api()->delete('taxonomies', $this->taxonomy->id());
    }

    public function testDataTypeManagerHasTaxonomyTermType()
    {
        $dataTypeManager = $this->getServiceLocator()->get('Omeka\DataTypeManager');
        $registeredNames = $dataTypeManager->getRegisteredNames();
        $this->assertContains('resource:taxonomy-term:DATA_TYPE_TEST', $registeredNames);
    }
}

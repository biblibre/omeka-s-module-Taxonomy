<?php

namespace Taxonomy\Test\Controller\Admin;

use Taxonomy\Test\TestCase;

class TaxonomyTermControllerTest extends TestCase
{
    protected $taxonomy;
    protected $taxonomyTerm;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = $this->api()->create('taxonomies', ['o:code' => 'CONTROLLER_TEST'])->getContent();
        $this->taxonomyTerm = $this->api()->create('taxonomy_terms', ['o:taxonomy' => ['o:id' => $this->taxonomy->id()], 'o:code' => 'CONTROLLER_TEST'])->getContent();
    }

    public function tearDown(): void
    {
        $this->api()->delete('taxonomy_terms', $this->taxonomyTerm->id());
        $this->api()->delete('taxonomies', $this->taxonomy->id());
    }

    public function testShowAction()
    {
        $this->dispatch($this->taxonomyTerm->adminUrl());
        $this->assertResponseStatusCode(200);
    }
}

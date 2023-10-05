<?php

namespace Taxonomy\Test\Controller\Admin;

use Taxonomy\Test\TestCase;

class TaxonomyControllerTest extends TestCase
{
    protected $taxonomy;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = $this->api()->create('taxonomies', ['o:code' => 'CONTROLLER_TEST'])->getContent();
    }

    public function tearDown(): void
    {
        $this->api()->delete('taxonomies', $this->taxonomy->id());
    }

    public function testShowAction()
    {
        $this->dispatch($this->taxonomy->adminUrl());
        $this->assertResponseStatusCode(200);
    }
}

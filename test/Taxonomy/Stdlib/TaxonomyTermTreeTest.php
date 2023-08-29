<?php

namespace Taxonomy\Test\Stdlib;

use Taxonomy\Test\TestCase;

class TaxonomyTermTreeTest extends TestCase
{
    protected $taxonomyTermTree;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomyTermTree = $this->getServiceLocator()->get('Taxonomy\TaxonomyTermTree');
    }

    public function testBuildtaxonomyTermTree()
    {
        $taxonomy = $this->api()->create('taxonomies', ['o:code' => 'TYPE'])->getContent();
        $physicalTerm = $this->api()->create('taxonomy_terms', ['o:code' => 'PHYSICAL', 'o:taxonomy' => ['o:id' => $taxonomy->id()]])->getContent();
        $bookTerm = $this->api()->create('taxonomy_terms', ['o:code' => 'BOOK', 'o:taxonomy' => ['o:id' => $taxonomy->id()], 'o:parent' => $physicalTerm->id()])->getContent();
        $dvdTerm = $this->api()->create('taxonomy_terms', ['o:code' => 'DVD', 'o:taxonomy' => ['o:id' => $taxonomy->id()], 'o:parent' => $physicalTerm->id()])->getContent();
        $digitalTerm = $this->api()->create('taxonomy_terms', ['o:code' => 'DIGITAL', 'o:taxonomy' => ['o:id' => $taxonomy->id()]])->getContent();

        $rootNodes = $this->taxonomyTermTree->buildTaxonomyTermTree($taxonomy->id());
        $this->assertCount(2, $rootNodes);
        $this->assertEquals('PHYSICAL', $rootNodes[0]['term']->code());
        $this->assertEquals('DIGITAL', $rootNodes[1]['term']->code());
        $this->assertNull($rootNodes[0]['parent']);
        $this->assertNull($rootNodes[1]['parent']);

        $this->assertCount(2, $rootNodes[0]['children']);
        $this->assertEquals('BOOK', $rootNodes[0]['children'][0]['term']->code());
        $this->assertEquals('PHYSICAL', $rootNodes[0]['children'][0]['parent']['term']->code());
        $this->assertEquals('DVD', $rootNodes[0]['children'][1]['term']->code());
        $this->assertEquals('PHYSICAL', $rootNodes[0]['children'][1]['parent']['term']->code());
    }
}

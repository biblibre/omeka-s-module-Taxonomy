<?php

namespace Taxonomy\Test\Api\Representation;

use Taxonomy\Test\TestCase;

class TaxonomyTermRepresentationTest extends TestCase
{
    protected $taxonomy;
    protected $taxonomyTerm;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = $this->api()->create('taxonomies', ['o:code' => 'REPRESENTATION_TEST'])->getContent();
        $this->taxonomyTerm = $this->api()->create('taxonomy_terms', ['o:taxonomy' => ['o:id' => $this->taxonomy->id()], 'o:code' => 'REPRESENTATION_TEST'])->getContent();
    }

    public function tearDown(): void
    {
        $this->api()->delete('taxonomy_terms', $this->taxonomyTerm->id());
        $this->api()->delete('taxonomies', $this->taxonomy->id());
    }

    public function testSubjectValueTotalCountDoesNotThrow()
    {
        $count = $this->taxonomyTerm->subjectValueTotalCount();
        $this->assertEquals(0, $count);
    }
}

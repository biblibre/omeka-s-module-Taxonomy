<?php

namespace Taxonomy\Test\Api\Representation;

use Taxonomy\Test\TestCase;

class TaxonomyRepresentationTest extends TestCase
{
    protected $taxonomy;

    public function setUp(): void
    {
        parent::setUp();

        $this->taxonomy = $this->api()->create('taxonomies', ['o:code' => 'REPRESENTATION_TEST'])->getContent();
    }

    public function tearDown(): void
    {
        $this->api()->delete('taxonomies', $this->taxonomy->id());
    }

    public function testSubjectValueTotalCountDoesNotThrow()
    {
        $count = $this->taxonomy->subjectValueTotalCount();
        $this->assertEquals(0, $count);
    }
}

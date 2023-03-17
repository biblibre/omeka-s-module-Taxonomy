<?php

namespace Taxonomy\Test;

class ApiTest extends TestCase
{
    public function testCreateTaxonomyEmpty()
    {
        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        $response = $this->api()->create('taxonomies', []);
    }

    public function testCreateTaxonomyWithCode()
    {
        $response = $this->api()->create('taxonomies', ['o:code' => 'LANG']);
        $taxonomy = $response->getContent();

        $this->assertEquals('LANG', $taxonomy->code());

        $this->api()->delete('taxonomies', $taxonomy->id());
    }

    public function testCreateTaxonomiesWithSameCode()
    {
        $taxonomy = $this->api()->create('taxonomies', ['o:code' => 'LANG2'])->getContent();

        try {
            $this->api()->create('taxonomies', ['o:code' => 'LANG2']);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class, $e);
        } finally {
            $em = $this->getEntityManager();
            $em = $em->create($em->getConnection(), $em->getConfiguration(), $em->getEventManager());
            $taxonomyEntity = $em->find('Taxonomy\Entity\Taxonomy', $taxonomy->id());
            $em->remove($taxonomyEntity);
            $em->flush();
        }
    }

    public function testCreateTaxonomyTermEmpty()
    {
        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        $response = $this->api()->create('taxonomy_terms', []);
    }

    public function testCreateTaxonomyTermWithCode()
    {
        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        $response = $this->api()->create('taxonomy_terms', ['o:code' => 'eng']);
    }

    public function testCreateTaxonomyTermWithCodeAndTaxonomyId()
    {
        $taxonomy = $this->api()->create('taxonomies', ['o:code' => 'LANG'])->getContent();
        $response = $this->api()->create('taxonomy_terms', ['o:code' => 'eng', 'o:taxonomy' => ['o:id' => $taxonomy->id()]]);
        $term = $response->getContent();

        $this->assertEquals('eng', $term->code());

        $this->api()->delete('taxonomy_terms', $term->id());
        $this->api()->delete('taxonomies', $taxonomy->id());
    }

    public function testCreateTaxonomyTermsWithSameCode()
    {
        $taxonomy = $this->api()->create('taxonomies', ['o:code' => 'LANG'])->getContent();
        $term = $this->api()->create('taxonomy_terms', ['o:code' => 'eng', 'o:taxonomy' => ['o:id' => $taxonomy->id()]])->getContent();

        try {
            $this->api()->create('taxonomy_terms', ['o:code' => 'eng', 'o:taxonomy' => ['o:id' => $taxonomy->id()]]);
            $this->fail();
        } catch (\Exception $e) {
            $this->assertInstanceOf(\Doctrine\DBAL\Exception\UniqueConstraintViolationException::class, $e);
        } finally {
            $em = $this->getEntityManager();
            $em = $em->create($em->getConnection(), $em->getConfiguration(), $em->getEventManager());
            $taxonomyEntity = $em->find('Taxonomy\Entity\Taxonomy', $taxonomy->id());
            $em->remove($taxonomyEntity);
            $em->flush();
        }
    }

    public function testBatchReplace()
    {
        [$titleProperty] = $this->api()->search('properties', ['term' => 'dcterms:title'])->getContent();
        [$languageProperty] = $this->api()->search('properties', ['term' => 'dcterms:language'])->getContent();
        $taxonomy = $this->api()->create('taxonomies', ['o:code' => 'LANG'])->getContent();
        $term = $this->api()->create('taxonomy_terms', [
            'o:code' => 'eng',
            'o:taxonomy' => [
                'o:id' => $taxonomy->id()
            ],
            'dcterms:title' => [
                [
                    'property_id' => $titleProperty->id(),
                    'type' => 'literal',
                    '@value' => 'English',
                ],
            ],
        ])->getContent();
        $item = $this->api()->create('items', [
            'dcterms:language' => [
                [
                    'property_id' => $languageProperty->id(),
                    'type' => 'literal',
                    '@value' => 'English',
                ],
            ],
        ])->getContent();

        $data = [
            'taxonomy' => [
                'replace_property_ids' => [$languageProperty->id()],
                'replace_taxonomy_id' => $taxonomy->id(),
            ],
        ];
        $this->api()->batchUpdate('items', [$item->id()], $data, ['detachEntities' => false]);

        $updatedItem = $this->api()->read('items', $item->id())->getContent();
        $languageValue = $updatedItem->value('dcterms:language');

        // Should be resource:taxonomy-term:LANG but LANG taxonomy was created
        // after module's bootstrap, so the data type manager doesn't know
        // about it yet, and it defaults to resource
        $this->assertEquals('resource', $languageValue->type());
        $this->assertEquals($term->id(), $languageValue->valueResource()->id());
    }
}

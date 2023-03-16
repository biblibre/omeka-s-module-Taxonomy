<?php
namespace Taxonomy\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractResourceEntityAdapter;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use Taxonomy\Api\Representation\TaxonomyTermRepresentation;
use Taxonomy\Entity\TaxonomyTerm;

class TaxonomyTermAdapter extends AbstractResourceEntityAdapter
{
    protected $sortFields = [
        'id' => 'id',
        'code' => 'code',
        'created' => 'created',
        'modified' => 'modified',
        'title' => 'title',
    ];

    protected $scalarFields = [
        'id' => 'id',
        'code' => 'code',
        'title' => 'title',
        'created' => 'created',
        'modified' => 'modified',
        'is_public' => 'isPublic',
        'thumbnail' => 'thumbnail',
    ];

    public function getResourceName()
    {
        return 'taxonomy_terms';
    }

    public function getRepresentationClass()
    {
        return TaxonomyTermRepresentation::class;
    }

    public function getEntityClass()
    {
        return TaxonomyTerm::class;
    }

    public function buildQuery(QueryBuilder $qb, array $query)
    {
        parent::buildQuery($qb, $query);

        if (isset($query['code'])) {
            $qb->andWhere($qb->expr()->eq(
                'omeka_root.code',
                $this->createNamedParameter($qb, $query['code'])
            ));
        }

        if (isset($query['taxonomy_id']) && is_numeric($query['taxonomy_id'])) {
            $taxonomyAlias = $this->createAlias();
            $qb->innerJoin(
                'omeka_root.taxonomy',
                $taxonomyAlias
            );
            $qb->andWhere($qb->expr()->eq(
                "$taxonomyAlias.id",
                $this->createNamedParameter($qb, $query['taxonomy_id']))
            );
        }
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        parent::hydrate($request, $entity, $errorStore);

        $data = $request->getContent();

        if (Request::CREATE === $request->getOperation() && isset($data['o:code'])) {
            $entity->setCode($data['o:code']);
        }

        if (Request::CREATE === $request->getOperation() && isset($data['o:taxonomy']['o:id'])) {
            $taxonomy = $this->getAdapter('taxonomies')->findEntity($data['o:taxonomy']['o:id']);
            $this->authorize($taxonomy, 'add-term');
            $entity->setTaxonomy($taxonomy);
        }
    }
}

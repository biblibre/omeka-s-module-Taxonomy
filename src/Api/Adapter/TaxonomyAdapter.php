<?php
namespace Taxonomy\Api\Adapter;

use Doctrine\ORM\QueryBuilder;
use Omeka\Api\Adapter\AbstractResourceEntityAdapter;
use Omeka\Api\Exception;
use Omeka\Api\Request;
use Omeka\Entity\EntityInterface;
use Omeka\Stdlib\ErrorStore;
use Taxonomy\Api\Representation\TaxonomyRepresentation;
use Taxonomy\Entity\Taxonomy;

class TaxonomyAdapter extends AbstractResourceEntityAdapter
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
        return 'taxonomies';
    }

    public function getRepresentationClass()
    {
        return TaxonomyRepresentation::class;
    }

    public function getEntityClass()
    {
        return Taxonomy::class;
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
    }

    public function hydrate(Request $request, EntityInterface $entity,
        ErrorStore $errorStore
    ) {
        parent::hydrate($request, $entity, $errorStore);

        $data = $request->getContent();

        if (Request::CREATE === $request->getOperation() && isset($data['o:code'])) {
            $entity->setCode($data['o:code']);
        }
    }
}
